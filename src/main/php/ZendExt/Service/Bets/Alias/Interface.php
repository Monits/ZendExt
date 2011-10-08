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
 * Defines a set of aliases for a given competition.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Alias
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Defines a set of aliases for a given competition.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Alias
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2010 company
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
interface ZendExt_Service_Bets_Alias_Interface
{
    /**
     * Retrieves the known aliases for a given code.
     *
     * @param string $code The code for which to retrieve aliases.
     *
     * @return array
     */
    public function getAliasesFor($code);
}