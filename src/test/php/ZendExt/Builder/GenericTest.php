<?php 
/*

   Copyright 2011 Monits
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
/**
 * Test for ZendExt_Builder_Generic
 *
 * @category  ZendExt
 * @package   ZendExt_Builder
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.1.0
 */

/**
 * Test for ZendExt_Builder_Generic
 *
 * @category  ZendExt
 * @package   ZendExt_Builder_Generic
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.1.0
 */
class GenericTest extends PHPUnit_Framework_TestCase
{

    /**
     * Set up method run before every test.
     *
     * @return void
     */
    public function setUp()
    {
        $config = new Zend_Config(array(
            'class' => 'stdClass',
            'fields' => array(
                'empty' => array(
                ),
                'hasDefault' => array(
                    'default' => 'foobar'
                ),
                'hasValidation' => array(
                    'validators' => array(
                        new Zend_Validate_Int()
                    )
                ),
                'hasRequired' => array(
                    'required' => true
                )
            )
        ));
        $this->_builder = ZendExt_Builder_Generic::factory($config);
    }

    /**
     * Test the factory method.
     *
     * @return void
     */
    public function testFactory()
    {
        try {
            ZendExt_Builder_Generic::factory(new Zend_Config(array()));
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {

        }

        try {
            ZendExt_Builder_Generic::factory(
                new Zend_Config(array('class' => 'stdClass'))
            );
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {
        }

        try {
            ZendExt_Builder_Generic::factory(
                new Zend_Config(array('fields' => 'stdClass'))
            );
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {
        }

        $config = new Zend_Config(array(
            'class' => 'stdClass',
            'fields' => array()
        ));
        $instance = ZendExt_Builder_Generic::factory($config);

        $this->assertType('ZendExt_Builder_Generic', $instance);

        try {
            ZendExt_Builder_Generic::factory(
                new Zend_Config(array(
                    'fields' => 'stdClass',
                    'class' => 'stdClass'
                ))
            );
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {
        }

        try {
            ZendExt_Builder_Generic::factory(
                new Zend_Config(array(
                    'class' => 'a',
                    'fields' => array()
                ))
            );
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {
        }

        $instance = ZendExt_Builder_Generic::factory(array(
            'class' => 'stdClass',
            'fields' => array()
        ));
        $this->assertType('ZendExt_Builder_Generic', $instance);
    }

    /**
     * Test with methods.
     *
     * @return void
     */
    public function testWith()
    {
        $this->assertSame(
            $this->_builder, $this->_builder->withHasValidation(1)
        );
        $this->assertSame(
            $this->_builder, $this->_builder->withHasDefault(0)
        );
        $this->assertSame(
            $this->_builder, $this->_builder->withEmpty(0)
        );
    }

    /**
     * Test validators.
     *
     * @return void
     */
    public function testValidate()
    {
        try {
            $this->_builder->withHasValidation('foo');
            $this->fail();
        } catch (ZendExt_Builder_ValidationException $e) {
            $this->assertEquals(
                'hasValidation',
                $e->getField()
            );

            $this->assertNotNull($e->getErrors());
        }

        $this->_builder->withHasValidation('1');

        $builder = ZendExt_Builder_Generic::factory(
            array(
                'class' => 'stdClass',
                'fields' => array(
                    'invalidValidator' => array(
                        'validators' => array(
                            'stdClass'
                        )
                    ),
                    'singleValidator' => array(
                        'validators' => new Zend_Validate_int()
                    )
                )
            )
        );

        try {
            $builder->withInvalidValidator('asd');
            $this->fail();
        } catch(ZendExt_Builder_Exception $e) {
        }

        $this->assertEquals(
            $builder,
            $builder->withSingleValidator(123)
        );
    }

    /**
     * Test default values.
     *
     * @return void
     */
    public function testDefault()
    {
        $this->assertEquals('foobar', $this->_builder->getDefault('hasDefault'));
        try {
            $this->_builder->getDefault('empty');
            $this->fail();
        } catch (Exception $e) {

            //This should throw some kind of error
        }

        $this->assertTrue($this->_builder->hasDefault('hasDefault'));
        $this->assertFalse($this->_builder->hasDefault('empty'));
        $this->assertFalse($this->_builder->hasDefault('hasValidation'));
    }


    /**
     * Test the build method in a very shallow manner.
     *
     * @return void
     */
    public function testBuild()
    {
        try {
            $this->_builder->build();
            $this->fail();
        } catch (ZendExt_Builder_Exception $e) {
        }

        $this->_builder->withHasRequired('foo');
        $result = $this->_builder->build();
        $this->assertType('stdClass', $result);
    }

    /**
     * Test the getFieldsNames method.
     *
     * @return void
     */
    public function testGetFieldNames()
    {
        $this->assertNotNull($this->_builder->getFieldsNames());
        $this->assertEquals(
            array('empty', 'hasDefault', 'hasValidation', 'hasRequired'),
            $this->_builder->getFieldsNames()
        );
    }
}

