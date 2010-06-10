<?php
/**
 * Abstract template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Abstract template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
abstract class ZendExt_Crud_TemplateAbstract implements ZendExt_Crud_Template
{
    private $_title = '';

    /**
     * Set the title of the form.
     *
     * @param $title The new title for the form.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * The content to be render between head() and footer().
     *
     * @return void
     */
    private function _renderContent()
    {
    }

    /**
     * Render the form.
     *
     * @return void
     */
    public function render()
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html>';
        echo '<head>';
        echo     '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        echo     '<title>'. $this->_title .'</title>';
        echo '</head>';
        echo '<body>';

        $this->_renderContent();

        echo '</body>';
        echo '</html>';
    }
}