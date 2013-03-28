<?php

//namespace FluentTests\Logger;
//
//use Fluent\Logger;
//use Fluent\Logger\ConsoleLogger;

class Fluent_Logger_ConsoleLoggerTest extends PHPUnit_Framework_TestCase
{
    public function testOpenMethod()
    {
        $logger = new Fluent_Logger_ConsoleLogger();
        $this->assertInstanceof('Fluent_Logger_ConsoleLogger', $logger, 'Logger::open should returns LoggerInterface inherited instance ');
    }
    
    public function testPostMethod()
    {
        $this->markTestIncomplete("this test does not support on 5.2");
        $fp = fopen("php://memory","r+");
        $logger = new Fluent_Logger_ConsoleLogger();
        $prop = new ReflectionProperty($logger,"handle");
        $prop->setAccessible(true);
        $prop->setValue($logger,$fp);

        $logger->post("debug.test",array("a"=>"b"));
        fseek($fp,0);
        $data = stream_get_contents($fp);
        $this->assertTrue((bool)preg_match("/debug.test\t\{\"a\":\"b\"\}/",$data),
            "ConsoleLogger::post could not write correctly.\nresult: {$data}");
        fclose($fp);
    }
}
