<?php
/**
 * Storage object for loggers.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Storage object for loggers.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      htttp://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Cron_Log
{

    /**
     * Launcher log.
     *
     * @var Zend_Log
     */
    private static $_launcher;

    /**
     * Process log.
     *
     * @var Zend_Log
     */
    private static $_process;

    /**
     * Get the logger for the launcher.
     *
     * @return Zend_Log
     */
    public static function getLauncherLog()
    {

        return self::$_launcher;
    }

    /**
     * Get the logger for the process.
     *
     * @return Zend_Log
     */
    public static function getProcessLog()
    {

        return self::$_process;
    }

    /**
     * Set the logger for the process.
     *
     * @param Zend_Log $logger The logger instance.
     *
     * @return void
     */
    public static function setProcessLog(Zend_Log $logger)
    {

        self::$_process = $logger;
    }

    /**
     * Set the logger for the launcher.
     *
     * @param Zend_Log $logger The logger instance.
     *
     * @return void
     */
    public static function setLauncherLog(Zend_Log $logger)
    {

        self::$_launcher = $logger;
    }
}
