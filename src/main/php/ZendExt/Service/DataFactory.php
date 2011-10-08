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
 * DataFactory feed service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * DataFactory feed service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_DataFactory
{
    const DATE_FORMAT = 'YYYYMMdd';

    const TIME_FORMAT = 'HH:mm:ss';

    const BASE_URL = 'http://www.datafactory.ws/clientes/xml/';

    const FIXTURE = 'ZendExt_Service_DataFactory_Fixture';

    private $_baseUrl = self::BASE_URL;

    private $_channels;

    private $_parsers = array();

    /**
     * Check for updates.
     *
     * @param timestamp $lastUpdate The timestamp of the last update.
     * @param string    $baseUrl    Optional. The base url to make requests on.
     */
    public function __construct($lastUpdate, $baseUrl=null)
    {
        $this->_checkChannels($lastUpdate);

        if ( $baseUrl ) {

            $this->_baseUrl = $baseUrl;
        }
    }

    /**
     * Check for updates.
     *
     * @param timestamp $lastUpdate The timestamp of the last update.
     *
     * @return void
     */
    private function _checkChannels($lastUpdate)
    {
        $params = ZendExt_Service_DataFactory_Channel::buildUrl($lastUpdate);
        $xml = $this->_requestXml(
            $this->_baseUrl.'index.php'.$params
        );

        $parser = new ZendExt_Service_DataFactory_Channel($xml);

        $this->_channels = $parser->getChannels();
    }

    /**
     * Check whether a given channel can be updated.
     *
     * @param string $channel The name of the channel to check for.
     *
     * @return boolean
     */
    public function canUpdate($channel)
    {
        return array_search($channel, $this->_channels) !== false;
    }

    /**
     * Get parsed channel data.
     *
     * @param string $parser The name of the parser class.
     *
     * @return object
     */
    public function getChannel($parser)
    {
        if ( isset($this->_parsers[$parser]) ) {

            return $this->_parsers[$parser];
        }

        $channel = constant($parser.'::CHANNEL_NAME');
        if (!$this->canUpdate($channel)) {

            return false;
        }

        $xml = $this->_requestXml($this->_baseUrl.'index.php?canal='.$channel);
        $this->_parsers[$parser] = new $parser($xml);

        return $this->_parsers[$parser];
    }

    /**
     * Request an XML document.
     *
     * @param string $url the url to fetch from.
     *
     * @return string
     */
    private function _requestXml($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}