<?php

/**
 * Service to send html mails massively.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Service to send html mails massively.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_Mailing
{
    protected $_view;
    protected $_transport;
    protected $_mail;
    protected $_subject;
    protected $_from;

    /**
     * Creates a new mailing service.
     * 
     * @param Zend_View $view The view object to render the templates.
     * @param array     $smtpConfig The smpt server configuration.
     * @param string    $mail Which mail to send, NOT the address
     *                        to send the e-mail.
     * @param string    $subject The mails' subject.
     * @param string    $from The mails' from address.
     *
     * @return void
     */
    public function __construct(Zend_View $view, array $smtpConfig, $mail,
        $subject, $from)
    {
        $this->_view = $view;
        $this->_mail = $mail;
        $this->_subject = $subject;
        $this->_from = $from;

        $this->_transport = new Zend_Mail_Transport_Smtp(
            $smtpConfig['host'], $smtpConfig
        );
    }


    /**
     * Assigns variables for the mail template.
     * 
     * @param string $var The variable name.
     * @param mixed $val  The variable value.
     *
     * @return void
     */
    public function assign($var, $val)
    {
        $this->_view->assign($var, $val);
    }

    /**
     * Sets which mail to send.
     * 
     * @param string $mail The mail name.
     *
     * @return void
     */
    public function setMail($mail)
    {
        $this->_mail = $mail;
    }


    /**
     * Retrieves which mail is going to be sent. 
     * 
     * @return string
     */
    public function getMail()
    {
        return $this->_mail;
    }

    /**
     * Sets the mails' subject.
     * 
     * @param string $subject The subject
     *
     * @return void
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }


    /**
     * Retrieves the mails' subject.
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Sets the mails' from address.
     * 
     * @param string $from The mail address.
     *
     * @return void
     */
    public function setFrom($from)
    {
        $this->_from = $from;
    }

    /**
     * Retrieves the mails' from address.
     * 
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * Sends a mail to the given address. 
     * 
     * @param string $to The e-mail address where to send the mail.
     *
     * @return void
     */
    public function send($to)
    {
        /**
         * This is renderer first of all so if something
         *  explodes no mail will be sent. 
         */
        $content = $this->_view->render($this->_mail . '.phtml');
        $mail = new Zend_Mail($this->_view->getEncoding());

        $mail->addTo($to);
        $mail->setSubject($this->_subject);
        $mail->setFrom($this->_from);
        $mail->setBodyHtml($content);

        $mail->send($this->_transport);

        $this->_view->clearVars();
    }

}
