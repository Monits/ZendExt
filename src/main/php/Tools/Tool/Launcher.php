/**
 * Code generation tool launcher.
 *
 * @category  Tools
 * @package   Tools_Tool
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

#!/usr/bin/php
<?php
require_once 'Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()
  ->registerNamespace('Zend_')
  ->registerNamespace('ZendExt_');

try {
    echo ZendExt_Tool::execute();
} catch (ZendExt_Tool_Exception $e) {
    echo 'An error ocurred: ' . $e->getMessage() . "\n";
}
