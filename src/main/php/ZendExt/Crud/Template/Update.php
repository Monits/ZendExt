<?php
/**
 * Update crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Update crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Crud_Template_Update extends ZendExt_Crud_TemplateAbstract
{
    protected $_view;

    /**
     * Crud template construct.
     *
     * @param Zend_View $view The view.
     */
    public function __construct(Zend_View $view)
    {
        $this->_view = $view;
    }

    /**
     * The content to be render between head() and footer().
     *
     * @return void
     */
    protected function _renderContent()
    {
        echo $this->_view->form;
    }
}