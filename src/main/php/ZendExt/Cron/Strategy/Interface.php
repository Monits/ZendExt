<?php
/**
 * Base interface for strategies.
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
 * Base interface for strategies.
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
interface ZendExt_Cron_Strategy_Interface
{

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
        Zend_Application_Bootstrap_BootstrapAbstract $bootstrap);

    /**
     * Process a bulk of data.
     *
     * @return void
     */
    public function processBulk();

    /**
     * Returns whether the strategy is done.
     *
     * @return boolean
     */
    public function isDone();

    /**
     * Shutdown the strategy.
     *
     * @return void
     */
    public function shutdown();

    /**
     * Set the manager.
     *
     * @param ZendExt_Cron_Manager $manager A manager instance.
     *
     * @return void
     */
    public function setManager(ZendExt_Cron_Manager $manager);
}
