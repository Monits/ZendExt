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
 * Boolean validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @copyright 2010 Monits.
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.5.0
 */

/**
 * Boolean validator.
 *
 * @category  ZendExt
 * @package   ZendExt_Validate
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits.
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.5.0
 * @link      http://www.zendext.com/
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
