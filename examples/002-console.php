<?php
require_once dirname(__FILE__).'/../src/Fluent/Autoloader.php';

//use Fluent\Autoloader,
//    Fluent\Logger\ConsoleLogger,
//    Fluent\Logger\FluentLogger,
//    Fluent\Logger\HttpLogger;

Fluent_Autoloader::register();


/**
 * Console Logger aims understanding fluent-logger usage.
 * You can play that without setup fluentd as ConsoleLogger use STDERR.
 */
$logger = new Fluent_Logger_ConsoleLogger();
$logger->post("debug.test",array("hello"=>"world"));
