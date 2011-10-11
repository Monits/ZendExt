<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Wrapper for Mercado Pago state update requests.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_MercadoPago
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * Wrapper for Mercado Pago state update requests.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_MercadoPago
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Service_MercadoPago_Payment
{
    const PAID = 'paid';

    const PENDING = 'pending';

    const CANCELLED = 'cancelled';

    const REJECTED = 'rejected';

    protected static $_comparison = array(
        'getId',
        'getOperationId',
        'getAccountId',
        'getItemId',
        'getProductName',
        'getProductPrice',
        'getShippingCost',
        'getTotalCost',
        'getExtra',
        'getPaymentMethod'
    );

    protected static $_states = array(
        'A' => self::PAID,
        'P' => self::PENDING,
        'C' => self::CANCELLED,
        'R' => self::REJECTED
    );

    protected $_data;

    /**
     * Create a new instance.
     *
     * @param array $data The payment data.
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Get the id assigned by Mercado Pago.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_data['mp_op_id'];
    }

    /**
     * Get the operation id set by the seller.
     *
     * @return integer
     */
    public function getOperationId()
    {
        return $this->_data['seller_op_id'];
    }

    /**
     * Get the sellers account id.
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->_data['acc_id'];
    }

    /**
     * Get the payments status.
     *
     * @return string
     */
    public function getStatus()
    {
        return self::$_states[$this->_data['status']];
    }

    /**
     * Get the item id.
     *
     * @return string
     */
    public function getItemId()
    {
        return $this->_data['item_id'];
    }

    /**
     * Get the product name.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_data['name'];
    }

    /**
     * Get the product price.
     *
     * @return float
     */
    public function getProductPrice()
    {
        return $this->_data['price'];
    }

    /**
     * Get the shipping cost.
     *
     * @return float
     */
    public function getShippingCost()
    {
        return $this->_data['shipping_amount'];
    }

    /**
     * Get the total cost.
     *
     * @return float
     */
    public function getTotalCost()
    {
        return $this->_data['total_amount'];
    }

    /**
     * Get the extra data set by the seller.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->_data['extra_part'];
    }

    /**
     * Get the payment method.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_data['payment_method'];
    }

    /**
     * Check whether the payment is ready.
     *
     * @return boolean
     */
    public function isPaid()
    {
        return $this->getStatus() == self::PAID;
    }

    /**
     * Check whether the payment is pending.
     *
     * @return boolean
     */
    public function isPending()
    {
        return $this->getStatus() == self::PENDING;
    }

    /**
     * Check whether the payment is cancelled.
     *
     * @return boolean
     */
    public function isCancelled()
    {
        return $this->getStatus() == self::CANCELLED;
    }

    /**
     * Check whther the payment is rejected.
     *
     * @return boolean
     */
    public function isRejected()
    {
        return $this->getStatus() == self::REJECTED;
    }

    /**
     * Create a new instance from a request object.
     *
     * @param Zend_Controller_Request_Abstract $request The request to use.
     *
     * @return ZendExt_Service_MercadoPago_Payment
     */
    public static function createFromRequest(
        Zend_Controller_Request_Abstract $request)
    {
        return new self($request->getParams());
    }

    /**
     * Compare two payments to see if they are the same.
     *
     * @param ZendExt_Service_MercadoPago_Payment $p      The payment to
     *                                                    compare.
     * @param boolean                             $strict Whether to compare
     *                                                    states. Default false.
     *
     * @return boolean
     */
    public function equals(ZendExt_Service_MercadoPago_Payment $p,
        $strict = false)
    {
        foreach (self::$_comparison as $method) {

            if ($this->$method() != $p->$method()) {

                return false;
            }
        }

        if ($strict && $this->getStatus() != $p->getStatus()) {

            return false;
        }

        return true;
    }
}
