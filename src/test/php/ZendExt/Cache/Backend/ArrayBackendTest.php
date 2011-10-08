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
 * Unit Test for the Array Cache Backend.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Unit Test for the Array Cache Backend.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class Zend_Cache_ArrayBackendTest extends Zend_Cache_CommonBackendTest
{
    /**
     * Build a new instance of the test.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('ZendExt_Cache_Backend_Array');
    }

    /**
     * Set up the test enviorment.
     *
     * @param boolean $notag Whether the backend has tags or not.
     *
     * @return void
     */
    public function setUp($notag = true)
    {
        $this->_instance = new ZendExt_Cache_Backend_Array(array());
        parent::setUp($notag);
    }

    /**
     * Tear down the test enviorment.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->_instance);
    }

    /**
     * Test for correct construction.
     *
     * @return void
     */
    public function testConstructorCorrectCall()
    {
        $test = new ZendExt_Cache_Backend_Array();
    }

    /**
     * Test the old clean mode.
     *
     * @return void
     */
    public function testCleanModeOld()
    {
        $this->_instance->setDirectives(array('logging' => false));
        $this->_instance->clean('old');
        // do nothing, just to see if an error occured
        $this->_instance->setDirectives(array('logging' => true));
    }

    /**
     * Test asking for tags fails silently
     *
     * @return void
     */
    public function testCleanModeMatchingTags()
    {
        $this->_instance->setDirectives(array('logging' => false));
        $this->_instance->clean('matchingTag', array('tag1'));
        // do nothing, just to see if an error occured
        $this->_instance->setDirectives(array('logging' => true));
    }

    /**
     * Test asking for tags fails silently
     *
     * @return void
     */
    public function testCleanModeNotMatchingTags()
    {
        $this->_instance->setDirectives(array('logging' => false));
        $this->_instance->clean('notMatchingTag', array('tag1'));
        // do nothing, just to see if an error occured
        $this->_instance->setDirectives(array('logging' => true));
    }

    /**
     * Stub to prevent unwanted inherited tests.
     *
     * @return void
     */
    public function testGetWithAnExpiredCacheId()
    {
    }

    /**
     * Stub to prevent unwanted inherited tests.
     *
     * @return void
     */
    public function testCleanModeMatchingTags2()
    {
    }

    /**
     * Stub to prevent unwanted inherited tests.
     *
     * @return void
     */
    public function testCleanModeNotMatchingTags2()
    {
    }

    /**
     * Stub to prevent unwanted inherited tests.
     *
     * @return void
     */
    public function testCleanModeNotMatchingTags3()
    {
    }
}