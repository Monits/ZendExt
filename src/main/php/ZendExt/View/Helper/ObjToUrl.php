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
 * Utily to generate urls from model objects.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved
 * @version   Release: 1.5.0
 * @link      http://www.monits.com
 * @since     1.5.0
 */

/**
 * Utily to generate urls from model objects.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.5.0
 * @link      http://www.monits.com/
 * @since     1.5.0
 */
class ZendExt_View_Helper_ObjToUrl extends Zend_View_Helper_Abstract
{

    private static $_templates;

    /**
     * Retrieves an url generated from the given object.
     *
     * @param mixed $obj The model object to use.
     *
     * @return string
     */
    public function objToUrl($obj)
    {
        // TODO: Maybe this would be more efficient using and interface with
        // a toArray and getType method instead of calling getters
        // and get_class? Benchmark!

        $conf = $this->getTemplates();
        $class = get_class($obj);

        if (!isset($conf[$class])) {
            throw new Exception('No url config for ' . $class);
        }

        $template = $conf[$class];

        preg_match_all('/%(\w+)%/', $template, $matches);
        $ret = $template;

        foreach ($matches[1] as $placeholder) {
            $method = 'get' . ucfirst($placeholder);

            $ret = str_replace('%' . $placeholder . '%', $obj->$method(), $ret);
        }

        return $ret;
    }

    /**
     * Retrieves the helper's templates.
     *
     * @return array
     */
    private function getTemplates()
    {
        if (null === self::$_templates) {
            throw new Exception(
                'Templates must be set before using the helper'
            );
        }

        return self::$_templates;
    }

    /**
     * Sets the url templates to use.
     *
     * The templates should contain placeholders which map to getter methods in
     * the model object, i.e:
     * <code>'/%id%/%name%'</code>
     * would be equivalent to
     * <code>"/" . $obj->getId() . '/' . $obj->getName()</code>
     *
     * @param array $templates The list of templates.
     *
     * @return void
     */
    public static function setTemplates(array $templates)
    {
        self::$_templates = $templates;
    }

}