<?php
/**
 * Test case for Multidb resource plugin.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Test case for Multidb resource plugin.
 *
 * @category  ZendExt
 * @package   ZendExt_Application_Resource
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
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
                    'shardingStrategy' => 'ZendExt_Sharding_Strategy_Random',
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

        $this->_multidb = new ZendExt_Application_Resource_Multidb($this->_config);
        $this->_multidb->init();
    }

    /**
     * Test the getShardingStrategy method.
     *
     * @return void
     */
    public function testGetShardingStrategy()
    {
        $this->assertNull($this->_multidb->getShardingStrategy('non-existing-table'));
        $this->assertEquals(
            'ZendExt_Sharding_Strategy_Null',
            $this->_multidb->getShardingStrategy('Random_Table_1')
        );
        $this->assertEquals(
            'ZendExt_Sharding_Strategy_Null',
            $this->_multidb->getShardingStrategy('Random_Table_2')
        );
        $this->assertEquals(
            'ZendExt_Sharding_Strategy_Random',
            $this->_multidb->getShardingStrategy('Random_Master_Table')
        );
    }

    /**
     * Test the getShardDbs method.
     *
     * @return void
     */
    public function testGetShardDbs()
    {
        $this->assertNull(
            $this->_multidb->getShardDbs(
                'non-existing-table', 0,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        try {
            $this->_multidb->getShardDbs(
                'Random_Master_Table', 0,
                'non-existing-operation'
            );

            $this->fail('Could request a shard for a non-existing operation');
        } catch (Zend_Application_Resource_Exception $e) {
            // This is expected
        }

        $this->assertEquals(
            $this->_config['shards']['master']['r'][0],
            $this->_multidb->getShardDbs(
                'Random_Master_Table', 0,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        $this->assertEquals(
            $this->_config['shards']['realshard']['r'][0],
            $this->_multidb->getShardDbs(
                'Random_Table_2', 0,
                ZendExt_Application_Resource_Multidb::OPERATION_READ
            )
        );

        $this->assertEquals(
            $this->_config['shards']['realshard']['w'][0],
            $this->_multidb->getShardDbs(
                'Random_Table_1', 0,
                ZendExt_Application_Resource_Multidb::OPERATION_WRITE
            )
        );
    }

    /**
     * Test the getDefaultShardDbs method.
     *
     * @return void
     */
    public function testGetDefaultShardDbs()
    {
        $this->assertNull(
            $this->_multidb->getDefaultShardDbs(
                'non-existing-table', 0
            )
        );

        $this->assertEquals(
            $this->_config['shards']['realshard']['default'],
            $this->_multidb->getDefaultShardDbs(
                'Random_Table_2', 0
            )
        );

        $this->assertEquals(
            $this->_config['shards']['realshard']['default'],
            $this->_multidb->getDefaultShardDbs(
                'Random_Table_1', 0
            )
        );

        $this->assertEquals(
            $this->_config['shards']['master']['default'],
            $this->_multidb->getDefaultShardDbs(
                'Random_Master_Table', 0
            )
        );
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

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_r']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_w');
        $adapterConfig = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_w']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_default');
        $adapterConfig = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_default']['username'],
            $adapterConfig['username']
        );


        $adapter = $this->_multidb->getDb('test_adapter_rw');
        $adapterConfig = $adapter->getConfig();

        $this->assertEquals(
            $this->_config['adapters']['test_adapter_rw']['username'],
            $adapterConfig['username']
        );
    }
}