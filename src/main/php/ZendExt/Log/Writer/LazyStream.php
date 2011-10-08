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
 * Lazy log stream writer.
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
 * Lazy log stream writer.
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
class ZendExt_Log_Writer_LazyStream extends Zend_Log_Writer_Stream
{
    private $_name;
    private $_mode;

    /**
     * Class Constructor.
     *
     * @param string|resource $streamOrUrl Stream or URL to open as a stream.
     * @param string          $mode        Mode, only if a URL is given.
     *
     * @throws Zend_Log_Exception
     */
    public function __construct($streamOrUrl, $mode = null)
    {
        // Setting the default
        if ($mode === null) {
            $mode = 'a';
        }

        if (is_resource($streamOrUrl)) {
            if (get_resource_type($streamOrUrl) != 'stream') {
                throw new Zend_Log_Exception('Resource is not a stream');
            }

            if ($mode != 'a') {
                throw new Zend_Log_Exception(
                    'Mode cannot be changed on existing streams'
                );
            }

            $this->_stream = $streamOrUrl;
        } else {
            if (is_array($streamOrUrl) && isset($streamOrUrl['stream'])) {
                $streamOrUrl = $streamOrUrl['stream'];
            }

            $this->_name = $streamOrUrl;


        }

        $this->_formatter = new Zend_Log_Formatter_Simple();
        $this->_mode = $mode;
    }

    /**
     * Open the file stream.
     *
     * @return void
     *
     * @throws Zend_Log_Exception
     */
    private function _openStream()
    {
        if (!is_resource($this->_stream)) {
            if (!($this->_stream = @fopen($this->_name, $this->_mode))) {
                $msg = '"' . $this->_name . '" cannot be opened with mode "'
                    . $this->_mode . '"';
                throw new Zend_Log_Exception($msg);
            }
        }
    }

    /**
     * Write a message to the log.
     *
     * @param array $event event data.
     *
     * @return void
     */
    protected function _write($event)
    {
        $this->_openStream();
        parent::_write($event);
    }

    /**
     * Close the stream resource.
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_name = null;
        parent::shutdown();
    }

    /**
     * Create a new instance of Zend_Log_Writer_LazyStream
     *
     * @param array|Zend_Config $config The config of the writer.
     *
     * @return Zend_Log_Writer_LazyStream
     *
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(
            array(
                'stream' => null,
                'mode'   => null,
            ), $config
        );

        $streamOrUrl = isset($config['url']) ?
            $config['url'] : $config['stream'];

        return new self(
            $streamOrUrl,
            $config['mode']
        );
    }
}