<?php
/**
 * Models code generator.
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
 * Models code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    itirabasso <itirabasso@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Tool_Generator_Model extends ZendExt_Tool_Generator_Abstract
{

    protected $_requiresSchema = true;

    private $_modelName = null;

    const CONSTRUCT_PARAM = 'data';

    /**
     * Retrieves the aditional options that the generator needs.
     *
     * @return Zend_Console_Getopt
     */
    protected function _getExtraOptions()
    {
        $opts = array(
                'table|t-s'     => 'The table\'s name.',
                'setters|S-s'	=> 'The setter method.'
            );

        return $opts;
    }

    /**
     * Retrives the identation for the given amount.
     *
     * @param int $amount The amount of indentations.
     *
     * @return string
     */
    private function _indent($amount = 1)
    {
        return str_repeat('    ', $amount);
    }

    /** Generates the code.
     *
     * @throws Exception
     *
     * @return void
     */
    protected function _doGenerate()
    {
        if (null === $this->_opts->table || $this->_opts->table === true) {
            foreach ($this->_schema as $k => $table) {
                $this->_generateModel($k);
            }
        } else {
            $tables = $this->_opts->getAsArray('table');
            foreach ($tables as $table) {
                $this->_generateModel($table);
            }
        }
    }

    /**
     * Generate a modle for the given table.
     *
     * @param string $table The name of the table whose model to generate.
     *
     * @return void
     *
     * @throws ZendExt_Exception
     */
    private function _generateModel($table)
    {

        if (!isset($this->_schema[$table])) {
            throw new ZendExt_Exception(
                'The table doesn\'t exist'
            );
        }

        $this->_modelName = $this->_getPascalCase($table);

        $className = $this->_getClassName(
            $this->_opts->namespace,
            $this->_modelName
        );

        $class = new Zend_CodeGenerator_Php_Class(
            array(
                'name' => $className
            )
        );

        foreach ($this->_schema[$table] as $k => $column) {
            $varName = $this->_getCamelCased(
                $this->_removeColumnPrefix($k, $this->_opts->prefix)
            );
            $class->setProperty(
                array(
                    'name' 		 => '_' . $varName,
                    'visibility' => 'protected'
                )
            );
        }

        $class->setDocblock(
            $this->_generateClassDocblock(
                $this->_modelName . ' model.',
                $className,
                true
            )
        );
        $methods = array();
        $methods[] = $this->_generateConstruct($table, $className);

        foreach ($this->_schema[$table] as $k => $column) {
            $paramName = $this->_getCamelCased(
                $this->_removeColumnPrefix($k, $this->_opts->prefix)
            );

            $getMethod = $this->_generateGetter($paramName, $column);

            $setMethod = null;

            if ($this->_opts->setters) {
                $setMethod = $this->_generateSetter($paramName, $column);
            }

            $methods[] = $getMethod;

            if (null !== $setMethod) {
                $methods[] = $setMethod;
            }
        }

        $class->setMethods($methods);

        $file = new Zend_CodeGenerator_Php_File();

        $file->setDocblock(
            $this->_generateFileDocblock(
                $this->_modelName . ' model.',
                $className
            )
        );

        $file->setClass($class);
        $this->_saveFile($file->generate(), $className);
    }

    /**
     * Retrieves the construct method.
     *
     * @param string $table      The name of the table whose model to generate.
     * @param string $returnType The datatype to be returned.
     *
     * @return Zend_CodeGenerator_Php_Method
     */
    private function _generateConstruct($table, $returnType)
    {
        $docReturnTag = new Zend_CodeGenerator_Php_Docblock_Tag_Return(
            array(
                'datatype' => $returnType
            )
        );

        $docParams = new Zend_CodeGenerator_Php_Docblock_Tag_Param(
            array(
                'paramName'   => self::CONSTRUCT_PARAM,
                'datatype'    => 'array|Zend_Db_Table_Row',
                'description' => 'The model data.'
            )
        );

        $docTags = new Zend_CodeGenerator_Php_Docblock_Tag(
            array(
                'name'        => 'throws',
                'description' => 'Exception'
            )
        );

        $constructDoc = new Zend_CodeGenerator_Php_Docblock(
            array(
                'shortDescription' =>
                    'Creates a new ' . $this->_modelName . ' model.',
                'tags' => array($docParams, $docReturnTag, $docTags)
            )
        );

        $params = new Zend_CodeGenerator_Php_Parameter(
            array(
                'name' => self::CONSTRUCT_PARAM
            )
        );

        $construct = new Zend_CodeGenerator_Php_Method(
            array(
                'name'     => '__construct',
                'parameters'   => array($params)
            )
        );

        $constructBody = new Zend_CodeGenerator_Php_Body();
        $body = '';
        $body .= 'if (is_array($' . self::CONSTRUCT_PARAM . ')) {' . PHP_EOL;

        foreach ($this->_schema[$table] as $k => $column) {
            $name = $this->_getCamelCased(
                $this->_removeColumnPrefix($k, $this->_opts->prefix)
            );
            $body .= $this->_indent()
                    . "\$this->_{$name} = "
                    . '$' . self::CONSTRUCT_PARAM . "['{$k}'];"
                    . PHP_EOL;
        }

        $body .= '} else if ($' . self::CONSTRUCT_PARAM
                . ' instanceof Zend_Db_Table_Row) {' . PHP_EOL;

        foreach ($this->_schema[$table] as $k => $column) {
            $name = $this->_getCamelCased(
                $this->_removeColumnPrefix($k, $this->_opts->prefix)
            );
            $body .= $this->_indent()
                    . "\$this->_{$name} = "
                    . '$' . self::CONSTRUCT_PARAM . "->{$k};" . PHP_EOL;
        }

        $body .= '} else {' . PHP_EOL;
        $body .= $this->_indent() . 'throw new Exception(' . PHP_EOL;
        $body .= $this->_indent(2)
                . "'Can not create model instance from the given value.'"
                . PHP_EOL;
        $body .= $this->_indent() . ');' . PHP_EOL;
        $body .= '}' . PHP_EOL;

        $constructBody->setContent($body);
        $construct->setBody($constructBody);
        $construct->setDocblock($constructDoc);

        return $construct;
    }

    /**
     * Retrieves the getter method for the given column.
     *
     * @param string $name 	 The column name.
     * @param array  $column The array with the column's data.
     *
     * @return Zend_CodeGenerator_Php_Method
     */
    private function _generateGetter($name, array $column)
    {
        $method = new Zend_CodeGenerator_Php_Method();

        $type = $this->_transformType($column['type']);

        if ($type == ZendExt_Tool_Generator_Abstract::PHP_TYPE_BOOLEAN) {
            $prefix = 'is';
        } else {
            $prefix = 'get';
        }

        $method->setName(
            $prefix
            . ucfirst($name)
        );

        $method->setBody('return $this->_' . $name . ';');

        $docReturnTag = new Zend_CodeGenerator_Php_Docblock_Tag_Return(
            array(
                'datatype' => $type
            )
        );

        $docblock = new Zend_CodeGenerator_Php_Docblock(
            array(
                'shortDescription' => 'Gets ' . $name,
                'tags' 			   => array($docReturnTag)
            )
        );

        $method->setDocblock($docblock);

        return $method;
    }

    /**
     * Retrieves the setter method for the given column.
     *
     * @param string $columnName The column name.
     * @param array  $column     The array with the column's data.
     *
     * @return Zend_CodeGenerator_Php_Method
     */
    private function _generateSetter($columnName, array $column)
    {
        $method = new Zend_CodeGenerator_Php_Method();

        $method->setName('set' . ucfirst($columnName));
        $method->setBody('$this->_' . $columnName . ' = $' . $columnName . ';');

        $method->setParameter(
            array(
                'name' => $columnName
            )
        );

        $docReturnTag = new Zend_CodeGenerator_Php_Docblock_Tag_Return(
            array(
                'datatype' => 'void'
            )
        );

        $docParamTag = new Zend_CodeGenerator_Php_Docblock_Tag_Param(
            array(
                'name'    		   => $columnName,
                'description'	   => 'Description.',
                'datatype'         => $this->_transformType($column['type'])
            )
        );

        $docblock = new Zend_CodeGenerator_Php_Docblock(
            array(
                'shortDescription' => 'Sets ' . $columnName,
                'tags' => array($docParamTag, $docReturnTag)
            )
        );

        $method->setDocblock($docblock);

        return $method;
    }

    /**
     * Attempts to convert the given type into a php one.
     *
     * @param string $dataType The data type.
     *
     * @return string
     */
    private function _transformType($dataType)
    {
        switch ($dataType) {
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BOOLEAN:
                return self::PHP_TYPE_BOOLEAN;

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_INTEGER:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_SMALLINT:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BIGINT:
                return self::PHP_TYPE_INTEGER;

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_DECIMAL:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_FLOAT:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_DOUBLE_PRECISION:
                return self::PHP_TYPE_FLOAT;

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BLOB:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_TEXT:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_CHAR:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BINARY_VARYING:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_VARCHAR:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_ENUM:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_DATE:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_TIME:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_TIMESTAMP:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_DATETIME:
                return self::PHP_TYPE_STRING;

            default:
                return "unknown ({$dataType})";
                break;
        }
    }
}