<?php
/**
 * ZendExt_Console_Getopt is a class to parse options for command-line
 * applications.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * ZendExt_Console_Getopt is a class to parse options for command-line
 * applications.
 *
 * @category  ZendExt
 * @package   ZendExt_Console
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 * @see Zend_Console_Getopt
 */
class ZendExt_Console_Getopt extends Zend_Console_Getopt
{

    /**
     * Parse command-line arguments for a single option.
     * 
     * Extended so not defined options are ignored instead
     * of make everything explode.
     *
     * @param  string $flag
     * @param  mixed  $argv
     * 
     * @throws Zend_Console_Getopt_Exception
     * 
     * @return void
     */
    protected function _parseSingleOption($flag, &$argv)
    {
        if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
            $flag = strtolower($flag);
        }
        
        if (isset($this->_ruleMap[$flag])) {
                parent::_parseSingleOption($flag, $argv);
        }
    }
    
}