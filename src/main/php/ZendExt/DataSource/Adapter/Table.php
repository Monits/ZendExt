<?php
/**
 * Datasource Table Adapter
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource_Adapter
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com
 * @since     1.0.0
 */

/**
 * Datasource Table Adapter
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource_Adapter
 * @author    itirabasso <itirabasso@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_DataSource_Adapter_Table implements ZendExt_DataSource_Adapter
{
    private $_table;

    /**
     * The adapter's table.
     *
     * @param Zend_Db_Table_Abstract $table The table.
     */
    public function __construct(Zend_Db_Table_Abstract $table)
    {
        $this->_table = $table;
    }

    /**
     * Retrieves the table's primary key.
     *
     * @return string/array
     */
    public function getPk()
    {
        return $this->_table->info(Zend_Db_Table_Abstract::PRIMARY);
    }

    /**
     * Retrieves the table's sequence.
     *
     * @return boolean
     */
    public function isSequence()
    {
        return $this->_table->info(Zend_Db_Table_Abstract::SEQUENCE);
    }

    /**
     * Retrieves the table.
     *
     * @param any $param Ignored.
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getTable($param = null)
    {
        return $this->_table;
    }
}