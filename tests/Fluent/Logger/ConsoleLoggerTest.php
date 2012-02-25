<?php

namespace FluentTests\Logger;

use Fluent\Logger;
use Fluent\Logger\ConsoleLogger;

class ConsoleLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testOpenMethod()
    {
        $logger = new ConsoleLogger();
        $this->assertInstanceof('Fluent\\Logger\\LoggerInterface', $logger, 'Logger::open should returns LoggerInterface inherited instance ');
    }
    
    public function testPostMethod()
    {
        $fp = fopen("php://memory","r+");
        $logger = new ConsoleLogger();
        $prop = new \ReflectionProperty($logger,"handle");
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
