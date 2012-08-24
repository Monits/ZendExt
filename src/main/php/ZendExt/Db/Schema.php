<?php
/**
 * Database schema descriptor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Database schema descriptor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.monits.com
 * @since     1.3.0
 */
class ZendExt_Db_Schema
{
    private $_db;
    private $_typeMappingAdapter;

    /**
     * Creates a new descriptor.
     *
     * @param string            $adapter The adapter to be used.
     * @param Zend_Config|array $config  The database configuration.
     *                                   Standard config for Zend_Db is required
     *
     * @return ZendExt_Db_Schema
     */
    public function __construct($adapter, $config)
    {
        $this->_db = Zend_Db::factory($adapter, $config);
    }

    /**
     * Retrieves a list with all tables in the specified database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->_db->listTables();
    }

    /**
     * Retrieves a description of the table schema.
     *
     * @param string $table Table to describe.
     *
     * @return array
     */
    public function describeTable($table)
    {
        $info = $this->_db->describeTable($table);
        $ret = array();

        foreach ($info as $column) {
            $col = array();

            $col['extra']['unsigned'] = $column['UNSIGNED'] == 1;

            $type = $this->getTypeMappingAdapter()->getType(
                $column['DATA_TYPE'],
                $col['extra']['unsigned']
            );

            $col['type'] = $type['name'];

            // Enum.
            if (isset($type['options'])) {
                $col['extra']['options'] = $type['options'];
            } else if (isset($type['min'])) { // Numeric.
                $col['extra']['min'] = $type['min'];
                $col['extra']['max'] = $type['max'];
            }

            $col['extra']['length'] = $column['LENGTH'];

            if ($column['PRECISION'] !==  null) {
                $col['extra']['precision'] = $column['PRECISION'];
            }

            if ($column['SCALE'] !== null) {
                $col['extra']['scale'] = $column['SCALE'];
            }

            $col['nullable'] = $column['NULLABLE'] == 1;
            $col['primary'] = $column['PRIMARY'] == 1;
            $col['sequence'] = $column['IDENTITY'] == 1;

            $mapper = $this->getTypeMappingAdapter();
            $col['default'] = $mapper->getDefault($column['DEFAULT']);

            $ret[$column['COLUMN_NAME']] = $col;
        }

        return $ret;

    }

    /**
     * Retrieves the type mapping adapter for the specified dbms.
     *
     * @return ZendExt_Db_Schema_TypeMappingAdapter_Generic
     */
    public function getTypeMappingAdapter()
    {
        if (!isset($this->_typeMappingAdapter)) {
            $this->_typeMappingAdapter
                = new ZendExt_Db_Schema_TypeMappingAdapter_Generic();
        }

        return $this->_typeMappingAdapter;
    }

}