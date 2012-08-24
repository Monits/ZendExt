<?php
/**
 * Abstract code generator.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/
/**
 * Abstract code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */
abstract class ZendExt_Tool_Generator_Abstract
{
    private static $_logger;

    private $_initialized = false;
    protected $_requiresSchema = false;
    protected $_schema;
    protected $_outputDir;
    protected $_opts;

    private $_docIdentation = 0;

    const PHP_TYPE_BOOLEAN = 'boolean';
    const PHP_TYPE_INTEGER = 'integer';
    const PHP_TYPE_STRING = 'string';
    const PHP_TYPE_FLOAT = 'float';

    /**
     * Creates a new generator.
     *
     * @param string $outputDir Where to save the generated files.
     *
     * @return ZendExt_Tool_Generator_Abstract
     */
    public function __construct($outputDir)
    {
        $this->_outputDir = $outputDir;
    }

    /**
     * Retrieves the generator logger.
     *
     * @return Zend_Log
     */
    protected function _getLogger()
    {
        if (null === self::$_logger) {
            self::$_logger = new Zend_Log();

            self::$_logger->addWriter(
                new Zend_Log_Writer_Stream('php://output')
            );
        }

        return self::$_logger;
    }

    /**
     * Retrieves the options that the generator needs.
     *
     * @return array
     */
    public final function getOptions()
    {
        $opts = array(
            'namespace|n-s' => 'The namespace.',
            'company|c-s' 	=> 'The company name.',
            'since|s-s'	    => 'Since which version the generated '
                . 'file exists',
            'link|l-s' 		=> 'The link.',
            'email|e-s'     => 'The mail'
        );

        if ($this->_requiresSchema) {
            $arr = array(
                'host|h-s'      => 'The database server host, default '
                    . 'to localhost',
                'dbname|D=s'    => 'The name of the database to use',
                'username|u=s'  => 'Connect to the database as this user',
                'password|p=s'  => 'The password for the database user',
                'prefix|P-s'    => 'The columns prefix.',
                'adapter|a=s'   => 'Which Zend_Db adapter to use'
            );
            $opts = array_merge($opts, $arr);
        }

        return array_merge($opts, $this->_getExtraOptions());
    }

    /**
     * Sets the generator options.
     *
     * @param Zend_Console_Getopt $opts The options.
     *
     * @throws ZendExt_Tool_Generator_Exception If the options are invalid.
     *
     * @return void
     */
    public final function setOptions(Zend_Console_Getopt $opts)
    {
        if ($this->_requiresSchema) {
            if ($opts->dbname === null || $opts->username === null
                || $opts->adapter === null || $opts->password === null) {
                throw new ZendExt_Tool_Generator_Exception(
                    'All database options are required.'
                );
            }
        }

        $this->_opts = $opts;
        $this->_initialized = true;

        if ($this->_requiresSchema) {
            $this->_getLogger()->debug('Schema required, getting it');

            $desc = new ZendExt_Db_Schema(
                $this->_opts->adapter,
                array(
                    'host' => $this->_opts->host === null ?
                        'localhost' : $this->_opts->host,
                    'dbname' => $this->_opts->dbname,
                    'username' => $this->_opts->username,
                    'password' => $this->_opts->password
                )
            );

            foreach ($desc->listTables() as $table) {
                $this->_schema[$table] = $desc->describeTable($table);
            }
        }

    }

    /**
     * Formats the column name in camel case.
     *
     * @param string  $column     The column's name.
     * @param string  $separator  The character that separates words.
     * @param boolean $pascalCase Wether to use PascalCase isntead of camelCase
     *
     * @return string
     */
    protected function _getCamelCased(
        $column, $separator = '_', $pascalCase = false
    )
    {
        $parts = explode($separator, $column);
        $partCount = count($parts);

        $start = $pascalCase ? 0  : 1;

        for ($i = $start; $i < $partCount; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return implode('', $parts);
    }

    /**
     * Removes the prefix of a column's name.
     *
     * @param string $columnName The column's name.
     * @param string $separator  The character indicating the end of the prefix.
     *
     * @return string
     */
    protected function _removeColumnPrefix($columnName, $separator = '_')
    {
        $pos = strpos($columnName, $separator);

        if (false === $pos) {
            return $columnName;
        }

        return substr($columnName, $pos + strlen($separator));
    }

    /**
     * Generates the code.
     *
     * @throws ZendExt_Tool_Generator_Exception
     *
     * @return void
     */
    public final function generate()
    {
        if (false === $this->_initialized) {
            throw new ZendExt_Tool_Generator_Exception(
                'Options must be set before generating code'
            );
        }

        $this->_doGenerate();
    }

    /**
     * Retrieves the file docblock.
     *
     * @param string $description The short description.
     * @param string $className	  The class name.
     * @param string $separator	  The classname separator.
     *
     * @return Zend_CodeGenerator_Php_Docblock
     */
    protected function _generateFileDocblock($description, $className,
            $separator = '_')
    {
        $tags = $this->_getGenericDocblockArray($className, false, $separator);

        return $this->_generateDocblock($description, $tags);
    }

    /**
     * Retrieves the class docblock.
     *
     * @param string  $description The short description.
     * @param string  $className   The class name.
     * @param boolean $author      True if the author have to be added.
     * @param string  $separator   The classname separator.
     *
     * @return Zend_CodeGenerator_Php_Docblock
     */
    protected function _generateClassDocblock($description, $className,
        $author = false,
        $separator = '_')
    {
        $tags = $this->_getGenericDocblockArray($className, true, $separator);

        return $this->_generateDocblock($description, $tags);
    }

    /**
     * Retrieves the docblock for the given args.
     *
     * @param string $description The docblock description.
     * @param array  $tags        The docblock tags.
     *
     * @return Zend_CodeGenerator_Php_Docblock
     */
    private function _generateDocblock($description, array $tags)
    {
        $docblock = new Zend_CodeGenerator_Php_Docblock();
        $docblock->setShortDescription($description);

        foreach ($tags as $tag) {
            $length = strlen($tag['name']);
            if ($length > $this->_docIdentation) {
                $this->_docIdentation = $length;
            }
        }

        $docTags = array();
        foreach ($tags as $tag) {
            $docTags[] = $this->_generateTag(
                $tag['name'], $tag['description']
            );
        }
        $docblock->setTags($docTags);

        return $docblock;
    }

    /**
     * Retrieves an array with docblock tags.
     *
     * @param string  $className The class name.
     * @param boolean $author    True if the author have to be added.
     * @param string  $separator The classname separator.
     *
     * @return array
     */
    private function _getGenericDocblockArray($className,
        $author = false, $separator = '_')
    {
        $className = explode($separator, $className);
        $category = $className[0];
        $namespace = implode(
            $separator,
            array_splice($className, 0, count($className) -1)
        );
        $copyright = $this->_opts->company ?
            date('Y') . ' ' . $this->_opts->company : date('Y') . ' Company';

        $license = 'Copyright (C) ' . date('Y') . '. All rights reserved';

        $version = 'Release: 1.0.0';

        $link = $this->_opts->link ? $this->_opts->link : 'www.example.com';

        $since = $this->_opts->since
            ? $this->_opts->since : '1.0.0';

        $ret = array(
            array(
                'name'        => 'category',
                'description' => $category
            ),
            array(
                'name'        => 'package',
                'description' => $namespace
            )
        );

        if ($author) {
            $ret[] = array(
                'name'        => 'author',
                'description' => $this->_getUsername()
                    . ' <'
                    . ($this->_opts->email ?
                        $this->_opts->email : 'email@host.com')
                    . '>'
            );
        }

        // This is ugly. But needed to get the author in the correct position.
        $ret = array_merge(
            $ret,
            array(
                array(
                    'name'        => 'copyright',
                    'description' => $copyright
                ),
                array(
                    'name'        => 'license',
                    'description' => $license
                ),
                array(
                    'name'        => 'version',
                    'description' => $version
                ),
                array(
                    'name'        => 'link',
                    'description' => $link
                ),
                array(
                    'name'        => 'since',
                    'description' => $since
                )
            )
        );

        return $ret;
    }

    /**
     * Retrieves a docblock tag.
     *
     * @param string $name        The tag's name.
     * @param string $description The tag's description.
     *
     * @return Zend_CodeGenerator_Php_Docblock_Tag
     */
    protected function _generateTag($name, $description)
    {
        $desc = str_repeat(' ', $this->_docIdentation - strlen($name));
        $desc .= $description ? $description : 'Description';
        return new Zend_CodeGenerator_Php_Docblock_Tag(
            array(
                'name'        => $name ? $name : 'unknown',
                'description' => $desc
            )
        );
    }

    /**
     * Retrieves the username.
     *
     * @return string
     */
    private function _getUsername()
    {
        return $_SERVER['USER'];
    }

    /**
     * Retrieves all the extra options that the generator needs.
     *
     * @return array An array formatted like needed by Zend_Console_Getopt.
     */
    protected function _getExtraOptions()
    {
        return array();
    }

    /**
     * Saves the given content in a file.
     *
     * The file name and path is generated based on the class name and
     * following the Zend naming conventions.
     *
     * @param string $content   The file content.
     * @param string $className The class name.
     *
     * @throws ZendExt_Tool_Generator_Exception
     *
     * @return void
     */
    protected final function _saveFile($content, $className)
    {
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $dir = $this->_outputDir . DIRECTORY_SEPARATOR . dirname($fileName);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);

            $this->_getLogger()->debug('Created ' . $dir . ' dir');
        } else if (!is_writable($dir)) {
            throw new ZendExt_Tool_Generator_Exception(
                'The output dir is not writable'
            );
        }

        $file = $this->_outputDir . DIRECTORY_SEPARATOR . $fileName;

        $this->_getLogger()->debug('Saving file ' . $file);

        file_put_contents($file, $content);
    }

    /**
     * Actually generates the code.
     *
     * @return void
     */
    protected abstract function _doGenerate();

    /**
     * Formats the column name in pascal case.
     *
     * @param string $column    The column's name.
     * @param string $separator The character that separates words.
     *
     * @return string
     */
    protected function _getPascalCase($column, $separator = '_')
    {
        return $this->_getCamelCased($column, $separator, true);
    }

    /**
     * Returns a class name using the given namespace and component.
     *
     * This is just to concetrate the concatenation logic and prevent
     * inconsistencies like if the _ is needed or not at the namespace's end.
     *
     * @param string $namespace The class namespace.
     * @param string $component The class component.
     *
     * @return string
     */
    protected function _getClassName($namespace, $component)
    {
        return $namespace . '_' . $this->_getPascalCase($component);
    }

}