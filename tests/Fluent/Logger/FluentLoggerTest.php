<?php

//namespace FluentTests\FluentLogger;
//
//use Fluent\Logger;
//use Fluent\Logger\FluentLogger;

class Fluent_Logger_FluentLoggerTest extends PHPUnit_Framework_TestCase
{
    const TAG = 'debug.test';
    const OBJECT_KEY = 'hello';
    const OBJECT_VALUE = 'world';

    public function tearDown()
    {
        Fluent_Logger_FluentLogger::clearInstances();
    }

    /**
     * Post will return true in the case of posting successfully
     */
    public function testPostWillReturnTrueInTheCaseOfPostingSuccessfully()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $socket = fopen("php://memory", "a+");

        /* localhost is dummy string. we set php://memory as a socket */
        $logger = Fluent_Logger_FluentLogger::open("localhost");
        $reflection = new ReflectionProperty("Fluent_Logger_FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);

        $this->assertTrue($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
        return $socket;
    }

    /**
     * @depends testPostWillReturnTrueInTheCaseOfPostingSuccessfully
     */
    public function testPostedStringIsJson($socket)
    {
        fseek($socket, 0);
        $actual = "";
        while ($string = fread($socket, 1024)) {
            $actual .= $string;
        }
        $this->assertStringMatchesFormat('["debug.test",%d,{"foo":"bar"}]', $actual);
    }

    /**
     * Post will return false in the case of posting unsuccessfully by reached max retry count
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByReachedMaxRetryCount()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        /* localhost is dummy string. we set php://memory as a socket */
        $logger = Fluent_Logger_FluentLogger::open("localhost");
        $this->setSocket($logger, fopen("php://memory", "r"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by writing failed
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByWritingFailed()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(false));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by connection aborted
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByConnectionAborted()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(""));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    private function setSocket($logger, $socket)
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $reflection = new ReflectionProperty("Fluent_Logger_FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }

    private function getMockOfLogger(array $method)
    {
        return $this->getMock("Fluent_Logger_FluentLogger", array("write"), array("localhost"));
    }

    /**
     * @dataProvider providesTransport
     */
    public function testGetTransportUri($host, $port, $expected_uri, $error_msg)
    {
        $actual_uri = Fluent_Logger_FluentLogger::getTransportUri($host,$port);
        $this->assertEquals($expected_uri,$actual_uri, $error_msg);
    }

    public function providesTransport()
    {
        return array(
            array("localhost",8080,"tcp://localhost:8080","unexpected uri returns"),
            array("127.0.0.1",8080,"tcp://127.0.0.1:8080","unexpected uri returns"),
            array("tcp://localhost",8080,"tcp://localhost:8080","unexpected uri returns"),
            array("tcp://127.0.0.1",8080,"tcp://127.0.0.1:8080","unexpected uri returns"),
            array("unix:///var/fluentd",0,"unix:///var/fluentd","unexpected uri returns"),
            array("unix:///var/fluentd",8080,"unix:///var/fluentd","unix domain uri have to ignores port number"),
            array("fe80::1",8080,"tcp://[fe80::1]:8080","ipv6 support failed"),
            array("tcp://fe80::1",8081,"tcp://[fe80::1]:8081","ipv6 support failed"),
            array("tcp://[fe80::1]",8082,"tcp://[fe80::1]:8082","ipv6 support failed"),
        );
    }

    public function testGetTransportUriCauseExcpetion()
    {
        try {
            Fluent_Logger_FluentLogger::getTransportUri("udp://localhost", 1192);
            $this->fail("getTransportUri does not thorow exception");
        } catch (Exception $e) {
            $this->assertInstanceOf("Exception",$e);
        }
    }

    public function testSetPacker()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_FluentLogger("localhost");
        $packer = new Fluent_Logger_JsonPacker();

        $prop = new ReflectionProperty($logger,"packer");
        $prop->setAccessible(true);
        $logger->setPacker($packer);
        $this->assertSame($packer, $prop->getValue($logger), "unexpected packer was set");
    }

    public function testGetPacker()
    {
        $logger = new Fluent_Logger_FluentLogger("localhost");

        $this->assertInstanceOf("Fluent_Logger_PackerInterface",$logger->getPacker(), "testGetPacker returns unexpected packer");
    }

    public function testClearInstances()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $prop = new ReflectionProperty("Fluent_Logger_FluentLogger","instances");
        $prop->setAccessible(true);

        Fluent_Logger_FluentLogger::open("localhost",1191);
        Fluent_Logger_FluentLogger::open("localhost",1192);
        $this->assertCount(2, $prop->getValue("FluentLogger"));

        Fluent_Logger_FluentLogger::clearInstances();
        $this->assertCount(0, $prop->getValue("FluentLogger"));
    }

    public function testMergeOptions()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_FluentLogger("localhost");
        $prop = new ReflectionProperty($logger,"options");
        $prop->setAccessible(true);

        $default = $prop->getValue($logger);

        $additional_options = array("socket_timeout"=>10);
        $logger->mergeOptions($additional_options);
        $this->assertEquals(array_merge($default,$additional_options),$prop->getValue($logger),"mergeOptions looks wired");
    }

    public function testMergeOptionsThrowsException()
    {
        $logger = new Fluent_Logger_FluentLogger("localhost");
        $additional_options = array("unexpected_key"=>10);
        try {
            $logger->mergeOptions($additional_options);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (Exception $e) {
            $this->assertInstanceOf("Exception",$e);
        }

    }

    public function testSetOptions()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_FluentLogger("localhost");
        $prop = new ReflectionProperty($logger,"options");
        $prop->setAccessible(true);

        $additional_options = array("socket_timeout"=>10);
        $logger->setOptions($additional_options);
        $this->assertEquals($additional_options,$prop->getValue($logger),"setOptions looks wired");
    }

    public function testConnect()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_FluentLogger("localhost",119223);
        $method = new ReflectionMethod($logger,"connect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (Exception $e) {
            $this->assertInstanceOf("Exception",$e);
        }
    }

    public function testGetOption()
    {
        $logger = new Fluent_Logger_FluentLogger("localhost",119223);
        $this->assertEquals(Fluent_Logger_FluentLogger::CONNECTION_TIMEOUT,$logger->getOption("socket_timeout"),
            "getOptions retunrs unexpected value");
    }

    public function testReconnect()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_FluentLogger("localhost",119223);
        $method = new ReflectionMethod($logger,"reconnect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("reconnect doesn't throw Exception");
        } catch (Exception $e) {
            $this->assertInstanceOf("Exception",$e);
        }
        $fp = fopen("php://memory","r");
        $prop = new ReflectionProperty($logger,"socket");
        $prop->setAccessible(true);
        $prop->setValue($logger,$fp);
        $method->invoke($logger);
    }
}
