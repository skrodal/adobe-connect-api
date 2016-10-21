<?php
	/**
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */

	namespace Connect\Auth;

	use Connect\Conf\Config;
	use Connect\Utils\Response;
	use Connect\Utils\Utils;

	class Dataporten {

		private $config;

		// private $userInfo;

		function __construct() {
			// Exits on OPTION call
			$this->checkCORS();
			// Dataporten username and pass (will exit on fail)
			$this->config = Config::getConfigFromFile(Config::get('auth')['dataporten']);
			// Exits on incorrect credentials
			$this->checkGateKeeperCredentials();
			// Will exit if client does not have required scope
			if(!$this->hasDataportenScope('admin')) {
				Response::error(403, "Client is missing required Dataporten scope(s) to access this API");
			};
			// Endpoint /userinfo/
			// $this->userInfo = $this->getUserInfo();
			// Endpoint /groups/me/groups/
			//$this->userGroups = $this->getUserGroups();
		}

		private function checkCORS() {
			// Access-Control headers are received during OPTIONS requests
			if(strcasecmp($_SERVER['REQUEST_METHOD'], "OPTIONS") === 0) {
				Response::result('CORS OK :-)');
			}
		}

		private function checkGateKeeperCredentials() {
			if(empty($_SERVER["PHP_AUTH_USER"]) || empty($_SERVER["PHP_AUTH_PW"])) {
				Response::error(401, "Unauthorized (Missing Dataporten API Gatekeeper Credentials)");
			}

			// Gatekeeper. user/pwd is passed along by the Dataporten Gatekeeper and must matched that of the registered API:
			if((strcmp($_SERVER["PHP_AUTH_USER"], $this->config['user']) !== 0) ||
				(strcmp($_SERVER["PHP_AUTH_PW"], $this->config['passwd']) !== 0)
			) {
				// The status code will be set in the header
				Response::error(401, "Unauthorized (Incorrect Dataporten API Gatekeeper Credentials)");
			}
		}

		private function hasDataportenScope($scope) {
			// Get the scope(s)
			$scopes = $_SERVER["HTTP_X_DATAPORTEN_SCOPES"];
			// Make array
			$scopes = explode(',', $scopes);

			// True/false
			return in_array($scope, $scopes);
		}

		/* Call /userinfo/ for name/email of user
		   -- Not currently using this info (only need Feide username (passed in header) and affiliation (/groups)
		public function getUserInfo() {
			return $this->protectedRequest('https://auth.dataporten.no/userinfo')['user'];
		}
		*/

		/**
		 * Check membership in ConnectAdmin Dataporten group. Returns membership or false.
		 * @return bool|mixed
		 */
		public function isConnectAdmin() {
			$membership = $this->protectedRequest("https://groups-api.dataporten.no/groups/me/groups/" . $this->config['connect-group-id']);
			return $membership;
		}

		/**
		 * If member of ConnectAdmin Dataporten group, return invitation url
		 * @return bool
		 */
		public function groupInvitationURL(){
			if($this->isSuperAdmin() || $this->isConnectAdmin() !== false){
				return $this->config['connect-group-invitation-url'];
			}
			return false;
		}


		private function protectedRequest($url) {
			$token = $_SERVER['HTTP_X_DATAPORTEN_TOKEN'];
			if(empty($token)) {
				Response::error(403, "Access denied: Dataporten token missing.");
			}

			$opts    = array(
				'http' => array(
					'method' => 'GET',
					'header' => "Authorization: Bearer " . $token,
				),
			);
			$context = stream_context_create($opts);
			$result  = file_get_contents($url, false, $context);
			return $result ? json_decode($result, true) : false;
		}
		/* Not used
			public function userAffiliation() {
				$affiliation = NULL;
				foreach($this->userGroups as $group) {
					if($group['type'] === 'fc:org') {
						if(!empty($group['membership']['primaryAffiliation'])) {
							return trim(strtolower($group['membership']['primaryAffiliation']));
						}
					}
				}
				//
				Response::error(401, "Your Feide affiliation was not found by the service ('primaryAffiliation')");
			}


			public function userDisplayName() {
				return $this->userInfo['name'];
			}
			*/

		/* Not used
		public function userFirstName() {
			return strtok($this->userDisplayName(), " ");
		}
		*/

		/* Currently not asking Dataporten GK for email
		public function userEmail() {
			return $this->userInfo['email'];
		}
		 */

		public function isSuperAdmin() {
			return strcasecmp($this->userOrgId(), "uninett.no") === 0;
		}

		public function userOrgId() {
			$userOrg = explode('@', $this->feideUsername());
			// e.g. 'uninett.no'
			return $userOrg[1];
		}

		public function feideUsername() {
			return $this->getFeideUsername();
		}

		private function getFeideUsername() {
			if(!isset($_SERVER["HTTP_X_DATAPORTEN_USERID_SEC"])) {
				Response::error(401, "The service was not able to find your Feide user account");
			}
			$userIdSec = NULL;
			// Get the username(s)
			$userid = $_SERVER["HTTP_X_DATAPORTEN_USERID_SEC"];
			// Future proofing...
			if(!is_array($userid)) {
				// If not already an array, make it so. If it is not a comma separated list, we'll get a single array item.
				$userid = explode(',', $userid);
			}
			// Fish for a Feide username
			foreach($userid as $key => $value) {
				if(strpos($value, 'feide:') !== false) {
					$value     = explode(':', $value);
					$userIdSec = $value[1];
				}
			}
			// No Feide...
			if(!isset($userIdSec)) {
				Response::error(401, "The service was not able to find your Feide user account");
			}

			// e.g. 'username@org.no'
			return $userIdSec;
		}

		public function userOrgName() {
			$userOrg = explode('@', $this->feideUsername());
			$userOrg = explode('.', $userOrg[1]);
			// e.g. 'uninett'
			return $userOrg[0];
		}

	}