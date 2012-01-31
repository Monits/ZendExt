<?php
/**
 * DataFactory fixture parser.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * DataFactory fixture parser.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Service_DataFactory_Fixture
{

    const LOCAL = 'local';

    const VISITOR = 'visitante';

    const CHANNEL_NAME = 'deportes.futbol.mundial.fixture';

    private static $_shortCode = array(
        'Argelia'         => 'DZA',
        'Eslovaquia'      => 'SVK'
    );

    private static $_shortNames = array(
        'Argelia'         => 'ALG',
        'Sudáfrica'       => 'RSA',
        'Inglaterra'      => 'ENG',
        'Eslovenia'       => 'SVN',
        'Alemania'        => 'GER',
        'Dinamarca'       => 'DEN',
        'Eslovaquia'      => 'SVK',
        'Holanda'         => 'NED',
        'Corea del Norte' => 'PRK',
        'Costa de Marfil' => 'CIV'
    );

    private static $_teamNames = array(
        'Nueva Zelanda'   => 'New Zeland',
        'México'          => 'Mexico',
        'Francia'         => 'France',
        'Uruguay'         => 'Uruguay',
        'Sudáfrica'       => 'South Africa',
        'Grecia'          => 'Greece',
        'Nigeria'         => 'Nigeria',
        'Corea del Sur'   => 'Korea Rep.',
        'Argentina'       => 'Argentina',
        'Estados Unidos'  => 'USA',
        'Eslovenia'       => 'Slovenia',
        'Argelia'         => 'Algeria',
        'Inglaterra'      => 'England',
        'Ghana'           => 'Ghana',
        'Australia'       => 'Australia',
        'Serbia'          => 'Serbia',
        'Alemania'        => 'Germany',
        'Dinamarca'       => 'Denmark',
        'Camerún'         => 'Cameroon',
        'Japón'           => 'Japan',
        'Holanda'         => 'Netherlands',
        'Paraguay'        => 'Paraguay',
        'Eslovaquia'      => 'Slovakia',
        'Italia'          => 'Italy',
        'Portugal'        => 'Portugal',
        'Corea del Norte' => 'Korea DPR',
        'Costa de Marfil' => 'Côte d\'Ivoire',
        'Brasil'          => 'Brazil',
        'Chile'           => 'Chile',
        'Suiza'           => 'Switzerland',
        'Honduras'        => 'Honduras',
        'España'          => 'Spain',
        '1º Grupo A'      => '1º Group A',
        '2º Grupo A'      => '2º Group A',
        '1º Grupo B'      => '1º Group B',
        '2º Grupo B'      => '2º Group B',
        '1º Grupo C'      => '1º Group C',
        '2º Grupo C'      => '2º Group C',
        '1º Grupo D'      => '1º Group D',
        '2º Grupo D'      => '2º Group D',
        '1º Grupo E'      => '1º Group E',
        '2º Grupo E'      => '2º Group E',
        '1º Grupo F'      => '1º Group F',
        '2º Grupo F'      => '2º Group F',
        '1º Grupo G'      => '1º Group G',
        '2º Grupo G'      => '2º Group G',
        '1º Grupo H'      => '1º Group H',
        '2º Grupo H'      => '2º Group H'
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

        $tz = $doc->getElementsByTagName('horaActual')
            ->item(0)->getAttribute('gmt');

        $dates = $doc->getElementsByTagName('fecha');

        $tz = $doc->getElementsByTagName('horaActual')
            ->item(0)->getAttribute('gmt');


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

                $datetime = new DateTime($matchDate.' '.$matchTime.' '.$tz);
                $datetime->setTimezone(new DateTimeZone('UTC'));

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
                            'datetime' => $datetime->format('Y-m-d H:i:s'),
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
        $name = trim($teamNode->nodeValue);
        $officialName = $this->_transformTeamName($name);
        $goals = $match->getElementsByTagName('goles'.$team)
            ->item(0)->nodeValue;
        $penaltyGoals = $match->getElementsByTagName('golesDefPenales'.$team)
            ->item(0)->nodeValue;

        $shortName = trim($teamNode->getAttribute('paisSigla'));

        if ($shortName === '') {

            $shortName = null;
            $code = null;
        } else {

            $code = $this->_getCodeFromName($name, $shortName);
            $shortName = $this->_transformTeamShortName($name, $shortName);
        }

        return array(
                   'name' => $officialName,
                   'goals' => $goals,
                   'penaltyGoals' => $penaltyGoals,
                   'code' => $code,
                   'shortName' => $shortName
               );
    }

    /**
     * Hackish method to transform some short names to English.
     *
     * @param string $name      The country's name.
     * @param string $shortName The country's default short name.
     *
     * @return string The short name in English.
     */
    private function _transformTeamShortName($name, $shortName)
    {
        if (isset(self::$_shortNames[$name])) {

            return self::$_shortNames[$name];
        } else {

            return $shortName;
        }
    }

    /**
     * Hackish method to transform short names into ISO country codes.
     *
     * @param string $name      The country's name.
     * @param string $shortName The country's short name.
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
     * Hackish method to transform some names to English.
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
