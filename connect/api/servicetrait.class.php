<?php
	namespace Connect\Api;

	use Connect\Vendor\JWT;

	trait ServiceTrait {

		/**
		 * @return string
		 */
		public function serviceVersion() {
			$response = $this->callConnectApi(array('action' => 'common-info'));

			return (string)$response->common->version;
		}

		/**
		 * Get user (admin)roles and Dataporten group invitation (if member).
		 *
		 * Importantly (and perhaps not well enough documented on my behalf),
		 * this function/route also returns the auth cookie for communicating with Adobe Connect
		 * Web Services (using JWT and Dataporten's own token as key).
		 *
		 * This function/route will generate a new token for each request - the client should only call
		 * this once per session (thus, client refresh == new session)
		 *
		 * @return mixed
		 */
		public function serviceAccessDetails() {
			if(empty($_SERVER['HTTP_X_DATAPORTEN_TOKEN'])) {
				Response::error(403, "Access denied: Dataporten token missing.");
			}
			$response['access'] = ['orgadmin' => false, 'superadmin' => false, 'role' => 'Gjest', 'desc' => 'Begrenset tilgang til informasjon'];
			// Store AC session cookie as a JWT, using Dataporten's token as key
			$response['access']['ac_token'] = JWT::encode($this->getSessionAuthCookie(), $_SERVER['HTTP_X_DATAPORTEN_TOKEN']);
			//
			$url = $this->dataporten->groupInvitationURL();
			//
			if($url !== false) {
				$response['access']['orgadmin']         = true;
				$response['access']['group-invitation'] = $url;
				$response['access']['role']             = 'OrgAdmin';
				$response['access']['desc']             = 'Tilgang til informasjon om din organisasjon og globale tall)';
			}
			if($this->dataporten->isSuperAdmin()) {
				$response['access']['superadmin'] = true;
				$response['access']['role']       = 'SuperAdmin';
				$response['access']['desc']       = 'Sjef! Du har tilgang til alt :)';
			}

			return $response;
		}
	}