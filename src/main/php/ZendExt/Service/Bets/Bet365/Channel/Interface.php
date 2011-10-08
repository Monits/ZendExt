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
 * Interface for Bet365.com service's channels.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Interface for Bet365.com service's channels.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
interface ZendExt_Service_Bets_Bet365_Channel_Interface
{
    /**
     * Retrieves the cookies needed in the request.
     *
     * @return string
     */
    public function getRawCookies();

    /**
     * Retrieves the url to request.
     *
     * Receives the match teams in case the url depends on them.
     *
     * @param string $local   The local team.
     * @param string $visitor The visitor team.
     *
     * @return string
     */
    public function getUrl($local, $visitor);

    /**
     * Retrieves the method used in the request.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Retrieves the post data to be sent.
     *
     * @return string
     */
    public function getRawPostData();

    /**
     * Retrieves the match payback ratio.
     *
     * @param string $local   The local team code.
     * @param string $visitor The visitor team code.
     *
     * @throws ZendExt_Service_Bets_Exception
     *
     * @return array The ratios are in the keys '1', 'X', '2'.
     */
    public function getMatchPayback($local, $visitor);

    /**
     * Sets the channel parser.
     *
     * @param ZendExt_Service_Bets_Parser_Interface $parser The parser.
     *
     * @return void
     */
    public function setParser(ZendExt_Service_Bets_Parser_Interface $parser);

}