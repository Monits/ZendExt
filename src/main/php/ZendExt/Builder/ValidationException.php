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
class ZendExt_Builder_ValidationException extends ZendExt_Exception
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

        $this->message = 'Error validating field ' . $field;

        if (!empty($errors)) {
            $this->message .=
                '. First error is: "' . array_shift($errors) . '"';
        }
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