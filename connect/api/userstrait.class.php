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
			$isMe = false;
			if(!$this->dataporten->isSuperAdmin() || is_null($username)) {
				$username = $this->dataporten->feideUsername() . 'assdffd';
				$isMe = true;
			}
			$request = ['action' => 'report-bulk-users', 'filter-login' => $username];
			// Lookup account info for requested user
			$response = $this->callConnectApi($request);
			// Ok search, but user does not exist (judged by missing metadata)
			if(!isset($response->{'report-bulk-users'}->row) && !$isMe) {
				// Respond with not found error in case it's a user lookup
				Response::error(404, "User $username not found");
			}
			// Empty response if request was /me/ and no user
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
		 * Returns the maximum number of users in Adobe Connect meetings concurrently in the last $days,
		 * and the number of times the maximum has been reached. The maximum is the peak number of users
		 * in any meetings at a single moment, whether one meeting, multiple concurrent meetings, or multiple
		 * overlapping meetings.
		 *
		 * Default days on the Adobe Connect service is 30. We can, however, override this.
		 *
		 * @param int $days
		 *
		 * @return array
		 */
		public function usersMaxConcurrent($days = 30) {
			$request  = ['action' => 'report-meeting-concurrent-users', 'length' => $days];
			$response = $this->callConnectApi($request);

			return [
				'count'      => (string)$response->{'report-meeting-concurrent-users'}->attributes()->{'max-users'},
				'frequency'  => (string)$response->{'report-meeting-concurrent-users'}->attributes()->{'max-participants-freq'},
				'since_days' => $days
			];
		}

	}