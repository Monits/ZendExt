<?php
/**
 * APN service notification model.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_APNS
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * APN service exception.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_APNS
 * @author    Juan Martín Sotuyo Dodero <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Service_APNS_Notification
{
    // TODO : Support launch-image. 
    //        Check how it behaves when set on pre-iOS4 devices...
    private $_alertMessage;
    private $_alertActionLocKey;
    private $_alertLocKey;
    private $_alertLocArgs;
    
    private $_badge;
    
    private $_sound;
    
    private $_customData;
    
    /**
     * Creates a new notification model to be sent through APNS.
     */
    public function __construct()
    {
        $this->_alertMessage = null;
        $this->_alertActionLocKey = null;
        $this->_alertLocKey = null;
        $this->_alertLocArgs = null;
        
        $this->_badge = null;
        
        $this->_sound = null;
        
        $this->_customData = array();
    }
    
    /**
     * Sets the badge for this notification.
     * 
     * @param int $badgeCount The badge for this notification.
     * 
     * @return void
     */
    public function setBadge($badgeCount)
    {
        $this->_badge = (int) $badgeCount;
    }
    
    /**
     * Sets the sound for this notification.
     * 
     * @param string $sound The sound for this notification.
     * 
     * @return void
     */
    public function setSound($sound)
    {
        $this->_sound = (string) $sound;
    }
    
    /**
     * Adds custom data for this notification.
     * 
     * @param string $key   The key for the custom key-value pair.
     * @param string $value The value for the custom key-value pair.
     * 
     * @return void
     */
    public function addCustomData($key, $value)
    {
        $this->_customData['key'] = $value;
    }
    
    /**
     * Removes the custom data for this notification associated with
     * the given key.
     * 
     * @param string $key The key for the custom key-value pair to remove.
     * 
     * @return void.
     */
    public function removeCustomData($key)
    {
        unset($this->_customData[$key]);
    }
    
    /**
     * Sets the alert for this notification.
     * 
     * @param string $alertMsg     The message for the alert in this
     *                             notification.
     * @param string $actionLocKey The key for the localized string to be
     *                             used instead of 'View'.
     * 
     * @return void
     */
    public function setAlert($alertMsg, $actionLocKey = null)
    {
        $this->_alertMessage = $alertMsg;
        $this->_alertActionLocKey = $actionLocKey;
        
        // Reset other values
        $this->_alertLocArgs = null;
        $this->_alertLocKey = null;
    }
    
    /**
     * Sets the alert for this notification, using localized strings.
     * 
     * @param string $locKey  The key of the localized string to be displayed.
     * @param array  $locArgs The arguments to be used inthe localized string.
     * 
     * @return void
     */
    public function setLocalizedAlert($locKey, array $locArgs)
    {
        $this->_alertLocArgs = $locArgs;
        $this->_alertLocKey = $locKey;
        
        // Reset other values
        $this->_alertMessage = null;
        $this->_alertActionLocKey = null;
    }
    
    /**
     * Serializes the notification to a json object as required by APNS.
     * 
     * @return string Json object for APNS.
     */
    public function serialize()
    {
        $message = array(
            'aps' => array()
        );
        
        // Setup the alert part...
        if (null !== $this->_alertActionLocKey) {
            $message['aps']['alert'] = array(
                'body' => $this->_alertMessage,
                'action-loc-key' => $this->_alertActionLocKey
            );
        } else if (null !== $this->_alertLocArgs) {
            $message['aps']['alert'] = array(
                'loc-key' => $this->_alertLocKey,
                'loc-args' => $this->_alertLocArgs
            );
        } else {
            $message['aps']['alert'] = $this->_alertMessage;
        }
        
        // ... the badge ...
        if (null !== $this->_badge) {
            $message['aps']['badge'] = $this->_badge;
        }
        
        // ... the custom data (preventing it from overriding aps data) ...
        $message = array_merge($this->_customData, $message);
        
        // ... and the sound
        if (null !== $this->_sound) {
            $message['aps']['sound'] = $this->_sound;
        }
        
        return Zend_Json::encode($message);
    }
}
