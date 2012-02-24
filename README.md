# Fluent Logger PHP

**fluent-logger-php** is a PHP library, to record the events to fluentd from PHP application.

[![Build Status](https://secure.travis-ci.org/chobie/fluent-logger-php.png)](http://travis-ci.org/chobie/fluent-logger-php)

## fluent-logger-php development branch available.

i'll decided to break some API so current development lines goes to development branch.
please also check development branch if you wanna contribute fluent-logger.

## Requirements

- PHP 5.3 higher
- fluentd v0.9.20 higher

## Installation

### using Vagrant

````
gem install vagrant --no-ri --no-rdoc
gem install chef --no-ri --no-rdoc

git clone https://github.com:fluent/fluent-logger-php.git
cd fluent-logger-php
vagrant up

# this may take 30 minutes over if you don't have the box.
# this box installed rbenv, ruby1.9.3-p0 and fluentd. you can play fluentd and php with this box. enjoy it!
# you can log in to the box with following command `vagrant ssh`
````

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

$logger = FluentLogger::open("localhost","24224");
$logger->post("debug.test",array("hello"=>"world"));
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
* Hiro Yoshikawa
* Shuhei Tanuma
* edy
* sasezaki