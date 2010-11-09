<?php
/**
 * Hydrator that call's another object's constructor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Hydrator that call's another object's constructor.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
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
     * @param array $row The row's data, as retrieved from the database.
     *
     * @return mixed The rich object created from the original row.
     */
    public function hydrate(array $row)
    {
        return new $this->_class($row);
    }
}