<?php
/**
 * Abstract DAO implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao
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
 * @package   ZendExt_Db_Dao
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
abstract class ZendExt_Db_Dao_Abstract
{
    /**
     * Defines the read operation.
     *
     * @var string
     */
    const OPERATION_READ = ZendExt_Application_Resource_Multidb::OPERATION_READ;

    /**
     * Defines the write operation.
     *
     * @var string
     */
    const OPERATION_WRITE =
        ZendExt_Application_Resource_Multidb::OPERATION_WRITE;

    const DATA_KEY_TABLE = 'table';
    const DATA_KEY_DEFAULT = 'default';


    private static $_tables = array();

    /**
     * @var ZendExt_Application_Resource_Multidb
     */
    private static $_config = null;

    protected $_tableClass = null;

    /**
     * @var ZendExt_Db_Dao_Hydrator_Interface
     */
    protected $_hydrator = null;

    /**
     * Class constructor.
     *
     * @param ZendExt_Db_Dao_Hydrator_Interface $hydrator The hydrator to apply to queries.
     *
     * @return ZendExt_Db_Dao_Abstract
     */
    public function __construct(ZendExt_Db_Dao_Hydrator_Interface $hydrator)
    {
        $this->_hydrator = $hydrator;
    }

    /**
     * Given an adapter get the corresponding table.
     *
     * @param Zend_Db_Adapter_Abstract $adapter The adapter to search for.
     *
     * @return Zend_Db_Table_Abstract The associated table.
     */
    private function _getTableForAdapter($adapter)
    {
        $key = spl_object_hash($adapter);
        if (!isset(self::$_tables[$this->_tableClass][$key])) {

            $table = new $this->_tableClass($adapter);
            self::$_tables[$this->_tableClass][$key] = $table;
        }

        return self::$_tables[$this->_tableClass][$key];
    }

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
    private function _getTableForShard($shard,
        $operation = self::OPERATION_READ)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        $adapter = self::$_config->getAdapterForTableShard(
            $this->_tableClass, $shard, $operation
        );

        return $this->_getTableForAdapter($adapter);
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
    private function _getTable($operation = self::OPERATION_READ,
        $shardingArg = null)
    {
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter();
        }

        $adapter = self::$_config->getAdapterForTable(
            $this->_tableClass, $operation, $shardingArg
        );

        return $this->_getTableForAdapter($adapter);
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
    private function _getAdapterForShard($shard,
        $operation = self::OPERATION_READ)
    {
        if (null === self::$_config) {

            return Zend_Db_Table_Abstract::getDefaultAdapter();
        }

        return self::$_config->getAdapterForTableShard(
            $this->_tableClass, $shard, $operation
        );
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

        // Make sure all previous connections are closed
        foreach (self::$_tables as $t) {
            foreach ($t as $table) {
                $table->getAdapter()->closeConnection();
            }
        }

        // Reset all local caches
        self::$_tables = array();
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
     * Create a query for all shards.
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForAllShards()
    {
        return $this->_selectForShards(
            $this->_getShardsForTable(self::OPERATION_READ)
        );
    }

    /**
     * Create a query for the given values shards.
     *
     * @param array $shardingArgs The values for which the query
     *                            will be executed.
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForShardWithValues(array $shardingArgs)
    {
        return $this->_selectForShards(
            self::$_config->getShardsForValues(
                $this->_tableClass, $shardingArgs
            )
        );
    }

    /**
     * Create a query for the given shards.
     *
     * @param array $shards The shards for which the query will be executed.
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForShards(array $shards)
    {
        $select = new ZendExt_Db_Dao_Select($this);

        $adapters = array();

        foreach ($shards as $shard) {
            $adapters = self::$_config->getAdapterForTableShard(
                $this->_tableClass, $shard, self::OPERATION_READ
            );
        }

        $select->setAdapters($adapters);

        return $select;
    }

    /**
     * Execute an update for an array of values in the correspoding shard.
     *
     * @param array  $data         The data to update.
     * @param string $where        SQL where clause.
     * @param array  $shardingArgs Array with values on which
     *                             to perform sharding.
     * @param array  $extra        Extra where conditions. If any needs quoting
     *                             set the where string as key with the
     *                             corresponding value. Optional.
     *
     * @return void
     */
    protected function _updateForShard($data, $where, array $shardingArgs,
        array $extra = array())
    {
        $shards = self::$_config->getShardsForValues(
            $this->_tableClass, $shardingArgs
        );


        $rowset = array();
        foreach ($shards as $shard => $valuesForShard) {
            $table = $this->_getTableForShard($shard, self::OPERATION_WRITE);
            $adapter = $table->getAdapter();

            $cond = $this->_quoteWhere(
                $adapter, $where, $valuesForShard, $extra
            );

            $table->update($data, $cond);
        }
    }

    /**
     * Prepare a where clause.
     *
     * @param Zend_Db_Adapter_Abstract $adapter The adapter to use to quote.
     * @param string                   $where   The base where clause.
     * @param mixed                    $value   The value to quote into  $where.
     * @param array                    $extra   Extra where conditions. If any
     *                                          needs quoting set the where
     *                                          string as key with the
     *                                          corresponding value. Optional.
     *
     * @return string the prepared where clause.
     */
    private function _quoteWhere(Zend_Db_Adapter_Abstract $adapter,
        $where = null, $value = null, array $extra = array())
    {
        if (null !== $where) {
            $where = $adapter->quoteInto($where, $value);
        } else {
            $where = '';
        }

        foreach ($extra as $key => $value) {

            if ('' != $where) {
                $where .= ' AND ';
            }

            if (is_string($key)) {

                $where .= $adapter->quoteInto($key, $value);
            } else {

                $where .= $key;
            }
        }

        return $where;
    }

    /**
     * Get all of the available shards for an operation on a given table.
     *
     * @param string $operation The operation to perform on the table.
     *                          See {@link #OPERATION_READ} and
     *                          {@link #OPERATION_WRITE}
     *
     * @return array
     */
    private function _getShardsForTable($operation)
    {
        return array_keys(
            self::$_config->getShardsForTable($this->_tableClass, $operation)
        );
    }

    /**
     * Retrieves the shard id for the given value.
     *
     * @param any $shardingArg The argument by which sharding is perfmored.
     *
     * @return int
     */
    private function _getShardIdForValue($shardingArg)
    {
        $shads = self::$_config->getShardsForValues(
            $this->_tableClass, array($shardingArg)
        );

        $shardIds = array_keys($shads);

        return $shardIds[0];
    }

    /**
     * Fetches all rows.
     *
     * @param Zend_Db_Dao_Select $select The query to be performed.
     *
     * @return array The row results, hydrated as configured.
     */
    protected function _fetchAll(ZendExt_Db_Dao_Select $select)
    {
        return $this->_fetch($select);
    }

    /**
     * Fetches a single row.
     *
     * @param Zend_Db_Dao_Select $select The query to be performed.
     *
     * @return array The row results, hydrated as configured.
     */
    protected function _fetchRow(ZendExt_Db_Dao_Select $select)
    {
        $select->limit(1);
        $rows = $this->_fetch($select);

        if (0 == count($rows)) {
            return null;
        }

        return $rows[0];
    }

    /**
     * Actually perform a select query.
     *
     * @param ZendExt_Db_Dao_Select $select The query whose rows to fetch.
     *
     * @return array
     */
    private function _fetch(ZendExt_Db_Dao_Select $select)
    {
        $stmts = $select->query();

        $data = array();

        foreach ($stmts as $stmt) {
            $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

            if (null !== $this->_hydrator) {
                // Hydrate every row
                foreach ($rows as $row) {
                    $data[] = $this->_hydrator->hydrate($row);
                }
            } else {
                // Just copy data
                $data = array_merge($data, $rows);
            }
        }

        return $data;
    }
}
