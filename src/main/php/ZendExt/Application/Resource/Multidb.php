<?php

/**
 * Real multidb support with slaves and sharding per table.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Real multidb support with slaves and sharding per table.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Application_Resource_Multidb
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defines the read operation.
     *
     * @var string
     */
    const OPERATION_READ = 'r';

    /**
     * Defines the write operation.
     *
     * @var string
     */
    const OPERATION_WRITE = 'w';

    const DATA_KEY_ADAPTER = 'adapter';
    const DATA_KEY_DEFAULT = 'default';

    protected $_dbs;

    protected $_shards = array();

    protected $_tables = array();

    protected $_dbData = array();

    protected $_shardingStrategies = array();

    /**
     * Initialize the Database Connections and sharding configuration.
     *
     * @return ZendExt_Application_Resource_Multidb
     */
    public function init()
    {
        $options = $this->getOptions();

        // Initialize multidb resource with configured adapters
        $this->_dbs = new Zend_Application_Resource_Multidb(
            $options['adapters']
        );
        $this->_dbs->init();

        // Get shards and tables configuration
        $this->_shards = $options['shards'];
        $this->_tables = $options['tables'];
    }

    /**
     * Retrieves the name of the sharding strategy to be used for a table.
     *
     * @param string $table The name of the table whose sharding strategy
     *                      is requested.
     *
     * @return string The name of the strategy to beused for sharding,
     *                null if none configured.
     */
    protected function _getShardingStrategy($table)
    {
        $shardName = $this->_getShardNameForTable($table);

        if (isset($this->_shards[$shardName])) {
            if (isset($this->_shards[$shardName]['shardingStrategy'])) {
                return $this->_shards[$shardName]['shardingStrategy'];
            }
        }

        return null;
    }

    /**
     * Retrieves the configuration for a requested shard for a given operation.
     *
     * @param string $shardName The name of the shard for which to request
     *                          the configuration.
     * @param int    $shardId   The id of the shard to retrieve (as obtained
     *                          from the sharding strategy).
     * @param string $operation The operation to perform on the table.
     *                          See {@link #OPERATION_READ} and
     *                          {@link #OPERATION_WRITE}
     *
     * @return string|array The name of the adapters that can be used for the
     *                      requested operation on the given table.
     *
     * @throws Zend_Application_Resource_Exception
     */
    protected function _getDbAdapters($shardName, $shardId, $operation)
    {
        if ($operation != self::OPERATION_READ
                && $operation != self::OPERATION_WRITE) {
            throw new Zend_Application_Resource_Exception(
                'Unrecognized operation requested.'
            );
        }

        if (isset($this->_shards[$shardName])) {
            if (isset($this->_shards[$shardName][$operation])) {
                if (isset($this->_shards[$shardName][$operation][$shardId])) {
                    return $this->_shards[$shardName][$operation][$shardId];
                }
            }
        }

        return null;
    }

    /**
     * Retrieves the default adapters for the db to which a given table belongs.
     *
     * @param string $table The name of the table whose default dbs
     *                      to retrieve.
     *
     * @return string|array The default adapters for the shard to which a given
     *                      table belongs.
     */
    protected function _getDefaultDbAdapters($table)
    {
        $shardName = $this->_getShardNameForTable($table);

        if (isset($this->_shards[$shardName])) {
            if (isset($this->_shards[$shardName]['default'])) {
                return $this->_shards[$shardName]['default'];
            }
        }

        return null;
    }

    /**
     * Retrieve the db adapter with the given name.
     *
     * @param string $name The name of the db adapter requested.
     *
     * @return Zend_Db_Adapter_Abstract The requested adapter.
     */
    public function getDb($name)
    {
        // Prevent the db from picking a default if there is none.
        if (null === $name) {
            $name = '';
        }

        return $this->_dbs->getDb($name);
    }

    /**
     * Retrieves the name of the shard to which a given table belongs.
     *
     * @param string $table The name of the table whose shard to retrieve.
     *
     * @return string The name of the shard to which the table belongs.
     */
    protected function _getShardNameForTable($table)
    {
        if (!isset($this->_tables[$table])) {
            return null;
        }

        return $this->_tables[$table];
    }

    /**
     * Get all of the available shards for an operation on a given table.
     *
     * @param string $table     The table name.
     * @param string $operation The operation to perform on the table.
     *                          See {@link #OPERATION_READ} and
     *                          {@link #OPERATION_WRITE}
     *
     * @return array
     */
    public function getAllShardsForTable($table, $operation)
    {
        $shardName = $this->_getShardNameForTable($table);
        if (isset($this->_shards[$shardName])) {
            if (isset($this->_shards[$shardName][$operation])) {

                return $this->_shards[$shardName][$operation];
            }
        }

        return null;
    }

    /**
     * Get the ids all of the available shards for a given table and operation.
     *
     * @param string $table     The table name.
     * @param string $operation The operation to perform on the table.
     *                          See {@link #OPERATION_READ} and
     *                          {@link #OPERATION_WRITE}
     *
     * @return array
     */
    public function getAllShardIdsForTable($table, $operation)
    {
        $shards = $this->getAllShardsForTable($table, $operation);

        if (null === $shards) {
            return null;
        }

        return array_keys($shards);
    }

    /**
     * Computes the shard id for the given table for a sharding value.
     *
     * @param string $table       The name of the table to get the id for.
     * @param any    $shardingArg The value on which to perform sharding.
     *
     * @return int The shard id for the current table and the sharding value.
     */
    protected function _getShardIdForValue($table, $shardingArg)
    {
        // If not already instantiated, create a new sharding strategy
        $shardingClass = $this->_getShardingStrategy($table);

        if (null === $shardingClass) {
            return null;
        }

        if (!isset($this->_shardingStrategies[$shardingClass])) {
            $this->_shardingStrategies[$shardingClass] = new $shardingClass();
        }

        return $this->_shardingStrategies[$shardingClass]->getShard(
            $shardingArg
        );
    }

    /**
     * Get an adapter for a given shard.
     *
     * @param string $table     The name of the table to get adapter for.
     * @param int    $shardId   The id of shard to be used.
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ}
     *                          and {@link #OPERATION_WRITE}
     *
     * @return Zend_Db_Adapter_Abstract The adapter to use.
     */
    public function getAdapterForTableShard($table, $shardId,
        $operation = self::OPERATION_READ)
    {
        $shardName = $this->_getShardNameForTable($table);
        if (null === $shardName) {

            return null;
        }

        $dbData = $this->_getDbData($shardName, $operation, $shardId);

        if (null === $dbData) {
            // Retrieve the adapter to be used for the instance
            $adapterNames = (array) $this->_getDbAdapters(
                $shardName,
                $shardId,
                $operation
            );

            if (0 === count($adapterNames)) {
                return null;
            }

            // Pick anyone at random
            $adapter = $this->_chooseAdapter($adapterNames);
            $dbData = $this->_setDbData(
                $shardName, $operation, $shardId, $adapter
            );
        }

        return $dbData;
    }

    /**
     * Get the default adapter for a given table configuration.
     *
     * @param string $table The table name.
     *
     * @return Zend_Db_Adapter_ABstract The adapter to use.
     */
    public function getDefaultAdapterForTable($table)
    {
        $shardName = $this->_getShardNameForTable($table);
        if (null === $shardName) {

            return null;
        }

        if (!isset($this->_dbData[$shardName][self::DATA_KEY_DEFAULT])) {

            $defaultDbs = (array) $this->_getDefaultDbAdapters($table);
            if (0 === count($defaultDbs)) {

                return null;
            }

            $adapter = $this->_chooseAdapter($defaultDbs);

            $this->_dbData[$shardName][self::DATA_KEY_DEFAULT] = $adapter;
        }

        return $this->_dbData[$shardName][self::DATA_KEY_DEFAULT];
    }

    /**
     * Get an adapter for table, optionally sharding.
     *
     * @param string $table       The name of table to get the adapter for.
     * @param string $operation   The operation to be performed on the table.
     *                            See {@link #OPERATION_READ}
     *                            and {@link #OPERATION_WRITE}
     * @param mixed  $shardingArg The value to shard by. Optional.
     *
     * @return Zend_Db_Adapter_Abstract The adapter to use.
     */
    public function getAdapterForTable($table,
        $operation = self::OPERATION_READ, $shardingArg = null)
    {
        if (null === $shardingArg) {

            return $this->getDefaultAdapterForTable($table);
        } else {

            $shardId = $this->_getShardIdForValue($table, $shardingArg);
            if (null === $shardId) {

                return null;
            }
            return $this->getAdapterForTableShard($table, $shardId, $operation);
        }
    }

    /**
     * Creates a new adapter using a random adapter from the given list.
     *
     * @param array $dbs The list of possible adapters to be used for the table.
     *
     * @return Zend_Db_Adapter_Abstract The newly created adapter.
     */
    private function _chooseAdapter(array $dbs)
    {
        $dbName = $dbs[array_rand($dbs)];
        return $this->getDb($dbName);
    }

    /**
     * Get the data for a given shard.
     *
     * @param string $shardName The name of the shard to fetch data for.
     * @param string $operation The operation type to fetch for.
     * @param string $shardId   The shard to fetch for.
     *
     * @return array An array containing the data.
     */
    protected function _getDbData($shardName, $operation, $shardId)
    {
        if (!isset($this->_dbData[$shardName][$operation][$shardId])) {
            $this->_dbData[$shardName][$operation][$shardId] = null;
        }

        return $this->_dbData[$shardName][$operation][$shardId];
    }

    /**
     * Set the data for a given db.
     *
     * @param string $db        The name of the db to set the data for.
     * @param string $operation The operation type to set the data for.
     * @param string $shardId   The shard to set the data for.
     * @param mixed  $value     The value to set.
     *
     * @return array the modified data.
     */
    protected function _setDbData($db, $operation, $shardId, $value)
    {
        $this->_dbData[$db][$operation][$shardId] = $value;
        return $this->_getDbData($db, $operation, $shardId);
    }

    /**
     * Computes the shard ids for the tables given shard values.
     *
     * @param string $table        The table to look for.
     * @param array  $shardingArgs Shard Array with values
     *                             on which to perform sharding.
     *
     * @return array
     */
    public function getShardsForValues($table, array $shardingArgs)
    {
        $shards = array();
        foreach ($shardingArgs as $id) {
            $shardId = $this->_getShardIdForValue($table, $id);
            if (null === $shardId) {

                return null;
            }

            $shards[$shardId][] = $id;
        }

        return $shards;
    }
}
