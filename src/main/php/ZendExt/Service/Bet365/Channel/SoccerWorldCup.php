<?php
/**
 * Channel for the soccer world cup on Bet365.com
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bet365_Channel
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
 * @package   ZendExt_Service_Bet365_Channel
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Service_Bet365_Channel_SoccerWorldCup
{

    /**
     * @var ZendExt_Service_Bet365_Channel_Parser
     */
    private $_parser;

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
        return $this->_parser->getMatchPayback($local, $visitor);
    }

    /**
     * Sets the channel parser.
     *
     * @param ZendExt_Service_Bets_Parser_Interface $parser The parser.
     *
     * @return void
     */
    public function setParser(ZendExt_Service_Bet365_Channel_Parser $parser)
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