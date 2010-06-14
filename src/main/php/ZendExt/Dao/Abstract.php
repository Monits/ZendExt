<?php
/**
 * Abstract DAO implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Dao
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Abstract DAO implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Dao
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
abstract class ZendExt_Dao_Abstract
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

    const DATA_KEY_TABLE = 'table';
    const DATA_KEY_ADAPTER = 'adapter';
    const DATA_KEY_DEFAULT = 'default';


    private static $_tables = array();

    private static $_shardingStrategies = array();

    private static $_config = null;


    protected $_tableClass = null;

    /**
     * Retrieves the table for the requested shard id.
     *
     * @param int    $shard     The id of shard to be used.
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ}
     *                          and {@link #OPERATION_WRITE}
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    protected function _getTableForShard($shard,
        $operation = self::OPERATION_READ)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        $shardData = $this->_getShardData($operation, $shard);
        if (!isset($tableList[self::DATA_KEY_TABLE])) {

            $adapter = $this->_getAdapterForShard($shard, $operation);

            $table = new $this->_tableClass($adapter);

            $shardData = $this->_setShardData(
                $operation, $shard, self::DATA_KEY_TABLE, $table
            );
        }

        return $shardData[self::DATA_KEY_TABLE];
    }

    /**
     * Retrieves the table instance to be used.
     *
     * @param string $operation   The operation to be performed on the table.
     *                            See {@link #OPERATION_READ}
     *                            and {@link #OPERATION_WRITE}
     * @param any    $shardingArg The value on which to perform sharding.
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    protected function _getTable($operation = self::OPERATION_READ,
        $shardingArg = null)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        // If the sharding arg is not present,
        // retrieve a connection from the default
        if (null === $shardingArg) {
            if (!isset(
                    self::$_tables[$this->_tableClass][self::DATA_KEY_DEFAULT]
                )) {
                $defaultDbs = (array) self::$_config->getDefaultShardDbs(
                    $this->_tableClass
                );

                // Pick anyone at random
                $adapter = $this->_chooseAdapter($defaultDbs);
                $table = new $this->_tableClass($adapter);
                self::$_tables[$this->_tableClass][self::DATA_KEY_DEFAULT]
                    = $table;
            }

            return self::$_tables[$this->_tableClass][self::DATA_KEY_DEFAULT];
        }

        // Apply sharding
        $shardId = $this->_getShardId($shardingArg);

        return $this->_getTableForShard($shardId, $operation);
    }

    /**
     * Computes the shard id for the current table given a sharding value.
     *
     * @param any $shardingArg The value on which to perform sharding.
     *
     * @return int The shard id for the current table and the sharding value.
     */
    protected function _getShardId($shardingArg)
    {
        // If not already instantiated, create a new sharding strategy
        $shardingClass = self::$_config->getShardingStrategy(
            $this->_tableClass
        );

        if (!isset(self::$_shardingStrategies[$shardingClass])) {
            self::$_shardingStrategies[$shardingClass] = new $shardingClass();
        }

        return self::$_shardingStrategies[$shardingClass]->getShard(
            $shardingArg
        );
    }

    /**
     * Get an adapter for a given shard.
     *
     * @param int    $shard     The id of shard to be used.
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ}
     *                          and {@link #OPERATION_WRITE}
     *
     * @return Zend_Db_Adapter_Abstract The adapter to use.
     */
    protected function _getAdapterForShard($shard,
        $operation = self::OPERATION_READ)
    {
        if (null === self::$_config) {

            return Zend_Db_Table_Abstract::getDefaultAdapter();
        }

        $shardData = $this->_getShardData($operation, $shard);

        if (empty($shardData)) {
            // Retrieve the adapter to be used for the instance
            $dbNames = (array) self::$_config->getShardDbs(
                $this->_tableClass,
                $shard,
                $operation
            );

            // Pick anyone at random
            $adapter = $this->_chooseAdapter($dbNames);
            $shardData = $this->_setShardData(
                $operation, $shard, self::DATA_KEY_DEFAULT, $adapter
            );
        }

        return $shardData[self::DATA_KEY_DEFAULT];
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
        return self::$_config->getDb($dbName);
    }

    /**
     * Retrieves the table assuming a default table adapter.
     *
     * @return Zend_Db_Table_abstract
     */
    private function _getTableForDefaultAdapter()
    {
        // No sharding configuration, assume a default adapter.
        if (!isset(self::$_tables[$this->_tableClass])) {
            self::$_tables[$this->_tableClass] = new $this->_tableClass();
        }

        return self::$_tables[$this->_tableClass];
    }

    /**
     * Retrieves the shard's data.
     *
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ}
     *                          and {@link #OPERATION_WRITE}
     * @param int    $shard     The id of shard to be used.
     *
     * @return array
     */
    private function _getShardData($operation, $shard)
    {
        // Make sure it exists
        if (!isset(self::$_tables[$this->_tableClass][$operation][$shard])) {
            self::$_tables[$this->_tableClass][$operation][$shard] = array();
        }

        return self::$_tables[$this->_tableClass][$operation][$shard];
    }

    /**
     * Sets a data value for the requested shard.
     *
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ}
     *                          and {@link #OPERATION_WRITE}
     * @param int    $shard     The id of shard to be used.
     * @param string $section   The section under which to store the data.
     * @param any    $data      The data to be stored.
     *
     * @return array The shard's data.
     */
    private function _setShardData($operation, $shard, $section, $data)
    {
        $table = $this->_tableClass;
        self::$_tables[$table][$operation][$shard][$section] = $data;

        return $this->_getShardData($operation, $shard);
    }

    /**
     * Configures the DAO to know which adapter to use for each request.
     *
     * @param ZendExt_Application_Resource_Multidb $config The configuration
     *                                                     to be used.
     *
     * @return void
     */
    public static function setConfiguration(
        ZendExt_Application_Resource_Multidb $config)
    {
        self::$_config = $config;

        // Reset all local caches
        self::$_tables = array();
        self::$_shardingStrategies = array();
    }

    /**
     * Retrieves the table instance to be used.
     *
     * @param string $operation   The operation to be performed on the table.
     *                            See {@link #OPERATION_READ}
     *                            and {@link #OPERATION_WRITE}
     * @param any    $shardingArg The value on which to perform sharding.
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    public function getTable($operation = self::OPERATION_READ,
        $shardingArg = null)
    {
        return $this->_getTable($operation, $shardingArg);
    }

    /**
     * Computes the shard ids for the tables given shard values.
     *
     * @param array $shardingArg Shard Array with values
     *                           on which to perform sharding.
     *
     * @return array
     */
    public function getShardsForValues(array $shardingArgs)
    {
        $shards = array();
        foreach ($shardingArgs as $id) {
            $shards[$this->_getShardId($id)][] = $id;
        }
        return $shards;
    }

    /**
     * Get the rows of the shards and retrieves a rowset.
     *
     * @param string $where        SQL where clause.
     * @param array  $shardingArgs Array with values
     *                             on which to perform sharding.
     *
     * @return array
     */
    public function selectForShard($where, array $shardingArgs)
    {
        $shards = $this->getShardsForValues($shardingArgs);

        $rowset = array();

        foreach ($shards as $shard => $idsForShard) {
            $table = $this->_getTableForShard($shard, self::OPERATION_READ);
            $adapter = $table->getAdapter();

            $select = $adapter->quoteInto($where, $idsForShard);
            foreach ($table->fetchAll($select) as $row) {
                $rowset[] = $row;
            }
        }
        return $rowset;
    }
}