<?php
require "Fluent.php";

$ev = \Fluent\Logger\FluentLogger::open("debug.test","localhost","24224");
//$ev = \Fluent\Logger\HttpLogger::open("debug.test","localhost","8888");
$ev->post(array("hello"=>"moe"));
