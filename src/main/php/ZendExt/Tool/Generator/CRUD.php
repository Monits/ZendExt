<?php
/**
 * Code generator CRUD.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
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
 * Code generator CRUD.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Tool_Generator_CRUD extends ZendExt_Tool_Generator_Abstract
{
    protected $_requiresSchema = true;

    /**
     * Actually generates the code.
     *
     * @return void
     */
    protected function _doGenerate()
    {
        $this->_getLogger()->debug(
            'Generating CRUD Controller class for table "' . $table . '"'
        );
    	
        $builder = $this->_opts->builder;

        $namespace = $this->_opts->namespace;

        $crudN = explode('_', $builder);
        $crudN = array_reverse($crudN);
        $crudName = $crudN[0];

        $crud = new Zend_CodeGenerator_Php_Class();

        $classDoc = $this->_generateClassDocblock(
            'CRUD for '.$crudName, $namespace
        );
        $crud->setDocblock($classDoc);

        $crud->setName($crudName.'Controller')
             ->setExtendedClass('ZendExt_Controller_CRUDAbstract')
             ->setProperties(
                 array(
                     array(
                         'name'         => '_builderClass',
                         'visibility'   => 'protected',
                         'defaultValue' => $builder,
                     ),
                     array(
                         'name'            => '_listTitle',
                         'visibility'   => 'protected',
                     ),
                     array(
                         'name'            => '_newTitle',
                         'visibility'   => 'protected',
                     ),
                     array(
                         'name'            => '_updateTitle',
                         'visibility'   => 'protected',
                     ),
                 )
             );

        $crud->setMethod($this->_getInitMethod());

        $file = new Zend_CodeGenerator_Php_File();

        $file->setDocblock(
            $this->_generateFileDocblock(
                'CRUD for '.$crudName,
                $namespace
            )
        );

        $file->setClass($crud);
        $this->_saveFile($file->generate(), $crudName.'Controller');
    }

    /**
     * Retrieve the init method for the CRUD.
     *
     * @return array
     *
     * @throws ZendExt_Tool_Generator_Exception
     */
    private function _getInitMethod()
    {
        $table = $this->_opts->dbtable;

        if (!isset($this->_schema[$table])) {
            throw new ZendExt_Tool_Generator_Exception(
                'The table doesn\'t exist'
            );
        }

        $docMethod = new Zend_CodeGenerator_Php_Docblock(
            array(
                'shortDescription' => 'Init the CRUD',
                'tags' => array(
                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(
                        array(
                            'datatype' => 'void',
                        )
                    )
                )
            )
        );

        $repository = '';
        $adapterType = '';
        if (isset($this->_opts->dao)) {
            $repository = $this->_opts->dao;
            $adapterType = 'Dao';
        } else if (isset($this->_opts->table)) {
            $repository = $this->_opts->table;
            $adapterType = 'Table';
        } else {
            throw new ZendExt_Tool_Generator_Exception(
                'The table or dao doesn\'t exist'
            );
        }

        $body = '$repository = new '.$repository.'();' ."\n".
                '$this->_dataSource = new ZendExt_DataSource_Adapter_'.
                $adapterType .'($repository);' ."\n\n".
                '$map = array();' ."\n";

        foreach ($this->_schema[$table] as $column => $value) {
            $field = $this->_getCamelCased(
                $this->_removeColumnPrefix($column)
            );
            $body .= '$map[\''.$field.'\'] = \'' . $column. '\';'."\n";
        }

        $body .= '$this->_fieldToColumnMap = $map;';

        return array(
            'name'     => 'init',
            'body'     => $body,
            'docblock' => $docMethod,
        );
    }

    /**
     * Retrieves all the extra options that the generator needs.
     *
     * @return array An array formatted like needed by Zend_Console_Getopt.
     */
    protected function _getExtraOptions()
    {
        return array(
            'builder|b=s' => 'The builder used to generate the CRUD.',
            'table|t-s' => 'Table used to generate the CRUD,'.
                           ' is not required if a dao is used.',
            'dao|d-s' => 'The dao used to generate the CRUD.'.
                         ' is not required if a table is used.',
            'dbtable|j=s' => 'The name of the table used'.
                             ' to generate the fieldToColumnMap',
        );
    }
}