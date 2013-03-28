<?php
require_once dirname(__FILE__).'/../src/Fluent/Autoloader.php';

//use Fluent\Autoloader,
//    Fluent\Logger\ConsoleLogger,
//    Fluent\Logger\FluentLogger,
//    Fluent\Logger\HttpLogger;

Fluent_Autoloader::register();

$logger = Fluent_Logger_HttpLogger::open("localhost","8888");

/* simple request */
$logger->post("debug.test",array("hello"=>"world"));
// 2011-10-01 03:33:34 +0900 debug.test: {"hello":"world"}