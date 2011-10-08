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
 * View helper to insert scripts elements with version info.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */


/**
 * View helper to insert scripts elements with version info.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_View_Helper_VersionedHeadScript
    extends Zend_View_Helper_HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_VersionedHeadScript';

    /**
     * Set the config data for the scripts.
     *
     * @param array|Zend_Config $config        The config data.
     * @param boolean           $ignoreDefault Whether to ignore the default
     *                                            setting or not.
     *
     * @return void
     */
    public function setVersions($config = array(), $ignoreDefault = false)
    {
        if ( $config instanceof Zend_Config ) {

            $config = $config->toArray();
            $config = $config['item'];
        }

        $scripts = array();
        $default = array();
        foreach ( $config as $script ) {

            $scripts[$script['name']] = $this->_createScript(
                $script['name'],
                $script
            );

            if ( !$ignoreDefault && isset($script['default'])
                && $script['default'] ) {

                $default[] = $script['name'];
            }
        }

        $container = $this->getContainer();
        $container['script'] = $scripts;

        foreach ( $default as $name ) {

            $this->appendFile($name);
        }
    }

    /**
     * Set a file versioner.
     *
     * @param Zend_Versioner $versioner A instance of a versioner.
     *
     * @return Zend_View_Helper_VersionedHeadScript Fluid interface
     */
    public function setVersioner(ZendExt_Versioner $versioner)
    {
        $container = $this->getContainer();
        $container['versioner'] = $versioner;

        return $this;
    }

    /**
     * Is the file specified a duplicate?.
     *
     * @param string $file The file name.
     *
     * @return boolean
     */
    protected function _isDuplicate($file)
    {
        foreach ($this->getContainer() as $item) {
            if ( $this->_isValid($item)
                && ($item->source === null)
                && array_key_exists('src', $item->attributes)
                && ($file == $item->attributes['src'])) {

                return true;
            }
        }
        return false;
    }

    /**
     * Create data item containing all necessary components of script.
     *
     * @param string $type       The type of data to create.
     * @param array  $attributes The attributes to add to the item.
     * @param string $content    The content for the item.
     *
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        if ( $content === null ) {

            $attributes['src'] = $this->_getVersionedUri($attributes['src']);
        }
        return parent::createData($type, $attributes, $content);
    }

    /**
     * Prepare the uri for output.
     *
     * @param string $name The name of the uri to prepare.
     *
     * @return string
     */
    private function _getVersionedUri($name)
    {
        $container = $this->getContainer();
        $version = null;

        if ( isset( $container['script'][$name] ) ) {
            $fileData = $container['script'][$name];

            $uri = $fileData->path;

            if ( $fileData->args ) {

                $uri .= '?'.$fileData->args;
            }

            if ( $fileData->external && $fileData->version === null ) {

                return $uri;
            } else {

                $version = $fileData->version;
            }
        } else {

            $uri = $name;
        }

        if ( $version === null ) {

            if ( isset($container['versioner'])
                && ($container['versioner'] instanceof ZendExt_Versioner) ) {

                $marker = strpos($uri, '?');
                if ( $marker !== false ) {

                    $fileName = substr($uri, 0, $marker);
                } else {

                    $fileName = $uri;
                }
                $version = $container['versioner']->getFileVersion($fileName);
            } else {

                return $uri;
            }
        }

        $marker = strpos($uri, '?');
        if ( $marker !== strlen($uri)-1 ) {

            if ( $marker === false ) {

                $uri .= '?';
            } else {

                $marker = strpos($uri, '&');
                if ( $marker !== false ) {

                    $uri .= ( $marker == strlen($uri)-1 ) ? '' : '&';
                } else {

                    $uri .= '&';
                }
            }
        }

        $uri .= 'v='.$version;

        return $uri;
    }

    /**
     * Create a script object.
     *
     * @param string $name   The name of the script.
     * @param array  $values Array of config options.
     *
     * @return stdclass The created script object.
     */
    private function _createScript($name, $values)
    {
        $script = new stdclass();
        $keys = array('path', 'version', 'args', 'external');
        if ( !isset($values['path']) ) {

            $values['path'] = '/js/'.$name.'.js';
        }

        foreach ($keys as $key) {

            if ( isset($values[$key]) ) {

                $script->$key = $values[$key];
            } else {

                $script->$key = null;
            }
        }

        return $script;
    }

    /**
     * Return versionedHeadScript object
     *
     * Returns versionedHeadScript helper object; optionally, allows specifying
     * a script or script file to include.
     *
     * @param string $mode      Script or file.
     * @param string $spec      Script/url.
     * @param string $placement Append, prepend, or set.
     * @param array  $attrs     Array of script attributes.
     * @param string $type      Script type and/or array of script attributes.
     *
     * @return Zend_View_Helper_VersionedHeadScript
     */
    public function versionedHeadScript(
        $mode = Zend_View_Helper_HeadScript::FILE, $spec = null,
        $placement = 'APPEND', array $attrs = array(),
        $type = 'text/javascript')
    {
        return $this->headScript($mode, $spec, $placement, $attrs, $type);
    }
}
