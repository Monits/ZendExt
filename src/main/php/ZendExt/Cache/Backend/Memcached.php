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
 * Cache backend implemented using memcached with support for multiget.
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
 * Cache backend implemented using memcached with support for multiget.
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
