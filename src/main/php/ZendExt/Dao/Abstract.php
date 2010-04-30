<?php
/**
 * Abstract DAO implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Dao
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Abstract DAO implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Dao
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
abstract class ZendExt_Dao_Abstract
{
    private static $_tables = array();

    protected $_tableClass = null;

    /**
     * Retrieves the table instance to be used.
     *
     * @return Zend_Db_Table_Abstract The table to be used by this DAO.
     */
    protected function _getTable()
    {
        if (!isset(self::$_tables[$this->_tableClass])) {
            /*
             * TODO : Once multiple db support is built into ZendExt
             * this will need refactoring.
             */
            self::$_tables[$this->_tableClass] = new $this->_tableClass();
        }

        return self::$_tables[$this->_tableClass];
    }
}