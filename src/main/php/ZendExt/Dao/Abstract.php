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
            // No sharding configuration, assume a default adapter.
            if (!isset(self::$_tables[$this->_tableClass])) {
                self::$_tables[$this->_tableClass] = new $this->_tableClass();
            }

            return self::$_tables[$this->_tableClass];
        }

        // Assure an entry for the table exists
        if (!isset(self::$_tables[$this->_tableClass])) {
            self::$_tables[$this->_tableClass] = array();
        }

        // If the sharding arg is not present, retrieve a connection from the default
        if (null === $shardingArg) {
            if (!isset(self::$_tables[$this->_tableClass]['default'])) {
                $defaultDbs = self::$_config->getDefaultShardDbs($this->_tableClass);

                if (!is_array($defaultDbs)) {
                    $defaultDbs = array($defaultDbs);
                }

                // Pick anyone at random
                self::$_tables[$this->_tableClass]['default'] = $defaultDbs[array_rand($defaultDbs)];
            }

            return self::$_tables[$this->_tableClass]['default'];
        }

        // Apply sharding
        $shardingClass = self::$_config->getShardingStrategy($this->_tableClass);
        if (!isset(self::$_shardingStrategies[$shardingClass])) {
            self::$_shardingStrategies[$shardingClass] = new $shardingClass();
        }

        $shardId = self::$_shardingStrategies[$shardingClass]->getShard($shardingArg);


        if (!isset(self::$_tables[$this->_tableClass][$operation])) {
            self::$_tables[$this->_tableClass][$operation] = array();
        }

        if (!isset(self::$_tables[$this->_tableClass][$operation][$shardId])) {
            // Retrieve the adapter to be used for the instance
            $dbNames = self::$_config->getShardDbs($this->_tableClass, $shardId, $operation);

            // Make sure it's an array
            if (!is_array($dbNames)) {
                $dbNames = array($dbNames);
            }

            $dbNameToUse = $dbNames[array_rand($dbNames)];

            $db = self::$_config->getDb($dbNameToUse);
            self::$_tables[$this->_tableClass][$operation][$shardId] = new $this->_tableClass($db);
        }

        return self::$_tables[$this->_tableClass][$operation][$shardId];
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
        $this->_config = $config;
    }
}