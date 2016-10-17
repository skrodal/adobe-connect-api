<?php
	use Connect\Conf\Config;

	// NOTE: Path below must be changed to point to config files' location!
	$configRoot = '/var/www/etc/adobe-connect/';
	// Remember to update .htacces as well:
	$apiBasePath = '/api/adobe-connect';
	// Shouldn't need to change anything below
	Config::add(
		[
			'altoRouter' => [
				'api_base_path' => $apiBasePath
			],
			'auth'       => [
				'dataporten'        => $configRoot . 'dataporten_config.js',
				'dataporten_client' => $configRoot . 'dataporten_client_config.js',
				'adobe_connect'     => $configRoot . 'adobe_connect_config.js'
			],
		    'utils'     => [
		    	'debug' => true
		    ]
		]);