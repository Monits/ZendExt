<?php
/**
 * Dao code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @copyright 2012 Monits
 * @license   Copyright (C) 2012. All rights reserved.
 * @version   Release: 1.6.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/*
*  Copyright 2012, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Dao code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Ignacio Mariano Tirabasso <itirabasso@monits.com>
 * @copyright 2012 Monits
 * @license   Copyright 2012. All rights reserved.
 * @version   Release: 1.6.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */
class ZendExt_Tool_Generator_Dao extends ZendExt_Tool_Generator_Abstract
{

    const TAB = '    ';

    protected $_requiresSchema = true;

    /**
     * Retrieves all the extra options that the generator needs.
     *
     * @return array An array formatted like needed by Zend_Console_Getopt.
     */
    protected function _getExtraOptions()
    {
        return array(
            'table|t-s'        => 'Which table to generate its DAO.',
            'modelnamespace|M=s' => 'The model namespace',
            'tablenamespace|T=s' => 'The table namespace'
        );
    }

    /**
     * Actually generates the code.
     *
     * @return void
     */
    protected function _doGenerate()
    {
        if (null === $this->_opts->table) {
            $this->_getLogger()->info(
                'No table option given, generating all tables'
            );

            $tables = array_keys($this->_schema);
        } else {
            $tables = $this->_opts->getAsArray('table');
        }

        foreach ($tables as $table) {
            $this->_getLogger()->info('Generating ' . $table . ' DAO');
            $this->_generateDao($table);
        }
    }

    /**
     * Generates a dao for the given table.
     *
     * @param string $table The table name.
     *
     * @throws ZendExt_Tool_Generator_Exception
     *
     * @return void
     */
    private function _generateDao($table)
    {

        if (!isset($this->_schema[$table])) {
            throw new ZendExt_Tool_Generator_Exception(
                'The asked table does not exists (' . $table . ')'
            );
        }

        $className = $this->_getClassName($this->_opts->namespace, $table);
        $name = $this->_getPascalCase($table);

        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('ZendExt_Db_Dao_Abstract')
            ->setProperty(
                array(
                    'name' => '_tableClass',
                    'visibility' => 'protected',
                    'defaultValue' => $this->_getClassName(
                        $this->_opts->tablenamespace,
                        $name
                    )
                 )
            )
            ->setMethod(
                array(
                    'name' => '__construct',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(
                        array(
                           'shortDescription' => 'DAO for ' . $name,
                           'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Return(
                                    array('datatype' => 'ZendExt_Db_Dao_Abstract') // TODO : Constantes?
                                )
                            )
                        )
                    ),
                    'body' => $this->_generateConstructBody($name)
                )
            );

        $desc = ucfirst($table) . ' DAO.';
        $doc = $this->_generateClassDocblock($desc, $className);

        $class->setDocblock($doc);
        $file = new Zend_CodeGenerator_Php_File();

        $file->setClass($class);
        $file->setDocblock($this->_generateFileDocblock($desc, $className));

        $this->_saveFile($file->generate(), $className);
        
    }

    /**
     * Retrieves the construct method body.
     * 
     * @param string $name The table's dao name.
     * 
     * @return string
     */
    private function _generateConstructBody($name)
    {
        $modelClassName = $this->_getClassName(
            $this->_opts->modelnamespace,
            $name
        );
        
        $body = '$hydrator = new ZendExt_Db_Dao_Hydrator_Constructor(' . PHP_EOL
            . str_repeat(self::TAB, 2) . "'" . $modelClassName . "'" . PHP_EOL
            . ');' . PHP_EOL;
        
        $body .= PHP_EOL;
        $body .= 'parent::__construct($hydrator);';
        
        return $body;
    }
    
}
