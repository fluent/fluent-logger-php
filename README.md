# Fluent Logger PHP

**fluent-logger-php** is a PHP library to record events to fluentd from a PHP application.

[![Build Status](https://secure.travis-ci.org/chobie/fluent-logger-php.png)](http://travis-ci.org/chobie/fluent-logger-php)

## fluent-logger-php development branch available.

Since we are currently making changes that affect the existing API, all the development
is happening on the developmenet branch. If you would like to contribute, please check out
the development branch.

## Requirements

- PHP 5.3 or higher
- fluentd v0.9.20 or higher

## Installation

### Using Composer

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

This library will be part of pear soon.

# Usage

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

* Stabilize method signatures.
* Improve performance and reliability.

# Restrictions

* Buffering and re-send support

PHP does not have threads. So, I strongaly recommend you use fluentd as a local fluent proxy.

````
apache2(mod_php)
fluent-logger-php
                 `-----proxy-fluentd
                                    `------aggregater fluentd
````

# License
Apache License, Version 2.0


# Contributors

* Aniele Alessandri
* Hiro Yoshikawa
* Shuhei Tanuma
* sasezaki
