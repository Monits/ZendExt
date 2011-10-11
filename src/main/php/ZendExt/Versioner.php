<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Keeps track of file versions.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Keeps track of file versions.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Versioner
{
    private $_cache;

    private $_keyPrefix = 'zversioner_';

    private $_checkInterval = 14400; // 4 hours

    private $_updateDelayTime = 1800; // 30 minutes

    private $_time = null;

    private $_files = array();

    /**
     * Create a new Zend_Versioner instance.
     *
     * @param Zend_Cache_Core $cache           Instance of Zend_Cache frontend
     *                                         to keep track of version data.
     * @param integer         $checkInterval   The seconds that must elapse
     *                                         before checking for updates.
     * @param integer         $updateDelayTime The seconds between a file
     *                                         modification and version update.
     * @param string          $keyPrefix       The prefix to use for cache.
     */
    public function __construct(Zend_Cache_Core $cache, $checkInterval = null,
        $updateDelayTime = null, $keyPrefix = null)
    {
        $this->_cache = $cache;
        $this->_time = time();

        if ($keyPrefix !== null) {
            $this->_keyPrefix = $keyPrefix;
        }

        if ($checkInterval !== null) {
            $this->_checkInterval = $checkInterval;
        }

        if ($updateDelayTime !== null) {
            $this->_updateDelayTime = $updateDelayTime;
        }
    }

    /**
     * Set a Zend_Cache frontend instance.
     *
     * @param Zend_Cache_Core $cache The cache instance to use.
     *
     * @return void
     */
    public function setCache(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Get the current Zend_Cache frontend instance being used.
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Set the key prefix to use in cache entries.
     *
     * You should only modify this before the class is used in any way.
     * And even then, it's discouraged to do so.
     *
     * @param string $prefix The prefix to set.
     *
     * @return void
     */
    public function setKeyPrefix($prefix)
    {
        $this->_keyPrefix = $prefix;
    }

    /**
     * Get the key prefix being used for cache entries.
     *
     * @return string
     */
    public function getKeyPrefix()
    {
        return $this->_keyPrefix;
    }

    /**
     * Set the interval between update checks.
     *
     * @param integer $checkInterval The interval to set.
     *
     * @return void
     */
    public function setCheckInterval($checkInterval)
    {
        $this->_checkInterval = $checkInterval;
    }

    /**
     * Get the interval between update checks.
     *
     * @return integer
     */
    public function getCheckInterval()
    {
        return $this->_checkInterval;
    }

    /**
     * Set the time that must elapse before a version is increased.
     *
     * This refers to the time after a file is modified.
     *
     * @param integer $updateDelayTime The time in seconds.
     *
     * @return void
     */
    public function setUpdateDelayTime($updateDelayTime)
    {
        $this->_updateDelayTime = $updateDelayTime;
    }

    /**
     * Get the time that must elapse before a version is increased.
     *
     * This refers to the time after a file is modified.
     *
     * @return integer
     */
    public function getUpdateDelayTime()
    {
        return $this->_updateDelayTime;
    }

    /**
     * Get the version for a given file.
     *
     * @param string $fileName The filename of the file to get the version of.
     *
     * @return integer
     */
    public function getFileVersion($fileName)
    {
        if (!isset($this->_files[$fileName])) {
            $fileData = $this->_getFileData($fileName);

            //See if it's time to check for new versions.
            $check = $fileData['lastCheckTime'] + $this->_checkInterval;
            if ($this->_time > $check) {

                if (!file_exists($fileName)) {
                    //This behaviour doesnt feel right
                    //but neither does throwing an exception
                    return 0;
                }

                $fileTime = filemtime($fileName);

                //If the elapsed time since the time was updated is big enough
                //increase the version number.
                if ($fileTime > $fileData['lastCheckTime']) {

                    if (($fileTime + $this->_updateDelayTime) < $this->_time) {

                        $fileData['version']++;
                        $fileData['lastCheckTime'] = $this->_time;
                    }

                    //We don't update the check time if the above condition is
                    //not meet because it means that the file was updated
                    //But we still cant say it was.
                } else {
                    //We make sure that the check time is updated
                    //to avoid unnecesary checking
                    $fileData['lastCheckTime'] = $this->_time;
                }

                $this->_setFileData($fileName, $fileData);
            }
        }

        return $this->_files[$fileName]['version'];
    }

    /**
     * Load the file's data from internal or external cache.
     *
     * @param string $fileName The file's name.
     *
     * @return array An array containing lastCheckTime and version
     */
    private function _getFileData($fileName)
    {
        if (!isset($this->_files[$fileName])) {
            $result = $this->_cache->load(
                $this->_keyPrefix . $this->_normalizeFilename($fileName)
            );

            if ($result === false) {
                $result = array(
                    'lastCheckTime' => $this->_time,
                    'version' => 1
                );

                $this->_setFileData($fileName, $result);
            } else {
                $this->_files[$fileName] = $result;
            }
        }

        return $this->_files[$fileName];
    }

    /**
     * Set a file's data to both caches.
     *
     * @param string $fileName The file's name.
     * @param array  $data     An array with fields lastCheckTime and version.
     *
     * @return void
     */
    private function _setFileData($fileName, array $data)
    {
        $this->_files[$fileName] = $data;
        $this->_cache->save(
            $data,
            $this->_keyPrefix . $this->_normalizeFilename($fileName)
        );
    }

    /**
     * Turn a filename into a cache-friendly string.
     *
     * @param string $fileName The filename to make cache-friendly.
     *
     * @return string
     */
    private function _normalizeFilename($fileName)
    {
        return md5($fileName);
    }

    /**
     * Clears the intermediate cache.
     *
     * THIS METHOD IS FOR TESTING ONLY!!!
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->_files = array();
    }
}