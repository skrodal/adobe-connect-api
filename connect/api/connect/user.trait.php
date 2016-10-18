<?php

	namespace Connect\Api\Connect;


	trait User {

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
			// Lookup account info for requested user
			$apiUserInfoResponse = $this->callConnectApi(
				array(
					'action'       => 'principal-list',
					'filter-login' => $username
				)
			);
			// Exit on error
			if(strcasecmp((string)$apiUserInfoResponse->status['code'], "ok") !== 0) {
				Response::error(400, 'User lookup failed: ' . $username . ': ' . (string)$apiUserInfoResponse->status['subcode']);
			}
			// Ok search, but user does not exist (judged by missing metadata)
			if(!isset($apiUserInfoResponse->{'principal-list'}->principal)) {
				Response::error(404, 'User ' . $username . ' not found');
			}

			// Done :-)
			return array(
				'principal_id'  => (string)$apiUserInfoResponse->{'principal-list'}->principal['principal-id'],
				'username'      => (string)$apiUserInfoResponse->{'principal-list'}->principal->login,
				'response_full' => $apiUserInfoResponse
			);
		}

		public function getUserCount() {

		}

	}