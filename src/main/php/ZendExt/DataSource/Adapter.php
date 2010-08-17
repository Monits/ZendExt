<?php
/**
 * Datasource adapter interface.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com
 * @since     1.0.0
 */

/**
 * Datasource adapter interface.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @author    imtirabasso <itirabasso@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
interface ZendExt_DataSource_Adapter
{
    /**
     * Retrieves the table's primary key.
     *
     * @return string/array
     */
    public function getPk();

    /**
     * Retrieves the table's sequence.
     *
     * @return boolean
     */
    public function isSequence();

    /**
     * Retrieves the table.
     *
     * @param any $param An extra param for the underlying class.
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getTable($param = null);
}