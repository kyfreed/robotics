<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Simple class autoloader
define('SITE_ROOT', dirname(dirname(__FILE__)));
define('CLASSES_ROOT', dirname(__FILE__));
spl_autoload_register(function($class_name) {
	 @include_once CLASSES_ROOT . '/' . $class_name . '.php';
});

/**
 * print_r and die. Pass any number of params, each one will be separated by an hr
 * */
function prd() {
	 while (ob_get_level())
		  ob_end_flush();
	 foreach (func_get_args() as $i => $arg) {
		  echo $i ? '<hr>' : '<pre>';
		  if ($arg === null)
				echo '<span style="background-color:#333;color:#FFF;">null</span>';
		  else if ($arg === true)
				echo '<span style="background-color:#333;color:#FFF;">true</span>';
		  else if ($arg === false)
				echo '<span style="background-color:#333;color:#FFF;">false</span>';
		  else
				print_r($arg);
	 }
	 exit;
}

// Set some site defaults
error_reporting(error_reporting() & ~E_NOTICE);
error_reporting(error_reporting() & ~E_DEPRECATED);
date_default_timezone_set('America/Los_Angeles');
// Connect to DB
Sql::auth(
	 /* server   */ 'frc.k12.tech',
	 /* user     */ 'kfreed_php',
	 /* password */ 'iusbxlFVCJFjAGZy',
	 /* db name  */ 'kfreed_scoutingapp',
	 /* port     */ 3306
);
$PAGE = new Page();
$USER = new User();


