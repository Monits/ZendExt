<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Data persistance utility for cron tasks.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Data persistance utility for cron tasks.
 *
 * @category  ZendExt
 * @package   ZendExt_Cron
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Cron_Persistance
{

    protected $_dataDir = 'data/';

    /**
     * The name of the process running.
     *
     * @var string
     */
    protected $_process = null;

    /**
     * Construct a new isntance.
     *
     * @param string $process The name of the process that uses the instance.
     * @param string $dataDir The path to the data directory.
     *
     * @return void
     */
    public function __construct($process, $dataDir = null)
    {
        $this->_process = $process;

        if (null !== $dataDir) {

            $this->_dataDir = $dataDir;
        }
    }

    /**
     * Persist data so a process can re use it later on.
     *
     * @param string $name An identifier for the data.
     * @param object $data The data object to persist.
     *
     * @return void
     */
    public function persist($name, $data)
    {
        $fileName = $this->_getPath($name);
        $fileDir = dirname($fileName);

        if ( !is_dir($fileDir) ) {

            mkdir($fileDir, 0744, true);
        }

        file_put_contents($fileName, serialize($data));
    }

    /**
     * Retrieve persisted data.
     *
     * @param string $name The data identifier.
     *
     * @return object The persisted data object
     *
     * @throws ZendExt_Exception
     */
    public function retrieve($name)
    {

        $fileName = $this->_getPath($name);

        if ( !$this->isPersisted($name) ) {

            throw new ZendExt_Exception(
                'Data persistance file '.$fileName.' not found.'
            );
        }

        return unserialize(file_get_contents($fileName));
    }

    /**
     * Check whether data is persisted.
     *
     * @param string $name The data identifier.
     *
     * @return boolean
     */
    public function isPersisted($name)
    {
        return file_exists($this->_getPath($name));
    }

    /**
     * Get the path for a given name.
     *
     * @param string $name The name.
     *
     * @return string
     */
    protected function _getPath($name)
    {
        return $this->_dataDir.$this->_process.'/'.$name.'.dat';
    }

    /**
     * Get the name of the current process.
     *
     * @return string
     */
    public function getCurrentProcess()
    {
        return $this->_process;
    }

    /**
     * Get the path to the data directory.
     *
     * @return string
     */
    public function getDataDirectory()
    {
        return $this->_dataDir;
    }
}
