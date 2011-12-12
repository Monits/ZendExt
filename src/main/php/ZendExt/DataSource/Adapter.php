<?php
/**
 * Datasource adapter interface.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Datasource adapter interface.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @author    imtirabasso <itirabasso@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
interface ZendExt_DataSource_Adapter
{
    /**
     * Retrieves the data source's primary key.
     *
     * @return string|array
     */
    public function getPk();

    /**
     * Retrieves wether the data soruce has a sequence primary key or not.
     *
     * @return boolean
     */
    public function isSequence();

    /**
     * Retrieves the field's data type.
     *
     * @param string $field The name of the field whose type to retrieve.
     *
     * @return string
     */
    public function getFieldType($field);

    /**
     * Checks if the field is nullable or not.
     *
     * @param sintrg $field The name of the field to check if it's nullable.
     *
     * @return boolean
     */
    public function isFieldNullable($field);

    /**
     * Inserts a new registry into the data source.
     *
     * @param array $data Associate array of col_name => value
     *                    for the new registry.
     *
     * @return void
     */
    public function insert(array $data);

    /**
     * Updates a registry already in the data source.
     *
     * @param array $data       Associate array of col_name => value
     *                          for the updated registry.
     * @param array $primaryKey The col_name => value for the primary key.
     *
     * @return void
     */
    public function update(array $data, array $primaryKey);

    /**
     * Deletes a registry already in the data source.
     *
     * @param array $primaryKey The col_name => value for the primary key.
     *
     * @return void
     */
    public function delete(array $primaryKey);

    /**
     * Creates an object to perform a query on the datasource.
     *
     * @return Zend_Db_Select
     */
    public function select();

    /**
     * Performs the given select query retrieving a single matchng record.
     *
     * @param Zend_Db_Select $select The query to be performed.
     *
     * @return ArrayAccess An object that can be accessed as an array using
     *                     column names as keys.
     */
    public function fetchOne(Zend_Db_Select $select);

    /**
     * Retrieves a paginator for the given query.
     *
     * @param Zend_Db_Select $select The select query to be paginated.
     *
     * @return Zend_Paginator
     */
    public function paginate(Zend_Db_Select $select);
}