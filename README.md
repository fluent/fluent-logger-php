# Fluent Logger PHP

** fluent-logger-php** is a PHP library, to record the events to fluentd from PHP application.

## Installation

````
git clone https://github.com/fluent/fluent-logger-php.git
cp -r src/Fluent <path/to/your_project>
````
this library will be able to install via pear command soon.

# Useage

````
<?php
// you can choose your own AutoLoader
require_once __DIR__.'/vendor/SplClassLoader.php';

use Fluent\Logger\FluentLogger;

$loader = new SplClassLoader('Fluent', __DIR__.'/src/');
$loader->register();

$logger = FluentLogger::open("debug.test","localhost","24224");
$logger->post(array("hello"=>"world"));
````

# License
Apache License, Version 2.0
