<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * DataFactory match model.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * DataFactory match model.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com
 * @since     1.0.0
 */
class ZendExt_Service_DataFactory_Match
{
    private $_local;

    private $_visitor;

    private $_datetime;

    private $_isFinished;

    private $_stadium;

    private $_group = null;

    private $_roundNumber;

    private $_number;

    /**
     * Create a new instance.
     *
     * @param array $data array with the data to populate the instance.
     */
    public function __construct(array $data)
    {
        $keys = array(
                    'local',
                    'visitor',
                    'datetime',
                    'isFinished',
                    'stadium',
                    'group',
                    'roundNumber',
                    'number'
                );

        foreach ($keys as $key) {

            $attr = '_'.$key;
            if (isset($data[$key])) {

                $this->$attr = $data[$key];
            } else {

                $this->$attr = null;
            }
        }

        if ( $this->_local ) {

            $this->_local =
                new ZendExt_Service_DataFactory_Team($this->_local);
        }

        if ( $this->_visitor ) {

            $this->_visitor =
                new ZendExt_Service_DataFactory_Team($this->_visitor);
        }
    }

    /**
     * Get the local team data.
     *
     * @return ZendExt_Service_DataFactory_Team
     */
    public function getLocal()
    {
        return $this->_local;
    }

    /**
     * Get the visitor team data.
     *
     * @return ZendExt_Service_DataFactory_Team
     */
    public function getVisitor()
    {
        return $this->_visitor;
    }

    /**
     * Get the match datetime in MySQL compatible format.
     *
     * @return string
     */
    public function getDateTime()
    {
        return $this->_datetime;
    }

    /**
     * Whether the match has finished or not.
     *
     * @return boolean
     */
    public function isFinished()
    {
        return $this->_isFinished;
    }

    /**
     * Get the stadium name.
     *
     * @return string
     */
    public function getStadiumName()
    {
        return $this->_stadium;
    }

    /**
     * Get the group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->_group;
    }

    /**
     * Get the round number.
     *
     * @return integer
     */
    public function getRoundNumber()
    {
        return $this->_roundNumber;
    }

    /**
     * Get the match number.
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->_number;
    }
}
