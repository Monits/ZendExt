<?php
/**
 * Cache backend implemented using memcached with support for multiget.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Cache backend implemented using memcached with support for multiget.
 *
 * @category  ZendExt
 * @package   ZendExt_Cache_Backend
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Cache_Backend_Memcached extends Zend_Cache_Backend_Memcached
{

    /**
     * Test if a cache is available for the given id and return it.
     *
     * @param string  $id                     Cache id
     * @param boolean $doNotTestCacheValidity If set to true, the cache
     *                                        validity won't be tested
     *
     * @return string|false cached datas, or false if not found
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $values = $this->_memcache->get($id);
        if (is_array($id) && is_array($values)) {

            $ret = array();
            foreach ($values as $key => $value) {

                $ret[$key] = $value[0];
            }

            return $ret;
        } else if (is_array($values)) {

            return $values[0];
        }

        return false;
    }
}
