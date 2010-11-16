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
     * Class constructor.
     *
     * @param Zend_Db_Table_Abstract $table The table.
     */
    public function __construct(Zend_Db_Table_Abstract $table)
    {
        $this->_table = $table;
    }

    /**
     * Retrieves the data source's primary key.
     *
     * @return string|array
     */
    public function getPk()
    {
        return $this->_table->info(Zend_Db_Table_Abstract::PRIMARY);
    }

    /**
     * Retrieves wether the data soruce has a sequence primary key or not.
     *
     * @return boolean
     */
    public function isSequence()
    {
        return $this->_table->info(Zend_Db_Table_Abstract::SEQUENCE);
    }

    /**
     * Inserts a new registry into the data source.
     *
     * @param array $data Associate array of col_name => value
     *                    for the new registry.
     *
     * @return void
     */
    public function insert(array $data)
    {
        $this->_table->insert($data);
    }

    /**
     * Updates a registry already in the data source.
     *
     * @param array $data       Associate array of col_name => value
     *                          for the updated registry.
     * @param array $primaryKey The col_name => value for the primary key,
     *                          or null if it's a sequence.
     *
     * @return void
     */
    public function update(array $data, array $primaryKey)
    {
        $where = $this->_primaryKeyToWhere($primaryKey);

        $this->_table->update($data, $where);
    }

    /**
     * Deletes a registry already in the data source.
     *
     * @param array $primaryKey The col_name => value for the primary key.
     *
     * @return void
     */
    public function delete(array $primaryKey)
    {
        $where = $this->_primaryKeyToWhere($primaryKey);

        $this->_table->delete($where);
    }

    /**
     * Creates an object to perform a query on the datasource.
     *
     * @return Zend_Db_Select
     */
    public function select()
    {
        return $this->_table->select();
    }

    /**
     * Performs the given select query retrieving a single matchng record.
     *
     * @param Zend_Db_Select $select The query to be performed.
     *
     * @return ArrayAccess An object that can be accessed as an array using
     *                     column names as keys.
     */
    public function fetchOne(Zend_Db_Select $select)
    {
        return $this->_table->fetchRow($select);
    }

    /**
     * Retrieves a paginator for the given query.
     *
     * @param Zend_Db_Select $select The select query to be paginated.
     *
     * @return Zend_Paginator
     */
    public function paginate(Zend_Db_Select $select)
    {
        return new Zend_Paginator(
            new Zend_Paginator_Adapter_DbTableSelect($select)
        );
    }

    /**
     * Retrieves the field's data type.
     *
     * @param string $field The name of the field whose type to retrieve.
     *
     * @return string
     */
    public function getFieldType($field)
    {
        $metadata = $this->_table->info(Zend_Db_Table_Abstract::METADATA);

        return $metadata[$field]['DATA_TYPE'];
    }

    /**
     * Checks if the field is nullable or not.
     *
     * @param sintrg $field The name of the field to check if it's nullable.
     *
     * @return boolean
     */
    public function isFieldNullable($field)
    {
        $metadata = $this->_table->info(Zend_Db_Table_Abstract::METADATA);

        return $metadata[$field]['NULLABLE'];
    }

    /**
     * Transforms a col_name => value array into a valid where condition.
     *
     * A valid where condition for Zend_Db_Table are in the form:
     *  col_name = ? => value
     *
     * @param array $primaryKey The primary key data to transform.
     *
     * @return array
     */
    private function _primaryKeyToWhere(array $primaryKey)
    {
        $where = array();

        // Transform the primary key array into a valid where condition
        foreach ($primaryKey as $column => $value) {
            $where[$column . ' = ?'] = $value;
        }

        return $where;
    }
}