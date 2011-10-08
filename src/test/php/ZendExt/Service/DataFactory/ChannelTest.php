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
 * Test for ZendExt_Service_DataFactory_Channel
 *
 * @category  ZendExt
 * @package   ZendExt_Service_DataFactory
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
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
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
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