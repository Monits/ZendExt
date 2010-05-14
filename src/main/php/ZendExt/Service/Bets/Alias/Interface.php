<?php
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