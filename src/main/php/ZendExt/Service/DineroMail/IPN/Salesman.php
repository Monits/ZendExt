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
 * Model for DineroMail's IPN v2 payment salesmen.
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
 * Model for DineroMail's IPN v2 payment salesmen.
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
class ZendExt_Service_DineroMail_IPN_Salesman extends ZendExt_Model_SimpleXML
{
    /**
     * Construct a new instance from an XML
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->_data = $xml;
    }

    /**
     * Get the salesman document type.
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->_getElementText('TIPODOC');
    }

    /**
     * Get the salesman document.
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->_getElementText('NUMERODOC');
    }
}