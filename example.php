<?php
require_once __DIR__.'/vendor/SplClassLoader.php';

use Fluent\Logger\ConsoleLogger,
    Fluent\Logger\FluentLogger,
    Fluent\Logger\HttpLogger;

$loader = new SplClassLoader('Fluent', __DIR__.'/src/');
$loader->register();

/**
 * autoload MsgPack_Coder (obsoleted)
$loader = new SplClassLoader(null, __DIR__.'/vendor');
$loader->register();
*/

//$ev = ConsoleLogger::open("debug.test",fopen("php://stdout","w"));

$logger = FluentLogger::open("debug.test","localhost","24224");

//$ev = HttpLogger::open("debug.test","localhost","8888");

/* simple request */
$logger->post(array("hello"=>"world"));
// 2011-10-01 03:33:34 +0900 debug.test: {"hello":"world"}