<?php

	namespace Connect\Api;

	trait GroupsTrait {
		/**
		 * TODO: Wire
		 * Get a list of all groups (or $orgsOnly = true for unique orgs using the service).
		 *
		 * @param bool $orgsOnly
		 *
		 * @return array
		 */
		public function groupsList($orgsOnly = false) {
			$request   = ['action' => 'principal-list', 'filter-type' => 'group'];
			$response  = $this->callConnectApi($request);
			$groupList = [];

			// Make a list of unique orgs
			foreach($response->{'principal-list'}->principal as $group) {
				if($orgsOnly == false) {
					$groupList[] = $group->name;
					continue;
				}
				// If only orgs should be returned:
				$org = explode('@', $group->name);
				$org = $org[sizeof($org) - 1];
				// ignore non-org groups, such as 'affiliation', 'member', 'test'
				if(strstr($org, '.')) {
					$groupList[] = $org;
				}
			}

			// Make unique, reset array index and sort
			$groupList = array_values(array_unique($groupList));
			sort($groupList);

			return $groupList;
		}
	}