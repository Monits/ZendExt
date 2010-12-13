<?php
/**
 * Utility for creating DineroMail's buy now buttons.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */

/**
 * Utility for creating DineroMail's buy now buttons.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DineroMail
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */
class ZendExt_Service_DineroMail_BuyNow
{
    const DOLAR = 2;

    const ARS = 1;

    private $_data;

    private $_image;

    private $_alt;

    private $_country;

    /**
     * Construct a new instance.
     *
     * @param integer $accountNumber The account number to user.
     * @param string  $country       One of the DineroMail supported countries.
     */
    public function __construct($accountNumber, $country = 'argentina')
    {
        $this->_data = array();
        $this->_data['E_Comercio'] = $accountNumber;
        $this->_data['Mensaje'] = 0;
        $this->_data['DireccionEnvio'] = 0;

        $this->_image = 'https://'.$country
            .'.dineromail.com/imagenes/botones/comprar_c.gif';
        $this->_alt = 'Pagar con DineroMail';
        $this->_country = $country;
    }

    /**
     * Set the alt text for the button.
     *
     * @param string $alt The new alt.
     */
    public function setAltText($alt)
    {
        $this->_alt = $alt;
    }

    /**
     * Set the item to sell data.
     *
     * @param string  $name     The item's name.
     * @param float   $price    The item's price.
     * @param string  $currency The item's price currency.
     *                          {@see ZendExt_Service_DineroMail_BuyNow::ARS}
     *                          and
     *                          {@see ZendExt_Service_DineroMail_BuyNow::DOLAR}.
     * @param integer $id       Optional. The item's id.
     */
    public function setItem($name, $price, $currency = self::ARS, $id = null)
    {
        $this->_data['NombreItem'] = $name;
        $this->_data['PrecioItem'] = $price;
        $this->_data['TipoMoneda'] = $currency;
        if (null !== $id) {
            $this->_data['NroItem'] = $id;
        }
    }

    /**
     * Set the vendor transaction id.
     *
     * @param string $id The transaction id.
     */
    public function setTransactionId($id)
    {
        $this->_data['trx_id'] = $id;
    }

    /**
     * Set the landing urls for after the purchase.
     *
     * @param string $success The success url.
     * @param string $fail    The fail url.
     */
    public function setUrls($success, $fail)
    {
        $this->_data['DireccionExito'] = $success;
        $this->_data['DireccionFracaso'] = $fail;
    }

    /**
     * Set the user data.
     *
     * @param string $name        The user's name.
     * @param string $lastName    The user's last name.
     * @param string $phoneNumber The user's phone number.
     * @param string $mail        The user's email address.
     */
    public function setUserData($name, $lastName, $phoneNumber, $mail)
    {
        $this->_data['usr_nombre'] = $name;
        $this->_data['usr_apellido'] = $lastName;
        $this->_data['usr_tel_numero'] = $phoneNumber;
        $this->_data['usr_email'] = $mail;
    }

    /**
     * Allow the user to attach a message to the purchase.
     */
    public function allowMessage()
    {
        $this->_data['Mensaje'] = 1;
    }

    /**
     * Allows you to show your site's logo when the user is making the purchase.
     *
     * @param string $url The logo's url.
     */
    public function setSiteLogo($url)
    {
        $this->_data['image_url'] = $url;
    }

    /**
     * Allow the user to enter the delivery address when making the purchase.
     */
    public function allowDeliveryAddress()
    {
        $this->_data['DireccionEnvio'] = 1;
    }

    /**
     * Set the button image.
     *
     * @param string $url The url where the image is located.
     */
    public function setButtonImage($url)
    {
        $this->_image = $url;
    }

    public function toString()
    {
        $form = '<form action="https://'.$this->_country
            .'.dineromail.com/Shop/Shop_Ingreso.asp" method="post">';

        foreach ($this->_data as $name => $value) {
            $form .= '<input type="hidden" name="'.$name.'" value="'.$value
                .'" />';
        }
        $form .= '<input type="image" src="'.$this->_image
            .'" name="submit" alt="'.$this->_alt.'"></form>';

        return $form;
    }
}