<?php
/**
 * Abstract betting service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Abstract betting service.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
abstract class ZendExt_Service_Bets_Abstract
    implements ZendExt_Service_Bets_Interface
{

    const TIME_OUT = 5;

    protected $_timeOut = self::TIME_OUT;
    protected $_availableChannels = array();
    protected $_channelClass;

    /**
     * Creates a new service.
     *
     * @param mixed $channel Which channel to use.
     *
     * @throws ZendExt_Service_Bets_Exception
     *
     * @return ZendExt_Service_Bets_Abstract
     */
    public function __construct($channel)
    {
        if (!in_array($channel, $this->_availableChannels)) {
            throw new ZendExt_Service_Bets_Exception(
                'The selected channel is not available.'
            );
        }

        $this->_channelClass = $channel;

    }

    /**
     * Retrieves the site's output to be parsed.
     *
     * @param string $url      The url from where get the output.
     * @param string $cookies  The raw cookies.
     * @param string $method   The request method.
     * @param string $postData The post data to sent if the request is post.
     *
     * @throws ZendExt_Service_Bets_Exception
     *
     * @return string
     */
    protected function _getOutput($url, $cookies = '', $method = 'GET',
        $postData = '')
    {
        $client = new Zend_Http_Client($url);

        $client->setConfig(array('timeout' => $this->_timeOut));
        $client->setHeaders('Cookie', $cookies);
        $client->setRawData($postData);

        $response = $client->request($method);

        if (!$response->isSuccessful()) {
            throw new ZendExt_Service_Bets_Exception(
                'Request failed, returned error code ' . $response->getStatus()
            );
        }

        return $response->getBody();
    }

}
