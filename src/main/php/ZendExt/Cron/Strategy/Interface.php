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
     * @param Zend_Config $config The config object for this strategy.
     *
     * @return void
     */
    public function init(Zend_Config $config);

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
}
