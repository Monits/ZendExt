<?php
/**
 * Template interface.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud
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
 * Template interface.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
interface ZendExt_Crud_Template
{
    /**
     * Render the form.
     *
     * @return void
     */
    public function render();
}