<?php
	namespace Connect\Utils;

	use Connect\Conf\Config;

	/**
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */
	class Utils {
		public static function log($text) {
			if(Config::get('utils')['debug']) {
				$trace  = debug_backtrace();
				$caller = $trace[1];
				error_log($caller['class'] . $caller['type'] . $caller['function'] . '::' . $caller['line'] . ': ' . $text);
			}
		}
	}