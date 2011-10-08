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
 * Interface for sharding strategies.
 *
 * @category  ZendExt
 * @package   ZendExt_Sharding_Strategy
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Interface for sharding strategies.
 *
 * @category  ZendExt
 * @package   ZendExt_Sharding_Strategy
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
interface ZendExt_Sharding_Strategy_Interface
{
    /**
     * Retrieves the shard to which a given value belongs.
     *
     * @param mixed $value The value by which to apply sharding.
     *
     * @return int The id of the shard to which the given value belongs.
     */
    public function getShard($value);
}