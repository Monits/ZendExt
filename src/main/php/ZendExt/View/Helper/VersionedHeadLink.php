<?php
/**
 * View helper to insert link elements with version info.
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
 * View helper to insert link elements with version info.
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
class ZendExt_View_Helper_VersionedHeadLink extends Zend_View_Helper_HeadLink
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_VersionedHeadLink';

    /**
     * Set the config data for the stylesheets.
     *
     * @param array|Zend_Config $config        The config data.
     * @param boolean           $ignoreDefault Ignore the default setting.
     *
     * @return Zend_View_Helper_VersionedHeadLink Fluid interface
     */
    public function setVersions( $config = array(), $ignoreDefault = false )
    {
        $default = array();
        $styles = array();

        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
            $config = $config['item'];
        }

        foreach ($config as $style) {
            $styles[$style['name']] = $this->_createStylesheet(
                $style['name'],
                $style
            );

            if (!$ignoreDefault && isset( $style['default'])
                && $style['default'] ) {

                $default[] = $style['name'];
            }
        }

        $container = $this->getContainer();
        $container['styles'] = $styles;

        foreach ($default as $name) {
            $this->appendStylesheet($name);
        }

        return $this;
    }

    /**
     * Set a file versioner.
     *
     * @param Zend_Versioner $versioner The versioner to use.
     *
     * @return Zend_View_Helper_VersionedHeadLink Fluid interface
     */
    public function setVersioner(Zend_Versioner $versioner)
    {
        $container = $this->getContainer();
        $container['versioner'] = $versioner;

        return $this;
    }

    /**
     * Is the linked stylesheet a duplicate?
     *
     * @param string $uri The uri to check.
     *
     * @return boolean
     */
    protected function _isDuplicateStylesheet($uri)
    {
        foreach ($this->getContainer() as $item) {
            if ($item instanceof stdClass
                && ($item->rel == 'stylesheet')
                && ($item->href == $uri)) {

                return true;
            }
        }
        return false;
    }

    /**
     * Create item for stylesheet link item.
     *
     * @param array $args The data to use.
     *
     * @return stdClass|false Returns false if stylesheet is a duplicate
     */
    public function createDataStylesheet(array $args)
    {
        $args[0] = $this->_getVersionedUri($args[0]);
        return parent::createDataStylesheet($args);
    }

    /**
     * Prepare the uri for output.
     *
     * @param string $name The uri to prepare.
     *
     * @return string
     */
    private function _getVersionedUri($name)
    {
        $container = $this->getContainer();
        $version = null;

        if (isset($container['styles'][$name])) {
            $fileData = $container['styles'][$name];

            $uri = $fileData->path;

            if ($fileData->args) {
                $uri .= '?'.$fileData->args;
            }

            if ($fileData->external && $fileData->version === null) {
                return $uri;
            } else {
                $version = $fileData->version;
            }
        } else {

            $uri = $name;
        }

        if ( $version === null ) {

            if ( isset($container['versioner'])
                && ($container['versioner'] instanceof Zend_Versioner) ) {

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
     * Add stylesheet version data.
     *
     * @param string $name   The name of the script.
     * @param array  $values Array of config values.
     *
     * @return stdclass The created script object.
     */
    private function _createStylesheet($name, $values)
    {
        $sheet = new stdclass();
        $keys = array('path', 'version', 'args', 'external');
        if ( !isset($values['path']) ) {

            $values['path'] = '/css/'.$name.'.css';
        }

        foreach ($keys as $key) {

            if ( isset($values[$key]) ) {

                $sheet->$key = $values[$key];
            } else {

                $sheet->$key = null;
            }
        }

        return $sheet;
    }

    /**
     * versionedHeadLink() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @param array  $attributes The attributes to set.
     * @param string $placement  Where to place the link.s
     *
     * @return Zend_View_Helper_VersionedHeadLink
     */
    public function versionedHeadLink(array $attributes = null,
        $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
    {
        return $this->headLink($attributes, $placement);
    }

    /**
     * Render link elements as string.
     *
     * @param string|int $indent the indentation to use.
     *
     * @return string
     */
    public function toString($indent = null)
    {
        $container = $this->getContainer();

        $styles = $container['styles'];
        $container->offsetUnset('styles');

        $versioner = null;
        if ( isset($container['versioner'])  ) {
            $versioner = $container['versioner'];
            $container->offsetUnset('versioner');
        }


        $result = parent::toString($indent);

        $container['styles'] = $styles;
        if ( $versioner !== null ) {
            $container['versioner'] = $versioner;
        }
        return $result;
    }
}