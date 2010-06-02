<?php
/**
 * Datasource Dao Adapter
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
 * Datasource Dao Adapter
 *
 * @category  ZendExt
 * @package	  ZendExt_DataSource_Adapter
 * @author    itirabasso <itirabasso@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_DataSource_Adapter_Dao implements ZendExt_DataSource_Adapter
{
    protected $_dao;

    /**
     * Sets the Dao into the adapter.
     *
     * @param ZendExt_Dao_Abstract $dao An instance of Dao
     *
     * @return void
     */
    public function __construct(ZendExt_Dao_Abstract $dao)
    {
        $this->_dao = $dao;
    }

    /**
     * Retrieves the table's primary key.
     *
     * @return string|array
     */
    public function getPk()
    {
        return $this->_dao->getTable(ZendExt_Dao_Abstract::OPERATION_WRITE)
                ->info(Zend_Db_Table_Abstract::PRIMARY);
    }

    /**
     * Retrieves the table's sequence.
     *
     * @return boolean
     */
    public function isSequence()
    {
        return $this->_dao->getTable(ZendExt_Dao_Abstract::OPERATION_WRITE)
                ->info(Zend_Db_Table_Abstract::SEQUENCE);
    }

    /**
     * Retrieves the table.
     *
     * @param any $param The sharding argument.
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getTable($param = null)
    {
        return $this->_dao
                    ->getTable(ZendExt_Dao_Abstract::OPERATION_WRITE, $param);

    }
}