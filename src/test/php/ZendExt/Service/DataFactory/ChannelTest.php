<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Test for ZendExt_Service_DataFactory_Channel
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Test for ZendExt_Service_DataFactory_Channel
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @author    jpcivile <jpcivile@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ChannelTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test parsing capabilities.
     *
     * @return void
     */
    public function testParse()
    {
        $xml = '<actualizaciones><canal>deportes.futbol.mundial.fixture.1.1</canal>'
            .'<canal>deportes.futbol.mundial.fixture.1.4</canal></actualizaciones>';

        $channel = new ZendExt_Service_DataFactory_Channel($xml);
        $result = array(
            'deportes.futbol.mundial.fixture.1.1',
            'deportes.futbol.mundial.fixture.1.4'
        );
        $this->assertEquals($result, $channel->getChannels());
    }

    /**
     * Test url building.
     *
     * @return void
     */
    public function testUrl()
    {
        $date = '2005-15-10 22:30:02';
        $this->assertEquals('?desde=20051015&hora=22:30:02', ZendExt_Service_DataFactory_Channel::buildUrl($date));
    }
}