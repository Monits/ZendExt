<?php
/**
 * Configuration & user interaction handler for ZendExt_Tool.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * Configuration & user interaction handler for ZendExt_Tool.
 *
 * @category  ZendExt
 * @package   ZendExt_Tool
 * @author    Juan Civile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_Tool_Config
{
    const GENERATOR = 'generator';

    const TABLE = 'table';

    const DB = 'db';

    protected $_config;

    protected $_consoleOpt;

    protected $_configFile;

    /**
     * Construct a new instance.
     *
     * @param Zend_Console_Getopt $getopt     An instance of Getopt.
     * @param string              $configFile The path to the config file.
     */
    public function __construct($getopt, $configFile = 'zxa.xml')
    {
        $this->_consoleOpt = $getopt;

        $this->_configFile = $configFile;
        if (file_exists($configFile)) {
            $this->_config = new Zend_Config_Xml($configFile, null, true);
        } else {
            $this->_config = new Zend_Config(array(), true);
        }
    }

    /**
     * Get a project level option.
     *
     * @param string $key The key to get.
     *
     * @return string
     */
    public function getProjectOption($key)
    {
        return $this->_getValue($key);
    }

    /**
     * Get a config option for a given generator.
     *
     * @param string $generator The name of the generator.
     * @param string $key       The name of the option to get.
     *
     * @return mixed
     */
    public function getGeneratorOption($generator, $key)
    {
        return $this->_getValue($key, self::GENERATOR, $generator);
    }

    /**
     * Get a config option for a given table.
     *
     * @param string $table The name of the table.
     * @param string $key   The name of the option to get.
     *
     * @return mixed
     */
    public function getTableOption($table, $key)
    {
        return $this->_getValue($key, self::TABLE, $table);
    }

    /**
     * Get a config option for the db.
     *
     * @param string $key The name of the option to get.
     *
     * @return mixed
     */
    public function getDbOption($key)
    {
        return $this->_getValue($key, self::DB);
    }

    /**
     * Get a option from the user.
     *
     * @param string $key The key of the option to get.
     *
     * @return string
     */
    public function getOption($key)
    {
        if ($this->_consoleOpt->$key) {

            return $this->_consoleOpt->$key;
        } else {

            return $this->_requestValue($key);
        }
    }

    /**
     * Get a value.
     *
     * @param string $key       The key of the value to get.
     * @param string $namespace The configuration namespace.
     * @param string $section   The section under which to get the value.
     *                          Optional.
     *
     * @return string
     */
    protected function _getValue($key, $namespace = null, $section = null)
    {
        if ($this->_consoleOpt->$key) {

            $value = $this->_consoleOpt->$key;
            if (!$this->_isInConfig($key, $namespace, $section)) {

                $this->_setConfig($key, $value, $namespace, $section);
            }
            return $value;
        } else {

            $value = $this->_getConfig($key, $namespace, $section);

            if ($value === false) {

                $value = $this->_requestValue($key, $namespace, $section);
                if ($value !== false) {

                    $this->_setConfig($key, $value, $namespace, $section);
                }
            }

            return $value;
        }
    }

    /**
     * Get a value from the config file.
     *
     * @param string $key       The key of the value to get.
     * @param string $namespace The configuration namespace.
     * @param string $section   The section under which to get the value.
     *                          Optional.
     *
     * @return string
     */
    protected function _getConfig($key, $namespace, $section = null)
    {
        if ($this->_isInConfig($key, $namespace, $section)) {

            $keys = array();
            $keys[] = $namespace;
            $keys[] = $section;

            return $this->_walkConfig($keys)->$key;
        } else {

            return false;
        }
    }

    /**
     * Check whether an entry is in the config file.
     *
     * @param string $key       The key of the value to check.
     * @param string $namespace The configuration namespace.
     * @param string $section   The section under which to check. Optional.
     *
     * @return boolean
     */
    protected function _isInConfig($key, $namespace = null, $section = null)
    {
        $keys = array();
        $keys[] = $namespace;
        $keys[] = $section;

        $config = $this->_walkConfig($keys);
        return isset($config->$key);
    }

    /**
     * Set a value in the config file.
     *
     * @param string $key       The key of the value to set.
     * @param string $value     The value to set.
     * @param string $namespace The configuration namespace.
     * @param string $section   The section under which to set the value.
     *                          Optional.
     *
     * @return void
     */
    protected function _setConfig($key, $value,
                                    $namespace = null, $section = null)
    {
        $keys = array();
        $keys[] = $namespace;
        $keys[] = $section;

        $this->_walkConfig($keys)->$key = $value;
    }

    /**
     * Walk the config tree following an array of keys.
     *
     * @param array $keys An array of keys to use when walking.
     *
     * @return Zend_Config
     */
    protected function _walkConfig($keys)
    {
        $keys = array_filter($keys);

        $config = $this->_config;
        while (($next = array_shift($keys)) !== null) {

            if (isset($config->$next)) {

                $config = $config->$next;
            } else {

                $config->$next = new Zend_Config(array(), true);
                $config = $config->$next;
            }
        }

        return $config;
    }

    /**
     * Request a value from the command line.
     *
     * @param string $key       The name to request it under.
     * @param string $namespace The namespace to request it under. Optional.
     * @param string $section   The section to request under. Optional.
     *
     * @return string
     */
    protected function _requestValue($key, $namespace = null, $section = null)
    {
        $prompt = 'Value for '.$key;

        if ($namespace !== null) {
            $prompt .= ' (for '.strtolower($namespace);
            if ($section !== null) {
                $prompt .= ' '.ucfirst(strtolower($section));
            }
            $prompt .= ')';
        }

        $prompt .= ': ';

        return readline($prompt);
    }

    /**
     * Destruct the instance.
     *
     * @return void
     */
    public function __destruct()
    {
        $writer = new Zend_Config_Writer_Xml();
        $writer->setConfig($this->_config)
            ->setFilename($this->_configFile)
            ->write();
    }
}
