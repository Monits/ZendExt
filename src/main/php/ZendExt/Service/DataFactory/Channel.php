<?php
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
        $document = DOMDocument::loadXML($xml);
        $this->_channels = $this->_parseChannels($document->getElementsByTagName('canal'));
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
     * @param string|integer $date Either a timestamp or a Zend_Date parsable date.
     *
     * @return string
     */
    public static function buildUrl($date)
    {
        $date = new Zend_Date($date);
        $day = $date->toString('YYYYMMdd');
        $time = $date->toString('HH:mm:ss');

        return '?desde='.$day.'&hora='.$time;
    }
}