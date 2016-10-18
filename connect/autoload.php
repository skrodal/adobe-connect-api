<?php
	// Define the paths to the directories holding class files
	$paths = array(
		'conf',
		'utils',
		'vendor',
		'auth',
		'api',
		'api/connect',
		'router'
	);
	// Add the paths to the class directories to the include path.
	set_include_path(dirname(__DIR__) . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));
/*
	// Add the file extensions to the SPL.
	spl_autoload_extensions(".class.php, .trait.php");
	// Register the default autoloader implementation in the php engine.
	spl_autoload_register();
	//
*/
	spl_autoload_extensions(".class.php"); // comma-separated list
	spl_autoload_register();
	require_once('connect/config.php');