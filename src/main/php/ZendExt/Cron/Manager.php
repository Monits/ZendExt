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
final class ZendExt_Cron_Manager
{
    const DATA_PATH = 'data/';

    const PROCESS_PATH = 'process/';

    private static $_defaultOptions = array(
        'manager' => array(
        )
    );

    /**
     * @var Zend_Log
     */
    private $_logger;

    /**
     * @var Zend_Config
     */
    private $_config;

    private $_processDir;

    private $_configFile;

    private $_forked = array();

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

        $this->_processDir = $this->_config->manager->processDir ?
            $this->_config->manager->processDir : self::PROCESS_PATH;

        if ( $this->_config->manager->processNamespace ) {

            Zend_Loader_Autoloader::getInstance()->registerNamespace(
                $this->_config->manager->processNamespace
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

            $this->_logger = new Zend_Log();
            $this->_logger->addWriter($writer);
            return;
        }

        $logConfig = $logConfig->toArray();
        if ($this->_config->manager->logPath) {

            $path = $this->_config->manager->logPath;
            foreach ($logConfig as $key => $value) {

                if (isset($value['writerName'])
                    && $value['writerName'] == 'Stream'
                    && isset($value['writerParams']['stream'])
                    && !is_resource($value['writerParams']['stream'])) {

                    $logConfig[$key]['writerParams']['stream'] = $path.'/'.
                        $value['writerParams']['stream'];
                }
            }

            if (!is_dir($path)) {

                mkdir($path, 0744, true);
            }
        }

        $this->_logger = Zend_Log::factory($logConfig);
    }

    /**
     * Fork & run a new process.
     *
     * @param string $process The name of the process to run.
     * @param array  $config  Config to override default process config.
     * @param string $name    Optional. Set a name for the process. IF no name
     *                        is set, the pid is used as name.
     *
     * @return integer|boolean The pid of the new process. False if it failed.
     */
    public function spawnProcess($process, array $config = array(),
                                $name = null)
    {
        $this->_log('Spawning new process...');

        // This assumes the application entry point is the launcher
        // Which is, I hope, a fairly good guess.
        $exec = 'php -c '.get_cfg_var('cfg_file_path')
            .' -f '.$_SERVER['PHP_SELF']
            .' -- -c '.$this->_configFile.' ';

        foreach ($config as $key => $value) {
            $exec .= $key.'='.$value.' ';
        }
        $exec .= $process;

        $pid = pcntl_fork();
        if ($pid == 0) {

            exec($exec, $out, $ret);
            die($ret);
        } else if ( $pid == -1 ) {

            $log = 'Trying to execute new process failed!'.PHP_EOL
                .'Strategy: '.$process.PHP_EOL
                .'Exec: '.$exec.PHP_EOL;
            $this->_log($log, 'crit');
            return false;
        } else {

            if (null === $name) {

                $name = $pid;
            }
            $this->_forked[$name] = $pid;
            return $pid;
        }
    }

    /**
     * Wait for all children processes to end.
     *
     * @return void
     */
    public function waitForChildren()
    {
        foreach ($this->_forked as $name => $pid) {

            $this->waitForChild($name);
        }
    }

    /**
     * Wait for a given child process to end.
     *
     * @param string $name The name of the process to wait for.
     *
     * @return boolean True on success, false if it exited with an error.
     */
    public function waitForChild($name)
    {
        if (isset($this->_forked[$name])) {

            $ret = pcntl_waitpid($this->_forked[$name], $status);
            unset($this->_forked[$name]);

            if ($ret == -1) {

                $this->_log(
                    'An error ocurred while waiting for the processes to end',
                    'crit'
                );

                return false;
            } else if ($ret != 0) {

                $this->_log(
                    'Process '.$ret.' exited with status '.$status
                );
            }
        }

        return true;
    }

    /**
     * Load a process.
     *
     * @param string $name The name of the process.
     *
     * @return void
     *
     * @throws Exception
     */
    private function _loadStrategy($name)
    {
        $loaded = Zend_Loader_Autoloader::autoload($name);

        if ( !$loaded ) {

            $processPath = $this->_processDir.'/'.$name.'.php';
            if ( !file_exists($processPath) ) {

                $err = 'The specified process was not found.';
                $this->_log($err, 'crit');
                throw new Exception($err);
            } else {

                require_once($processPath);
                if (!class_exists($name)) {

                    $err = 'The specified process was not found.';
                    $this->_log($err, 'crit');
                    throw new Exception($err);
                }

            }
        }
    }

    /**
     * Spawn a new process.
     *
     * @param string $processName The name of the process for the process.
     * @param array  $extraConfig Extra config to override the process config.
     *
     * @return void
     */
    public function process($processName, array $extraConfig = array())
    {
        $this->_loadStrategy($processName);

        try {

            $process = new $processName(
                $this,
                $this->_config->process,
                $extraConfig
            );
            $process->execute();
        } catch ( ZendExt_Cron_LockException $e ) {

            $this->_log($e->getMessage(), 'crit');
        } catch ( ZendExt_Cron_ErrorException $e ) {

            $this->_log($e->getMessage(), 'crit');
        } catch ( Exception $e ) {

            $this->_log($e->__toString(), 'crit');
        }

        $this->waitForChildren();
        if (isset($process)) {
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
        $this->_config = new Zend_Config(self::$_defaultOptions, true);

        if (!$configFile) {
            $configFile = self::CONFIG_FILE;
        }

        if (!file_exists($configFile)) {
            $this->_log('Cant find the config file!! Crashing hard.', 'warn');
        } else {

            try {
                $config = new Zend_Config_Xml($configFile);
            } catch ( Zend_Config_Exception $e ) {

                $this->_log(
                    'Config file parsing failed, crashing hard.', 'warn'
                );
                $this->_log($e->__toString(), 'crit');

                die(1);
            }

            $this->_config->merge($config);
        }
    }

    /**
     * Log a message.
     *
     * @param string $output The output to log.
     * @param string $level  The output level.
     *
     * @return void
     */
    protected function _log($output, $level = 'info')
    {
        if ($this->_logger) {

            try {
                $this->_logger->$level($output);
            } catch (Exception $e) {
                error_log($output);
                error_log($e->__toString());
            }
        } else {

            error_log($output);
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

        $this->_log($output, $level);
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
        if ($error) {
            error_log($error['message']);
        }
    }

    /**
     * Run a number of strategies.
     *
     * @param string|array $process    The strategies to run.
     * @param string       $configFile The path to the config file.
     * @param array        $extra      Override default process config.
     *
     * @return void
     */
    public static function run($process, $configFile, array $extra = array())
    {
        $manager = new ZendExt_Cron_Manager($configFile);
        if (!is_array($process)) {

            $process = array($process);
        }

        $last = array_shift($process);
        foreach ($process as $name) {

            $manager->spawnProcess($name, $extra);
        }

        $manager->process($last, $extra);
    }
}
