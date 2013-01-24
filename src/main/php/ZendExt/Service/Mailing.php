<?php

/**
 * Service to send html mails massively.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Service to send html mails massively.
 *
 * @category  ZendExt
 * @package   ZendExt_Service
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
class ZendExt_Service_Mailing
{
    protected $_view;
    protected $_transport;
    protected $_mail;
    protected $_subject;
    protected $_from;
    protected $_fromName;
    protected $_cc;
    protected $_bcc;

    /**
     * Creates a new mailing service.
     *
     * @param Zend_View                          $view      The view object to
     *                                                      render the
     *                                                      templates.
     * @param array|Zend_Mail_Transport_Abstract $transport An instance of a
     *                                                      transport or the
     *                                                      configuration.
     * @param string                             $mail      Which mail to send,
     *                                                      NOT the address to
     *                                                      send the e-mail.
     * @param string                             $subject   The mails' subject.
     * @param string                             $from      The mails' from
     *                                                      address.
     * @param string                             $fromName  The mails' sender.
     *
     * @return void
     */
    public function __construct(Zend_View $view, $transport,
        $mail = null, $subject = null, $from = null, $fromName = null)
    {
        $this->_view = $view;
        $this->_mail = $mail;
        $this->_subject = $subject;
        $this->_from = $from;
        $this->_fromName = $fromName;

        if ($transport instanceof Zend_Mail_Transport_Abstract) {

            $this->_transport = $transport;
        } else {

            $this->_transport = new Zend_Mail_Transport_Smtp(
                $transport['host'], $transport
            );
        }
    }

    /**
     * Assigns variables for the mail template.
     *
     * @param string $var The variable name.
     * @param mixed  $val The variable value.
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
     * Sets the name of the sender.
     *
     * @param string $from The sender's address.
     *
     * @return void
     */
    public function setFrom($from)
    {
        $this->_from = $from;
    }

    /**
     * Retrieves the sender's address.
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * Sets the sender's name.
     *
     * @param string $name The sender's name.
     *
     * @return void
     */
    public function setFromName($name)
    {
        $this->_fromName = $name;
    }

    /**
     * Retrieves the sender's name.
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->_fromName;
    }

    /**
     * Sends a mail to the given address.
     *
     * @param string|array $to The e-mail addresses where to send the mail.
     *
     * @return void
     * @throws ZendExt_Service_Mailing_EmptyRecipientException
     */
    public function send($to = null)
    {
        /**
         * This is rendered first of all so if something
         *  explodes no mail will be sent.
         */
        $content = $this->_view->render($this->_mail . '.phtml');
        $mail = new Zend_Mail($this->_view->getEncoding());

        if ($to === null && $this->_cc === null && $this->_bcc === null) {
            throw new ZendExt_Service_Mailing_EmptyRecipientException();
        }

        if ($to != null) {
            $mail->addTo($to);
        }

        if ($this->_cc != null) {
            $mail->addCc($ths->_cc);
        }

        if ($this->_bcc != null) {
            $mail->addBcc($ths->_bcc);
        }

        $mail->setSubject($this->_subject);
        $mail->setFrom($this->_from, $this->_fromName);
        $mail->setBodyHtml($content);
        $mail->setBodyText(strip_tags($content));

        // TODO : Prepare plain text version of mail to be set!!!

        $mail->send($this->_transport);
    }

    /**
     * Clears all the templates variables.
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_view->clearVars();
    }

    /**
     * Adds Cc recipient.
     *
     * @param string|array $to The e-mail addresses where to send the mail.
     *
     * @return void.
     */
    public function addCc($to)
    {
        $this->_cc = $to;
    }

    /**
     * Adds Bcc recipient.
     *
     * @param string|array $to The e-mail addresses where to send the mail.
     *
     * @return void.
     */
    public function addBcc($to)
    {
        $this->_Bcc = $to;
    }
}
