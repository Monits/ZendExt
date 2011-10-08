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
 * DataFactory channel list.
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
 * DataFactor channel list
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
class ZendExt_Service_DataFactory_Channel
{
    private $_channels;

    /**
     * Construct a new instance.
     *
     * @param string $xml The channel data XML
     */
    public function __construct($xml)
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        $this->_channels = $this->_parseChannels(
            $document->getElementsByTagName('canal')
        );
    }

    /**
     * Transform a node list into a usable array.
     *
     * @param DOMNodeList $channelNodeList The list of nodes to transform.
     *
     * @return array An array with the names of the channels.
     */
    private function _parseChannels($channelNodeList)
    {
        $channels = array();

        foreach ($channelNodeList as $node) {

            $channels[] = $node->nodeValue;
        }

        return $channels;
    }

    /**
     * Get the list of channels that can be updated.
     *
     * @return array An array with the names of the channels.
     */
    public function getChannels()
    {

        return $this->_channels;
    }

    /**
     * Build the url for getting channels using a certain date.
     *
     * @param string|integer $date Either a timestamp
     * 							   or a Zend_Date parsable date.
     *
     * @return string
     */
    public static function buildUrl($date)
    {
        $date = new Zend_Date($date);
        $day = $date->toString(ZendExt_Service_DataFactory::DATE_FORMAT);
        $time = $date->toString(ZendExt_Service_DataFactory::TIME_FORMAT);

        return '?desde='.$day.'&hora='.$time;
    }
}