<?php
/**
 * Utility for requesting payments to DineroMail's IPN v2 service.
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
 * Utility for requesting payments to DineroMail's IPN v2 service.
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
class ZendExt_Service_DineroMail_IPN_Request
{
    const REQUEST_PARAM = 'DATA';

    const URI = 'https://argentina.dineromail.com/Vender/Consulta_IPN.asp';

    private $_accountNumber;

    private $_password;

    private $_payments;

    private $_response;

    /**
     * Construct a new instance.
     *
     * @param string $accountNumber The account number to fetch for.
     * @param string $password      The accounts password.
     * @param array  $payments      The id of the payments to fetch.
     */
    public function __construct($accountNumber, $password, array $payments)
    {
        $this->_accountNumber = $accountNumber;
        $this->_password = $password;
        $this->_payments = $payments;
    }

    /**
     * Get the payments from the API.
     *
     * @return array an array of {@link ZendExt_Service_DineroMail_IPN_Payment}
     *
     * @throws ZendExt_Service_DineroMail_Exception
     */
    public function getPayments()
    {
        $this->_makeRequest();
        return $this->_data;
    }

    /**
     * Make the request to the server.
     *
     * @throws ZendExt_Service_DineroMail_Exception
     */
    private function _makeRequest()
    {
        $client = new Zend_Http_Client(self::URI);

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(
            array(
                'curloptions' => array(
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => true
                )
            )
        );

        $client->setAdapter($adapter);

        $client->setMethod('POST')
            ->setParameterPost(self::REQUEST_PARAM, $this->_generatePost());

        try {

            $response = $client->request();
        } catch (Zend_Http_Client_Exception $e) {

            throw new ZendExt_Service_DineroMail_Exception(
                'Could not complete the request.'.PHP_EOL.$e->__toString()
            );
        }

        if (!$response->getBody() || $response->isError()) {

            throw new ZendExt_Service_DineroMail_Exception(
                'Got an empty or error response from DineroMail'
            );
        }

        $this->_parseResponse($response);
    }

    /**
     * Generate the XML for the POST request.
     *
     * @return string
     */
    private function _generatePost()
    {
        $res = '<REPORTE>';
        $res .= '<NROCTA>'.$this->_accountNumber.'</NROCTA>';
        $res .= '<CONSULTA>';
        $res .= '<CLAVE>'.$this->_password.'</CLAVE>';
        $res .= '<TIPO>1</TIPO>';
        $res .= '<OPERACIONES>';
        foreach ($this->_payments as $payment) {
            $res .= '<ID>'.$payment.'</ID>';
        }
        $res .= '</OPERACIONES>';
        $res .= '</CONSULTA>';
        $res .= '</DETALLE>';
        $res .= '</REPORTE>';

        return $res;
    }

    /**
     * Parse the response.
     *
     * @param Zend_Http_Response $response
     *
     * @throws ZendExt_Service_DineroMail_Exception
     */
    private function _parseResponse(Zend_Http_Response $response)
    {
        $doc = new SimpleXMLElement($response->getBody());

        $message = $responseData->xpath('//ESTADOREPORTE');
        if (false === $message) {

            throw new ZendExt_Service_DineroMail_Exception(
                'Malformed response.'
            );
        }

        $code = array_shift($message);
        if (self::SUCCESS != $code) {

            throw new ZendExt_Service_DineroMail_Exception(
                'Error response with code: ' . $code
            );
        }

        $operations = $responseData->xpath('//OPERACION');
        if (false === $operations) {

            throw new ZendExt_Service_DineroMail_Exception(
                'Recieved a success response code with no payments.'
            );
        }

        $this->_response = array();
        foreach ($operations as $operation) {

            $this->_response[] =
            ZendExt_Service_DineroMail_IPN_Payment::createFromSimpleXML(
                $operation
            );
        }
    }
}