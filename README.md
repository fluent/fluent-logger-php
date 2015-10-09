# Fluent Logger PHP

**fluent-logger-php** is a PHP library to record events to fluentd from a PHP application.

[![Build Status](https://secure.travis-ci.org/fluent/fluent-logger-php.png)](http://travis-ci.org/fluent/fluent-logger-php)

## Requirements

- PHP 5.3 or higher
- fluentd v0.9.20 or higher

## Installation

### Using Composer

composer.json

```json
{
    "require": {
        "fluent/logger": "v0.3.7"
    }
}
```

# Usage

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use Fluent\Logger\FluentLogger;
$logger = new FluentLogger("localhost","24224");
$logger->post("debug.test",array("hello"=>"world"));
```

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
                                    `------aggregator fluentd
````

# License
Apache License, Version 2.0


# Contributors

* Daniele Alessandri
* Hiro Yoshikawa
* Kazuki Ohta
* Shuhei Tanuma
* Sotaro KARASAWA
* edy
* kiyoto
* sasezaki
* satokoma
* DQNEO
