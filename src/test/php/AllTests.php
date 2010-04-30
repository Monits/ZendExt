<?php
/**
 * Runs all tests for ZendExt.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

require_once 'PHPUnit/Framework.php';

/**
 * Runs all tests for ZendExt.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class AllTests
{
    /**
     * Configures all test suites.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HStore');

        // TODO : Add suites!!

        return $suite;
    }
}