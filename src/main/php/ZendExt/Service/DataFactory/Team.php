<?php 
/*

   Copyright 2011 Monits
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
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