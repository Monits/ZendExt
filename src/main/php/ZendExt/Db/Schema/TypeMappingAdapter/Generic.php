<?php
/**
 * Generic adapter to map non-standard data types to standard ones.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Schema_TypeMappingAdapter
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Generic adapter to map non-standard data types to standard ones.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Schema_TypeMappingAdapter
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */
class ZendExt_Db_Schema_TypeMappingAdapter_Generic
{
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    const CURRENT_DATE = 'CURRENT_DATE';
    const CURRENT_TIME = 'CURRENT_TIME';

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_BIGINT = 'bigint';
    const TYPE_DOUBLE_PRECISION = 'double precision';
    const TYPE_BLOB = 'blob';
    const TYPE_TEXT = 'text';
    const TYPE_BINARY_VARYING = 'binary varying';
    const TYPE_VARCHAR = 'varchar';
    const TYPE_ENUM = 'enum';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'datetime';
    const TYPE_CHAR = 'char';
    const TYPE_FLOAT = 'float';

    /**
     * Attempts to retrieve a more standard type.
     *
     * @param string $type     The original type.
     * @param string $unsigned Whether the type is unsigned or not.
     *
     * @return array
     */
    public function getType($type, $unsigned)
    {
        switch ($type) {
            case 'bool':
                return array('name' => self::TYPE_BOOLEAN);
            case 'tinyint':
                return array(
                    'name' => self::TYPE_INTEGER,
                    'min' => $unsigned ? 0 : -128,
                    'max' => $unsigned ? 255 : 127
                );
            case 'smallint':
                return array(
                    'name' => self::TYPE_SMALLINT,
                    'min' => $unsigned ? 0 : -32768,
                    'max' => $unsigned ? 65535 : 32767
                );
            case 'mediumint':
                return array(
                    'name' => self::TYPE_INTEGER,
                    'min' => $unsigned ? 0 : -8388608,
                    'max' => $unsigned ? 16777215 : 8388607
                );
            case 'int':
                return array(
                    'name' => self::TYPE_INTEGER,
                    'min' => $unsigned ? 0 : -2147483648,
                    'max' => $unsigned ? 4294967295 : 2147483647
                );
            case 'bigint':
            case 'serial':
                return array(
                    'name' => self::TYPE_BIGINT,
                    'min' => $unsigned ? 0 : -9223372036854775808,
                    'max' => $unsigned ? 18446744073709551615
                                            : 9223372036854775807
                );
            case 'double':
                return array('name' => self::TYPE_DOUBLE_PRECISION);
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
                return array('name' => self::TYPE_BLOB);
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
                return array('name' => self::TYPE_TEXT);
            case 'varbinary':
                return array('name' => self::TYPE_BINARY_VARYING);

            case (strtolower(substr($type, 0, 4)) == 'enum'):
                return array(
                    'name' => self::TYPE_ENUM,
                    'options' => $this->_parseEnum($type)
                );
            default:
                // For types that max and min is not necessary.
                break;
        }

        return array('name' => $type);
    }

    /**
     * Retrieves a parsed enum.
     *
     * This parsing implementation works for enums defined in this format:
     * enum('a', 'b', 'c')
     *
     * @param string $enum The enum definition.
     *
     * @return array
     */
    protected function _parseEnum($enum)
    {
        /*
         *  I chose this implementation over regexp because is easier
         *  to mantain and understand.
         */
        $enum = str_replace('enum', '', $enum);

        // Remove first and last parenthesis.
        $enum = substr($enum, 1, -1);

        $expl = explode(',', $enum);
        $ret = array();

        foreach ($expl as $i) {
            // Remove whitespaces and quotes.
            $ret[] = substr(trim($i), 1, -1);
        }

        return $ret;
    }

    /**
     * Attempts to retrieve a more standard default constant.
     *
     * @param string $value The default value.
     *
     * @return string The standarized default value if found any.
     */
    public function getDefault($value)
    {
        switch ($value) {
            case 'CURRENT_TIMESTAMP()':
            case 'NOW()':
                return self::CURRENT_TIMESTAMP;

            case 'CURRENT_DATE()':
            case 'CURDATE()':
                return self::CURRENT_DATE;

            case 'CURRENT_TIME()':
            case 'CURTIME()':
                return self::CURRENT_TIME;

            default:
                // No special default found
                break;
        }

        return $value;
    }

}