<?php
/**
 * DataFactory fixture parser.
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
 * DataFactory fixture parser.
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
class ZendExt_Service_DataFactory_Fixture
{

    const LOCAL = 'local';

    const VISITOR = 'visitante';

    const CHANNEL_NAME = 'deportes.futbol.mundial.fixture';

    private static $_shortCode = array(
        'Argelia'         => 'DZA',
        'Eslovaquia'      => 'SVK',
        'Sudáfrica'       => 'RSA',
        'Inglaterra'      => 'ENG',
        'Eslovenia'       => 'SVN',
        'Alemania'        => 'GER',
        'Dinamarca'       => 'DEN',
        'Holanda'         => 'NED',
        'Eslovaquia'      => 'SVK',
        'Corea del Norte' => 'PRK',
        'Costa de Marfil' => 'CIV'
    );

    private static $_teamNames = array(
        'Nueva Zelanda' => 'N. Zelanda'
    );

    const STATE_FINISHED = 'Finalizado';

    private $_matches;

    /**
     * Instance a new Fixture with the data parsed from the XML
     *
     * @param string $xml The XML to parse.
     */
    public function __construct($xml)
    {

        $this->_matches = $this->_parseFixture(DOMDocument::loadXML($xml));
    }

    /**
     * Parse a fixture.
     *
     * @param DOMDocument $doc The XML document DOM
     *
     * @return array
     */
    private function _parseFixture(DOMDocument $doc)
    {
        $result = array();
        $dates = $doc->getElementsByTagName('fecha');

        foreach ($dates as $date) {

            $group = $date->getAttribute('nombre');
            if (substr($group, 0, -2) == 'Grupo') {

                $group = substr($group, -1);
            } else {

                $group = null;
            }
            $roundNumber = $date->getAttribute('nivel');

            $matches = $date->getElementsByTagName('partido');
            foreach ($matches as $match) {

                $matchDate = $match->getAttribute('fecha');
                $matchTime = $match->getAttribute('hora');

                $timestamp = new Zend_Date(
                    $matchDate,
                    ZendExt_Service_DataFactory::DATE_FORMAT
                );
                $timestamp->setTime(
                    $matchTime,
                    ZendExt_Service_DataFactory::TIME_FORMAT
                );

                $state = $match->getElementsByTagName('estado')
                    ->item(0)->nodeValue;
                $isFinished = $state == self::STATE_FINISHED;

                $number = $match->getAttribute('nro');

                $stadium = $match->getAttribute('nombreEstadio');

                $local = $this->_getTeamData($match, self::LOCAL);
                $visitor = $this->_getTeamData($match, self::VISITOR);
                $data = array(
                            'group' => $group,
                            'roundNumber' => $roundNumber,
                            'timestamp' => $timestamp->getTimestamp(),
                            'isFinished' => $isFinished,
                            'local' => $local,
                            'visitor' => $visitor,
                            'stadium' => $stadium,
                            'number' => $number
                        );

                $result[] = new ZendExt_Service_DataFactory_Match($data);
            }
        }

        return $result;
    }

    /**
     * Retrieve team name, goals and penalty goals, for either team.
     *
     * @param DOMElement $match The node that has the data.
     * @param string     $team  Either 'local' or 'visitante'
     *
     * @return array
     */
    private function _getTeamData(DOMElement $match, $team)
    {
        $teamNode = $match->getElementsByTagName($team)->item(0);
        $name = $this->_transformTeamName($teamNode->nodeValue);
        $goals = $match->getElementsByTagName('goles'.$team)
            ->item(0)->nodeValue;
        $penaltyGoals = $match->getElementsByTagName('golesDefPenales'.$team)
            ->item(0)->nodeValue;
        $shortName = $teamNode->getAttribute('paisSigla');
        if ($shortName === '') {

            $shortName = null;
            $code = null;
        } else {

            $code = $this->_getCodeFromName($name, $shortName);
        }



        return array(
                   'name' => $name,
                   'goals' => $goals,
                   'penaltyGoals' => $penaltyGoals,
                   'code' => $code,
                   'shortName' => $shortName
               );
    }

    /**
     * Hackish method to transform short names into ISO country codes.
     *
     * @param string $name      The countrys name.
     * @param string $shortName The countrys short name.
     *
     * @return string The country code.
     */
    private function _getCodeFromName($name, $shortName)
    {
        if ( isset(self::$_shortCode[$name]) ) {

            return self::$_shortCode[$name];
        } else {

            return $shortName;
        }
    }

    /**
     * Return the team name that the app will be using.
     *
     * @param string $name The name to transform.
     *
     * @return string
     */
    private function _transformTeamName($name)
    {
        if (isset(self::$_teamNames[$name])) {

            return self::$_teamNames[$name];
        } else {

            return $name;
        }
    }

    /**
     * Get an array of ZendExt_DataFactory_Service_Match.
     *
     * @return array
     */
    public function getMatchData()
    {
        return $this->_matches;
    }
}
