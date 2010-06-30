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
     * @param string $title The new title for the form.
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
    protected function _renderContent()
    {
    }

    /**
     * Render the form.
     *
     * @return void
     */
    public function render()
    {
        if (Zend_Layout::getMvcInstance() === null) {
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            echo '<html>';
            echo '<head>';
            echo '<style type="text/css">';
            $this->_formStyle();
            echo '</style>';
            echo     '<meta http-equiv="Content-Type" content="text/html;
                         charset=UTF-8" />';
            echo     '<title>'. $this->_title .'</title>';
            echo '</head>';
            echo '<body>';
        }

        $this->_renderContent();

        if (Zend_Layout::getMvcInstance() === null) {
            echo '<script type=\'text/javascript\'>',
                    'function checkField(fieldName)',
                    '{',
                        'var field = document.getElementById(fieldName);',
                        'var checkbox = document.getElementById(',
                            '\'check\' + fieldName',
                        ');',

                        'check = checkbox.getAttribute(\'checked\');',

                        'if (false == checkbox.checked) {',
                            'field.setAttribute(\'disabled\', true);',
                        '} else {',
                            'field.removeAttribute(\'disabled\')',
                        '}',
                    '}',
                '</script>';

            echo '</body>';
            echo '</html>';
        }
    }

    /**
     * Set the style of the form.
     *
     * @return string
     */
    private function _formStyle()
    {

        echo 'dt{float:left;display:inline;clear:left;width:230px}' .
             'dd ul.errors{display:inline-block;margin:3px 0;color:red;}' .
             'input{vertical-align: top;}' .
             'dd{margin:0 0 10px;float:left}' .
             '.checkboxLabel{margin-left: 30px; width: 200px;}';

        return $style;
    }
}