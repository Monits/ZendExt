<?php
/**
 * API for Mercado Pagos Sonda WebService.
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
 * API for Mercado Pagos Sonda WebService.
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
class ZendExt_Service_MercadoPago_Sonda
{
    const SONDA_URI = 'https://www.mercadopago.com/mla/sonda';

    const MERCADOPAGO_ID = 'mp_op_id';

    const ACCOUNT_ID = 'acc_id';

    const TOKEN = 'sonda_key';

    const OPERATION_ID = 'seller_op_id';

    const SUCCESS = 'OK';

    protected $_accountId;

    protected $_token;

    protected $_mpId;

    protected $_operationId;

    protected $_data = null;

    /**
     * Construct a new instance.
     *
     * @param integer $accountId   The Mercado Pago account id.
     * @param string  $token       The accounts Sonda token.
     * @param integer $mpId        The payments' mercado pago id.
     * @param string  $operationId Optinal. The operation id set by the seller.
     */
    public function __construct($accountId, $token, $mpId, $operationId = null)
    {
        $this->_accountId = $accountId;
        $this->_token = $token;
        $this->_mpId = $mpId;
        $this->_operationId = $operationId;
    }

    /**
     * Get the payment data from sonda.
     *
     * @return ZendExt_Service_MercadoPago_Payment
     */
    public function getPaymentData()
    {
        $this->_makeRequest();

        return $this->_data;
    }

    /**
     * Make the request to Sonda.
     *
     * @return void
     *
     * @throws ZendExt_Service_MercadoPago_Sonda_Exception
     */
    protected function _makeRequest()
    {
        $client = new Zend_Http_Client(self::SONDA_URI);
        $client->setMethod('POST')
            ->setParameterPost(self::MERCADOPAGO_ID, $this->_mpId)
            ->setParameterPost(self::ACCOUNT_ID, $this->_accountId)
            ->setParameterPost(self::TOKEN, $this->_token)
            ->setParameterPost(self::OPERATION_ID, $this->_operationId);

        try {

            $response = $client->request();
        } catch (Zend_Http_Client_Exception $e) {

            throw new ZendExt_Service_MercadoPago_Sonda_Exception(
                'Could not complete the request.'
            );
        }

        if (!$response->getBody() || $response->isError()) {

            throw new ZendExt_Service_MercadoPago_Sonda_Exception(
                'Got an empty or error response from Sonda'
            );
        }

        $this->_parseResponse($response);
    }

    /**
     * Parse a Sonda response.
     *
     * @param Zend_Http_Response $response The response object.
     *
     * @return void
     *
     * @throws ZendExt_Service_MercadoPago_Sonda_Exception
     */
    protected function _parseResponse(Zend_Http_Response $response)
    {
        $responseData = new SimpleXMLElement($response->getBody());

        $message = $responseData->xpath('//message');
        if (false === $message) {

            throw new ZendExt_Service_MercadoPago_Sonda_Exception(
                'Invalid response'
            );
        }

        if (self::SUCCESS != array_shift($message)) {

            $this->_data = null;
            return;
        }

        $operation = $responseData->xpath('//operation');
        if (false === $operation) {

            throw new ZendExt_Service_MercadoPago_Sonda_Exception(
                'Invalid response'
            );
        }

        $result = array();
        $operation = array_shift($operation);
        foreach ($operation->children() as $key => $value) {

            $result[$key] = (string) $value;
        }

        $this->_data = new ZendExt_Service_MercadoPago_Payment($result);
    }
}