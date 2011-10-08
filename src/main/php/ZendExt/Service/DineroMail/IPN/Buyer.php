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
 * Model for DineroMail's IPN v2 payments buyer.
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
 * Model for DineroMail's IPN v2 payment buyer.
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
class ZendExt_Service_DineroMail_IPN_Buyer extends ZendExt_Model_SimpleXML
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
     * Get the buyers email.
     *
     * @return string
     */
    public function getMail()
    {
        return $this->_getElementText('EMAIL');
    }

    /**
     * Get the buyers address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->_getElementText('DIRECCION');
    }

    /**
     * Get the buyers comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getElementText('COMENTARIO');
    }


    /**
     * Get the buyers name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getElementText('NOMBRE');
    }

    /**
     * Get the buyers phone number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->_getElementText('TELEFONO');
    }

    /**
     * Get the buyers document type.
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->_getElementText('TIPODOC');
    }

    /**
     * Get the buyers document.
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->_getElementText('NUMERODOC');
    }
}