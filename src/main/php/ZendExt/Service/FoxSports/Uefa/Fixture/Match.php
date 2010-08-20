<?php
/**
 * FoxSports uefa fixture match.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_FoxSports_Uefa_Fixture
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * FoxSports uefa fixture match.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_FoxSports_Uefa_Fixture
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Service_FoxSports_Uefa_Fixture_Match
{
    const OUTCOME_HOME_WON = 'home';
    const OUTCOME_AWAY_WON = 'away';
    const OUTCOME_TIE = 'tie';
    
    private $_away;
    private $_home;
    private $_outcome;
    private $_stadium;
    
    /**
     * Instanciates a new fixture match.
     * 
     * @param string $away    The match's away team.
     * @param string $home 	  The match's home team.
     * @param string $outcome The match's outcome.
     * @param string $stadium The match's stadium.
     * 
     * @throws ZendExt_Exception
     * 
     * @return ZendExt_Service_FoxSports_Uefa_Fixture_Match
     */
    public function __construct($away = null, $home = null, $outcome = null,
        $stadium = null)
    {
        if (null !== $outcome) {
            $validOutcome = in_array(
                $outcome,
                array(
                    self::OUTCOME_AWAY_WON,
                    self::OUTCOME_HOME_WON,
                    self::OUTCOME_TIE
                )
            );
            
            if (!$validOutcome) {
                throw new ZendExt_Exception('The given outcome is not valid.');
            }
        }
        
        $this->_away = $away;
        $this->_home = $home;
        $this->_outcome = $outcome;
        $this->_stadium = $stadium;
    }
    
    /**
     * Retrieves the match's home team.
     * 
     * @return string
     */
    public function getHome()
    {
        return $this->_home;
    }
    
    /**
     * Retrieves the match's away team.
     *
     * @return string
     */
    public function getAway()
    {
        return $this->_away;
    }
    
    /**
     * Retrieves the match outcome.
     * 
     * @return string
     */
    public function getOutcome()
    {
        return $this->_outcome;
    }
    
    /**
     * Retrieves the match stadium.
     * 
     * @return string
     */
    public function getStadium()
    {
        return $this->_stadium;
    }
}
