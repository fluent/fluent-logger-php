# Fluent Logger PHP

**fluent-logger-php** is a PHP library, to record the events to fluentd from PHP application.

[![Build Status](https://secure.travis-ci.org/chobie/fluent-logger-php.png)](http://travis-ci.org/chobie/fluent-logger-php)

## Requirements

- PHP 5.3 higher
- fluentd v0.9.20 higher

## Installation

### using Composer

composer.json
````
{
    "name": "my-project",
    "version": "1.0.0",
    "require": {
        "fluent/logger": "master-dev"
    }
}
````

````
wget http://getcomposer.org/composer.phar
php -d detect_unicode=0 composer.phar install
````

### copy directory

````
git clone https://github.com/fluent/fluent-logger-php.git
cp -r src/Fluent <path/to/your_project>
````

this library will be able to install via pear command soon.

# Useage

````
<?php
// you can choose your own AutoLoader
require_once __DIR__.'/src/Fluent/Autoloader.php';

use Fluent\Logger\FluentLogger;

Fluent\Autoloader::register();

$logger = FluentLogger::open("debug.test","localhost","24224");
$logger->post(array("hello"=>"world"));
````

# Todos

* stabilized method signature.
* improve performance and reliability.

# Restrictions

* buffering and re-send support

basically, php does not have thread feature. so i strongaly recommend 
to use fluentd as a local fluent proxy.

````
apache2(mod_php)
fluent-logger-php
                 `-----proxy-fluentd
                                    `------aggregater fluentd
````

# License
Apache License, Version 2.0


# Contributors

* Daniele Alessandri
* Shuhei Tanuma
* sasezaki
