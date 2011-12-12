<?php
/**
 * Tables code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Tables code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Ignacio Tirabasso <itirabasso@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Tool_Generator_Table extends ZendExt_Tool_Generator_Abstract
{
    protected $_requiresSchema = true;

    /**
     * Retrieves all the extra options that the generator needs.
     *
     * @return array An array formatted like needed by Zend_Console_Getopt.
     */
    protected function _getExtraOptions()
    {
        return array(
            'table|t-s' => 'The table to generate.',
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
            $this->_getLogger()->info('Generating ' . $table . ' table');
            $this->_generateTable($table);
        }
    }

    /**
     * Generates a table for the given table.
     *
     * @param string $table The table name.
     *
     * @throws ZendExt_Tool_Generator_Exception
     *
     * @return void
     */
    private function _generateTable($table)
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
            ->setExtendedClass('Zend_Db_Table_Abstract')
            ->setProperty(
                array(
                    'name' => '_name',
                    'visibility' => 'protected',
                    'defaultValue' => $table
                 )
            );

        $pks = array();

        foreach ($this->_schema[$table] as $column => $def) {
            if ($def['primary']) {
                $pks[] = $column;
            }
        }

        $defaultValue = new Zend_CodeGenerator_Php_Property_DefaultValue();
        if (count($pks) == 1) {
            $value = $pks[0];
            $type = Zend_CodeGenerator_Php_Property_DefaultValue::TYPE_STRING;
        } else {
            $value = 'array(';
            foreach ($pks as $pk) {
                $value .= "'" . addcslashes($pk, "'") . "', ";
            }
            $value .= ')';
            $type = Zend_CodeGenerator_Php_Property_DefaultValue::TYPE_CONSTANT;
        }

        $class->setProperty(
            array(
                'name' 	       => '_primary',
                'visibility'   => 'protected',
                'defaultValue' => array(
                    'value' => $value,
                    'type'  => $type
                )
            )
        );

        $desc = ucfirst($table) . ' table.';
        $doc = $this->_generateClassDocblock($desc, $className);

        $class->setDocblock($doc);
        $file = new Zend_CodeGenerator_Php_File();

        $file->setClass($class);
        $file->setDocblock($this->_generateFileDocblock($desc, $className));

        $this->_saveFile($file->generate(), $className);
    }

}
