<?php
	/**
	 * Required scope:
	 *    - admin
	 * @author Simon Skrødal
	 * @since  October 2016
	 */
	namespace Connect;

	session_start();


	date_default_timezone_set('CET');

	###	   LOAD DEPENDENCIES	###
	require_once('connect/autoload.php');

	use Connect\Router\Router;
	use Connect\Utils\Utils;
	
	if(!empty($_SESSION['test'])) {
		Utils::log($_SESSION['test']);
	} else {
		$_SESSION['test'] = "Session is set";
		Utils::log("SESSION NOT SET!");
	}
	// Init
	$router = new Router();

