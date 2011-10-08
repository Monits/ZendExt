<?php 
/*

   Copyright 2011 Monits
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
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
                    "message = {$e->getMessage()}; " .
                    "code = {$e->getCode()}; " .
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
                "message = {$e->getMessage()}; " .
                "code = {$e->getCode()}; " .
                'exception class = ' . get_class($e),
                E_USER_WARNING
            );
        }
    }

    /**
     * Factory method for ZendExt_Log_Writer_Mail.
     *
     * @param Zend_Config|array $config The config.
     *
     * @return ZendExt_Log_Writer_Mail
     */
    public static function factory($config)
    {
        if ($config instanceof Zend_Config) {

            $config = $config->toArray();
        }

        $transport = null;
        if ($config['transport'] == 'smtp') {

            $transport = new Zend_Mail_Transport_Smtp(
                $config['host'],
                $config['config']
            );
        }

        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($config['to']);
        $mail->setFrom($config['from']);
        $mail->setSubject($config['subject']);

        return new ZendExt_Log_Writer_Mail($mail, null, $transport);
    }
}
