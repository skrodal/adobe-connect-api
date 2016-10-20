<?php

	namespace Connect\Api;

	trait GroupsTrait {
		/**
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
					$groupList[] = (string)$group->name;
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


		/**
		 * 1) Grab all rooms
		 * 2) Find the principal-ids with a filter for `type` `host` using rooms's `sco-id` to get the `permissions-info`
		 * 3) Loop through and grab only the first `principal-id`, which should be the original Host of the room
		 *
		 * @param null $org
		 *
		 * @return array
		 */
		function groupsHostsCount($org = NULL) {
			// All meeting rooms on server
			$responseRooms = $this->_roomsAll();
			$hosts         = [];
			foreach($responseRooms->row as $room) {
				$host = $this->callConnectApi(['action'               => 'permissions-info',
				                               'acl-id'               => (string)$room->attributes()->{'sco-id'},
				                               'filter-permission-id' => 'host'
				]);

				// User ID as key (to keep unique array)
				$hosts[(string)$host->permissions->principal->attributes()->{'principal-id'}] = true;
			}
			return count($hosts);
		}
	}