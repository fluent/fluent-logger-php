<?php
require_once __DIR__.'/../vendor/autoload.php';

use Fluent\Autoloader,
    Fluent\Logger\ConsoleLogger,
    Fluent\Logger\FluentLogger,
    Fluent\Logger\HttpLogger;

//$logger = ConsoleLogger::open("debug.test",fopen("php://stdout","w"));
$logger = FluentLogger::open("localhost","24224");
//$logger = HttpLogger::open("debug.test","localhost","8888");

/* simple request */
$logger->post("debug.test",array("hello"=>"world"));
// 2011-10-01 03:33:34 +0900 debug.test: {"hello":"world"}