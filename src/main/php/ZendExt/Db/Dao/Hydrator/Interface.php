<?php 
/*

   Copyright 2011 Monits
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
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