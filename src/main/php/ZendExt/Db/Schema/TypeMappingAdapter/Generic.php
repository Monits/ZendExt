<?php
/**
 * Generic adapter to map non-standard data types to standard ones.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Schema_TypeMappingAdapter
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Generic adapter to map non-standard data types to standard ones.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Schema_TypeMappingAdapter
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Db_Schema_TypeMappingAdapter_Generic
{
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    const CURRENT_DATE = 'CURRENT_DATE';
    const CURRENT_TIME = 'CURRENT_TIME';

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
                return array('name' => 'boolean');
            case 'tinyint':
                return array(
                    'name' => 'integer',
                    'min' => $unsigned ? 0 : -128,
                    'max' => $unsigned ? 255 : 127
                );
            case 'smallint':
                return array(
                    'name' => 'smallint',
                    'min' => $unsigned ? 0 : -32768,
                    'max' => $unsigned ? 65535 : 32767
                );
            case 'mediumint':
                return array(
                    'name' => 'integer',
                    'min' => $unsigned ? 0 : -8388608,
                    'max' => $unsigned ? 16777215 : 8388607
                );
            case 'int':
                return array(
                    'name' => 'integer',
                    'min' => $unsigned ? 0 : -2147483648,
                    'max' => $unsigned ? 4294967295 : 2147483647
                );
            case 'bigint':
            case 'serial':
                return array(
                    'name' => 'bigint',
                    'min' => $unsigned ? 0 : -9223372036854775808,
                    'max' => $unsigned ? 18446744073709551615
                                            : 9223372036854775807
                );
            case 'double':
                return array('name' => 'double precision');
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
                return array('name' => 'blob');
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
                return array('name' => 'text');
            case 'varbinary':
                return array('name' => 'binary varying');
            default:
                // For types that max and min is not necessary.
                return array('name' => $type);
        }
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
                return $value;
        }
    }

}