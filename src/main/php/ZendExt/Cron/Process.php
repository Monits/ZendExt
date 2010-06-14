<?php
/**
 * Main process for Cron tasks.
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
 * Main process for Cron tasks.
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
final class ZendExt_Cron_Process
{
    private static $_defaultOptions = array(
                                          'configDir' => 'config/strategies',
                                          'outputFile' => 'php://stdout',
                                          'log' => array(
                                              'path' => 'log/'
                                          ),
                                          'pid' => array(
                                              'path' => 'pid/'
                                          )
                                      );

    /**
     * The strategy being used.
     *
     * @var ZendExt_Cron_Strategy_Interface
     */
    private $_strategy;

    /**
     * The config being used.
     *
     * @var Zend_Config
     */
    private $_config;

    /**
     * The log being used.
     *
     * @var Zend_Log
     */
    private $_logger;

    private $_pidFile;

    /**
     * Creates a new offline process.
     *
     * @param ZendExt_Cron_Strategy_Interface $strategy The strategy to apply
     *                                                  during execution.
     * @param Zend_Config                     $config   The config data to use.
     * @param array                           $extra    Override config.
     */
    public function __construct(ZendExt_Cron_Strategy_Interface $strategy,
        Zend_Config $config, array $extra = array())
    {

        $this->_strategy = $strategy;

        $this->_loadConfig($config, $extra);
        $this->_setupLogger();

        $this->_pidFile = $this->_config->pid->path.'/'.$this->_config->pid->file;
    }

    /**
     * Load and set the config for the process.
     *
     * @param Zend_Config $config The config data to use.
     * @param array       $extra  Extra config to override defaults.
     *
     * @return void
     */
    private function _loadConfig($config, array $extra = array())
    {
        $this->_config = new Zend_Config(self::$_defaultOptions, true);
        $this->_config->merge($config);

        $strategyReflector = new ReflectionClass($this->_strategy);
        $fileName = $strategyReflector->getConstant('CONFIG_FILE');

        $configFile = $this->_config->configDir.'/'.$fileName.'.xml';
        $this->_config->merge(new Zend_Config_Xml($configFile, 'process'));

        $this->_config->merge(new Zend_Config($extra));
    }

    /**
     * Init everything needed for execution.
     *
     * @return void
     */
    private function _init()
    {
        $this->_logger->info('Initializing process...');
        $bootstrap = $this->_bootstrap();


        if (!file_exists($this->_config->pid->path)) {

            mkdir($this->_config->pid->path, 0744, true);
        }
        file_put_contents($this->_pidFile, getmypid());
        $this->_strategy->init($this->_config->strategy, $bootstrap);

        $this->_allowsProgress = $this->_strategy
            instanceOf ZendExt_Cron_Strategy_MeasurableInterface;
    }

    /**
     * Bootstrap whatever the strategy needs.
     *
     * @return Zend_Application_Bootstrap_BootstrapAbstract|NULL
     *
     * @throws ZendExt_Cron_ErrorException
     */
    private function _bootstrap()
    {
        $this->_logger->info('Bootstraping...');
        $appConfig = $this->_config->strategy->app;
        if ($appConfig) {

            try {
                $app = new Zend_Application(
                    'cron',
                    $appConfig
                );

                if ($appConfig->resources) {

                    $resources = $appConfig->resources->toArray();
                    reset($resources);
                    $resources = current($resources);
                } else {

                    $resources = null;
                }
                $app->bootstrap($resources);
            } catch (Exception $e) {

                $this->_logger->crit($e->__toString());
                throw new ZendExt_Cron_ErrorException(
                    'An unexpected error caused the process to stop running.'
                );

            }

            return $app->getBootstrap();
        }

        return null;
    }

    /**
     * Setup logging utilities.
     *
     * @return void
     */
    private function _setupLogger()
    {

        $logConfig = $this->_config->log;
        if (!file_exists($logConfig->path)) {

            mkdir($logConfig->path, 0744, true);
        }

        $this->_logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream(
            $logConfig->path.'/'.$logConfig->file
        );

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

            $this->_logger->addWriter($mailWriter);
        }

        $this->_logger->addWriter($writer);

        ZendExt_Cron_Log::setProcessLog($this->_logger);
    }

    /**
     * Perform cleanup after the process is done.
     *
     * @return void
     */
    public function cleanup()
    {

        $this->_logger->info('Cleaning up...');

        $this->_strategy->shutdown();

        unlink($this->_pidFile);
    }

    /**
     * Execute the process.
     *
     * @return void
     *
     * @throws ZendExt_Cron_LockException  Another process is locking execution.
     * @throws ZendExt_Cron_ErrorException The proces has failed due to an
     *                                     internal error.
     */
    public function execute()
    {
        if ( file_exists($this->_pidFile) ) {

            $strategyReflector = new ReflectionClass($this->_strategy);
            $msg = 'A lock file was found when trying to execute '
                .$strategyReflector->getName();

            $this->_logger->info($msg);
            throw new ZendExt_Cron_LockException($msg);
        }

        $this->_init();

        try {

            $x = 0;
            $start = microtime(true);
            while ( !$this->_strategy->isDone() ) {

                $this->_logger->info('Processing bulk #'.$x);
                $this->_strategy->processBulk();

                $this->showStats($start);

                $this->_logger->info('Bulk processing done, sleeping...');
                sleep($this->_config->sleepTime);

                $x++;
            }

            $end = microtime(true);

        } catch ( Exception $e ) {

            $this->_logger->crit($e->__toString());
            $this->forceCleanup();

            throw new ZendExt_Cron_ErrorException(
                'An unexpected error caused the process to stop running.'
            );
        }

        $info = 'Total execution time:'.($end - $start).' with '
            .($x * $this->_config->sleepTime).'s spent sleeping';
        $this->_logger->info($info);

        $info = 'Peak memory usage during execution: '
            .memory_get_peak_usage().' bytes';
        $this->_logger->info($info);
    }

    /**
     * Force a cleanup. To be used only when the process crashed.
     *
     * @return void
     */
    public function forceCleanup()
    {

        $this->_logger->info('Something happend and cleanup was forced.');
        $this->cleanup();
    }

    /**
     * Show the current execution staticts on screen.
     *
     * @param integer $startTime When the process started.
     *
     * @return void
     */
    public function showStats($startTime)
    {

        $stats = '';

        if ( $this->_allowsProgress ) {

            $delta = microtime(true) - $startTime + $this->_config->sleepTime;
            $total = $this->_strategy->getTotalRecords();
            $processed = $this->_strategy->getProcessedRecords();

            $eta = ( $total * ( $delta / $processed ) ) - $delta;
            $progress = ($processed / $total) * 100;

            $stats .= 'Progress: '.round($progress, 2).'% ETA '
                .round($eta, 2).PHP_EOL;
        }

        $memoryUse = memory_get_usage();
        // TODO : Complete this!
        $cpuUse = 0;

        $stats .= 'CPU use: '.$cpuUse.'%'.PHP_EOL;
        $stats .= 'Memory usage: '.$memoryUse.' bytes'.PHP_EOL;

        file_put_contents($this->_config->outputFile, $stats);
    }

}
