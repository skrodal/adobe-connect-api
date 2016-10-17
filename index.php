<?php
	/**
	 * Required scope:
	 *    - admin
	 * @author Simon Skrødal
	 * @since  October 2016
	 */
	namespace Connect;

	###	   LOAD DEPENDENCIES	###
	require_once('connect/autoload.php');

	use Connect\Router\Router;

	// Init
	$router = new Router();