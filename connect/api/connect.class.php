<?php
	/**
	 * Provides responses for all routes.
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */
	namespace Connect\Api;

	use Connect\Auth\Dataporten;
	use Connect\Conf\Config;
	use Connect\Utils\Response;
	use Connect\Utils\Utils;

	class Connect {
		private $dataporten, $config;
		use GroupsTrait, MeetingsTrait, OrgsTrait, RoomsTrait, ServiceTrait, UsersTrait;

		// Traits
		function __construct(Dataporten $dataPorten) {
			// Will exit on fail
			$this->config     = Config::getConfigFromFile(Config::get('auth')['adobe_connect']);
			$this->dataporten = $dataPorten;
			Utils::log(json_encode($_SESSION));
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
			$action            = $params['action'];
			$url               = $this->config['connect-api-base'] . http_build_query($params);
			$response          = false;
			try {
				// Make the call
				$response = simplexml_load_file($url);
				// Will check the response for a status of OK, or return an error otherwise
				$this->checkConnectResponse($action, $response);
			} catch(Exception $e) {
				Response::error(400, "API request [$action] failed: " . $e->getMessage());
			}

			return $response;
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
				Response::error(401, "Error when authenticating to the Adobe Connect API using client API credentials. Set-Cookie not present in response");
			}
			// Extract session cookie and store in session
			$acSessionCookie            = substr($headers['Set-Cookie'], strpos($headers['Set-Cookie'], '=') + 1);
			$_SESSION['ac-auth-cookie'] = substr($acSessionCookie, 0, strpos($acSessionCookie, ';'));

			return $_SESSION['ac-auth-cookie'];
		}

		function checkConnectResponse($action, $response) {
			// Check any type of response code Connect may give
			// At the moment, errors all return same msg, but have implemented distinctions nonetheless as they could prove useful
			if(isset($response->status->attributes()->code)) {
				$responseCode = (string)$response->status->attributes()->code;
				switch($responseCode) {
					case 'ok'           :
						return;
					case 'invalid'      :
						Response::error(400, "Request [$action] failed [$responseCode]: " . (string)$response->status->invalid->attributes()->subcode);
						break;
					case 'no-access'    :
						Response::error(400, "Request [$action] failed: [$responseCode] " . (string)$response->status->attributes()->subcode);
						break;
					case 'no-data'      :
						Response::error(400, "Request [$action] failed [$responseCode]: There is no data available (in response to an action that would normally result in returning data)");
						break;
					case 'too-much-data':
						Response::error(400, "Request [$action] failed [$responseCode]: Too much data - the action should have returned a single result but is actually returning multiple results");
						break;
					// Generic (non-existing) error (future-proofing, in case of changes to Connect WebServices)
					default             :
						Response::error(400, "Request [$action] failed [$responseCode]: An unknown error occurred");
				}
			} else {
				// XML response false:
				Response::error(400, "Request [$action] failed: No response from the Adobe Connect Server. Please check that the service is running.");
			}
		}
	}