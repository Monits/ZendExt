<?php
/**
 * Real multidb support with slaves and sharding per table.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Real multidb support with slaves and sharding per table.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
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

        // Configure DAO
        ZendExt_Dao_Abstract::setConfiguration($this);
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
        $shard = $this->_getDbForTable($table);

        if (isset($this->_shards[$shard])) {
            if (isset($this->_shards[$shard]['shardingStrategy'])) {
                return $this->_shards[$shard]['shardingStrategy'];
            }
        }

        return null;
    }

    /**
     * Retrieves the configuration for a requested shard for a given operation.
     *
     * @param string $db        The name of the database for which to request
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
    protected function _getDbAdapters($db, $shardId, $operation)
    {
        if ($operation != self::OPERATION_READ
                && $operation != self::OPERATION_WRITE) {
            throw new Zend_Application_Resource_Exception(
                'Unrecognized operation requested.'
            );
        }

        if (isset($this->_shards[$db])) {
            if (isset($this->_shards[$db][$operation])) {
                if (isset($this->_shards[$db][$operation][$shardId])) {
                    return $this->_shards[$db][$operation][$shardId];
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
        $shard = $this->_getDbForTable($table);

        if (isset($this->_shards[$shard])) {
            if (isset($this->_shards[$shard]['default'])) {
                return $this->_shards[$shard]['default'];
            }
        }

        return null;
    }

    /**
     * Retrieve the db with the given name.
     *
     * @param string $name The name of the db requested.
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
     * Retrieves the shard to which a given table belongs.
     *
     * @param string $table The name of the table whose shard to retrieve.
     *
     * @return string The name of the shard to which the table belongs.
     */
    protected function _getDbForTable($table)
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
    public function getShardsForTable($table, $operation)
    {
        $db = $this->_getDbForTable($table);
        if (isset($this->_shards[$db])) {
            if (isset($this->_shards[$db][$operation])) {

                return $this->_shards[$db][$operation];
            }
        }

        return null;
    }

    /**
     * Computes the shard id for the current table given a sharding value.
     *
     * @param string $table       The name of the table to get the id for.
     * @param any    $shardingArg The value on which to perform sharding.
     *
     * @return int The shard id for the current table and the sharding value.
     */
    protected function _getShardId($table, $shardingArg)
    {
        // If not already instantiated, create a new sharding strategy
        $shardingClass = $this->_getShardingStrategy($table);

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
        $db = $this->_getDbForTable($table);
        if (null === $db) {

            return null;
        }

        $dbData = $this->_getDbData($db, $operation, $shardId);

        if (null === $dbData) {
            // Retrieve the adapter to be used for the instance
            $dbNames = (array) $this->_getDbAdapters(
                $db,
                $shardId,
                $operation
            );

            // Pick anyone at random
            $adapter = $this->_chooseAdapter($dbNames);
            $dbData = $this->_setDbData(
                $db, $operation, $shardId, $adapter
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
        $db = $this->_getDbForTable($table);
        if (null === $db) {

            return null;
        }

        if (!isset($this->_dbData[$db][self::DATA_KEY_DEFAULT])) {

            $defaultDbs = (array) $this->_getDefaultDbAdapters($table);
            $adapter = $this->_chooseAdapter($defaultDbs);

            $this->_dbData[$db][self::DATA_KEY_DEFAULT] = $adapter;
        }

        return $this->_dbData[$db][self::DATA_KEY_DEFAULT];
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

            $shardId = $this->_getShardId($table, $shardingArg);
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
     * Get the data for a given db.
     *
     * @param string $db        The name of the db to fetch data for.
     * @param string $operation The operation type to fetch for.
     * @param string $shardId   The shard to fetch for.
     *
     * @return array An array containing the data.
     */
    protected function _getDbData($db, $operation, $shardId)
    {
        if (!isset($this->_dbData[$db][$operation][$shardId])) {
            $this->_dbData[$db][$operation][$shardId] = null;
        }

        return $this->_dbData[$db][$operation][$shardId];
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
            $shards[$this->_getShardId($table, $id)][] = $id;
        }   

        return $shards;
    }   
}
