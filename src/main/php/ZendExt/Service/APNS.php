<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Service for Apple Push Notifications Service (APNS).
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Service for Apple Push Notifications Service (APNS).
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @author    Juan Martín Sotuyo Dodero <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_APNS
{
    const SANDBOX_PUSH_URL = 'ssl://gateway.sandbox.push.apple.com:2195';
    const SANDBOX_FEEDBACK_URL = 'ssl://feedback.sandbox.push.apple.com:2196';
    
    const PRODUCTION_PUSH_URL = 'ssl://gateway.push.apple.com:2195';
    const PRODUCTION_FEEDBACK_URL = 'ssl://feedback.push.apple.com:2196';
    
    private $_certificatePath;
    
    private $_sandbox;
    
    /**
     * Construct a new service for push notifications.
     * 
     * @param string $certificatePath Path to the certificate used
     *                                to connect to APNS. MUST be a pem file.
     * @param string $sandbox         Wether if the certificate is for sandbox
     *                                usage or not.
     */
    public function __construct($certificatePath, $sandbox = false)
    {
        $this->_certificatePath = $certificatePath;
        $this->_sandbox = $sandbox;
    }

    /**
     * Pushes the given message to all the devices identified by the given tokens.
     * 
     * @param string|array                      $tokens  The tokens to which to
     *                                                   send the notification.
     * @param ZendExt_Service_APNS_Notification $message The message to be sent.
     * 
     * @return void
     * @throws ZendExt_Service_APNS_CommunicationException
     */
    public function push($tokens, ZendExt_Service_APNS_Notification $message)
    {
        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_certificatePath);
        
        $pushUrl = $this->_sandbox ? self::SANDBOX_PUSH_URL : self::PRODUCTION_PUSH_URL;
        
        $fd = stream_socket_client($pushUrl, $error, $errorString, 100, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fd) {
            throw new ZendExt_Service_APNS_CommunicationException();
        } else {
            $json = $message->serialize();
            
            foreach ($tokens as $token) {
                $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($json)) . $json;
                $fwrite = fwrite($fd, $msg);
                if (!$fwrite) {
                    throw new ZendExt_Service_APNS_CommunicationException();
                }
            }
        }
        
        fclose($fd);
    }
    
    /**
     * Checks the feedback channel to see if any tokens are no longer valid.
     * 
     * @return array List of tokens which are no longer valid.
     * @throws ZendExt_Service_APNS_CommunicationException
     */
    public function checkFeedback() {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_certificatePath);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        
        $feedbackUrl = $this->_sandbox ? self::SANDBOX_FEEDBACK_URL : self::PRODUCTION_FEEDBACK_URL;
        
        $fd = stream_socket_client($feedbackUrl, $error, $errorString, 100, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fd) {
            throw new ZendExt_Service_APNS_CommunicationException();
        }
        
        $removedDevices = array();
        
        while ($devcon = fread($fd, 38)) {
            $arr = unpack('H*', $devcon);
            $token = substr(trim(implode('', $arr)), 12, 64);
            
            if (!empty($token)) {
                $removedDevices[] = $token;
            }
        }
        
        fclose($fd);
        
        return $removedDevices;
    }
}
