<?php
if (!defined('AMASING_ROOT')) define('AMASING_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');

class Config {
	//ADDED by praz. incase we want to debug the api class and all incoming requests. This is a temp fix until we can have a more thorough logging infrastructure.
	const LOGGER_ON = false;


	public static function isLoggerEnabled() { return self::LOGGER_ON; }

	const LOGGER_FILE =  'logger.txt';
	public static function loggerFile() { return AMASING_ROOT . self::LOGGER_FILE; }

	// default session timeout: 1800 sec = 30 min.
	const SESSION_TIMEOUT = 1800;
	// session timeout for bots: 30 days
	const ROBOT_SESSION_TIMEOUT = 2592000;
	
	
	public static function devMode() {
		//require_once 'util.php';
		//if(!Util::getCurrentDomain())
		//	return false;
		//return strpos(strtolower(Util::getCurrentDomain()), 'textito.com') === false;
                return false; // making this a statis return value. Could add more logic to make this more sophesticated down the road
	}

}
?>