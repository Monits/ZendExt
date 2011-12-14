<?php
/**
 * Utility for parsing DineroMail's IPN v2 pings.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail_IPN
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.5.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Utility for parsing DineroMail's IPN v2 pings.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail_IPN
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.5.0
 */
class ZendExt_Service_DineroMail_IPN_Ping
{
    const REQUEST_PARAM = 'Notificacion';

    /**
     * @var DOMDocument
     */
    private $_data;

    /**
     * Construct a new instance from the ping XML.
     *
     * @param string $data The string containing the XML passed on.
     */
    public function __construct($data)
    {
        $this->_data = new DOMDocument();
        $this->_data->loadXML($data);
    }

    /**
     * Get the id of the operations in the ping.
     *
     * @return DOMNodeList
     */
    public function getOperationList()
    {
        $xpath = new DOMXPath($this->_data);
        $ops = $xpath->query('//operacion/id/text()');

        $result = array();
        for ($i = 0; $i < $ops->length; $i++) {
            $result[] = $ops->item($i)->nodeValue;
        }

        return $result;
    }

    /**
     * Create a new instance from a request.
     *
     * @param Zend_Controller_Request_Abstract $request The request to use.
     *
     * @return ZendExt_Service_DineroMail_IPN_Ping
     */
    public static function createFromRequest(
        Zend_Controller_Request_Abstract $request)
    {
        return new self($request->getParam(self::REQUEST_PARAM));
    }
}
