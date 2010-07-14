<?php
/**
 * Database schema descriptor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Database schema descriptor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com
 * @since     1.0.0
 */
class ZendExt_Db_Schema
{
    private $_db;
    private $_typeMappingAdapter;

    /**
     * Creates a new descriptor.
     *
     * @param Zend_Config|array $config The database configuration.
     *                                  Standard config for Zend_Db is required
     *                                  plus the adapter to use.
     *
     * @return ZendExt_Db_Schema
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        // TODO: Throw exception if no adapter was given?

        $this->_db = Zend_Db::factory($config['adapter'], $config);
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

            if (isset($type['min'])) {
                $col['extra']['min'] = $type['min'];
                $col['extra']['max'] = $type['max'];
            }

            $col['extra']['length'] = $column['LENGTH'];

            $col['extra']['precision'] =
                $column['PRECISION'] ===  null ? false : $column['PRECISION'];

            $col['extra']['scale'] =
                $column['SCALE'] === null ? false : $column['SCALE'];

            $col['nullable'] = $column['NULLABLE'] == 1;
            $col['primary'] = $column['PRIMARY'] == 1;
            $col['sequence'] = $column['IDENTITY'] == 1;

            $col['default'] = $this->getTypeMappingAdapter()->getDefault($column['DEFAULT']);

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
            $this->_typeMappingAdapter = new ZendExt_Db_Schema_TypeMappingAdapter_Generic();
        }

        return $this->_typeMappingAdapter;
    }

}