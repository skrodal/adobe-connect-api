<?php
	/**
	 * Provides responses for all routes.
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */

	namespace Connect\Api;

	use Connect\Api\Connect\Meetings;
	use Connect\Api\Connect\Rooms;
	use Connect\Auth\Dataporten;
	use Connect\Conf\Config;
	use Connect\Utils\Response;
	use Connect\Utils\Utils;
	// We use session to store auth cookie from AC
	session_start();

	class Connect {
		private $dataporten, $config;
		// Traits
		use \Service, \Org, \User, \Meetings, \Rooms;
		function __construct(Dataporten $dataPorten) {
			// Will exit on fail
			$this->config     = Config::getConfigFromFile(Config::get('auth')['adobe_connect']);
			$this->dataporten = $dataPorten;
			// Todo: run a usercheck (org)
		}

		/**
		 * Utility function for AC API calls.
		 *
		 * @param array $params
		 *
		 * @return SimpleXMLElement
		 */
		private function callConnectApi($params = array()) {
			$params['session'] = $this->getSessionAuthCookie();

			$url = $this->config['connect-api-base'] . http_build_query($params);
			$xml = false;
			try {
				$xml = simplexml_load_file($url);
			} catch(Exception $e) {
				Response::error(400, 'API request failed: ' . $e->getMessage());
			}
			if(!$xml) {
				Response::error(400, 'API request returned no data.');
			}
			return $xml;
		}

		/**
		 * Authenticate API user on AC service and grab returned cookie. If auth already in place, return cookie.
		 *
		 * @throws Exception
		 * @return boolean
		 */
		private function getSessionAuthCookie() {
			if(isset($_SESSION['ac-auth-cookie'])) {
				return $_SESSION['ac-auth-cookie'];
			}

			Utils::log('Info: Creating new session.');
			//
			$url     = $this->config['connect-api-base'] . 'action=login&login=' . $this->config['connect-api-userid'] . '&password=' . $this->config['connect-api-passwd'];
			$headers = get_headers($url, 1);
			// Look for the session cookie from AC
			if(!isset($headers['Set-Cookie'])) {
				Response::error(401, 'Error when authenticating to the Adobe Connect API using client API credentials. Set-Cookie not present in response.');
			}
			// Extract session cookie and store in session
			$acSessionCookie            = substr($headers['Set-Cookie'], strpos($headers['Set-Cookie'], '=') + 1);
			$_SESSION['ac-auth-cookie'] = substr($acSessionCookie, 0, strpos($acSessionCookie, ';'));

			return $_SESSION['ac-auth-cookie'];
		}


	}