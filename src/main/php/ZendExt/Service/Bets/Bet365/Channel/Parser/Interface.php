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
 * Bets service channels' parser's interface.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel_Parser
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Bets service channels' parser's interface.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel_Parser
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
interface ZendExt_Service_Bets_Bet365_Channel_Parser_Interface
{
    /**
     * Sets the text to be parsed.
     *
     * @param string $text The text.
     *
     * @return void
     */
    public function setInputText($text);

}