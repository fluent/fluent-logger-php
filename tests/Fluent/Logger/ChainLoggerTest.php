<?php

//namespace FluentTests\FluentLogger;
//
//use Fluent\Logger;
//use Fluent\Logger\ChainLogger;
//use Fluent\Logger\FluentLogger;

class Fluent_Logger_ChainLoggerTest extends PHPUnit_Framework_TestCase
{
    const TAG = 'debug.test';
    const OBJECT_KEY = 'hello';
    const OBJECT_VALUE = 'world';

    public function testChains()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $logger = new Fluent_Logger_ChainLogger();
        $a = new Fluent_Logger_FluentLogger("localhost");
        $b = new Fluent_Logger_FluentLogger("localhost");

        $r = fopen("php://memory","r");
        $w = fopen("php://memory","a+");

        $this->setSocket($a, $r);
        $this->setSocket($b, $w);

        $logger->addLogger($a);
        $logger->addLogger($b);

        $retval = $logger->post(self::TAG, array(self::OBJECT_KEY => self::OBJECT_VALUE));
        fseek($w,0);
        $data = fread($w,8192);

        $this->assertTrue($retval, "post successfull");
        $this->assertStringMatchesFormat('["debug.test",%d,{"hello":"world"}]', $data);
        $this->assertEquals(1,count($logger->getAvailableLoggers()), "failed logger should be ignore");
    }


    private function setSocket($logger, $socket)
    {
        $reflection = new ReflectionProperty("Fluent_Logger_FluentLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }
}
