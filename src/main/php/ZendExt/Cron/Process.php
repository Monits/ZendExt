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
                                          'logDir' => 'log/'
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

    /**
     * Creates a new offline process.
     *
     * @param ZendExt_Cron_Strategy_Interface $strategy The strategy to apply during execution.
     * @param Zend_Config                     $config   The config data to use.
     */
    public function __construct(ZendExt_Cron_Strategy_Interface $strategy, Zend_Config $config)
    {

        $this->_strategy = $strategy;

        $this->_loadConfig($config);
        $this->_setupLogger();
    }

    /**
     * Load and set the config for the process.
     *
     * @param Zend_Config $config The config data to use.
     *
     * @return void
     */
    private function _loadConfig($config)
    {
        $this->_config = new Zend_Config(self::$_defaultOptions, true);
        $this->_config->merge($config);

        $strategyReflector = new ReflectionClass($this->_strategy);
        $fileName = $strategyReflector->getConstant('CONFIG_FILE');

        $configFile = $this->_config->configDir.'/'.$fileName.'.xml';
        $this->_config->merge(new Zend_Config_Xml($configFile, 'process'));
    }

    /**
     * Init everything needed for execution.
     *
     * @return void
     */
    private function _init()
    {

        $this->_logger->info('Initializing process...');

        file_put_contents($this->_config->pidFile, getmypid());
        $this->_strategy->init($this->_config->strategy);

        $this->_allowsProgress = $this->_strategy instanceOf ZendExt_Cron_Strategy_MeasurableInterface;
    }

    /**
     * Setup logging utilities.
     *
     * @return void
     */
    private function _setupLogger()
    {

        $logConfig = $this->_config->log;
        if (!file_exists($this->_config->logDir)) {

            mkdir($this->_config->logDir, 0744, true);
        }

        $this->_logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream($this->_config->logDir.'/'.$this->_config->logFile);

        $mail = new Zend_Mail();

        $to = $logConfig->recipients->toArray();
        $mail->addTo($to['mail']);
        $mail->setSubject($logConfig->subject);
        $mail->setFrom($logConfig->from);

    //    $writer2 = new Zend_Log_Writer_Mail($mail);
      //  $writer2->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));

        $this->_logger->addWriter($writer);
        //$this->_logger->addWriter($writer2);

        ZendExt_Cron_Log::setProcessLog($this->_logger);
    }

    /**
     * Perform cleanup after the process is done.
     *
     * @return void
     */
    private function _cleanup()
    {

        $this->_logger->info('Cleaning up...');

        $this->_strategy->shutdown();
        unlink($this->_config->pidFile);
    }

    /**
     * Execute the process.
     *
     * @return void
     *
     * @throws ZendExt_Cron_LockException  Another process is locking execution.
     * @throws ZendExt_Cron_ErrorException The proces has failed due to an internal error.
     */
    public function execute()
    {

        if ( file_exists($this->_config->pidFile) ) {

            $msg = 'A lock file was found when trying to execute a process with pid '.getmypid().'.';

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

            throw new ZendExt_Cron_ErrorException('An unexpected error caused the process to stop running.');
        }

        $info = 'Total execution time:'.($end - $start).' with '.($x * $this->_config->sleepTime).'s spent sleeping';
        $this->_logger->info($info);
        $this->_logger->info('Peak memory usage during execution: '.memory_get_peak_usage().' bytes');

        $this->_cleanup();
    }

    /**
     * Force a cleanup. To be used only when the process crashed.
     *
     * @return void
     */
    public function forceCleanup()
    {

        $this->_logger->info('Something happend and cleanup was forced.');
        $this->_cleanup();
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

            $stats .= 'Progress: '.round($progress, 2).'% ETA '.round($eta, 2).PHP_EOL;
        }

        $memoryUse = memory_get_usage();
        // TODO : Complete this!
        $cpuUse = 0;

        $stats .= 'CPU use: '.$cpuUse.'%'.PHP_EOL;
        $stats .= 'Memory usage: '.$memoryUse.' bytes'.PHP_EOL;

        file_put_contents($this->_config->outputFile, $stats);
    }

}
