<?php
/**
 * Model for DineroMail's IPN v2 payment items.
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
 * Model for DineroMail's IPN v2 payment items.
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
class ZendExt_Service_DineroMail_IPN_Item extends ZendExt_Model_SimpleXML
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
     * Get the items description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_getElementText('DESCRIPCION');
    }

    /**
     * Get the item's price currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getElementText('MONEDA');
    }

    /**
     * Get the price per unit.
     *
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->_getElementText('PRECIOUNITARIO');
    }

    /**
     * Get the quantity of this item.
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->_getElementText('CANTIDAD');
    }
}