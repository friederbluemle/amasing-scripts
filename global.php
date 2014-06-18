<?php
// set error reporting
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

// set time zone
date_default_timezone_set('America/Los_Angeles');

// define the root path of the application
if (!defined('AMASING_ROOT')) define('AMASING_ROOT', dirname(__FILE__).'/');

// include config
require_once AMASING_ROOT . 'config.php';
require_once AMASING_ROOT . 'util.php';
require_once AMASING_ROOT . 'db.php';


?>