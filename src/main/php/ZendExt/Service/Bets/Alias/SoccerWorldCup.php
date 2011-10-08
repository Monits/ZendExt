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
 * The Soccer World Cup aliases for each country.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Alias
 * @copyright 2010 monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * The Soccer World Cup aliases for each country.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets_Alias
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_Bets_Alias_SoccerWorldCup
    implements ZendExt_Service_Bets_Alias_Interface
{
    protected $_aliases = array(
        'SUD' => array('Sud&aacute;frica'),
        'URU' => array('Uruguay'),
        'FRA' => array('Francia'),
        'MEX' => array('M&eacute;xico'),
        'KOR' => array('Corea del Sur'),
        'ARG' => array('Argentina'),
        'GRE' => array('Grecia'),
        'NIG' => array('Nigeria'),
        'ING' => array('Inglaterra'),
        'DZA' => array('Argelia'),
        'SVK' => array('Eslovaquia'),
        'USA' => array('Estados Unidos', 'EE.UU.'),
        'SER' => array('Serbia'),
        'ALE' => array('Alemania'),
        'GHA' => array('Ghana'),
        'AUS' => array('Australia'),
        'HOL' => array('Holanda'),
        'JPN' => array('Jap&oacute;n'),
        'CAM' => array('Camer&uacute;n'),
        'DIN' => array('Dinamarca'),
        'ITA' => array('Italia'),
        'NZL' => array('Nueva Zelanda'),
        'PAR' => array('Paraguay'),
        'ESL' => array('Eslovenia'),
        'CMA' => array('Costa de Marfil'),
        'BRA' => array('Brasil'),
        'POR' => array('Portugal'),
        'CNO' => array('Corea del Norte', 'Korea DPR'),
        'HON' => array('Honduras'),
        'ESP' => array('Espa&ntilde;a'),
        'CHI' => array('Chile'),
        'SUI' => array('Suiza')
    );

    /**
     * Retrieves the known aliases for a given code.
     *
     * @param string $code The code for which to retrieve aliases.
     *
     * @return array
     */
    public function getAliasesFor($code)
    {
        if (isset($this->_aliases[$code])) {
            return $this->_aliases[$code];
        }

        return array();
    }
}