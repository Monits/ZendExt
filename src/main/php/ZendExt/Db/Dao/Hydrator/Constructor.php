<?php
/**
 * Hydrator that call's another object's constructor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Hydrator that call's another object's constructor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Db_Dao_Hydrator_Constructor
        implements ZendExt_Db_Dao_Hydrator_Interface
{
    protected $_class;

    /**
     * Class constructor.
     *
     * @param string $class The class to be used when hydrating.
     *
     * @return ZendExt_Db_Dao_Hydrator_Constructor
     */
    public function __construct($class)
    {
        $this->_class = $class;
    }

    /**
     * Hydrate a row as retrieved from the database into a rich object.
     *
     * The row names are converted to camelCase.
     *
     * @param array $row The row's data, as retrieved from the database.
     *
     * @return mixed The rich object created from the original row.
     */
    public function hydrate(array $row)
    {
        $translated = array();
        foreach ($row as $key => $value) {
            if (strpos($key, '_') !== false) {
                $key = $this->_toCamelCase($key);
            }
            $translated[$key] = $value;
        }
        return new $this->_class($translated);
    }

    
    /**
     * Translates a string with underscores into camel case
     *
     * @param    string   $str                     String in underscore format
     * @return   string                            $str translated into camel case
     */
    private function _toCamelCase($str)
    {
        $parts = explode('_', $str);
        $partCount = count($parts);

        for ($i = 1; $i < $partCount; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return implode('', $parts);
    }
}
