<?php
require "Fluent.php";

$ev = \Fluent\Logger\FluentLogger::open("debug.test","localhost","24224");
//$ev = \Fluent\Logger\HttpLogger::open("debug.test","localhost","8888");
$ev->post(array("hello"=>"moe"));

/* hasn't implemented yet.
	
	$e_buy = $ev->create_event("buy","item");
	$e_user = $ev->create_event("user","name");
	$e_user->name("chobie");
	$e_buy->with($e_user)->post();
*/