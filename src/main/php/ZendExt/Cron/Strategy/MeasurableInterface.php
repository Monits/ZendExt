<?php
/**
 * Interface for strategies with progess report capabilities.
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
 * Interface for strategies with progess report capabilities.
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
interface ZendExt_Cron_Strategy_MeasurableInterface
    extends ZendExt_Cron_Strategy_Interface
{

    /**
     * Get the total number of records to be processed.
     *
     * @return integer
     */
    public function getTotalRecords();

    /**
     * Get the number of already processed records.
     *
     * @return integer
     */
    public function getProcessedRecords();
}
