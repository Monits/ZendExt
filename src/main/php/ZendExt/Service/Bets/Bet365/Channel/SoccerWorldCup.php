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
 * Channel for the soccer world cup on Bet365.com
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Channel for the soccer world cup on Bet365.com
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
class ZendExt_Service_Bets_Bet365_Channel_SoccerWorldCup
{

    /**
     * @var ZendExt_Service_Bets_Bet365_Channel_Parser
     */
    private $_parser;

    /**
     * @var ZendExt_Service_Bets_Alias_Interface
     */
    private $_aliases;

    /**
     * Creates a new SoccerWorldCup for Bet365.
     *
     * @return ZendExt_Service_Bets_Bet365_Channel_SoccerWorldCup
     */
    public function __construct()
    {
        $this->_aliases = new ZendExt_Service_Bets_Alias_SoccerWorldCup();
    }

    /**
     * Retrieves the cookies needed in the request.
     *
     * @return string
     */
    public function getRawCookies()
    {
        return null;
    }

    /**
     * Retrieves the url to request.
     *
     * Receives the match teams in case the url depends on them.
     *
     * @return string
     */
    public function getUrl()
    {
        return null;
    }

    /**
     * Retrieves the method used in the request.
     *
     * @return string
     */
    public function getMethod()
    {
        return null;
    }

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
    public function getMatchPayback($local, $visitor)
    {
        /*
         * We don't really know which aliases may be in use,
         * so we brute-force them. Not nice, but should be less than
         * 10 combinations.
         */
        foreach ($this->_aliases->getAliasesFor($visitor) as $v) {
            foreach ($this->_aliases->getAliasesFor($local) as $l) {
                $ret = $this->_parser->getMatchPayback($l, $v);

                if (null !== $ret) {
                    return $ret;
                } else {
                    // If it's null maybe the teams are swapped on bet365.com
                    $ret = $this->_parser->getMatchPayback($v, $l);

                    if (null !== $ret) {
                        $local = $ret['1'];
                        $ret['1'] = $ret['2'];
                        $ret['2'] = $local;

                        return $ret;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Sets the channel parser.
     *
     * @param ZendExt_Service_Bets_Parser_Interface $parser The parser.
     *
     * @return void
     */
    public function setParser(
            ZendExt_Service_Bets_Bet365_Channel_Parser $parser)
    {
        $this->_parser = $parser;
    }

    /**
     * Retrieves the post data to be sent.
     *
     * @return string
     */
    public function getRawPostData()
    {
        return null;
    }

}