<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Unit Test for ZendExt_Versioner.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Unit Test for ZendExt_Versioner.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class VersionerTest extends PHPUnit_Framework_TestCase
{
    private $_cache;

    private $_versioner;

    /**
     * Set it up!.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_cache = Zend_Cache::factory(
            'Core',
            new ZendExt_Cache_Backend_Array(),
            array(
                'automatic_serialization' => true
            )
        );
        $this->_versioner = new ZendExt_Versioner($this->_cache, 1, 1);
        $this->_file = 'testing-foo-bar.js';
    }

    /**
     * Tear down.
     *
     * @return void
     */
    public function tearDown()
    {
        if (file_exists($this->_file)) {

            unlink($this->_file);
        }
    }

    /**
     * Test getting the version of a new file.
     *
     * @return void
     */
    public function testNewFile()
    {
        $version = $this->_versioner->getFileVersion($this->_file);
        $this->assertEquals(1, $version);
    }

    /**
     * Test if versions are persisted to cache.
     *
     * @return void
     */
    public function testVersionPersistance()
    {
        $version = $this->_versioner->getFileVersion($this->_file);

        $this->_versioner->cleanCache();
        clearstatcache();
        $this->assertEquals(
            $version,
            $this->_versioner->getFileVersion($this->_file)
        );
    }

    /**
     * Test if a version is increased in the right conditions.
     *
     * @return void
     */
    public function testVersionIncrease()
    {
        $version = $this->_versioner->getFileVersion($this->_file);
        $this->assertEquals(1, $version);

        $cacheKey = $this->_versioner->getKeyPrefix().md5($this->_file);
        $cacheObj = $this->_cache->load($cacheKey);
        $versionerTime = $cacheObj['lastCheckTime'];
        $cacheObj['lastCheckTime'] = $versionerTime - 10;
        $this->_cache->save($cacheObj, $cacheKey);

        $this->_versioner->cleanCache();
        clearstatcache();

        $this->assertEquals(0, $this->_versioner->getFileVersion($this->_file));

        touch($this->_file, $versionerTime - 5);
        $this->_versioner->cleanCache();
        clearstatcache();

        $this->assertEquals(2, $this->_versioner->getFileVersion($this->_file));

        unlink($this->_file);
    }

    /**
     * Check that after cleaning cache, it all stills works fine.
     *
     * @return void
     */
    public function testFileRemove()
    {
        $version = $this->_versioner->getFileVersion($this->_file);
        $this->assertEquals(1, $version);

        $this->_versioner->cleanCache();
        clearstatcache();
        $this->_cache->remove(
            $this->_versioner->getKeyPrefix().md5($this->_file)
        );

        $this->_versioner->setCheckInterval(100);

        $this->assertEquals(1, $this->_versioner->getFileVersion($this->_file));
    }

    /**
     * Test get/set for key prefix.
     *
     * @return void
     */
    public function testKeyPrefix()
    {
        $value = 'asd';
        $this->_versioner->setKeyPrefix($value);
        $this->assertEquals($value, $this->_versioner->getKeyPrefix());
    }

    /**
     * Test get/set for check interval.
     *
     * @return void
     */
    public function testCheckInterval()
    {
        $value = 100;
        $this->_versioner->setCheckInterval($value);
        $this->assertEquals($value, $this->_versioner->getCheckInterval());
    }

    /**
     * Test get/set for update delay time.
     *
     * @return void
     */
    public function testUpdateDelay()
    {
        $value = 100;
        $this->_versioner->setUpdateDelayTime($value);
        $this->assertEquals($value, $this->_versioner->getUpdateDelayTime());
    }

    /**
     * Test get/set for cache.
     *
     * @return void
     */
    public function testCache()
    {
        $this->assertEquals($this->_cache, $this->_versioner->getCache());
        $this->_versioner->setCache($this->_cache);
        $this->assertEquals($this->_cache, $this->_versioner->getCache());
    }
}