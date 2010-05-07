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


    private static $_tables = array();

    private static $_shardingStrategies = array();

    private static $_config = null;


    protected $_tableClass = null;

    /**
     * Retrieves the table for the requested shard id.
     *
     * @param int    $shard     The id of shard to be used.
     * @param string $operation The operation to be performed on the table.
     *                          See {@link #OPERATION_READ} and {@link #OPERATION_WRITE}
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    protected function _getTableForShard($shard, $operation = self::OPERATION_READ)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        // Assure an entry for the table exists
        if (!isset(self::$_tables[$this->_tableClass])) {
            self::$_tables[$this->_tableClass] = array();
        }

        if (!isset(self::$_tables[$this->_tableClass][$operation])) {
            self::$_tables[$this->_tableClass][$operation] = array();
        }

        if (!isset(self::$_tables[$this->_tableClass][$operation][$shard])) {
            // Retrieve the adapter to be used for the instance
            $dbNames = (array) self::$_config->getShardDbs($this->_tableClass, $shard, $operation);

            // Pick anyone at random
            $table = $this->_createTableWithAnyAdapter($dbNames);
            self::$_tables[$this->_tableClass][$operation][$shard] = $table;
        }

        return self::$_tables[$this->_tableClass][$operation][$shard];
    }

    /**
     * Retrieves the table instance to be used.
     *
     * @param string $operation   The operation to be performed on the table.
     *                            See {@link #OPERATION_READ} and {@link #OPERATION_WRITE}
     * @param any    $shardingArg The value on which to perform sharding.
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    protected function _getTable($operation = self::OPERATION_READ, $shardingArg = null)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        // Assure an entry for the table exists
        if (!isset(self::$_tables[$this->_tableClass])) {
            self::$_tables[$this->_tableClass] = array();
        }

        // If the sharding arg is not present, retrieve a connection from the default
        if (null === $shardingArg) {
            if (!isset(self::$_tables[$this->_tableClass]['default'])) {
                $defaultDbs = (array) self::$_config->getDefaultShardDbs($this->_tableClass);

                // Pick anyone at random
                $table = $this->_createTableWithAnyAdapter($defaultDbs);
                self::$_tables[$this->_tableClass]['default'] = $table;
            }

            return self::$_tables[$this->_tableClass]['default'];
        }

        // Apply sharding
        $shardId = $this->_getShardId($shardingArg);

        return $this->_getTableForShard($shardId, $operation);
    }

    /**
     * Computes the shard id for the current table given the value by which to shard.
     *
     * @param any $shardingArg The value on which to perform sharding.
     *
     * @return int The shard id for the current table and value by which to shard.
     */
    private function _getShardId($shardingArg)
    {
        // If not already instantiated, create a new sharding strategy
        $shardingClass = self::$_config->getShardingStrategy($this->_tableClass);
        if (!isset(self::$_shardingStrategies[$shardingClass])) {
            self::$_shardingStrategies[$shardingClass] = new $shardingClass();
        }

        return self::$_shardingStrategies[$shardingClass]->getShard($shardingArg);
    }

    /**
     * Creates a new table using a random adapter from the given list.
     *
     * @param array $dbs The list of possible adapters to be used for the table.
     *
     * @return Zend_Db_Table_Abstract The newly created table.
     */
    private function _createTableWithAnyAdapter(array $dbs)
    {
        $dbName = $dbs[array_rand($dbs)];
        $db = self::$_config->getDb($dbName);
        return new $this->_tableClass($db);
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
     * Configures the DAO to know which adapter to use for each request.
     *
     * @param ZendExt_Application_Resource_Multidb $config The configuration to be used.
     *
     * @return void
     */
    public static function setConfiguration(ZendExt_Application_Resource_Multidb $config)
    {
        self::$_config = $config;
    }
}