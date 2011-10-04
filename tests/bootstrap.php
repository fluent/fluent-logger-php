<?php

require_once dirname(__DIR__).'/vendor/SplClassLoader.php';

$loader = new SplClassLoader('Fluent', dirname(__DIR__).'/src/');
$loader->register();

