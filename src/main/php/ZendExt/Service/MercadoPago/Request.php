<?php
/**
 * Wrapper for Mercado Pago state update requests.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_MercadoPago
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
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
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Service_MercadoPago_Request
{
    const PAID = 'paid';

    const PENDING = 'pending';

    const CANCELLED = 'cancelled';

    const REJECTED = 'rejected';

    protected static $_states = array(
        'A' => self::PAID,
        'P' => self::PENDING,
        'C' => self::CANCELLED,
        'R' => self::REJECTED
    );

    protected $_request;

    /**
     * Create a new instance.
     *
     * @param Zend_Controller_Request_Abstract $request The request object.
     */
    public function __construct(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }

    /**
     * Get the id assigned by Mercado Pago.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_request->getParam('mp_op_id');
    }

    /**
     * Get the operation id set by the seller.
     *
     * @return integer
     */
    public function getOperationId()
    {
        return $this->_request->getParam('seller_op_id');
    }

    /**
     * Get the sellers account id.
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->_request->getParam('acc_id');
    }

    /**
     * Get the payments status.
     *
     * @return string
     */
    public function getStatus()
    {
        return self::$_states[$this->_request->getParam('status')];
    }

    /**
     * Get the item id.
     *
     * @return string
     */
    public function getItemId()
    {
        return $this->_request->getParam('item_id');
    }

    /**
     * Get the product name.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_request->getParam('name');
    }

    /**
     * Get the product price.
     *
     * @return float
     */
    public function getProductPrice()
    {
        return $this->_request->getParam('price');
    }

    /**
     * Get the shipping cost.
     *
     * @return float
     */
    public function getShippingCost()
    {
        return $this->_request->getParam('shipping_amount');
    }

    /**
     * Get the total cost.
     *
     * @return float
     */
    public function getTotalCost()
    {
        return $this->_request->getParam('total_amount');
    }

    /**
     * Get the extra data set by the seller.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->_request->getParam('extra_part');
    }

    /**
     * Get the payment method.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_request->getParam('payment_method');
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
}
