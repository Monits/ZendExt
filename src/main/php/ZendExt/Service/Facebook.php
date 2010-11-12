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

    const PHOTO_SQUARE = 'square';

    const PHOTO_SMALL = 'small';

    const PHOTO_LARGE = 'large';

    const GRAPH_URL = 'https://graph.facebook.com';

    const COOKIE_PREFIX = 'fbs_';

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

    private $_appId;

    private $_apiSecret;

    private $_cookie;

    /**
     * Construct a new FB API service.
     *
     * @param string  $appId		  The application id.
     * @param string  $apiKey         The API key given by FB.
     * @param string  $apiSecret      The API secret given by FB.
     * @param boolean $generateSecret Whether session API calls should
     *                                   generate a new secret.
     */
    public function __construct($appId, $apiKey, $apiSecret,
        $generateSecret=false)
    {
        $this->_appId = $appId;
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
     * Get the user uid.
     *
     * @return integer
     */
    public function getUserId()
    {
        if ($this->hasValidCookie()) {
            $cookie = $this->_getCookieParams();
            if (isset($cookie['uid'])) {
                return $cookie['uid'];
            }

            $response = $this->_makeGraphCall('me', $this->getAccessToken());
            if (isset($response['id'])) {
                return $response['id'];
            }
        }
        return null;
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
    public function getUserEmail($userId)
    {
        $query = "SELECT email FROM user WHERE uid=\"$userId\"";
        $data = $this->_fb->api_client->fql_query($query);

        return $data[0]['email'];
    }

    /**
     * Increment the users counter.
     *
     * @param integer $userId The user id.
     *
     * @return void
     */
    public function incrementCounter($userId )
    {
        $params = array(
            'uid' => $userId
        );
        $this->_makeApiCall('dashboard.incrementCount', $params);
    }

    /**
     * Set the users counter to a value.
     *
     * @param integer $userId The user id.
     * @param integer $count  The new value. Defaults to 0.
     *
     * @return void
     */
    public function setCounter($userId, $count = 0)
    {
        $params = array(
            'uid' => $userId,
            'count' => $count
        );
        $this->_makeApiCall('dashboard.setCount', $params);
    }

    /**
     * This method publishes a post into the stream.
     *
     * {@link http://developers.facebook.com/docs/reference/rest/stream.publish}
     *
     * @param array $attachment  An array with the post attachment.
     * @param array $actionLinks An array with the post's action links.
     * @param int   $uid         The id of the user whose stream will get the
     *                           publish.
     *
     * @return string
     */
    public function streamPublish(array $attachment, array $actionLinks, $uid)
    {
        $params = array(
            'attachment' => Zend_Json::encode($attachment),
            'action_links' => Zend_Json::encode($actionLinks),
            'uid' => $uid
        );

        return $this->_makeApiCall('stream.publish', $params);
    }

    /**
     * Get the users info.
     *
     * @param array   $fields An array with the names of the fields to retrieve.
     * @param integer $userId The id of the user to get infor of.
     *
     * @return array
     */
    public function getUserInfo(array $fields, $userId)
    {
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

            if ('sig' !== $key) {
                $requestStr .= $key . '=' . $value;
            }
        }
        $sig = $requestStr . $this->_apiSecret;
        return md5($sig);
    }


    /**
     * Generate the profile pic url.
     *
     * @param string $uid  The facebook uid.
     * @param string $type The size of the image to ask for. Has to be one of:
     *                     {@see ZendExt_Service_Facebook::SMALL},
     *                     {@see ZendExt_Service_Facebook::SQUARE} or
     *                     {@see ZendExt_Service_Facebook::LARGE}.
     *
     * @return string
     */
    public function generateProfilePicUrl($uid, $type = self::SMALL)
    {
        return self::GRAPH_URL.'/'.$uid.'/picture?type='.$type;
    }

    /**
     * Check whether we have a valid facebook cookie.
     *
     * @return boolean
     */
    public function hasValidCookie()
    {
        $params = $this->_getCookieParams();
        return isset($params['sig'])
            && $this->_makeSig($params) == $params['sig'];
    }

    /**
     * Get the params in the session cookie.
     *
     * @return array
     */
    private function _getCookieParams()
    {
        if (null === $this->_cookie) {
            $cookie = substr(
                $this->_request->getCookie(self::COOKIE_PREFIX.$this->_appId),
                1,
                -1
            );

            $this->_cookie = array();
            foreach (explode('&', urldecode($cookie)) as $item) {
                $tokens = explode('=', $item);
                $this->_cookie[$tokens[0]] = $tokens[1];
            }
        }

        return $this->_cookie;
    }

    /**
     * Get the access token for the current session.
     *
     * @return string
     */
    public function getAccessToken()
    {
        $params = $this->_getCookieParams();
        if (isset($params['access_token'])) {
            return $params['access_token'];
        } else {
            return null;
        }
    }

    /**
     * Make a call to the graph api.
     *
     * @param string $method The method to call.
     * @param string $token  The access token to use.
     * @param array $params  Extra params.
     *
     * @return array
     */
    private function _makeGraphCall($method, $token, array $params = array())
    {
        $url = self::GRAPH_URL . '/' . $method;
        $params['access_token'] = $token;

        $url .= '?';
        foreach ($params as $key => $value) {
            $url .= $key .'='.$value.'&';
        }
        $url = substr($url, 0, -1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        curl_close($ch);

        return Zend_Json::decode($res);
    }
}
