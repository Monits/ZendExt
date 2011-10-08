<?php 
/*

   Copyright 2011 Monits
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
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
    const SELECT_WITH_FROM_PART    = true;
    const SELECT_WITHOUT_FROM_PART = false;

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
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    private function _getTableForShard($shard,
        $operation = ZendExt_Application_Resource_Multidb::OPERATION_READ)
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
     * Create a query for all shards.
     *
     * @param bool $withFromPart Whether or not to include the from part of
     *                           the select based on the table
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForAllShards($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $shards = array();

        if (null !== self::$_config) {
            $shards = self::$_config->getAllShardIdsForTable(
                $this->_tableClass,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            );
        }

        return $this->_selectForShards($shards, $withFromPart);
    }

    /**
     * Create a query for the given values shards.
     *
     * @param array $shardingArgs The values for which the query
     *                            will be executed.
     * @param bool $withFromPart Whether or not to include the from part of
     *                           the select based on the table
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForShardWithValues(array $shardingArgs,
                        $withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $shards = array();

        if (null !== self::$_config) {
            $shards = array_keys(
                self::$_config->getShardsForValues(
                    $this->_tableClass, $shardingArgs
                )
            );
        }

        return $this->_selectForShards($shards, $withFromPart);
    }

    /**
     * Create a query for the given shards.
     *
     * @param array $shards The shards for which the query will be executed.
     * @param bool $withFromPart Whether or not to include the from part of
     *                           the select based on the table
     *
     * @return ZendExt_Db_Dao_Select
     */
    protected function _selectForShards(array $shards,
                                $withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new ZendExt_Db_Dao_Select();

        $tables = array();

        if (null === self::$_config) {
            // No sharding config, go to the default adapter
            $tables[] = $this->_getTableForDefaultAdapter();
        } else {
            foreach ($shards as $shard) {
                $tables[] = $this->_getTableForShard(
                    $shard,
                    ZendExt_Application_Resource_Multidb::OPERATION_READ
                );
            }
        }

        $select->setTables($tables);

        if (self::SELECT_WITH_FROM_PART === $withFromPart) {
            $select->from($tables[0], Zend_Db_Table_Select::SQL_WILDCARD);
        }

        return $select;
    }

    /**
     * Execute an update for an array of values in the correspoding shards.
     *
     * @param array $data         The data to update.
     * @param mixed $where        SQL where clause or array of clause => value.
     * @param array $shardingArgs Optional. Array with values on which to
     *                            perform sharding. Defaults to all shards.
     *
     * @return int Total number of rows updated.
     */
    protected function _update($data, $where, array $shardingArgs = array())
    {
        // If no config, go to default adapter - no sharding
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter()->update($data, $where);
        }

        // Get the appropiate shards
        if (empty($shardingArgs)) {
            $shards = self::$_config->getAllShardIdsForTable(
                $this->_tableClass,
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            );
        } else {
            $shards = array_keys(
                self::$_config->getShardsForValues(
                    $this->_tableClass, $shardingArgs
                )
            );
        }

        // Perform the query on each one
        $total = 0;
        foreach ($shards as $shard) {
            $table = $this->_getTableForShard(
                $shard, ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            );

            $total += $table->update($data, $where);
        }

        return $total;
    }

    /**
     * Execute a delete for an array of values in the correspoding shards.
     *
     * @param mixed $where        SQL where clause or array of clause => value.
     * @param array $shardingArgs Optional. Array with values on which to
     *                            perform sharding. Defaults to all shards.
     *
     * @return int Total number of rows deleted.
     */
    protected function _delete($where, array $shardingArgs = array())
    {
        // If no config, go to default adapter - no sharding
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter()->delete($where);
        }

        // Get the appropiate shards
        if (empty($shardingArgs)) {
            $shards = self::$_config->getAllShardIdsForTable(
                $this->_tableClass,
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            );
        } else {
            $shards = array_keys(
                self::$_config->getShardsForValues(
                    $this->_tableClass, $shardingArgs
                )
            );
        }

        // Perform the query on each one
        $total = 0;
        foreach ($shards as $shard) {
            $table = $this->_getTableForShard(
                $shard, ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            );

            $total += $table->delete($where);
        }

        return $total;
    }

    /**
     * Inserts a new row.
     *
     * @param array $data        Column-value pairs.
     * @param mixed $shardingArg Optional. The argument that specifies to
     *                           which shard the row should go. Uses the
     *                           default if not specified.
     *
     * @return mixed         The primary key of the row inserted.
     */
    protected function _insert(array $data, $shardingArg = null)
    {
        // If no config, go to default adapter - no sharding
        if (null === self::$_config) {
            return $this->_getTableForDefaultAdapter()->insert($data);
        }

        // Get the appropiate shard
        if (null === $shardingArg) {
            // No sharding arg specified, go to the default adapter
            $adapter = self::$_config->getDefaultAdapterForTable(
                $this->_tableClass
            );
        } else {
            // Go to the appropiate shard
            $adapter = self::$_config->getAdapterForTable(
                $this->_tableClass,
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE,
                $shardingArg
            );
        }

        $table = $this->_getTableForAdapter($adapter);
        return $table->insert($data);
    }

    /**
     * Retrieves a paginator for the given query.
     *
     * If an Hydrator is set, results will be hydrated.
     *
     * @param ZendExt_Db_Dao_Select $select The query to be paginated.
     *
     * @return Zend_Paginator
     */
    protected function _paginate(ZendExt_Db_Dao_Select $select)
    {
        $selectAdapter = new ZendExt_Paginator_Adapter_DbDaoSelect($select);

        // Add hydration if needed
        if (null !== $this->_hydrator) {
            $selectAdapter = new ZendExt_Paginator_Adapter_CallbackDecorator(
                array($this->_hydrator, 'hydrate'),
                $selectAdapter
            );
        }

        return new Zend_Paginator($selectAdapter);
    }

    /**
     * Fetches all rows.
     *
     * @param ZendExt_Db_Dao_Select $select The query to be performed.
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
     * @param ZendExt_Db_Dao_Select $select The query to be performed.
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

    /**
     * Returns table information.
     *
     * You can elect to return only a part of this information by supplying
     * its key name, otherwise all information is returned as an array.
     *
     * @param null|string $key The specific info part to return OPTIONAL.
     *
     * @return mixed
     */
    protected function _info($key = null)
    {
        /*
         * We need a table instance, no matter which one
         * (all tables should have the same schema, no matter the adapter)
         */

        // Was there a table already cached?
        if (isset(self::$_tables[$this->_tableClass])) {
            $tables = self::$_tables[$this->_tableClass];

            reset($tables);
            $table = current($tables);
        } else {

            // No table previously created.... is there a sharding config?
            if (self::$_config === null) {
                $table = $this->_getTableForDefaultAdapter();
            } else {
                // There is, just get the default adapter, and a table for that
                $adapter = self::$_config->getDefaultAdapterForTable(
                    $this->_tableClass
                );
                $table = $this->_getTableForAdapter($adapter);
            }
        }

        return $table->info($key);
    }
}
