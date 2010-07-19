<?php
/**
 * Abstract code generator.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * Abstract code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
abstract class ZendExt_Tool_Generator_Abstract
{
    private $_initialized = false;
    protected $_requiresSchema = false;
    protected $_schema;
    protected $_outputDir;
    protected $_opts;

    private $_docIdentation = 0;

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
     * Retrieves the options that the generator needs.
     *
     * @return array
     */
    public final function getOptions()
    {
        $opts = array();

        if ($this->_requiresSchema) {
            $opts = array(
                'host|h-s'      => 'The database server host, default '
                    . 'to localhost',
                'dbname|D=s'          => 'The name of the database to use',
                'username|u=s'  => 'Connect to the database as this user',
                'password|p=s'  => 'The password for the database user',
                'adapter|a=s'   => 'Which Zend_Db adapter to use',

            );
        }

        return array_merge($opts, $this->_getExtraOptions());
    }

    /**
     * Sets the generator optionsl.
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
            $desc = new ZendExt_Db_Schema(array(
                'host' => $this->_opts->host === null ?
                	'localhost' : $this->_opts->host,
                'dbname' => $this->_opts->dbname,
                'username' => $this->_opts->username,
                'password' => $this->_opts->password,
                'adapter' => $this->_opts->adapter
            ));

            foreach ($desc->listTables() as $table) {
                $this->_schema[$table] = $desc->describeTable($table);
            }
        }

    }

    /**
     * Formats the column name in camel case.
     *
     * @param string $column    The column's name.
     * @param string $separator The character that separates words.
     *
     * @return string
     */
    protected function _getCamelCased($column, $separator = '_')
    {
        $parts = explode($separator, $column);
        $partCount = count($parts);

        for ($i = 1; $i < $partCount; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return implode('', $parts);
    }

    /**
     * Removes the prefix of a column's name.
     *
     * @param string $columnName The column's name.
     * @param string $separator  The character indicating the end of the prefix
     *
     * @return string
     */
    protected function _removeColumnPrefix($columnName, $separator = '_')
    {
        $pos = strpos($columnName, $separator);

        if (false === $pos) {
            return $columnName;
        }

        return substr($columnName, $pos + 1);
    }

    /**
     * Generates the code.
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

    protected function _generateFileDocblock($description, $className,
        $companyName = null, $version = null,
        $link = null, $email = null,
        $separator = '_')
    {
        $tags = $this->_getGenericDocblockArray($className, $companyName,
                $version, $link, $email, null, $separator);

        return $this->_generateDocblock($description, $tags);
    }

    protected function _generateClassDocblock($description, $className,
        $companyName = null, $version = null,
        $link = null, $email = null,
        $author = null,
        $separator = '_')
    {
        $tags = $this->_getGenericDocblockArray($className, $companyName,
                $version, $link, $email, $author, $separator);

        return $this->_generateDocblock($description, $tags);
    }

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

    private function _getGenericDocblockArray($className,
        $companyName = null, $version = null,
        $link = null, $email = null,
        $author = null,
        $separator = '_')
    {
        $className = explode($separator, $className);
        $category = $className[0];
        $namespace = implode($separator,
            array_splice($className, 0, count($className) -1));
        $copyright = $companyName ?
            date('Y') . ' ' . $companyName : 'Copyright';
        $license = 'Copyright (C) ' . date('Y') . '. All rights reserved';
        $since = $version;
        $version = $version ? $version : 'Realease: ' . $version;
        $link = $link ? $link : 'www.example.com';
        $since = $version ? $version : 'File version';

        $ret = array(
            array(
                'name'        => 'category',
                'description' => $category
            ),
            array(
                'name'        => 'package',
                'description' => $namespace
            ),
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
        );
        if ($author)
        {
            $ret[] = array(
            	'name'        => 'author',
                'description' => $this->_getUsername()
                    . ' <' . ($email ? $email : 'email') . '>'
            );
        }

        return $ret;
    }

    /**
     * Retrieves a docblock tag.
     *
     * @param string $name 		  The tag's name.
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
     * @param string $content  The file content.
     * @param string $className The class name.
     *
     * @return void
     */
    protected final function _saveFile($content, $className)
    {
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $dir = dirname($fileName);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        } else if (!is_writable($dir)) {
            throw new ZendExt_Tool_Generator_Exception(
            	'The output dir is not writable'
            );
        }

        file_put_contents(
            $this->_outputDir . DIRECTORY_SEPARATOR . $fileName,
            $content
        );
    }

    /**
     * Actually generates the code.
     *
     * @return void
     */
    protected abstract function _doGenerate();

}