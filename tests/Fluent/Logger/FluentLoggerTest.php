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
        $logger = FluentLogger::open(self::TAG);
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);

        $this->assertTrue($logger->post(array("foo" => "bar")), "Post method returned boolean");
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
        $logger = FluentLogger::open(self::TAG);
        $this->setSocket($logger, fopen("php://memory", "r"));

        $this->assertFalse($logger->post(array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by writing failed
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByWritingFailed()
    {
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(false));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by connection aborted
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByConnectionAborted()
    {
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(""));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * check sending format is valid.
     *
     * expected format.
     * [<Tag>, <Unixtime>, {object}]
     */
    public function testPackImplMethod()
    {
        $string = FluentLogger::pack_impl(self::TAG, array(self::OBJECT_KEY => self::OBJECT_VALUE));
        $this->assertStringMatchesFormat('["%s",%d,{"%s":"%s"}]', $string, "Returned json format string");
        return json_decode($string, true);
    }

    /**
     * @depends testPackImplMethod
     */
    public function testPackImplReturnsTag($result)
    {
        $this->assertSame(self::TAG, $result[0], "Returned Tag");
    }

    /**
     * @depends testPackImplMethod
     */
    public function testPackImplReturnsTimestamp($result)
    {
        $now = time();
        $this->assertLessThanOrEqual($now, $result[1], "Included packed Unixtimestamp");
        $this->assertGreaterThanOrEqual($now - 10, $result[1], "Included packed Unixtimestamp");
    }

    /**
     * @depends testPackImplMethod
     */
    public function testPackImplReturnsObject($result)
    {
        $this->assertEquals(array(self::OBJECT_KEY => self::OBJECT_VALUE), $result[2], "Returned object");
    }
    
    public function testPackImplWithAdditionalTag()
    {
        $addtionalTag = "hoge";
        $string = FluentLogger::pack_impl(self::TAG, array(self::OBJECT_KEY => self::OBJECT_VALUE), $addtionalTag);
        $result = json_decode($string, true);
        $this->assertSame(self::TAG . "." . $addtionalTag, $result[0]);
    }

    private function setSocket($logger, $socket)
    {
        $reflection = new \ReflectionProperty("Fluent\Logger\FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }

    private function getMockOfLogger(array $method)
    {
        return $this->getMock("Fluent\Logger\FluentLogger", array("write"), array(self::TAG));
    }
}
