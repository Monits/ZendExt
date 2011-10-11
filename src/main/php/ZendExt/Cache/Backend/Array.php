<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Cache backend implemented using a PHP array.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */


/**
 * Cache backend implemented using a PHP array.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Cache_Backend_Array
    extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{

    private $_values;

    private $_lifetime;

    private $_lastModified;

    /**
     * Constructor.
     *
     * @param array $options Associative array of options.
     *
     * @return void
     *
     * @throws Zend_Cache_Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->_values = array();
        $this->_lifetime = array();
        $this->_lastModified = array();
    }

    /**
     * Test if a cache is available for the given id and return it
     *
     * @param string  $id                     Cache id
     * @param boolean $doNotTestCacheValidity If set to true, the cache validity
     *                                        won't be tested
     *
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if ( isset($this->_values[$id]) ) {

            if ( $doNotTestCacheValidity || !$this->_isExpired($id) ) {

                return $this->_values[$id];
            }
        }

        return false;
    }

    /**
     * Test if a cache is available or not (for the given id).
     *
     * @param string $id The cache id.
     *
     * @return mixed|false (a cache is not available) or "last modified"
     *                     timestamp (int) of the available cache record
     */
    public function test($id)
    {
        return !$this->_isExpired($id) ? $this->_lastModified[$id] : false;
    }

    /**
     * Save some string datas into a cache record.
     *
     * @param string  $data             Datas to cache.
     * @param string  $id               Cache id.
     * @param array   $tags             Array of strings, the cache record will
     *                                  be tagged by each string entry.
     * @param integer $specificLifetime Set a specific lifetime for this cache
     *                                  record (null => infinite lifetime).
     *
     * @return boolean
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if ( $specificLifetime === false ) {

            $lifetime = $this->_directives['lifetime'];
        } else if ( $specificLifetime === null || !is_int($specificLifetime) ) {

            $lifetime = null;
        } else {

            $lifetime = $specificLifetime;
        }

        $this->_values[$id] = $data;
        $this->_lifetime[$id] = $lifetime;
        $this->_lastModified[$id] = time();

        return true;
    }

    /**
     * Remove a cache record.
     *
     * @param string $id Cache id.
     *
     * @return boolean
     */
    public function remove($id)
    {
        if ( isset($this->_values[$id]) ) {

            unset($this->_values[$id]);
            unset($this->_lifetime[$id]);
            unset($this->_lastModified[$id]);

            return true;
        } else {

            return false;
        }
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries.
     * Zend_Cache::CLEANING_MODE_OLD              => remove old cache entries.
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => does nothing.
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove all cache entries.
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove all cache entries.
     *
     * @param string $mode Clean mode.
     * @param array  $tags Array of tags.
     *
     * @return boolean
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL,
        $tags = array())
    {
        switch($mode) {
            case Zend_Cache::CLEANING_MODE_OLD:
                foreach (array_keys($this->_values) as $key) {

                    if ( $this->_isExpired($key) ) {

                        $this->remove($key);
                    }
                }
                break;

            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                /* We dont support tags, so no entry will match */
                ; //This is here so that CodeSniffer wont complain
                break;

            case Zend_Cache::CLEANING_MODE_ALL:
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
            default:
                $this->_values = array();
                $this->_lifetime = array();
                $this->_lastModified = array();
                break;
        }

        return true;
    }

    /**
     * Check if a given key has expired.
     *
     * @param string $id The id to check for expiration.
     *
     * @return boolean
     */
    private function _isExpired($id)
    {
        if ( isset($this->_lifetime[$id]) ) {

            $lifetime = $this->_lifetime[$id];

            if ( $lifetime === null ) {

                return false;
            } else {

                return $lifetime < (time() - $this->_lastModified[$id]);
            }
        }

        return true;
    }
}