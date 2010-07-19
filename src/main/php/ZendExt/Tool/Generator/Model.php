<?php

class ZendExt_Tool_Generator_Model extends ZendExt_Tool_Generator_Abstract
{

    protected $_requiresSchema = true;

    private $_setters = array();
    private $_allSetters = false;

    private $_modelName = null;

    private $_tableSquema = null;

    const CONSTRUCT_PARAM = 'data';

    /**
     * Retrieves the aditional options that the generator needs.
     *
     * @return Zend_Console_Getopt
     */
    protected function _getExtraOptions()
    {
        $opts = array(
    			'table|t=s'     => 'The model\'s name.',
                'prefix|P-s'    => 'The column prefix.',
                'namespace|n-s' => 'The namespace.',
                'company|c-s' 	=> 'The company name.',
                'version|v-s'	=> 'The file version.',
                'link|l-s' 		=> 'The link.',
                'email|e-s'     => 'The mail',
                'setters|s-s'	=> 'The setter method.'
            );

        return $opts;
    }

    /**
     * Retrives the identation for the given amount.
     *
     * @param int $amount
     *
     * @return string
     */
    private function ident($amount = 1)
    {
        return str_repeat('    ', $amount);
    }

    /** Generates the code.
     *
     * @return void
     */
    protected function _doGenerate()
    {

        $this->_modelName = ucfirst($this->_opts->table);
        $className = $this->_opts->namespace . '_' . $this->_modelName;

        if (!isset($this->_schema[$this->_opts->table])) {
            throw new Exception(
                'The table doesn\'t exist'
            );
        }
        $this->_tableSquema = $this->_schema[$this->_opts->table];

        if (null !== $this->_opts->setters) {
            if (strtoupper($this->_opts->setters) == 'ALL') {
                $this->_allSetters = true;
            } else {
                $this->_setters = $this->_opts->getAsArray('setters');
            }
        }

        $class = new Zend_CodeGenerator_Php_Class(
            array(
                'name' => $className
            )
        );

        foreach ($this->_tableSquema as $k => $column) {
            $class->setProperty(
                array(
                    'name' 		 => '_' . $this->_getCamelCased($k),
                    'visibility' => 'protected'
                )
            );
        };

        $class->setDocblock(
            $this->_generateClassDocblock(
                $this->_modelName . ' model.',
                $className,
                $this->_opts->company,
                $this->_opts->version,
                $this->_opts->link,
                $this->_opts->email,
                true
            )
        );

        $methods = array();
        $methods[] = $this->_generateConstruct($className);

        foreach ($this->_tableSquema as $k => $column) {

            $paramName = $this->_getCamelCased($this->_removeColumnPrefix($k));

            $getMethod = $this->_generateGetter($paramName, $column);

            $setMethod = null;

            if (in_array($k, $this->_setters) || $this->_allSetters) {
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
                $className,
                $this->_opts->company,
                $this->_opts->version,
                $this->_opts->link,
                $this->_opts->email
            )
        );

        $file->setClass($class);
        $this->_saveFile($file->generate(), $className);
    }

    /**
     * Retrieves the construct method.
     *
     * @param string $returnType The datatype to be returned.
     *
     * @return Zend_CodeGenerator_Php_Method
     */
    private function _generateConstruct($returnType)
    {
        $docReturnTag = new Zend_CodeGenerator_Php_Docblock_Tag_Return(
            array(
            	'datatype' => $returnType
            )
        );

        $docParams = new Zend_CodeGenerator_Php_Docblock_Tag_Param(
            array(
                'name'        => self::CONSTRUCT_PARAM,
                'datatype'    => 'array|Zend_Db_Table_Row',
                'description' => 'The user data'
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
                'shortDescription' => 'Creates a new ' . $this->_modelName . ' model.',
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
            	'name'     => '__constructor',
                'parameters'   => array($params)
            )
        );

        $constructBody = new Zend_CodeGenerator_Php_Body();
        $body = '';
        $body .= 'if (is_array($' . self::CONSTRUCT_PARAM . ')) {' . PHP_EOL;

        foreach ($this->_tableSquema as $k => $column) {
            $name = $this->_getCamelCased($this->_removeColumnPrefix($k));
            $body .= $this->ident()
            		. "\$this->_{$name} = "
                    . '$' . self::CONSTRUCT_PARAM . "['{$name}'];"
                    . PHP_EOL;

        };

        $body .= '} else if ($' . self::CONSTRUCT_PARAM
                . ' instanceof Zend_Db_Table_Row) {' . PHP_EOL;

        foreach ($this->_tableSquema as $k => $column) {
            $name = $this->_getCamelCased($this->_removeColumnPrefix($k));
            $body .= $this->ident()
            		. "\$this->_{$name} = "
                    . '$' . self::CONSTRUCT_PARAM . "->{$k};" . PHP_EOL;
        };

        $body .= '} else {' . PHP_EOL;
        $body .= $this->ident() . 'throw new Exception(' . PHP_EOL;
        $body .= $this->ident(2)
                . "'Can not create model instance from the given value.'"
                . PHP_EOL;
        $body .= $this->ident() . ');' . PHP_EOL;
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
        $method->setName('get' . ucfirst($name));
        $method->setBody('return $this->_' . $name . ';');

        $docReturnTag = new Zend_CodeGenerator_Php_Docblock_Tag_Return(
             array(
             	'datatype' => $this->_transformType($column['type'])
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
     * @param string $name 	 The column name.
     * @param array  $column The array with the column's data.
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
     * attempts to convert the given type into a php one.
     *
     * @param string $dataType The data type.
     *
     * @return string
     */
    private function _transformType($dataType)
    {
        switch ($dataType) {
            case 'boolean':
                return 'boolean';
        	case 'integer':
        	case 'smallint':
    		case 'integer':
            case 'double':
            case 'double precision':
                return 'int';
            case 'blob':
            case 'text':
            case 'binary varying':
            case 'varchar':
            case 'char':
            case 'datetime':
            case strpos($dataType, 'enum'):
                return 'string';
            default:
                return "unknown ({$dataType})";
        }
    }
}