<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Model for DineroMail's IPN v2 payments.
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
 * Model for DineroMail's IPN v2 payments.
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
class ZendExt_Service_DineroMail_IPN_Payment extends ZendExt_Model_SimpleXML
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
     * Get the payment id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_getElementText('ID');
    }

    /**
     * Get the date the payment was created on.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_getElementText('FECHA');
    }

    /**
     * Get the payments' status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_getElementText('ESTADO');
    }

    /**
     * Get the number of the transaction.
     *
     * This is the internal DineroMail id.
     *
     * @return integer
     */
    public function getTransacionNumber()
    {
        return $this->_getElementText('NUMTRANSACCION');
    }

    /**
     * Get the payments' total.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->_getElementText('MONTO');
    }

    /**
     * Get the payments' net total.
     *
     * @return float
     */
    public function getNetTotal()
    {
        return $this->_getElementText('MONTONETO');
    }

    /**
     * Get the method of payment.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_getElementText('METODOPAGO');
    }

    /**
     * Get the media throught the payment will be handled.
     *
     * @return string
     */
    public function getPaymentMedia()
    {
        return $this->_getElementText('MEDIOPAGO');
    }

    /**
     * Get the number of payments it's made in.
     *
     * @return string
     */
    public function getPayments()
    {
        return $this->_getElementText('CUOTAS');
    }

    /**
     * Get the buyer's data.
     *
     * @return ZendExt_Service_DineroMail_IPN_Buyer
     */
    public function getBuyer()
    {
        return new ZendExt_Service_DineroMail_IPN_Buyer(
            $this->_getElement('COMPRADOR')
        );
    }

    /**
     * Get the salesman's data.
     *
     * @return ZendExt_Service_DineroMail_IPN_Salesman
     */
    public function getSalesman()
    {
        return new ZendExt_Service_DineroMail_IPN_Salesman(
            $this->_getElement('VENDEDOR')
        );
    }

    /**
     * Get the items purchased.
     *
     * @return array An array of {@ link ZendExt_Service_DineroMail_IPN_Item}
     */
    public function getItems()
    {
        $ret = array();
        $itemList = $this->_getElement('ITEMS');
        foreach ($itemList->children() as $itemNode) {
            $ret[] = new ZendExt_Service_DineroMail_IPN_Item($itemNode);
        }

        return $ret;
    }
}