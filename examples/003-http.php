<?php
require_once __DIR__.'/../src/Fluent/Autoloader.php';

use Fluent\Autoloader,
    Fluent\Logger\ConsoleLogger,
    Fluent\Logger\FluentLogger,
    Fluent\Logger\HttpLogger;

Autoloader::register();

$logger = HttpLogger::open("debug.test","localhost","8888");

/* simple request */
$logger->post(array("hello"=>"world"));
// 2011-10-01 03:33:34 +0900 debug.test: {"hello":"world"}