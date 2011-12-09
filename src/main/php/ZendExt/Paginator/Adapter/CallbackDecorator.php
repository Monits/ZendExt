<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * A decorator for Zend_Paginator_Adapter that applies a callback function.
 *
 * @category  ZendExt
 * @package   ZendExt_Paginator_Adapter
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * A decorator for Zend_Paginator_Adapter that applies a callback function.
 *
 * @category  ZendExt
 * @package   ZendExt_Paginator_Adapter
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Paginator_Adapter_CallbackDecorator
    implements Zend_Paginator_Adapter_Interface
{
    /**
     * @var Zend_Paginator_Adapter_Interface
     */
    protected $_adapter;

    /**
     * @var callback
     */
    protected $_callback;

    /**
     * @var boolean
     */
    protected $_callbackIsConstructor = false;

    /**
     * Creates a new ZendExt_Paginator_Adapter_CallbackDecorator instance
     *
     * @param callback                         $callback The callback function
     *                                                   to apply to each
     *                                                   element.
     * @param Zend_Paginator_Adapter_Interface $adapter  The adapter to
     *                                                   decorate.
     *
     * @return void
     *
     * @throws ZendExt_Exception
     */
    public function __construct($callback,
                        Zend_Paginator_Adapter_Interface $adapter)
    {
        // Make sure callback is valid
        if (!is_callable($callback)) {
            if (is_string($callback) && class_exists($callback)) {
                $this->_callbackIsConstructor = true;
            } else {
                throw new ZendExt_Exception(
                    'Invalid callback supplied. '
                    . 'Provide either a function or a class name'
                );
            }
        }

        $this->_callback = $callback;
        $this->_adapter = $adapter;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param integer $offset           Page offset
     * @param integer $itemCountPerPage Number of items per page
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $items = $this->_adapter->getItems($offset, $itemCountPerPage);

        if ($this->_callbackIsConstructor) {
            // Create elements with the obtained data
            $ret = array();
            foreach ($items as $item) {
                $ret[] = new $this->_callback($item);
            }
        } else {
            // Apply callback function
            $ret = array();
            foreach ($items as $item) {
                $ret[] = call_user_func($this->_callback, $item);
            }
        }

        return $ret;
    }

    /**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
    public function count()
    {
        return $this->_adapter->count();
    }
}