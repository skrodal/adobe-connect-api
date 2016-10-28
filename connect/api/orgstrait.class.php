<?php

	namespace Connect\Api;

	trait OrgsTrait {

		public function orgUsers($org) {
			$request  = ['action' => 'report-bulk-users'];
			//
			if(!$this->dataporten->isSuperAdmin()) {
				$org = $this->dataporten->userOrgId();
			}
			$request['filter-like-login'] = $org;
			$response = $this->callConnectApi($request);
			return $response->{'report-bulk-users'};
		}

		/**
		 * Sorted list with { org.no : usercount }
		 *
		 * @return array
		 */
		public function orgsUserCount() {
			$request  = ['action' => 'principal-list', 'filter-type' => 'user'];
			$response = $this->callConnectApi($request);
			$orgs     = [];

			foreach($response->{'principal-list'}->principal as $user) {
				$org = explode('@', $user->login);
				// Only users with a org.xx name
				if(isset($org[1]) && strstr($org[1], '.')) {
					isset($orgs[$org[1]]) ? $orgs[$org[1]]++ : $orgs[$org[1]] = 1;
				}
			}
			ksort($orgs);
			return $orgs;
		}
	}