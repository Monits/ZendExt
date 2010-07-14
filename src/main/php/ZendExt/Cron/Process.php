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
abstract class ZendExt_Cron_Process
{
    private static $_defaultOptions = array(
                                          'configDir' => 'config/strategies',
                                          'outputFile' => 'php://stdout',
                                          'pid' => array(
                                              'path' => 'pid/'
                                          ),
                                          'strategy' => array(
                                              'stub' => 'bar'
                                          )
                                      );

    /**
     * The config being used.
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * The log being used.
     *
     * @var Zend_Log
     */
    protected $_logger;

    /**
     * @var ZendExt_Cron_Persistance
     */
    protected $_persistance;

    private $_pidFile = false;

    protected $_allowProgress = false;

    protected $_hasRun = false;

    /**
     * @var ZendExt_Cron_Manager
     */
    protected $_manager;

    /**
     * Creates a new offline process.
     *
     * @param ZendExt_Cron_Manager $manager The manager instance.
     * @param Zend_Config          $config  The config data to use.
     * @param array                $extra   Override config.
     *
     * @throws ZendExt_Cron_ErrorException
     */
    public final function __construct(ZendExt_Cron_Manager $manager,
        Zend_Config $config, array $extra = array())
    {
        $this->_manager = $manager;
        $this->_loadConfig($config, $extra);
        $this->_setupLogger();

        $this->_lock();

        $this->_logger->info('Initializing process...');
        $this->_bootstrap();

        if ($this->_config->data->dir) {

            $dataDir = $this->_config->data->dir.'/';
            $this->_persistance = new ZendExt_Cron_Persistance(
                get_class($this), $dataDir
            );
        }

        try {

            $this->_init();
        } catch (Exception $e) {

            $this->_logger->crit('An error ocurred during init!');
            $this->_logger->crit($e->__toString());

            $this->_unlock();
            throw new ZendExt_Cron_ErrorException(
                'An error ocurred during init. Execution aborted.'
            );
        }
    }

    /**
     * Get the path to the pid file.
     *
     * If this returns a falsy value, locking will be disabled.
     *
     * @return string
     */
    protected function _getPidFileName()
    {
        $configPath = $this->_config->pid->path;
        return $configPath.'/'.$this->_config->pid->file;
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

        $configFile = $this->_config->configDir.'/'
            .$this->_configFileName;

        $this->_config->merge(new Zend_Config_Xml($configFile, 'process'));
        $this->_config->merge(new Zend_Config($extra));
    }


    /**
     * Lock the process so that no other instance can run.
     *
     * @return void
     */
    private function _lock()
    {
        $this->_pidFile = $this->_getPidFileName();

        $this->_checkLock();
        if ($this->_pidFile) {

            $pidPath = dirname($this->_pidFile);
            if (!file_exists($pidPath)) {

                mkdir($pidPath, 0744, true);
            }

            file_put_contents($this->_pidFile, getmypid());
        }
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
        $appConfig = $this->_config->app;
        if ($appConfig) {

            try {
                $app = new Zend_Application(
                    'cron',
                    $appConfig
                );

                if ($appConfig->resources) {

                    $resources = array_keys($appConfig->resources->toArray());
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

            $this->_bootstrap = $app->getBootstrap();
        }
    }

    /**
     * Setup logging utilities.
     *
     * @return void
     */
    private function _setupLogger()
    {
        $logConfig = $this->_config->log;
        if (!$logConfig) {

            $writer = new Zend_Log_Writer_Stream('php://stdout');

            $this->_logger = new Zend_Log();
            $this->_logger->addWriter($writer);
            return;
        }

        $logConfig = $logConfig->toArray();
        if ($this->_config->logPath) {

            $path = $this->_config->logPath;
            foreach ($logConfig as $key => $value) {

                if (isset($value['writerName'])
                    && $value['writerName'] == 'Stream'
                    && isset($value['writerParams']['stream'])
                    && !is_resource($value['writerParams']['stream'])) {

                    $logConfig[$key]['writerParams']['stream'] = $path.'/'.
                        $value['writerParams']['stream'];
                }
            }

            if (!file_exists($path)) {

                mkdir($path, 0744, true);
            }
        }

        $this->_logger = Zend_Log::factory($logConfig);
    }

    /**
     * Destructor.
     *
     * @return void
     */
    public final function cleanup()
    {
        $this->_logger->info('Cleaning up...');

        try {

            $this->_shutdown();
        } catch (Exception $e) {

            $this->_logger->crit('An exception was thrown while cleaning up...');
            $this->_logger->crit($e->__toString());
        }

        $this->_unlock();
    }

    /**
     * Unlock the process to allow for new instances to be executed.
     *
     * @return void
     *
     * @throws ZendExt_Cron_ErrorException
     */
    private function _unlock()
    {
        if ($this->_pidFile) {

            if (!file_exists($this->_pidFile)) {

                $err = 'No lock file was found when trying to erase it!';
                $this->_logger->err($err);
                throw new ZendExt_Cron_ErrorException($err);
            }

            unlink($this->_pidFile);
        }
    }

    /**
     * Execute the process.
     *
     * @return void
     *
     * @throws ZendExt_Cron_ErrorException The proces has failed due to an
     *                                     internal error.
     */
    public function execute()
    {
        if ($this->_hasRun) {

            throw new ZendExt_Cron_ErrorException(
                'This instance has already been executed before'
            );
        }

        $this->_hasRun = true;
        $start = microtime(true);

        try {

            for ($bulk = 0; !$this->_isDone(); $bulk++) {

                $this->_logger->info('Processing bulk #'.$bulk);
                $this->_processBulk();

                $this->_showStats($start);

                $this->_logger->info('Bulk processing done, sleeping...');
                sleep($this->_config->sleepTime);
            }

            $end = microtime(true);

        } catch ( Exception $e ) {

            $this->_logger->crit($e->__toString());

            throw new ZendExt_Cron_ErrorException(
                'An unexpected error caused the process to stop running.'
            );
        }

        $info = 'Total execution time:'.($end - $start).' with '
            .($bulk * $this->_config->sleepTime).'s spent sleeping';
        $this->_logger->info($info);

        $info = 'Peak memory usage during execution: '
            .memory_get_peak_usage().' bytes';
        $this->_logger->info($info);
    }

    /**
     * Check whether we been locked out!
     *
     * @return void
     *
     * @throws ZendExt_Cron_LockException
     */
    private function _checkLock()
    {
        if ($this->_pidFile && file_exists($this->_pidFile)) {

            $msg = 'A lock file was found when trying to execute '
                .get_class($this);

            $this->_logger->info($msg);
            throw new ZendExt_Cron_LockException($msg);
        }
    }

    /**
     * Show the current execution staticts on screen.
     *
     * @param integer $startTime When the process started.
     *
     * @return void
     */
    private function _showStats($startTime)
    {
        $stats = '';

        $total = $this->_getTotalRecords();
        $processed = $this->_getProcessedRecords();

        if ($total && $processed) {

            $delta = microtime(true) - $startTime + $this->_config->sleepTime;

            $eta = ( $total * ( $delta / $processed ) ) - $delta;
            $progress = ($processed / $total) * 100;

            $stats .= 'Progress: '.round($progress, 2).'% ETA '
                .round($eta, 2).PHP_EOL;
        }

        $memoryUse = memory_get_usage();

        $cpuUse = 0;
        switch(PHP_OS) {

        case 'Linux':

            $res = file_get_contents('/proc/loadavg');
            $values = explode(' ', $res);

            if (count($values) > 0) {
                $cpuUse = $values[0];
            }
            break;

        case 'FreeBSD':
        case 'Darwin':
        case 'NetBSD':
        case 'OpenBSD':

            $res = exec('sysctl -n vm.loadavg');
            $values = explode(' ', $res);

            if (isset($values[1]) && is_numeric($values[1])) {
                $cpuUse = $values[1];
            }
            break;
        default:
            $cpuUse = 0;
            break;
        }

        $stats .= 'CPU use: '.$cpuUse.PHP_EOL;
        $stats .= 'Memory usage: '.$memoryUse.' bytes'.PHP_EOL;

        file_put_contents($this->_config->outputFile, $stats);
    }

    /** Everything from here on down is meant for overriding **/

    /**
     * Process a bulk of data.
     *
     * @return void
     */
    protected abstract function _processBulk();

    /**
     * Returns whether the strategy is done.
     *
     * @return boolean
     */
    protected abstract function _isDone();

    /**
     * Get the total number of records to be processed.
     *
     * @return integer
     */
    protected function _getTotalRecords()
    {
        return null;
    }

    /**
     * Get the number of already processed records.
     *
     * @return integer
     */
    protected function _getProcessedRecords()
    {
        return null;
    }

    /**
     * Init everything needed for execution.
     *
     * This method will be called just before execution starts.
     *
     * @return void
     */
    protected function _init()
    {
    }

    /**
     * Clean up after execution.
     *
     * This method is called after execution.
     *
     * @return void
     */
    protected function _shutdown()
    {
    }
}
