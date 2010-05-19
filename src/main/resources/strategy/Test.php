<?php
/**
 * Launcher script for offline processes.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron_Strategy
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Test strategy that works random times creating random loads of work.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron_Strategy
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class Test implements ZendExt_Cron_Strategy_MeasurableInterface
{
    const CONFIG_FILE = 'Test';

    /**
     * The config object.
     *
     * @var Zend_Config
     */
    private $_config;

    /**
     * The number of processed rows.
     *
     * @var integer
     */
    private $_processed;

    /**
     * The total number of rows.
     *
     * @var integer
     */
    private $_total;

    /**
     * A *really* big string.
     *
     * @var string
     */
    private $_bigString = '';

    private $_manager;

    /**
     * Init the strategy.
     *
     * @param Zend_Config                                  $config    Config for
     *                                                                this
     *                                                                strategy.
     * @param Zend_Application_Bootstrap_BootstrapAbstract $bootstrap Instance.
     *
     * @return void
     */
    public function init(Zend_Config $config,
        Zend_Application_Bootstrap_BootstrapAbstract $bootstrap)
    {

        $this->_config = $config;

        $this->_processed = 0;
        $this->_total = $config->total;

        if ( ZendExt_Cron_Persistance::isPersisted('bigString') ) {

            $logger = ZendExt_Cron_Log::getProcessLog();
            $logger->info(ZendExt_Cron_Persistance::retrieve('bigString'));
        }
    }

    /**
     * Process a bulk of data.
     *
     * @return void
     *
     */
    public function processBulk()
    {
        sleep(rand(2, 5));
        $this->_bigString .= str_repeat('test', rand(10, 20));

        $this->_processed += rand(30, 50);
    }

    /**
     * Shutdown the strategy.
     *
     * @return void
     */
    public function shutdown()
    {

        ZendExt_Cron_Persistance::persist('bigString', $this->_bigString);
        $this->_bigString = '';
    }

    /**
     * Get the total number of records to be processed.
     *
     * @return integer
     */
    public function getTotalRecords()
    {

        return $this->_total;
    }

    /**
     * Get the number of already processed records.
     *
     * @return integer
     */
    public function getProcessedRecords()
    {

        return $this->_processed;
    }

    /**
     * Returns whether the strategy is done.
     *
     * @return boolean
     */
    public function isDone()
    {
        return $this->_processed >= $this->_total;
    }

    /**
     * Set the manager.
     *
     * @param ZendExt_Cron_Manager $manager A manager instance.
     *
     * @return void
     */
    public function setManager(ZendExt_Cron_Manager $manager)
    {
        $this->_manager = $manager;
    }
}
