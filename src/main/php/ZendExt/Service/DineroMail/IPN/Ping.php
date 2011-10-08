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
 * Utility for parsing DineroMail's IPN v2 pings.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail_IPN
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */

/**
 * Utility for parsing DineroMail's IPN v2 pings.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail_IPN
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
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
     * @param $data the string containing the XML passed on.
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
