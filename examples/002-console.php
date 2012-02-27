<?php
require_once __DIR__.'/../src/Fluent/Autoloader.php';

use Fluent\Autoloader,
    Fluent\Logger\ConsoleLogger,
    Fluent\Logger\FluentLogger,
    Fluent\Logger\HttpLogger;

Autoloader::register();


/**
 * Console Logger aims understanding fluent-logger usage.
 * You can play that without setup fluentd as ConsoleLogger use STDERR.
 */
$logger = new ConsoleLogger();
$logger->post("debug.test",array("hello"=>"world"));
