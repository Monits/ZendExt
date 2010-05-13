<?php
/**
 * View helper to insert inline scripts elements with version info.
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
 * View helper to insert inline scripts elements with version info.
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
class ZendExt_View_Helper_VersionedInlineScript
    extends ZendExt_View_Helper_VersionedHeadScript
{

    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_VersionedInlineScript';

    /**
     * Return versionedInlineScript object.
     *
     * Returns versionedInlineScript helper object; optionally, allows
     * specifying a script or script file to include.
     *
     * @param string $mode      Script or file.
     * @param string $spec      Script/url.
     * @param string $placement Append, prepend, or set.
     * @param array  $attrs     Array of script attributes.
     * @param string $type      Script type and/or array of script attributes.
     *
     * @return Zend_View_Helper_VersionedInlineScript
     */
    public function versionedInlineScript(
        $mode = Zend_View_Helper_HeadScript::FILE, $spec = null,
        $placement = 'APPEND', array $attrs = array(),
        $type = 'text/javascript')
    {
        return $this->versionedHeadScript(
            $mode,
            $spec,
            $placement,
            $attrs,
            $type
        );
    }
}