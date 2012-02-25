<?php

namespace FluentTests\Logger;

use Fluent\Logger;
use Fluent\Logger\FileLogger;
use Fluent\Logger\Entity;

class FileLoggerTest extends \PHPUnit_Framework_TestCase
{
    private static $FILE_PATH = "/tmp/fluent_logger_php.test.";

    public function setUp()
    {
        self::$FILE_PATH = self::$FILE_PATH . uniqid();
        $this->cleanUpFile();
    }

    private function cleanUpFile()
    {
        if (is_file(self::$FILE_PATH)) {
            unlink(self::$FILE_PATH);
        }
        touch(self::$FILE_PATH);
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
        $logger = FileLogger::open(self::$FILE_PATH);
        $this->assertInstanceof('Fluent\\Logger\\LoggerInterface', $logger, 'Logger::open should returns LoggerInterface implemented instance ');
    }

    public function testPostMethod()
    {
        $this->cleanUpFile();
        $tag = 'test.filelogger_post';

        $logger = new FileLogger(self::$FILE_PATH);
        $this->assertTrue($logger->post($tag, array("a" => "b")));

        $data = file_get_contents(self::$FILE_PATH);
        $expected = preg_quote("$tag\t" . '{"a":"b"}');
        $this->assertRegExp("/$expected/", $data,
            "FileLogger::post could not write correctly.\nresult: {$data}");
    }

    public function testPostMultiLine()
    {
        $this->cleanUpFile();

        $logger = new FileLogger(self::$FILE_PATH);
        $lines = array(
            array('a' => 'b'),
            array('c' => 'd'),
        );
        $tag = 'test.filelogger_multiline';
        foreach ($lines as $k => $line) {
            $this->assertTrue($logger->post($tag, $line));
        }

        foreach (file(self::$FILE_PATH) as $k => $data_line) {
            if (strlen(trim($data_line)) == 0) {
                continue;
            }
            $expected = preg_quote("$tag\t" . json_encode($lines[$k]));
            $this->assertRegExp("/$expected/", $data_line,
                "FileLogger::post could not write correctly.");
        }
    }

    public function testPost2()
    {
        $this->cleanUpFile();
        $tag = 'test.filelogger_post2';

        $logger = new FileLogger(self::$FILE_PATH);
        $entity = new Entity($tag, array("a" => "b"));
        $this->assertTrue($logger->post2($entity));

        $data = file_get_contents(self::$FILE_PATH);
        $expected = preg_quote("$tag\t" . '{"a":"b"}');
        $this->assertRegExp("/$expected/", $data,
            "FileLogger::post2 could not write correctly.");
    }
}
