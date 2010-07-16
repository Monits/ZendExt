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
 * @since     1.0.0
 */

/**
 * Abstract code generator.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool_Generator
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
abstract class ZendExt_Tool_Generator_Abstract
{
    private $_initialized = false;
    protected $_requiresSchema = false;
    protected $_schema;
    protected $_outputDir;
    protected $_opts;

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
    protected function getCamelCased($column, $separator = '_')
    {
        $parts = explode($separator, $column);
        $partCount = count($parts);

        for ($i = 1; $i < $partCount; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }

        return implode('', $parts);
    }

    /**
     * Formats a column's name in field's name format.
     *
     * @param string $columnName The column's name.
     *
     * @return string
     */
    protected function getFieldByColumn($columnName)
    {
        return substr($columnName, strpos($columnName, '_') + 1);
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
     * Saves the given content in the given filename.
     *
     * @param string $content  The file content.
     * @param string $fileName The file name.
     *
     * @return void
     */
    protected final function _saveFile($content, $fileName)
    {
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