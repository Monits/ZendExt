<?php
/**
 * New crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * New crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Crud_Template_New extends ZendExt_Crud_TemplateAbstract
{
    protected $_view;

    /**
     * Crud template construct.
     *
     * @param Zend_View $view
     */
    public function __construct(Zend_View $view)
    {
        $this->_view = $view;
    }

    /**
     * Render the form.
     *
     * @param string $title The title of the form.
     *
     * @return void
     */
    public function render($title = null)
    {
        if (null !== $title) {
            $this->setTitle($title);
        }

        $this->header();
        // TODO : Hacer HTML completo y bonito
        echo $this->_view->form;

        $this->footer();
    }
}