<?php

namespace FluentTests\Logger;

use Fluent\Logger;
use Fluent\Logger\FileLogger;

class FileLoggerTest extends \PHPUnit_Framework_TestCase
{
    const FILE_PATH = "/tmp/fluent_logger_php.test";

    public static function setupBeforeClass()
    {
        if (is_file(self::FILE_PATH)) {
            unlink(self::FILE_PATH);
        }
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testFileCouldNotOpen()
    {
        $logger = new FileLogger('/dev/null/hoge');
    }

    public function testOpenMethod()
    {
        $logger = FileLogger::open(self::FILE_PATH);
        $this->assertInstanceof('Fluent\\Logger', $logger, 'Logger::open should return Logger instance ');
    }

    public function testPostMethod()
    {
        $logger = FileLogger::open(self::FILE_PATH);
        $logger->post("debug.test",array("a"=>"b"));
        $data = file_get_contents(self::FILE_PATH);
        $this->assertTrue((bool)preg_match('/debug\.test\t\{"a":"b"\}/',$data),
            "FileLogger::post could not write correctly.\nresult: {$data}");
    }
}
