<?php
/**
 * Interface for Bet365.com service's channels.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel
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
 * Interface for Bet365.com service's channels.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Bet365_Channel
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
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