<?php
/**
 * Manager for cron process.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Manager for cron process.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Cron_Manager
{
    const DATA_PATH = 'data/';

    const STRATEGY_PATH = 'strategy/';

    /**
     * @var Zend_Log
     */
    private $_log;

    /**
     * @var Zend_Config
     */
    private $_config;

    private $_strategyDir;

    private $_configFile;

    private $_forked = 0;

    /**
     * Instance a new manager.
     *
     * @param string $configFile The path to the config file.
     *
     * @return void
     */
    public function __construct($configFile)
    {
        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'errorHandler'));

        $this->_configFile = $configFile;
        $this->_loadConfig($configFile);
        $this->_init();
    }

    /**
     * Init the manager.
     *
     * @return void
     */
    private function _init()
    {
        $this->_initLog();

        $dataDir = $this->_config->data->dir ?
                $this->_config->data->dir.'/' : self::DATA_PATH;
        ZendExt_Cron_Persistance::setDataDirectory($dataDir);

        $this->_strategyDir = $this->_config->manager->strategyDir ?
            $this->_config->manager->strategyDir : self::STRATEGY_PATH;

        if ( $this->_config->manager->strategyNamespace ) {

            Zend_Loader_Autoloader::getInstance()->registerNamespace(
                $this->_config->manager->strategyNamespace
            );
        }
    }

    /**
     * Init the logger.
     *
     * @return void
     */
    private function _initLog()
    {
        $logConfig = $this->_config->manager->log;
        if (!$logConfig) {

            //We dont have any config, so just do something default
            $writer = new Zend_Log_Writer_Stream('php://stdout');

            $this->_log = new Zend_Log();
            $this->_log->addWriter($writer);
            return;
        }

        if (!file_exists($logConfig->path)) {

            mkdir($logConfig->path, 0744, true);
        }

        $this->_log = new Zend_Log();

        if ($logConfig->mail) {

            if ($logConfig->mail->transport == 'smtp') {

                $transport = new Zend_Mail_Transport_Smtp(
                    $logConfig->mail->host,
                    $logConfig->mail->config->toArray()
                );
            } else {

                $transport = new Zend_Mail_Transport_Sendmail();
            }

            Zend_Mail::setDefaultTransport($transport);

            $mail = new Zend_Mail('UTF-8');
            if (is_string($logConfig->mail->to)) {
                $to = $logConfig->mail->to;
            } else {
                $to = $logConfig->mail->to->toArray();
            }
            $mail->addTo($to);
            $mail->setFrom($logConfig->mail->from);
            $mail->setSubject($logConfig->mail->subject);

            $filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);

            $mailWriter = new Zend_Log_Writer_Mail($mail);
            $mailWriter->addFilter($filter);

            $this->_log->addWriter($mailWriter);
        }

        $streamWriter = new Zend_Log_Writer_Stream(
            $logConfig->path.'/'.$logConfig->file
        );
        $this->_log->addWriter($streamWriter);
    }

    /**
     * Fork & run a new process.
     *
     * @param string $strategy The name of the strategy to run.
     * @param array  $config   Config to override default strategy config.
     *
     * @return void
     */
    public function spawnProcess($strategy, array $config = array())
    {
        $this->_log->info('Spawning new process...');

        // This assumes the application entry point is the launcher
        // Which is, I hope, a fairly good guess.
        $exec = 'php -c '.get_cfg_var('cfg_file_path')
            .' -f '.$_SERVER['PHP_SELF']
            .' -- -c '.$this->_configFile.' ';

        foreach ($config as $key => $value) {
            $exec .= $key.'='.$value.' ';
        }
        $exec .= $strategy;

        $this->_forked++;
        $pid = pcntl_fork();
        if ($pid == 0) {

            exec($exec, $out, $ret);
            die($ret);
        } else if ( $pid == -1 ) {

            $log = 'Trying to execute new process failed!'.PHP_EOL
                .'Strategy: '.$strategy.PHP_EOL
                .'Exec: '.$exec.PHP_EOL;
            $this->_log->crit($log);
        }
    }

    /**
     * Wait for all children processes to end.
     *
     * @return void
     */
    private function _waitForChildren()
    {
        while ($this->_forked) {
            $ret = pcntl_wait($status);
            $this->_forked--;

            if ( $ret == -1 ) {

                $this->_log->crit(
                    'An error ocurred while waiting for the processes to end '
                );
                return;
            } else if ( $ret != 0 ) {

                $this->_log->info(
                    'Process '.$ret.' exited with status '.$status
                );
            }
        }
    }

    /**
     * Load a strategy.
     *
     * @param string $name The name of the strategy.
     *
     * @return void
     */
    private function _loadStrategy($name)
    {
        $loaded = Zend_Loader_Autoloader::autoload($name);

        if ( !$loaded ) {

            $strategyPath = $this->_strategyDir.'/'.$name.'.php';
            if ( !file_exists($strategyPath) ) {

                $this->_log->crit('The specified strategy was not found.');
                return;
            } else {

                require_once($strategyPath);
                if (!class_exists($name)) {

                    $this->_log->crit('The specified strategy was not found.');
                    return;
                }

            }
        }
    }

    /**
     * Spawn a new process.
     *
     * @param string $strategyName The name of the strategy for the process.
     * @param array  $extraConfig  Extra config to override the strategy config.
     *
     * @return void
     */
    public function process($strategyName, array $extraConfig = array())
    {
        $this->_loadStrategy($strategyName);
        ZendExt_Cron_Persistance::setCurrentProcess($strategyName);

        $cleanup = false;
        try {

            $strategy = new $strategyName();
            $strategy->setManager($this);

            $process = new ZendExt_Cron_Process(
                $strategy,
                $this->_config->process,
                $extraConfig
            );
            $process->execute();
            $cleanup = true;
        } catch ( ZendExt_Cron_LockException $e ) {

            $this->_log->crit($e->getMessage());
        } catch ( ZendExt_Cron_ErrorException $e ) {

            $this->_log->crit($e->getMessage());
        } catch ( Exception $e ) {

            $this->_log->crit($e->__toString());
        }

        $this->_waitForChildren();
        if ($cleanup) {

            $process->cleanup();
        }
    }

    /**
     * Load the config file.
     *
     * @param string $configFile The path to the config file.
     *
     * @return void
     */
    private function _loadConfig($configFile)
    {
        if ( !$configFile ) {

            $configFile = self::CONFIG_FILE;
        }

        if (!file_exists($configFile)) {
            error_log('Cant find the config file!! Crashing hard.');
            die(1);
        }

        try {

            $this->_config = new Zend_Config_Xml($configFile);
        } catch ( Zend_Config_Exception $e ) {

            error_log('Config file parsing failed, crashing hard.');
            error_log($e->__toString());

            die(1);
        }
    }

    /**
     * Custom error handler for errors triggered by PHP itself.
     *
     * @param integer $errno   The code of the error raised.
     * @param string  $errstr  The error message.
     * @param string  $errfile The file in which the error was found.
     * @param integer $errline The line at which the error was found.
     * @param array   $errcont The active symbol table when the error occurred.
     *
     * @return boolean True if the error was handled, false if the default
     *                      error handler should process it.
     */
    public function errorHandler($errno, $errstr, $errfile, $errline,
        array $errcont)
    {

        if ( $errno == E_STRICT ) {

            return false;
        }

        $output =
            'An error ocurred:'.PHP_EOL.
            $errstr.PHP_EOL.
            'Error on line '.$errline.' in file '.$errfile;

        if ( $errno == E_USER_ERROR ) {

            $level = 'crit';

        } else {

            $level = 'err';
        }

        if ($this->_log) {

            try {
                $this->_log->$level($output);
            } catch (Exception $e) {
                error_log($output);
                error_log($e->__toString());
            }
        } else {

            error_log($output);
        }

        return false;
    }

    /**
     * Custom shutdown handler.
     *
     * This is called by PHP when the script is being shutdown,
     * it checks for errors and logs accordingly.
     *
     * @return void
     */
    public function shutdownHandler()
    {

        $error = error_get_last();
        error_log($error['message']);
    }

    /**
     * Run a number of strategies.
     *
     * @param string|array $strategy   The strategies to run.
     * @param string       $configFile The path to the config file.
     * @param array        $extra      Override default strategy config.
     *
     * @return void
     */
    public static function run($strategy, $configFile, array $extra = array())
    {
        $manager = new ZendExt_Cron_Manager($configFile);
        if (!is_array($strategy)) {

            $strategy = array($strategy);
        }

        $last = array_shift($strategy);
        foreach ($strategy as $name) {

            $manager->spawnProcess($name, $extra);
        }

        $manager->process($last, $extra);
    }
}
