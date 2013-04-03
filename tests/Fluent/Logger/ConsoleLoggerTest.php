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
        $stream = fopen("php://memory","r+");
        $logger = new ConsoleLogger($stream);

        $logger->post("debug.test",array("a"=>"b"));
        fseek($stream, 0);

        $data = stream_get_contents($stream);
        $this->assertTrue((bool)preg_match("/debug.test\t\{\"a\":\"b\"\}/",$data),
            "ConsoleLogger::post could not write correctly.\nresult: {$data}");

        fclose($stream);
    }
}
