<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Model for DineroMail's IPN v2 payments buyer.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail_IPN
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
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
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
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