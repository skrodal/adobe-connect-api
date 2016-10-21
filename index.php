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

	// Init
	$router = new Router();

	if (!is_writable(session_save_path())) {
		Utils::log( 'Session path "'.session_save_path().'" is not writable for PHP!' );
	}