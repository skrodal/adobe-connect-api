<?php
	/**
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */

	namespace Connect\Conf;

	class Config {
		protected static $config = array();

		public static function get($name, $default = NULL) {
			return isset(self::$config[$name]) ? self::$config[$name] : $default;
		}

		public static function add($parameters = array()) {
			self::$config = array_merge(self::$config, $parameters);
		}

		// Loads external config files
		public static function getConfigFromFile($file) {
			$config = file_get_contents($file);
			// Sanity
			if($config === false) {
				Response::error(404, 'En feil oppstod i samtale med tjener: Config not found');
			}

			return json_decode($config, true);
		}
	}