<?php
/**
 * Test for lazy stream logger.
 *
 * @category  ZendExt
 * @package   ZendExt_Log_Writer
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Test for lazy stream logger.
 *
 * @category  ZendExt
 * @package   ZendExt_Log_Writer
 * @author    Juan Pablo Civile <jpcivile@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class LazyStreamTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for when resource is not a stream.
     *
     * @return void
     */
    public function testConstructorThrowsWhenResourceIsNotStream()
    {
        $resource = xml_parser_create();
        try {
            $writer = new ZendExt_Log_Writer_LazyStream($resource);
            $writer->write(array('message' => 'message-to-log'));
            self::fail();
        } catch (Exception $e) {
            self::assertType('Zend_Log_Exception', $e);
            self::assertRegExp('/not a stream/i', $e->getMessage());
        }
        xml_parser_free($resource);
    }

    /**
     * Test valid stream construction.
     *
     * @return void
     */
    public function testConstructorWithValidStream()
    {
        $stream = fopen('php://memory', 'w+');
        new ZendExt_Log_Writer_LazyStream($stream);
    }

    /**
     * Test for valid url construction.
     *
     * @return void
     */
    public function testConstructorWithValidUrl()
    {
        new ZendExt_Log_Writer_LazyStream('php://memory');
    }

    /**
     * Test for error on invalid params on construction.
     *
     * @return void
     */
    public function testConstructorThrowsWhenModeSpecifiedForExistingStream()
    {
        $stream = fopen('php://memory', 'w+');
        try {
            new ZendExt_Log_Writer_LazyStream($stream, 'w+');
            self::fail();
        } catch (Exception $e) {
            self::assertType('Zend_Log_Exception', $e);
            self::assertRegExp('/existing stream/i', $e->getMessage());
        }
    }

    /**
     * Test for error on unopenable stream.
     *
     * @return void
     */
    public function testConstructorThrowsWhenStreamCannotBeOpened()
    {
        try {
            $writer = new ZendExt_Log_Writer_LazyStream('');
            $writer->write(array('message' => 'message-to-log'));
            self::fail();
        } catch (Exception $e) {
            self::assertType('Zend_Log_Exception', $e);
            self::assertRegExp('/cannot be opened/i', $e->getMessage());
        }
    }

    /**
     * Test write.
     *
     * @return void
     */
    public function testWrite()
    {
        $stream = fopen('php://memory', 'w+');
        $fields = array('message' => 'message-to-log');

        $writer = new ZendExt_Log_Writer_LazyStream($stream);
        $writer->write($fields);

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        self::assertContains($fields['message'], $contents);
    }

    /**
     * Test error when the stream is closed.
     *
     * @return void
     */
    public function testWriteThrowsWhenStreamWriteFails()
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new ZendExt_Log_Writer_LazyStream($stream);
        $writer->write(array('message' => 'foo'));
        fclose($stream);

        try {
            $writer->write(array('message' => 'foo'));
            self::fail();
        } catch (Exception $e) {
            self::assertType('Zend_Log_Exception', $e);
        }
    }

    /**
     * Test that shutdown works correctly.
     *
     * @return void
     */
    public function testShutdownClosesStreamResource()
    {
        $writer = new ZendExt_Log_Writer_LazyStream('php://memory', 'w+');
        $writer->write(array('message' => 'this write should succeed'));

        $writer->shutdown();

        try {
            $writer->write(array('message' => 'this write should fail'));
            self::fail();
        } catch (Exception $e) {
            self::assertType('Zend_Log_Exception', $e);
        }
    }

    /**
     * Test formatter setter.
     *
     * @return void
     */
    public function testSettingNewFormatter()
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new ZendExt_Log_Writer_LazyStream($stream);
        $expected = 'foo';

        $formatter = new Zend_Log_Formatter_Simple($expected);
        $writer->setFormatter($formatter);

        $writer->write(array('bar'=>'baz'));
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        self::assertContains($expected, $contents);
    }

    /**
     * Test the factory method with a stream.
     *
     * @return void
     */
    public function testFactoryStream()
    {
        $cfg = array('log' => array('memory' => array(
            'writerName'   => "Mock",
            'writerParams' => array(
                'stream' => 'php://memory',
                'mode'   => 'a'
            )
        )));

        $logger = Zend_Log::factory($cfg['log']);
        self::assertTrue($logger instanceof Zend_Log);
    }

    /**
     * Test the factory method with a url.
     *
     * @return void
     */
    public function testFactoryUrl()
    {
        $cfg = array('log' => array('memory' => array(
            'writerName'   => "Mock",
            'writerParams' => array(
                'url'  => 'http://localhost',
                'mode' => 'a'
            )
        )));

        $logger = Zend_Log::factory($cfg['log']);
        self::assertTrue($logger instanceof Zend_Log);
    }
}