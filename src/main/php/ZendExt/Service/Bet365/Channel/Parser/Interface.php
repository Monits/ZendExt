<?php
/**
 * Bets service channels' parser's interface.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bet365_Channel_Parser
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
 * @package   ZendExt_Service_Bet365_Channel_Parser
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
interface ZendExt_Service_Bet365_Channel_Parser_Interface
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