<?php
/**
 * Model for DineroMail's IPN v2 payment salesmen.
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
 * Model for DineroMail's IPN v2 payment salesmen.
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
class ZendExt_Service_DineroMail_IPN_Salesman extends ZendExt_Model_SimpleXML
{
    /**
     * Construct a new instance from an XML
     *
     * @param SimpleXMLElement $xml The xml.
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