<?php
/**
 * Interface for hydrators to transform database rows into rich objects.
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
 * Interface for hydrators to transform database rows into rich objects.
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
interface ZendExt_Db_Dao_Hydrator_Interface
{
    /**
     * Hydrate a row as retrieved from the database into a rich object.
     *
     * @param array  $row The row's data, as retrieved from the database.
     *
     * @return mixed The rich obejct created from the original row.
     */
    public function hydrate(array $row);
}