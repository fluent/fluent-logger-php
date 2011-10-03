<?php

namespace FluentTests\Logger;

use Fluent\Logger;
use Fluent\Logger\ConsoleLogger;

class ConsoleLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testOpenMethod()
    {
        $fp = fopen("php://memory","r+w");
        $logger = ConsoleLogger::open("debug.test",$fp);

        //$this->assertTrue(Logger::$current instanceof ConsoleLogger,"ConsoleLogger::open returns other class.");
        //$this->assertTrue(Logger::$current instanceof ConsoleLogger,"ConsoleLogger::open returns other class.");
        //$this->assertEquals(Logger::$current, $logger,
        //    "ConsoleLogger does not set Fluent\\Logger::\$current correctly");
        fclose($fp);
    }
    
    public function testPostMethod()
    {
        $fp = fopen("php://memory","r+");
        $logger = ConsoleLogger::open("debug.test",$fp);
        $logger->post(array("a"=>"b"));
        fseek($fp,0);
        $data = stream_get_contents($fp);
        $this->assertTrue((bool)preg_match('/debug\.test: \{"a":"b"\}/',$data),
            "ConsoleLogger::post could not write correctly.\nresult: {$data}");
        fclose($fp);
    }
}
