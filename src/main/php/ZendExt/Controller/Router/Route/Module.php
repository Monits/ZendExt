<?php
/**
 * A Module route that only matches a url if the controller and action exist.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller_Router_Route
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
 * A Module route that only matches a url if the controller and action exist.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller_Router_Route
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Controller_Router_Route_Module
    extends Zend_Controller_Router_Route_Module
{
    /**
     * Matches a user submitted path. Assigns and returns an array of variables
     * on a successful match.
     *
     * @param string  $path    Path used to match against this routing map
     * @param boolean $partial Wether the matched path should be set or not.
     *
     * @return array An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {
        $ret = parent::match($path, $partial);
        if (false === $ret) {
            return $ret;
        }

        // If the dispatcher is not set, we can never tell
        if (!$this->_dispatcher) {
            return $ret;
        }

        $controller = $this->_dispatcher->formatControllerName(
            $ret[$this->_controllerKey]
        );

        $module = $ret[$this->_moduleKey];
        $moduleName = $this->_dispatcher->formatModuleName(
            $ret[$this->_moduleKey]
        );

        // Add module prefix
        if ($ret[$this->_moduleKey] != $this->_defaults[$this->_moduleKey]
                || $this->_dispatcher->getParam('prefixDefaultModule')) {
            $controllerClass = $moduleName . '_' . $controller;
        } else {
            $controllerClass = $controller;
        }

        // if class is not present, attempt to load it
        if (!class_exists($controllerClass, false)) {
            if (!$this->_loadController(
                $controller, $controllerClass, $module
            )) {
                return false;
            }
        }

        $action = $this->_dispatcher->formatActionName(
            $ret[$this->_actionKey]
        );

        // Valdiate action
        $reflection = new ReflectionClass($controllerClass);
        if (!$reflection->hasMethod($action)) {
            return false;
        }

        return $ret;
    }

    /**
     * Loads a controller for the given module.
     *
     * @param string $controller      The controller to be loaded.
     * @param string $controllerClass The class of the controller to load.
     * @param string $moduleName      The name of the module in which the
     *                                controller is present.
     *
     * @return boolean True if the controller was successfully loaded,
     *                 false otherwise.
     */
    protected function _loadController($controller, $controllerClass,
                                       $moduleName)
    {
        // Convert parts to file path
        $filename = str_replace('_', DIRECTORY_SEPARATOR, $controller)
                    . '.php';
        $controllerDirs = $this->_dispatcher->getControllerDirectory();

        $filepath = $controllerDirs[$moduleName]
            . DIRECTORY_SEPARATOR . $filename;

        // Make sure it exists
        if (!Zend_Loader::isReadable($filepath)) {
            return false;
        }

        include_once $filepath;

        if (!class_exists($controllerClass, false)) {
            return false;
        }

        return true;
    }

    /**
     * Instantiates route based on passed Zend_Config structure.
     *
     * @param Zend_Config $config The configuration for the new instance
     *
     * @return ZendExt_Controller_Router_Route_Module
     */
    public static function getInstance(Zend_Config $config)
    {
        $frontController = Zend_Controller_Front::getInstance();

        $defs       = ($config->defaults instanceof Zend_Config)
                ? $config->defaults->toArray() : array();
        $dispatcher = $frontController->getDispatcher();
        $request    = $frontController->getRequest();

        return new self($defs, $dispatcher, $request);
    }
}