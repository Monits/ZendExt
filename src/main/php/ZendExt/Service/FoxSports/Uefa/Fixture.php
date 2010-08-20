<?php
/**
 * FoxSports uefa fixture parsing service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_FoxSports_Uefa
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * FoxSports uefa fixture parsing service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_FoxSports_Uefa
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Service_FoxSports_Uefa_Fixture
{
    
    const FEED_URL = 'http://msn.foxsports.com/nugget/28500_chlg';
    
    /**
     * Retrieves an array of matches.
     * 
     * @return array 
     */
    public static function getMatches()
    {
        $ch = curl_init(self::FEED_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $dom = new DOMDocument();
        $dom->loadXML($response);
        
        return self::_parseFixture($dom);
        
    }
    
    /**
     * Parse a fixture.
     *
     * @param DOMDocument $doc The XML document DOM
     *
     * @return array
     */
    private static function _parseFixture(DOMDocument $doc)
    {
        $ret = array();
        $schedules = $doc->getElementsByTagName('schedules')->item(0);
        
        foreach ($schedules->getElementsByTagName('game-schedule') as $game) {
            
            $d = $game->getElementsByTagName('date')->item(0);
            $t = $game->getElementsByTagName('time')->item(0);
            
            $tmp = $d->getAttribute('year') . '-'
                . $d->getAttribute('month') . '-'
                . $d->getAttribute('date')
                . ' ' . $t->getAttribute('hour')
                . ':' . $t->getAttribute('minute') . ':00';
                
            $dObj = new Zend_Date($tmp);
            $dObj->setTimezone('UTC');
            
            $date = $dObj->toString('YYYY-MM-dd HH:mm:ss');
            
            $stadium = $game->getElementsByTagName('stadium')->item(0)
                ->getAttribute('name');
            
            $home = $game->getElementsByTagName('home-team')->item(0);
            $homeTeam = $home->getElementsByTagName('team-info')->item(0)
                ->getAttribute('display-name');
            
            $away = $game->getElementsByTagName('visiting-team')->item(0);
            $awayTeam = $away->getElementsByTagName('team-info')->item(0)
                ->getAttribute('display-name');
            
            $outcome = $home->getElementsByTagName('outcome')->item(0);
            
            $matchOutcome = null;
            
            if (null !== $outcome) {
                switch ($outcome->getAttribute('outcome')) {
                    case 'Win':
                        $matchOutcome =
                            ZendExt_Service_FoxSports_Uefa_Fixture_Match::OUTCOME_HOME_WON;
                        break;
                    case 'Loss':
                        $matchOutcome =
                            ZendExt_Service_FoxSports_Uefa_Fixture_Match::OUTCOME_AWAY_WON;
                        break;
                    case 'Tie':
                        $matchOutcome =
                            ZendExt_Service_FoxSports_Uefa_Fixture_Match::OUTCOME_TIE;
                }
            }
            
            $ret[] = new ZendExt_Service_FoxSports_Uefa_Fixture_Match(
                $awayTeam, $homeTeam, $matchOutcome, $stadium
            );
        }
        
        return $ret;
    }
    
}