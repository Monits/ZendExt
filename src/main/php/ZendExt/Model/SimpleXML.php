<?php
/**
 * Abstract model using {@link SimpleXMLElement} as a base.
 *
 * @category  ZendExt
 * @package   ZendExt_Model
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Abstract model using {@link SimpleXMLElement} as a base.
 *
 * @category  ZendExt
 * @package   ZendExt_Model
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */
abstract class ZendExt_Model_SimpleXML
{
    /**
     * @var SimpleXMLElement
     */
    private $_data;

    /**
     * Get the text value of an element with a given name.
     *
     * @param string $nodeName The name of the node.
     *
     * @return string the value.
     */
    protected function _getElementText($nodeName)
    {
        $node = $this->_getElement($nodeName);
        if (null === $node) {
            return null;
        } else {
            return (string) $node;
        }
    }

    /**
     * Get an element with a given name.
     *
     * @param string $nodeName The name of the node.
     *
     * @return SimpleXMLElement
     */
    protected function _getElement($nodeName)
    {
        $result = $this->_data->xpath('//'.$nodeName);
        if (false === $result) {
            return null;
        } else {
            return array_shift($result);
        }
    }
}