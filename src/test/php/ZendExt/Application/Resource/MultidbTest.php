<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Test case for Multidb resource plugin.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Test case for Multidb resource plugin.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class MultidbTest extends PHPUnit_Framework_TestCase
{
    protected $_multidb;

    protected $_config;

    /**
     * Creates a multidb instance for tests.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_config = array(
            'adapters' => array(
                'test_adapter_r' => array(
                    'adapter'  => 'pdo_mysql',
                    'host'     => 'localhost',
                    'username' => 'user_read_only',
                    'password' => 'pass',
                    'dbname'   => 'db'
                ),
                'test_adapter_w' => array(
                    'adapter'  => 'pdo_mysql',
                    'host'     => 'localhost',
                    'username' => 'user_write_only',
                    'password' => 'pass',
                    'dbname'   => 'db'
                ),
                'test_adapter_default' => array(
                    'adapter'  => 'pdo_mysql',
                    'host'     => 'localhost',
                    'username' => 'user_default',
                    'password' => 'pass',
                    'dbname'   => 'db'
                ),
                'test_adapter_rw' => array(
                    'adapter'  => 'pdo_mysql',
                    'host'     => 'localhost',
                    'username' => 'user_readwrite',
                    'password' => 'pass',
                    'dbname'   => 'db'
                )
            ),
            'shards' => array(
                'realshard' => array(
                    'shardingStrategy' => 'ZendExt_Sharding_Strategy_Null',
                    'r' => array(
                        array(
                            'test_adapter_r',
                            'test_adapter_w'
                        )
                    ),
                    'w' => array(
                        'test_adapter_w'
                    ),
                    'default' => 'test_adapter_default'
                ),
                'master' => array(
                    'shardingStrategy' => 'ZendExt_Sharding_Strategy_Null',
                    'r' => array(
                        'test_adapter_rw'
                    ),
                    'w' => array(
                        'test_adapter_rw'
                    ),
                    'default' => 'test_adapter_rw'
                ),
            ),
            'tables' => array(
                'Random_Table_1' => 'realshard',
                'Random_Table_2' => 'realshard',
                'Random_Master_Table' => 'master',
            )
        );

        $this->_multidb = new ZendExt_Application_Resource_Multidb(
            $this->_config
        );
        $this->_multidb->init();
    }

    /**
     * Test the getDb method.
     *
     * @return void
     */
    public function testGetDb()
    {
        try {
            $this->_multidb->getDb('non-existing-adapter');

            $this->fail('could retrieve an adapter for a non-existing adapter name.');
        } catch (Zend_Application_Resource_Exception $e) {
            // This is expected
        }

        try {
            $this->_multidb->getDb(null);

            $this->fail('could retrieve an adapter for a non-existing adapter name.');
        } catch (Zend_Application_Resource_Exception $e) {
            // This is expected
        }

        $adapter = $this->_multidb->getDb('test_adapter_r');
        $adapterConfig = $adapter->getConfig();

        // Make sure it's the same one
        $this->assertEquals(
            $this->_config['adapters']['test_adapter_r']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_w');
        $adapterConfig = $adapter->getConfig();

        // Make sure it's the same one
        $this->assertEquals(
            $this->_config['adapters']['test_adapter_w']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_default');
        $adapterConfig = $adapter->getConfig();

        // Make sure it's the same one
        $this->assertEquals(
            $this->_config['adapters']['test_adapter_default']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_rw');
        $adapterConfig = $adapter->getConfig();

        // Make sure it's the same one
        $this->assertEquals(
            $this->_config['adapters']['test_adapter_rw']['username'],
            $adapterConfig['username']
        );
    }

    /**
     * Test the geAllShardsForTable method.
     *
     * @return void
     */
    public function testGetAllShardIdsForTable()
    {
        $this->assertNull(
            $this->_multidb->getAllShardIdsForTable(
                'Non_Existing_Table',
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            )
        );

        $this->assertNull(
            $this->_multidb->getAllShardIdsForTable(
                'Random_Table_1',
                'foobar'
            )
        );

        $operation = ZendExt_Application_Resource_Multidb::OPERATION_WRITE;
        $this->assertEquals(
            array_keys($this->_config['shards']['realshard'][$operation]),
            $this->_multidb->getAllShardIdsForTable(
                'Random_Table_1',
                $operation
            )
        );
    }

    /**
     * Test the geAllShardsForTable method.
     *
     * @return void
     */
    public function testGetAllShardsForTable()
    {
        $this->assertNull(
            $this->_multidb->getAllShardsForTable(
                'Non_Existing_Table',
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            )
        );

        $this->assertNull(
            $this->_multidb->getAllShardsForTable(
                'Random_Table_1',
                'foobar'
            )
        );

        $operation = ZendExt_Application_Resource_Multidb::OPERATION_WRITE;
        $this->assertEquals(
            $this->_config['shards']['realshard'][$operation],
            $this->_multidb->getAllShardsForTable(
                'Random_Table_1',
                $operation
            )
        );
    }

    /**
     * Test get adapter for table shard.
     *
     * @return void
     */
    public function testGetAdapterForTableShard()
    {
        $operation = ZendExt_Application_Resource_Multidb::OPERATION_WRITE;
        $adapter = $this->_multidb->getAdapterForTableShard(
            'Random_Table_1', 0, $operation
        );
        $config = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_w']['username'],
            $config['username']
        );


        $operation = ZendExt_Application_Resource_Multidb::OPERATION_READ;
        $adapter = $this->_multidb->getAdapterForTableShard(
            'Random_Table_1', 0, $operation
        );
        $config = $adapter->getConfig();
        $this->assertContains(
            $config['username'],
            array(
                $this->_config['adapters']['test_adapter_w']['username'],
                $this->_config['adapters']['test_adapter_r']['username']
            )
        );

        $adapter2 = $this->_multidb->getAdapterForTableShard(
            'Random_Table_1', 0, $operation
        );
        $config2 = $adapter2->getConfig();
        $this->assertEquals($config['username'], $config2['username']);

        $adapter2 = $this->_multidb->getAdapterForTableShard(
            'Random_Table_2', 0, $operation
        );
        $config2 = $adapter2->getConfig();
        $this->assertEquals($config['username'], $config2['username']);


        $this->assertNull(
            $this->_multidb->getAdapterForTableShard(
                'non-existant-table', 0, $operation
            )
        );
    }


    /**
     * Test get default adapter for table.
     *
     * @return void
     */
    public function testGetDefaultAdapterForTable()
    {
        $adapter = $this->_multidb->getDefaultAdapterForTable(
            'non-existant-table'
        );
        $this->assertNull($adapter);

        $adapter = $this->_multidb->getDefaultAdapterForTable(
            'Random_Master_Table'
        );
        $config = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_rw']['username'],
            $config['username']
        );

        $adapter = $this->_multidb->getDefaultAdapterForTable(
            'Random_Table_1'
        );
        $config = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_default']['username'],
            $config['username']
        );
    }

    /**
     * Test getAdapterForTable.
     *
     * @return void
     */
    public function testGetAdapterForTable()
    {
        $adapter = $this->_multidb->getAdapterForTable(
            'non-existant-table'
        );
        $this->assertNull($adapter);

        $operation = ZendExt_Application_Resource_Multidb::OPERATION_READ;
        $adapter = $this->_multidb->getAdapterForTable(
            'Random_Table_1',
            $operation
        );
        $config = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_default']['username'],
            $config['username']
        );


        $adapter = $this->_multidb->getAdapterForTable(
            'Random_Table_1', $operation, 'sarasa'
        );
        $config = $adapter->getConfig();
        $this->assertContains(
            $config['username'],
            array(
                $this->_config['adapters']['test_adapter_r']['username'],
                $this->_config['adapters']['test_adapter_w']['username']
            )
        );

        $operation = ZendExt_Application_Resource_Multidb::OPERATION_WRITE;
        $adapter = $this->_multidb->getAdapterForTable(
            'Random_Table_1', $operation, 'sarasa'
        );
        $config = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_w']['username'],
            $config['username']
        );

        $adapter2 = $this->_multidb->getAdapterForTable(
            'Random_Table_1', $operation, 'sarasa'
        );
        $config2 = $adapter2->getConfig();
        $this->assertEquals(
            $config['username'],
            $config2['username']
        );

        $adapter = $this->_multidb->getAdapterForTable(
            'Random_Master_Table', $operation, 'asd'
        );
        $config = $adapter->getConfig();
        $this->assertEquals(
            $this->_config['adapters']['test_adapter_rw']['username'],
            $config['username']
        );
    }

    /**
     * Test getShardsForValues.
     *
     * @return void
     */
    public function testGetShardsForValues()
    {
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY,
            $this->_multidb->getShardsForValues(
                'Random_Master_Table',
                array(1, 2, 3)
            )
        );
    }



    /**
     * Test what happens when no config or invalid config is set.
     *
     * @return void
     */
    public function testMissingConfig()
    {
        $config = array(
            'adapters' => array(),
            'tables' => array(),
            'shards' => array()
        );
        $multidb = new ZendExt_Application_Resource_Multidb(
            $config
        );
        $multidb->init();

        $this->assertNull(
            $multidb->getDefaultAdapterForTable('foo')
        );

        $this->assertNull(
            $multidb->getAdapterForTable(
                'foo',
                ZendExt_Application_Resource_Multidb::OPERATION_READ,
                'asd'
            )
        );

        $this->assertNull(
            $multidb->getShardsForValues('foo', array(1))
        );

        $config = array(
            'adapters' => array(),
            'tables' => array(
                'foo' => 'bar'
            ),
            'shards' => array()
        );
        $multidb = new ZendExt_Application_Resource_Multidb(
            $config
        );
        $multidb->init();

        $this->assertNull(
            $multidb->getAdapterForTableShard(
                'foo',
                1,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        $this->assertNull(
            $multidb->getDefaultAdapterForTable(
                'foo'
            )
        );

        $config = array(
            'adapters' => array(),
            'tables' => array(
                'foo' => 'bar'
            ),
            'shards' => array(
                'bar' => array()
            )
        );
        $multidb = new ZendExt_Application_Resource_Multidb(
            $config
        );
        $multidb->init();

        $this->assertNull(
            $multidb->getDefaultAdapterForTable(
                'foo'
            )
        );
        $this->assertNull(
            $multidb->getAdapterForTableShard(
                'foo',
                1,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        $config = array(
            'adapters' => array(),
            'tables' => array(
                'foo' => 'bar'
            ),
            'shards' => array(
                'bar' => array(
                    'r' => array()
                )
            )
        );
        $multidb = new ZendExt_Application_Resource_Multidb(
            $config
        );
        $multidb->init();

        $this->assertNull(
            $multidb->getAdapterForTableShard(
                'foo',
                1,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        $this->assertNull(
            $multidb->getAdapterForTable(
                'foo',
                ZendExt_Application_Resource_Multidb::OPERATION_READ,
                1
            )
        );
    }

    /**
     * Test whether an exception is thrown when an invalid operation is used.
     *
     * @return void
     */
    public function testInvalidOperation()
    {
        try {
            $this->_multidb->getAdapterForTable('Random_Table_1', 'foo', 'bar');
            $this->fail();
        } catch (Zend_Application_Resource_Exception $e) {
        }
    }
}
