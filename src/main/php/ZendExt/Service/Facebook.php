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
    private static $_params = array(
                                  'locale'  => 'fb_sig_locale',
                                  'friends' => 'fb_sig_friends',
                                  'ajax'    => 'fb_sig_is_ajax',
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

    /**
     * Construct a new FB API service.
     *
     * @param string  $apiKey         The API key given by FB.
     * @param string  $apiSecret      The API secret given by FB.
     * @param boolean $generateSecret Whether session API calls should
     * 								  generate a new secret.
     */
    public function __construct($apiKey, $apiSecret, $generateSecret=false)
    {
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
        return $this->_fb->get_loggedin_user();
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
}