<?php
/*
*  Copyright 2013, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Test for ZendExt_Db_Dao_Hydrator_Constructor
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @copyright 2013 Monits
 * @license   Copyright (C) 2013. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Test for ZendExt_Db_Dao_Hydrator_Constructor
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao_Hydrator
 * @author    Esteban Ordano <eordano@monits.com>
 * @copyright 2013 Monits
 * @license   Copyright 2013. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test the hydratation of a table with underscores in the column names
     *
     * @return void
     */
    public function testHydrateUnderscoredColumns()
    {
        $args = array('someValue' => 'Juan Carlos', 'camel_case' => 'Perez');

        $hydrator = new ZendExt_Db_Dao_Hydrator_Constructor('HydrateMock');
        $retval = $hydrator->hydrate($args);

        $this->assertEquals($retval->getSomeValue(), 'Juan Carlos');
        $this->assertEquals($retval->getCamelCase(), 'Perez');
    }
}

class HydrateMock
{
    private $_someValue;

    private $_camelCase;

    public function __construct($elements) 
    {
        $this->_someValue = $elements['someValue'];
        $this->_camelCase = $elements['camelCase'];
    }

    public function getSomeValue()
    {
        return $this->_someValue;
    }

    public function getCamelCase()
    {
        return $this->_camelCase;
    }
}
