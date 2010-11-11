<?php

$app = new FakturoidPairing('config.ini');



// autoload
function __autoload($class)
{
	include_once dirname(__FILE__) . '/app/' . $class . '.php';
}

// helpers
function dump($var)
{
	var_dump($var);
}