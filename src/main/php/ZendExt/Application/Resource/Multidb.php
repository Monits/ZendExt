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


    protected $_dbs;

    protected $_shards = array();

    protected $_tables = array();

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
    public function getShardingStrategy($table)
    {
        $shard = $this->_getShardForTable($table);

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
     * @param string $table     The name of the table for which to request
     *                          a shard.
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
    public function getShardDbs($table, $shardId, $operation)
    {
        if ($operation != self::OPERATION_READ
                && $operation != self::OPERATION_WRITE) {
            throw new Zend_Application_Resource_Exception(
                'Unrecognized operation requested.'
            );
        }

        $shard = $this->_getShardForTable($table);

        if (isset($this->_shards[$shard])) {
            if (isset($this->_shards[$shard][$operation])) {
                if (isset($this->_shards[$shard][$operation][$shardId])) {
                    return $this->_shards[$shard][$operation][$shardId];
                }
            }
        }

        return null;
    }

    /**
     * Retrieves the default dbs for the shard to which a given table belongs.
     *
     * @param string $table The name of the table whose default dbs
     *                      to retrieve.
     *
     * @return string|array The default dbs for the shard to which a given
     *                      table belongs.
     */
    public function getDefaultShardDbs($table)
    {
        $shard = $this->_getShardForTable($table);

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
    protected function _getShardForTable($table)
    {
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
        $shard = $this->_getShardForTable($table);
        if (isset($this->_shards[$shard])) {
            if (isset($this->_shards[$shard][$operation])) {

                return $this->_shards[$shard][$operation];
            }
        }

        return array();
    }
}