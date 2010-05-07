<?php
/**
 * DataFactory team model.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * DataFactory team model.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_DataFactory_Team
{
    private $_name;

    private $_goals;

    private $_penaltyGoals;

    private $_code;

    private $_shortName;

    /**
     * Instance a new team.
     *
     * @param array $data The data to populate the instance with.
     */
    public function __construct(array $data)
    {
        $keys = array(
                    'name',
                    'goals',
                    'penaltyGoals',
                    'shortName',
                    'code'
                );

        foreach ($keys as $key) {

            $attr = '_'.$key;
            if (isset($data[$key])) {

                $this->$attr = $data[$key];
            } else {

                $this->$attr = null;
            }
        }
    }

    /**
     * Get the team's name.
     *
     * @return string
     */
    public function getName()
    {

        return $this->_name;
    }

    /**
     * Get the number of goals scored by the team.
     *
     * @return integer
     */
    public function getGoals()
    {

        return $this->_goals;
    }

    /**
     * Get the number of penalty goals scored by the team.
     *
     * @return integer
     */
    public function getPenaltyGoals()
    {

        return $this->_penaltyGoals;
    }

    /**
     * Get the team's short name.
     *
     * @return string
     */
    public function getShortName()
    {

        return $this->_shortName;
    }

    /**
     * Get the team's code.
     *
     * @return string
     */
    public function getCode()
    {

        return $this->_code;
    }
}