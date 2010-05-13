<?php
/**
 * Unit Test for ZendExt_Versioner.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
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

}