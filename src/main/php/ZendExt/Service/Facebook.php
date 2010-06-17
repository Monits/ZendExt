<?php
/**
 * Wrapper for Facebook API library.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

//*WARNING*: Do *NOT* under pain of slow death, remove the next line!!!
//This wont autoload, so if you remove it, everything blows up.
//And I mean *everything*!!
require_once('facebook/facebook.php');

/**
 * Wrapper for Facebook API library.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_Facebook
{
    const API_URL = 'https://api.facebook.com/restserver.php';

    private static $_params = array(
                                  'locale'  => 'fb_sig_locale',
                                  'friends' => 'fb_sig_friends',
                                  'ajax'    => 'fb_sig_is_ajax',
                                  'userId'  => 'fb_sig_user',
                                  'invited' => 'ids'
                              );

    /**
     * Instance of FB lib.
     *
     * @var Facebook
     */
    private $_fb;

    /**
     * A request object instance.
     *
     * @var Zend_Controller_Request_Abstract
     */
    private $_request;

    private $_apiKey;

    private $_apiSecret;

    /**
     * Construct a new FB API service.
     *
     * @param string  $apiKey         The API key given by FB.
     * @param string  $apiSecret      The API secret given by FB.
     * @param boolean $generateSecret Whether session API calls should
     *                                   generate a new secret.
     */
    public function __construct($apiKey, $apiSecret, $generateSecret=false)
    {
        $this->_apiKey = $apiKey;
        $this->_apiSecret = $apiSecret;

        $this->_fb = new Facebook($apiKey, $apiSecret, $generateSecret);
    }

    /**
     * Set the request object.
     *
     * @param Zend_Controller_Request_Abstract $request The instance to set.
     *
     * @return void
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }

    /**
     * Get the request object.
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Require the user to have the application installed.
     *
     * Checks whether an user is logged in and has installed the app.
     * In case it's not, it redirects the user to install the app.
     *
     * @return integer The user's uid
     */
    public function requireLogin()
    {
        return $this->_fb->require_login();
    }

    /**
     * Get the user uid.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_request->getParam(self::$_params['userId']);
    }

    /**
     * Get the user's locale.
     *
     * @return string
     */
    public function getUserLocale()
    {
        return $this->_request->getParam(self::$_params['locale']);
    }

    /**
     * Check whether the request is AJAX.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return $this->_request->getParam(self::$_params['ajax']) == 1;
    }

    /**
     * Get an array of uids that user invited on the last request.
     *
     * @return array
     */
    public function getInvitedIds()
    {
        return $this->_request->getParam(self::$_params['invited']);
    }

    /**
     * Get the uids of the friends of the current user that installed the app.
     *
     * @return array
     */
    public function getAuthorizedFriends()
    {
        $friends = $this->_fb->api_client->friends_getAppUsers();
        if (is_array($friends)) {
            return $friends;
        }

        return array();
    }

    /**
     * Retrieves the mail of the requested user provided he has granted access.
     *
     * @param string $userId The id of the user whose mail to request.
     *
     * @return string
     */
    public function getUserEmail($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getUserId();
        }

        $query = "SELECT email FROM user WHERE uid=\"$userId\"";
        $data = $this->_fb->api_client->fql_query($query);

        return $data[0]['email'];
    }

    /**
     * Increment the users counter.
     *
     * @param integer $userId The user id. If null, defaults to current user.
     *
     * @return void
     */
    public function incrementCounter($userId = null)
    {
        if ($userId === null) {

            $userId = $this->getUserId();
        }

        $params = array(
            'uid' => $userId
        );
        $this->_makeApiCall('dashboard.incrementCount', $params);
    }

    /**
     * Set the users counter to a value.
     *
     * @param integer $userId The user id. If null, defaults to current user.
     * @param integer $count  The new value. Defaults to 0.
     *
     * @return void
     */
    public function setCounter($userId = null, $count = 0)
    {
        if ($userId === null) {

            $userId = $this->getUserId();
        }

        $params = array(
            'uid' => $userId,
            'count' => $count
        );
        $this->_makeApiCall('dashboard.setCount', $params);
    }


    /**
     * Get the users info.
     *
     * @param array   $fields An array with the names of the fields to retrieve.
     * @param integer $userId The id of the user to get infor of.
     *
     * @return array
     */
    public function getUserInfo(array $fields, $userId = null)
    {
        if (null === $userId) {

            $userId = $this->getUserId();
        }

        return $this->_fb->api_client->users_getInfo($userId, $fields);
    }

    /**
     * Make a request to rest API.
     *
     * @param string $method The api method to use.
     * @param array  $params Params to pass on.
     * @param string $format The format.
     *
     * @return string The response given by the API
     */
    private function _makeApiCall($method, array $params = array(),
        $format = 'xml')
    {
        $get = array(
            'method' => $method,
            'api_key' => $this->_apiKey,
            'format' => $format
        );

        $post = $params;
        $post['v'] = '1.0';
        $post['call_id'] = microtime(true);
        $post['sig'] = $this->_makeSig(array_merge($get, $post));

        $getStr = '';
        foreach ($get as $key => $value) {

            $getStr .= $key .'='.$value.'&';
        }
        $getStr = substr($getStr, 0, -1);

        $postStr = '';
        foreach ($post as $key => $value) {

            $postStr .= $key .'='.$value.'&';
        }
        $postStr = substr($postStr, 0, -1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL.'?'.$getStr);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    /**
     * Generate the FB API sig for a given list of params.
     *
     * @param array $params An array of key => value params.
     *
     * @return string
     */
    private function _makeSig($params)
    {
        ksort($params);

        $requestStr = '';
        foreach ($params as $key => $value) {

            $requestStr .= $key . '=' . $value;
        }
        $sig = $requestStr . $this->_apiSecret;
        return md5($sig);
    }
}
