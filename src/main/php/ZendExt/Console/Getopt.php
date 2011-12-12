<?php
/**
 * ZendExt_Console_Getopt is a class to parse options for command-line
 * applications.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * ZendExt_Console_Getopt is a class to parse options for command-line
 * applications.
 *
 * @category  ZendExt
 * @package   ZendExt_Console
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @see       Zend_Console_Getopt
 * @since     1.3.0
 */
class ZendExt_Console_Getopt extends Zend_Console_Getopt
{

    /**
     * Parse command-line arguments for a single option.
     *
     * Extended so not defined options are ignored instead
     * of make everything explode.
     *
     * @param string $flag  The option to parse.
     * @param mixed  &$argv The array of received options.
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

    /**
     * Retrieves an array from a list of values delimited by commas.
     *
     * @param string $flag The option to parse.
     *
     * @return array
     */
    public function getAsArray($flag)
    {
        $str = $this->$flag;

        if (null === $str) {
            return null;
        }

        return explode(',', $str);
    }

}
