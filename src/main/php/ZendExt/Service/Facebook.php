<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Wrapper for Facebook API library.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
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
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_Facebook
{
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

        $this->_fb = new Facebook(
            array(
                'appId' => $appId,
                'secret' => $apiSecret
            )
        );
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
        return $this->_fb->getUser();
    }

    /**
     * Get the uids of the friends of the current user that installed the app.
     *
     * @return array
     */
    public function getAuthorizedFriends()
    {
        $friends = $this->_fb->api(
        	array(
        		'method' => 'friends.getappusers'
        	)
        );
        if (is_array($friends)) {
            return $friends;
        }

        return array();
    }

    /**
     * Get the uids of the friends of the given user.
     *
     * @return array
     */
    public function getFriends($userId = null)
    {
        if ($userId === null) {
            $userId = $this->getUserId();
        }

        $friends = $this->_fb->api('/' . $userId . '/friends');

        if (is_array($friends)) {
            return $friends['data'];
        }

        return array();
    }

    /**
     * Performs a FQL query.
     *
     * @return array
     */
    public function execFql($query)
    {
        $ret = $this->_fb->api(
        	array(
        		'method' => 'fql.query',
        	    'query' => $query
        	)
        );

        if (is_array($ret)) {
            return $ret;
        }

        return array();
    }

    /**
     * Post a message on the given wall (own by default).
     *
     * @param string  $msg      The message to be posted.
     * @param integer $userId   The id of the user on whose wall to post.
     * @param string  $picture  The picture associated with the post.
     * @param string  $actions  The actions for this post.
     * @param string  $link     The link associated with the post.
     * @param string  $linkName The name of the link associated with the post.
     *
     * @return void
     */
    public function postFeed($msg, $userId = null, $picture = null,
        $actions = null, $link = null, $linkName = null, $linkDescription = null)
    {
        if ($userId === null) {
            $userId = $this->getUserId();
        }

        $opts = array(
            'message' => $msg
        );

        if (null !== $actions) {
            $opts['actions'] = $actions;
        }

        if (null !== $link) {
            $opts['link'] = $link;
        }

        if (null !== $linkName) {
            $opts['name'] = $linkName;
        }

        if (null !== $linkDescription) {
            $opts['description'] = $linkDescription;
        }

        if (null !== $picture) {
            $opts['picture'] = $picture;
        }

        $this->_fb->api(
        	'/' . $userId . '/feed',
        	'POST',
            $opts
        );
    }

    /**
     * Get the users info.
     *
     * @param integer $userId The id of the user to get infor of.
     *
     * @return array
     */
    public function getUserInfo($userId)
    {
        return $this->_fb->api(
            '/' . $userId
        );
    }

    /**
     * Generate the profile pic url.
     *
     * @param string $uid  The facebook uid.
     * @param string $type The size of the image to ask for. Has to be one of:
     *                     {@see ZendExt_Service_Facebook::PHOTO_SMALL},
     *                     {@see ZendExt_Service_Facebook::PHOTO_SQUARE} or
     *                     {@see ZendExt_Service_Facebook::PHOTO_LARGE}.
     *
     * @return string
     */
    public function generateProfilePicUrl($uid, $type = self::PHOTO_SMALL)
    {
        return self::GRAPH_URL.'/'.$uid.'/picture?type='.$type;
    }

    /**
     * Retrieves the signed request values.
     *
     * @return array
     */
    public function getSignedRequest() {
        $request = $this->_request->getParam('signed_request');

        if ($request === null) {
            return null;
        }

        list($encoded_sig, $payload) = explode('.', $request, 2);

        // decode the data
        $sig = $this->_base64UrlDecode($encoded_sig);
        $data = json_decode($this->_base64UrlDecode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $this->_apiSecret, $raw = true);
        if ($sig !== $expected_sig) {
            return null;
        }

        return $data;
    }

    /**
     * Internal function to decode signed requests.
     *
     * @param string $input The input to process.
     *
     * @return string
     */
    protected function _base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
  
    /**
     * Checks if the request is from an admin of the given page.
     * 
     * @param string $pageId The id of the facebook's page.
     * 
     * @return boolean
     */
    public function isPageAdmin($pageId)
    {
        $signedRequest = $this->getSignedRequest();
        
        if (isset($signedRequest['page']['admin'])) {
            return ($signedRequest['page']['admin'] >= 1 ? true : false);
        } else {
            return null;
        }
    }
}
