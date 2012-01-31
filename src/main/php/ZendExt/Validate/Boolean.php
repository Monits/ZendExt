<?php
/**
 * Boolean validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @copyright 2011 Monits.
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.5.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Boolean validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits.
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.5.0
 * @link      http://www.monits.com/
 * @since     1.5.0
 */
class ZendExt_Validate_Boolean extends Zend_Validate_Abstract
{
    const INVALID_VALUE = 'invalidValue';

    protected $_messageTemplates = array(
        self::INVALID_VALUE   => "'%value%' is not a boolean value.",
    );

    /**
     * Check whether the value is a valid boolean or not.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean Whether the value is valid or not.
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_bool($value)) {
            $this->_error(self::INVALID_VALUE);
            return false;
        }

        return true;
    }
}
