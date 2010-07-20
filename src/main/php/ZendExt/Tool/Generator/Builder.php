<?php
/**
 * Builders code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * Builders code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Tool_Generator_Builder extends ZendExt_Tool_Generator_Abstract
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
        	'table|t-s'        => 'Which table to generate its builder.',
            'modelnamespace=s' => 'The model namespace'
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
            $tables = array_keys($this->_schema);
        } else {
            $tables = $this->_opts->getAsArray('table');
        }

        foreach ($tables as $table) {
            $this->_generateBuilder($table);
        }

    }

    /**
     * Generates a builder for the given table.
     *
     * @param string $table The table name.
     *
     * @throws ZendExt_Tool_Generator_Exception
     *
     * @return void
     */
    private function _generateBuilder($table)
    {

        if (!isset($this->_schema[$table])) {
            throw new ZendExt_Tool_Generator_Exception(
            	'The asked table does not exists (' . $table . ')'
			);
        }

        $name = $this->_opts->namespace . ucfirst($table);

        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($name)
            ->setExtendedClass('ZendExt_Builder_Generic')
            ->setProperty(array(
                'name' => '_class',
		        'visibility' => 'protected',
			    'defaultValue' => $this->_opts->modelnamespace
                    . ucfirst($table)
             ))
            ->setMethod(array(
                'name' => '__construct',
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
               	    'shortDescription' => 'Creates a new builder',
            	    'tags' => array(
                        new Zend_CodeGenerator_Php_Docblock_Tag_Return(
                            array('datatype' => $name)
                        )
                    )
                )),
                'body' => '$this->_fields = '
                  . $this->_getTableFields($table) . ';'
        ));

        $desc = ucfirst($table) . ' model builder.';

        $class->setDocblock($this->_generateClassDocblock($desc, $name));
        $file = new Zend_CodeGenerator_Php_File();

        $file->setClass($class);
        $file->setDocblock($this->_generateFileDocblock($desc, $name));

        $this->_saveFile($file->generate(), $name);
    }

    /**
     * Retrieves the php code representing the table's builder fields.
     *
     * @param string $table The table name.
     *
     * @return string
     */
    private function _getTableFields($table)
    {
        $ret = 'array(' . PHP_EOL;

        foreach ($this->_schema[$table] as $column => $desc) {
            $ret .= self::TAB . $this->_getColumnField($desc, $column)
                . PHP_EOL;
        }

        // Remove last comma
        return substr($ret, 0, strlen($ret) - 2) . PHP_EOL . ')';
    }

    /**
     * Retrieves the php code representing the given column.
     *
     * @param array $desc The table description.
     * @param string $name Which column to use.
     *
     * @return string
     */
    private function _getColumnField(array $desc, $name) {
        $ret =
        	"'"
            . $this->_getCamelCased($this->_removeColumnPrefix($name))
            . "' => array("
            . PHP_EOL;

        if (!$desc['nullable']) {
            $ret .= str_repeat(self::TAB, 2) . "'required' => true," . PHP_EOL;
        }

        $default = null;

        if ($desc['default'] === null) {
           if ($desc['nullable']) {
                $default = 'null';
            }
        } else {
            $default = $desc['default'];
        }

        if ($default !== null) {
            $ret .= str_repeat(self::TAB, 2) . "'default' => "
             . $this->_transformDefaultToPhp($default)
            . ',' . PHP_EOL;
        }

        $validator = $this->_getValidatorForColumn($desc);

        if (null !== $validator) {
            $ret .= str_repeat(self::TAB, 2) . "'validators' => array("
                . PHP_EOL .str_repeat(self::TAB, 3) . $validator . PHP_EOL
                . str_repeat(self::TAB, 2) . ')';
        } else {
            $ret = substr($ret, 0, -2);
        }

        return $ret . PHP_EOL . self::TAB . '),';
    }

    /**
     * Retrieves the php code to validate the given column.
     *
     * @param array $column
     */
    private function _getValidatorForColumn($column)
    {

        switch ($column['type']) {
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_VARCHAR:
                return 'new Zend_Validate_StringLength(array(' . PHP_EOL .
                    str_repeat(self::TAB, 4) . "'min' => 0, 'max' => "
                    . $column['extra']['length']
                    . PHP_EOL . str_repeat(self::TAB, 3) . '))';

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_INTEGER:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_SMALLINT:
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BIGINT:
                return 'new Zend_Validate_Int(),' . PHP_EOL
                    . str_repeat(self::TAB, 3)
                    . 'new Zend_Validate_GreaterThan('
                    . ($column['extra']['min'] - 1)
                    . '),' . PHP_EOL
                    . str_repeat(self::TAB, 3)
                    . 'new Zend_Validate_LessThan('
                    . ($column['extra']['max'] + 1)
                    . ')';

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_BOOLEAN:
                return 'new Zend_Validate_Boolean()';

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_DECIMAL:
                return 'new Zend_Validate_Float()';

            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_TIMESTAMP:
                return 'new Zend_Validate_Date(array(' . PHP_EOL
                    . str_repeat(self::TAB, 4)
                    . "'format' => Zend_Date::ISO_8601" . PHP_EOL
                    . str_repeat(self::TAB, 3) . '))';
            case ZendExt_Db_Schema_TypeMappingAdapter_Generic::TYPE_ENUM:

                for ($i = 0; $i < count($column['extra']['options']); $i++) {
                    $column['extra']['options'][$i] =
                    	"'" . $column['extra']['options'][$i] . "'";
                }

                return 'new Zend_Validate_InArray(array(' . PHP_EOL
                	. str_repeat(self::TAB, 4)
                	. implode(
                        $column['extra']['options'],
                        ',' . PHP_EOL . str_repeat(self::TAB, 4)
                      ) . PHP_EOL
                    . str_repeat(self::TAB, 3) . '))';

            default:
                return null;

        }

    }

    /**
     * Retrieves an equivalent to the default value given in php "format".
     *
     * @param string $default The default value.
     *
     * @return string The transformed value.
     */
    private function _transformDefaultToPhp($default)
    {
        switch ($default) {
            case
                ZendExt_Db_Schema_TypeMappingAdapter_Generic::CURRENT_TIMESTAMP:

                return 'date(\'c\')';
            case
                ZendExt_Db_Schema_TypeMappingAdapter_Generic::CURRENT_DATE:

                return 'date(\'Y-m-d\')';
            case
                ZendExt_Db_Schema_TypeMappingAdapter_Generic::CURRENT_TIME:

                return 'date(\'h:i:s\')';
            default:
                return $default;
                break;
        }
    }

}