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
     * Create the header of the form.
     *
     * @return void
     */
    public function header()
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html>';
        echo '<head>';
        echo     '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        echo     '<title>'. $this->_title .'</title>';
        echo '</head>';
        echo '<body>';
    }

    /**
     * Create the footer of the form.
     *
     * @return void
     */
    public function footer()
    {
        echo '</body>';
        echo '</html>';
    }

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
}