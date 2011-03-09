<?php
/**
 * Update crud template.
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
 * Update crud template.
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