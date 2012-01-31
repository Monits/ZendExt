<?php
/**
 * Uri validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @copyright 2011 Monits.
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Uri validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits.
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */
class ZendExt_Validate_Uri extends Zend_Validate_Abstract
{
    const INVALID_URI = 'invalidUri';

    protected $_messageTemplates = array(
        self::INVALID_URI   => '"%value%" is not a valid URI.
            Remember to start with http:// or https://',
    );

    /**
     * Check whether the value is a valid uri or not.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean Whether the value is valid or not.
     */
    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URI);
            return false;
        }

        return true;
    }
}
