<?php

namespace FluentTests\FluentLogger;

use Fluent\Logger;
use Fluent\Logger\FluentLogger;

class FluentLoggerTest extends \PHPUnit_Framework_TestCase
{
    const TAG = 'debug.test';
    const OBJECT_KEY = 'hello';
    const OBJECT_VALUE = 'world';

    /**
     * Post will return true in the case of posting successfully
     */
    public function testPostWillReturnTrueInTheCaseOfPostingSuccessfully()
    {
        $socket = fopen("php://memory", "a+");

        /* localhost is dummy string. we set php://memory as a socket */
        $logger = FluentLogger::open("localhost");
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
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
        /* localhost is dummy string. we set php://memory as a socket */
        $logger = FluentLogger::open("localhost");
        $this->setSocket($logger, fopen("php://memory", "r"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by writing failed
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByWritingFailed()
    {
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
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(""));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    private function setSocket($logger, $socket)
    {
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }

    private function getMockOfLogger(array $method)
    {
        return $this->getMock("Fluent\Logger\FluentLogger", array("write"), array("localhost"));
    }

    /**
     * @dataProvider providesTransport
     */
    public function testGetTransportUri($host, $port, $expected_uri, $error_msg)
    {
        $actual_uri = FluentLogger::getTransportUri($host,$port);
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
}
