<?php

	namespace Connect\Api;

	use Connect\Utils\Response;

	trait UsersTrait {
		/**
		 * If no user specified (or user is not SuperAdmin), request details for logged on user.
		 *
		 * @param $username
		 *
		 * @return array|bool
		 */
		public function userInfo($username = NULL) {
			if(!$this->dataporten->isSuperAdmin() || is_null($username)) {
				$username = $this->dataporten->feideUsername();
			}
			$request = ['action' => 'report-bulk-users', 'filter-login' => $username];
			// Lookup account info for requested user
			$response = $this->callConnectApi($request);
			// Ok search, but user does not exist (judged by missing metadata)
			if(!isset($response->{'report-bulk-users'}->row)) {
				Response::error(404, "User $username not found");
			}

			return $response->{'report-bulk-users'}->row;
		}


		/**
		 * User count total or for a specific org (requires SuperAdmin priv.)
		 *
		 * @param null $org
		 *
		 * @return int
		 */
		public function usersCount($org = NULL) {
			$request = ['action' => 'principal-list', 'filter-type' => 'user'];
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

		/**
		 * @param null $days
		 *
		 * @return array
		 */
		public function usersMaxConcurrent($days = NULL) {
			$request           = ['action' => 'report-meeting-concurrent-users'];
			$request['length'] = is_null($days) ? 7 : $days;
			$response          = $this->callConnectApi($request);

			return [
				'count'      => $response->{'report-meeting-concurrent-users'}->attributes()->{'max-users'},
				'frequency'  => $response->{'report-meeting-concurrent-users'}->attributes()->{'max-participants-freq'},
				'since_days' => $days
			];
		}

	}