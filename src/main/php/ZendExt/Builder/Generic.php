<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * A generic builder.
 *
 * @category  ZendExt
 * @package   ZendExt_Builder
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * A generic builder.
 *
 * Can only build instances of classes that receive an
 * associative array of values in their constructor as their
 * first and only argument.
 *
 * @category  ZendExt
 * @package   ZendExt_Builder
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Builder_Generic
{
    protected $_data = array();

    protected $_fields = array();

    protected $_class;

    /**
     * Default handler for method calls.
     *
     * @param string $methodName The name of the method called.
     * @param array  $args       The arguments passed to the method.
     *
     * @return ZendExt_Builder_Generic
     *
     * @throws ZendExt_Builder_Exception
     */
    public function __call($methodName, $args)
    {
        if (isset($methodName[4]) && 'with' === substr($methodName, 0, 4)) {
            $var = strtolower($methodName[4]) . substr($methodName, 5);
            $val = $args[0];

            if (!isset($this->_fields[$var])) {
                throw new ZendExt_Builder_Exception('Unknown field: ' . $var);
            }

            if (isset($this->_fields[$var]['validators'])) {
                $this->_validateField(
                    $this->_fields[$var]['validators'], $var, $val
                );
            }

            $this->_data[$var] = $val;
        } else {
            throw new ZendExt_Builder_Exception(
                'Unknown method: ' . $methodName
            );
        }

        return $this;
    }

    /**
     * Validates the given field with the requested validators.
     *
     * @param array|Zend_Validate_Interface $validators The validators
     *                                                  to be applied.
     * @param string                        $var        The name of the var
     *                                                  being validated.
     * @param any                           $val        The value to be
     *                                                  validated.
     *
     * @return void
     *
     * @throws ZendExt_Builder_Exception
     * @throws ZendExt_Builder_ValidationException
     */
    private function _validateField($validators, $var, $val)
    {
        if (!is_array($validators)) {
            $validators = array($this->_fields[$var]['validators']);
        }

        if (null === $val && $this->hasDefault($var)
                && $this->getDefault($var) === null) {
            return;
        }

        foreach ($validators as $validator) {
            if (!$validator instanceof Zend_Validate_Interface) {
                throw new ZendExt_Builder_Exception(
                    'Validators must be instances of Zend_Validate_Interface'
                );
            }

            if (!$validator->isValid($val)) {
                throw new ZendExt_Builder_ValidationException(
                    $var, $validator->getMessages()
                );
            }
        }
    }

    /**
     * Creates a builder given it's configuration.
     *
     * @param array|Zend_Config $config The configuration with which
     *                                     to create a builder.
     *
     * @return ZendExt_Builder_Generic
     *
     * @throws ZendExt_Builder_Exception
     */
    public static function factory($config)
    {
        $builder = new ZendExt_Builder_Generic();
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (!isset($config['class']) || !class_exists($config['class'])) {
            throw new ZendExt_Builder_Exception(
                'Invalid config, no class to be builded specified.'
            );
        }

        if (!isset($config['fields']) || !is_array($config['fields'])) {
            throw new ZendExt_Builder_Exception(
                'Invalid config, no fields specified.'
            );
        }

        $builder->_class = $config['class'];
        $builder->_fields = $config['fields'];

        return $builder;
    }

    /**
     * Actually builds am instance of the requested class.
     *
     * @return Instance of $_class
     *
     * @throws ZendExt_Builder_Exception
     */
    public function build()
    {
        foreach ($this->_fields as $field => $config) {

            if (array_key_exists('default', $config)
                    && !isset($this->_data[$field])) {
                $this->_data[$field] = $config['default'];
            }

            if (isset($config['required']) && true === $config['required']
                    && !array_key_exists($field, $this->_data)) {
                throw new ZendExt_Builder_Exception(
                    "The field '$field' is required but missing."
                );
            }
        }

        return new $this->_class($this->_data);
    }

    /**
     * Get the names of the fields.
     *
     * @return array
     */
    public function getFieldsNames()
    {
        return array_keys($this->_fields);
    }

    /**
     * Get the default value of the field.
     *
     * @param string $field The field.
     *
     * @return mixed
     */
    public function getDefault($field)
    {
        return $this->_fields[$field]['default'];
    }

    /**
     * Checks if the given field has default value.
     *
     * @param string $field The field.
     *
     * @return boolean
     */
    public function hasDefault($field)
    {
        return array_key_exists('default', $this->_fields[$field]);
    }
}
