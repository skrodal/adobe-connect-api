<?php

	namespace Connect\Api;

	trait UsersTrait {

		/**
		 * If no user specified (or user is not SuperAdmin), request details for logged on user.
		 *
		 * @param $username
		 *
		 * @return array|bool
		 */
		public function getUserInfo($username = NULL) {
			if(!$this->dataporten->isSuperAdmin() || is_null($username)) {
				$username = $this->dataporten->feideUsername();
			}
			$request = array('action' => 'principal-list', 'filter-login' => $username);
			// Lookup account info for requested user
			$response = $this->callConnectApi($request);
			// Ok search, but user does not exist (judged by missing metadata)
			if(!isset($response->{'principal-list'}->principal)) {
				Response::error(404, 'User ' . $username . ' not found');
			}

			return $response;
		}


		/**
		 * User count total or for a specific org (requires SuperAdmin priv.)
		 *
		 * @param null $org
		 *
		 * @return int
		 */
		public function getUserCount($org = NULL) {
			$request = array('action' => 'principal-list', 'filter-type' => 'user');
			// Ensure that non-SuperAdmins can only make request for home org
			if(!$this->dataporten->isSuperAdmin() && !is_null($org)) {
				$org = $this->dataporten->userOrgId();
			}
			if(!is_null($org)) {
				$request['filter-like-login'] = $org;
			}

			$response  = $this->callConnectApi($request);
			$userCount = count($response->{'principal-list'}->principal);

			return $userCount;
		}
	}