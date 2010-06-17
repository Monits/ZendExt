<?php
/**
 * Extended mail writer for Zend_Log.
 *
 * @category  ZendExt
 * @package   ZendExt_Log_Writer
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Extended mail writer for Zend_Log.
 *
 * @category  ZendExt
 * @package   ZendExt_Log_Writer
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Log_Writer_Mail extends Zend_Log_Writer_Mail
{
    protected $_transport = null;

    /**
     * Construct the writer.
     * Constructs the mail writer; requires a Zend_Mail instance, and takes an
     * optional Zend_Layout instance.  If Zend_Layout is being used,
     * $this->_layout->events will be set for use in the layout template.
     *
     * @param Zend_Mail                    $mail      Mail instance
     * @param Zend_Layout                  $layout    Layout instance;
     *                                                 optional
     * @param Zend_Mail_Transport_Abstract $transport Transport instance;
     *                                                 optional
     *
     * @return void
     */
    public function __construct(Zend_Mail $mail, Zend_Layout $layout = null,
        Zend_Mail_Transport_Abstract $transport = null)
    {
        parent::__construct($mail, $layout);
        $this->_transport = $transport;
    }

    /**
     * Sends mail to recipient(s) if log entries are present.  Note that both
     * plaintext and HTML portions of email are handled here.
     *
     * @return void
     */
    public function shutdown()
    {
        // If there are events to mail, use them as message body.  Otherwise,
        // there is no mail to be sent.
        if (empty($this->_eventsToMail)) {
            return;
        }

        if ($this->_subjectPrependText !== null) {
            // Tack on the summary of entries per-priority to the subject
            // line and set it on the Zend_Mail object.
            $numEntries = $this->_getFormattedNumEntriesPerPriority();
            $this->_mail->setSubject(
                "{$this->_subjectPrependText} ({$numEntries})"
            );
        }


        // Always provide events to mail as plaintext.
        $this->_mail->setBodyText(implode('', $this->_eventsToMail));

        // If a Zend_Layout instance is being used, set its "events"
        // value to the lines formatted for use with the layout.
        if ($this->_layout) {
            // Set the required "messages" value for the layout.  Here we
            // are assuming that the layout is for use with HTML.
            $this->_layout->events =
                implode('', $this->_layoutEventsToMail);

            // If an exception occurs during rendering, convert it to a notice
            // so we can avoid an exception thrown without a stack frame.
            try {
                $this->_mail->setBodyHtml($this->_layout->render());
            } catch (Exception $e) {
                trigger_error(
                    'exception occurred when rendering layout; ' .
                    'unable to set html body for message; ' .
                    'message = {$e->getMessage()}; ' .
                    'code = {$e->getCode()}; ' .
                    'exception class = ' . get_class($e),
                    E_USER_NOTICE
                );
            }
        }

        // Finally, send the mail.  If an exception occurs, convert it into a
        // warning-level message so we can avoid an exception thrown without a
        // stack frame.
        try {
            $this->_mail->send($this->_transport);
        } catch (Exception $e) {
            trigger_error(
                'unable to send log entries via email; ' .
                'message = {$e->getMessage()}; ' .
                'code = {$e->getCode()}; ' .
                'exception class = ' . get_class($e),
                E_USER_WARNING
            );
        }

    }
}
