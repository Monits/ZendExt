<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Service for Mercado Pagos Comprar Ahora.
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
 * Service for Mercado Pagos Comprar Ahora.
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
class ZendExt_Service_MercadoPago_BuyNow
{
    protected $_successUrl;

    protected $_processUrl;

    protected $_cancelUrl;

    protected $_itemId;

    protected $_name;

    protected $_currency;

    protected $_price;

    protected $_shippingCost;

    protected $_shippingCostMode;

    protected $_shippingMode;

    protected $_operationId;

    protected $_accountId;

    protected $_extra;

    protected $_image =
        'https://www.mercadopago.com/org-img/MP3/buy_now_02.gif';

    protected $_altText = 'Buy now';

    protected $_enc;

    protected static $_keys = array(
        '_successUrl'       => 'url_succesfull',
        '_processUrl'       => 'url_process',
        '_cancelUrl'        => 'url_cancel',
        '_itemId'           => 'item_id',
        '_name'             => 'name',
        '_currency'         => 'currency',
        '_price'            => 'price',
        '_shippingCost'     => 'shipping_cost',
        '_shippingCostMode' => 'ship_cost_mode',
        '_shippingMode'     => 'op_retira',
        '_operationId'      => 'seller_op_id',
        '_accountId'        => 'acc_id',
        '_extra'            => 'extra_part',
        '_enc'              => 'enc'
    );

    /**
     * Construct a new instance.
     *
     * @param integer $accountId   The account id assigned by mercado pago.
     * @param string  $enc         Security code.
     * @param string  $operationId Optional. The sellers operation id.
     */
    public function __construct($accountId, $enc, $operationId = null)
    {
        $this->_accountId = $accountId;
        $this->_enc = $enc;

        if ($operationId !== null) {
            $this->_operationId = $operationId;
        }
    }

    /**
     * Set the callbacks urls.
     *
     * @param string $success The url to send the user after success.
     * @param string $process Optional. The url to send the user when the
     *                        payment is being processed.
     * @param string $cancel  Optional. The url to send the user to when the
     *                        payment is cancelled.
     *
     * @return void
     */
    public function setCallbacks($success, $process = '', $cancel = '')
    {
        $this->_successUrl = $success;
        $this->_processUrl = $process;
        $this->_cancelUrl = $cancel;
    }

    /**
     * Set the product info.
     *
     * @param string $name     The name of the product.
     * @param float  $price    The price of the product.
     * @param string $id       Optional. A product id.
     * @param string $currency Optional. The currency to be used.
     *
     * @return void
     */
    public function setProduct($name, $price, $id = null, $currency = 'ARG')
    {
        $this->_name = $name;
        $this->_price = $price;
        $this->_itemId = $id;
        $this->_currency = $currency;
    }

    /**
     * Set the shipping info.
     *
     * @param float  $cost     The shipping cost.
     * @param string $costMode The shipping code mode.
     * @param string $mode     The shipping mode.
     *
     * @return void
     */
    public function setShipping($cost, $costMode, $mode)
    {
        $this->_shippingCost = $cost;
        $this->_shippingCostMode = $costMode;
        $this->_shippingMode = $mode;
    }

    /**
     * Set the image for the button.
     *
     * @param string $image The url for the imagen.
     *
     * @return void
     */
    public function setImage($image)
    {
        $this->_image = $image;
    }

    /**
     * Set the alt text for the button.
     *
     * @param string $text The text to set.
     *
     * @return void
     */
    public function setAltText($text)
    {
        $this->_altText = $text;
    }

    /**
     * Set the extra part.
     * 
     * @param string $extra The value to set.
     *
     * @return void
     */
    public function setExtra($extra)
    {
        $this->_extra = $extra;
    }

    /**
     * Render the form.
     * 
     * @param string $id    Optional. An id to set to the form.
     * @param array  $class Optional. CSS classes to apply to the form.
     *
     * @return void
     */
    public function renderForm($id = 'buynow', array $class = array())
    {
        echo '<form target="MercadoPago"'
            .' action="https://www.mercadopago.com/mla/buybutton"'
            .' method="POST" id="'.htmlentities($id).'" class="'
            .htmlentities(implode(' ', $class)).'">'.PHP_EOL;
        echo '<input type="image" src="'.htmlentities($this->_image)
            .'" border="0" alt="'.htmlentities($this->_altText).'">'.PHP_EOL;

        foreach (self::$_keys as $property => $name) {
            echo '<input type="hidden" name="'.$name.'" value="'
                .htmlentities($this->$property).'">'.PHP_EOL;
        }
        echo '</form>'.PHP_EOL;
    }
}
