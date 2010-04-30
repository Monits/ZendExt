<?php
/**
 * A builder validation exception.
 *
 * @category  ZendExt
 * @package   ZendExt_Builder
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * A builder validation exception.
 *
 * @category  ZendExt
 * @package   ZendExt_Builder
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Builder_ValidationException extends Exception
{
    protected $_field;
    protected $_errors = null;

    /**
     * Creates a new ZendExt_Builder_ValidationException instance.
     *
     * @param string $field  The name of the field whose validation failed.
     * @param array  $errors The errors reported by the validator.
     *
     * @return ZendExt_Builder_ValidationException
     */
    public function __construct($field, array $errors)
    {
        $this->_field = $field;
        $this->_errors = $errors;
    }

    /**
     * Retrieves the errors reported by the validator.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Retrieves the name of the field whose valdiation failed.
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }
}