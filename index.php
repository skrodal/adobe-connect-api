<?php
	/**
	 * Required scope:
	 *    - admin
	 * @author Simon Skrødal
	 * @since  October 2016
	 */
	namespace Connect;

	date_default_timezone_set('CET');

	###	   LOAD DEPENDENCIES	###
	require_once('connect/autoload.php');

	use Connect\Router\Router;
	use Connect\Utils\Utils;

	// Init
	$router = new Router();