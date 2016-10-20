<?php

	namespace Connect\Api;

	trait OrgsTrait {

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





	function getOrgSharedRooms($feide_org) {
		checkArgs(__FUNCTION__, func_get_args());
		$feide_org = strtolower($feide_org);
		$description = 'All shared meeting rooms created on the Adobe Connect service belonging to organisation ' . $feide_org . '.';
		// Folder names are excluding '.no'
		$requested_org = substr($feide_org, 0, -3);
		// 11004 is the ID of the 'shared meetings' folder
		// First, check to see if org has a subfolder in shared meetings.
		$sharedRoomsResponse = callConnectApi(array(
			'action'           => 'sco-expanded-contents',
			'sco-id'           => '11004',
			'filter-type'      => 'folder',
			'filter-name'      => $requested_org,       // Name must be equal to org (sans '.no') - case insensitive
			'session'          => $GLOBALS['apiCookie']
		));

		// No hits
		if(sizeof($sharedRoomsResponse['expanded-scos']) == 0) {
			return array(
				'status'           => FALSE,
				'meta'             => array('method'      => __FUNCTION__,
				                            'org'          => $feide_org,
				                            'description'  => $description,
				                            'cached_sec'   => CACHE_60MIN_TTL
				),
				'message'       => $feide_org . ' does not have a shared meetings folder named "' . $requested_org . '"',
				'raw'           => $sharedRoomsResponse
			);
		}

		// Extract sco-id of org folder
		$orgSharedFolderId = isset($sharedRoomsResponse['expanded-scos']['sco']['@attributes']['sco-id']) ? $sharedRoomsResponse['expanded-scos']['sco']['@attributes']['sco-id'] : NULL;
		// We have the sco for the org's shared meeting folder, now grab all rooms
		$sharedRoomsResponse = callConnectApi(array(
			'action'           => 'sco-expanded-contents',
			'sco-id'           => $orgSharedFolderId,
			'filter-type'      => 'meeting',
			'session'          => $GLOBALS['apiCookie']
		));

		return array(
			'status'           => TRUE,
			'meta'             => array('method'      => __FUNCTION__,
			                            'org'          => $feide_org,
			                            'description'  => $description,
			                            'cached_sec'   => CACHE_60MIN_TTL
			),
			'room_count'       => count($sharedRoomsResponse['expanded-scos']['sco']),
			'rooms'            => $sharedRoomsResponse['expanded-scos']['sco'],
			'raw'              => $sharedRoomsResponse
		);
	}

	function getOrgGroups($feide_org) {
		$description = 'All groups pertinent to organisation ' . $feide_org . '.';
		checkArgs(__FUNCTION__, func_get_args());
		$groups                = getGlobalGroups();
		$pertinentGlobalGroups = array('member', 'student', 'employee', 'staff', 'faculty', 'affiliate');
		$orgGroups             = array();
		$globalGroups          = array();
		$orgIndex              = 0;
		$globalIndex           = 0;

		foreach($groups['groups'] as $group) {
			if(strpos($group['name'], $feide_org) !== FALSE) {
				$orgGroups[$orgIndex]['id']   = $group['id'];
				$orgGroups[$orgIndex]['name'] = $group['name'];
				$orgIndex++;
			} else if(in_array($group['name'], $pertinentGlobalGroups)) {
				$globalGroups[$globalIndex]['id']   = $group['id'];
				$globalGroups[$globalIndex]['name'] = $group['name'];
				$globalIndex++;
			}
		}

		return array('status'        => TRUE,
		             'meta'          => array('method'      => __FUNCTION__,
		                                      'org'         => $feide_org,
		                                      'description' => $description,
		                                      'cached_sec'  => CACHE_60MIN_TTL
		             ),
		             'count'         => count($orgGroups) + count($globalGroups),
		             'count_org'     => count($orgGroups),
		             'count_global'  => count($globalGroups),
		             'groups_org'    => $orgGroups,
		             'groups_global' => $globalGroups,
		);
	}

	function getOrgGroupsExtended($feide_org) {
		$description = 'All groups pertinent for organisation ' . $feide_org . ' and the users belonging to each group.';
		checkArgs(__FUNCTION__, func_get_args());
		// Get the groups for this org
		$orgGroups    = getOrgGroups($feide_org);
		$groupMembers = array();
		if(!apc_exists('connect.orgGroupsExtended.' . $feide_org)) {
			// Get users for each ORG LOCAL group
			foreach($orgGroups['groups_org'] as $groupIndex => $group) {
				// Get group members
				$groupMembers[$group['name']] = callConnectApi(array('action'            => 'principal-list',
				                                                     'group-id'          => $group['id'],
				                                                     'filter-is-member'  => 'true',
				                                                     'filter-like-login' => $feide_org,
				                                                     'filter-type'       => 'user',
				                                                     'session'           => $GLOBALS['apiCookie']
				));
				if(isset($groupMembers[$group['name']]['principal-list']['principal'])) {
					$groupMembers[$group['name']]['principal-list']['principal'] = fix_assoc($groupMembers[$group['name']]['principal-list']['principal']);
					foreach($groupMembers[$group['name']]['principal-list']['principal'] as $memberIndex => $member) {
						$orgGroups['groups_org'][$groupIndex]['users'][$memberIndex]['id']    = $member['@attributes']['principal-id'];
						$orgGroups['groups_org'][$groupIndex]['users'][$memberIndex]['name']  = $member['name'];
						$orgGroups['groups_org'][$groupIndex]['users'][$memberIndex]['email'] = @$member['email'];
						$orgGroups['groups_org'][$groupIndex]['users'][$memberIndex]['login'] = $member['login'];
					}
				}
				$orgGroups['groups_org'][$groupIndex]['user_count'] = isset($orgGroups['groups_org'][$groupIndex]['users']) ? count($orgGroups['groups_org'][$groupIndex]['users']) : 0;
			}
			// Get users for each ORG GLOBAL group
			foreach($orgGroups['groups_global'] as $groupIndex => $group) {
				// Get group members
				$groupMembers[$group['name']] = callConnectApi(array('action'            => 'principal-list',
				                                                     'group-id'          => $group['id'],
				                                                     'filter-is-member'  => 'true',
				                                                     'filter-type'       => 'user',
				                                                     'filter-like-login' => $feide_org,
				                                                     'session'           => $GLOBALS['apiCookie']
				));
				if(isset($groupMembers[$group['name']]['principal-list']['principal'])) {
					$groupMembers[$group['name']]['principal-list']['principal'] = fix_assoc($groupMembers[$group['name']]['principal-list']['principal']);
					foreach($groupMembers[$group['name']]['principal-list']['principal'] as $memberIndex => $member) {
						$orgGroups['groups_global'][$groupIndex]['users'][$memberIndex]['id']    = $member['@attributes']['principal-id'];
						$orgGroups['groups_global'][$groupIndex]['users'][$memberIndex]['name']  = $member['name'];
						$orgGroups['groups_global'][$groupIndex]['users'][$memberIndex]['email'] = @$member['email'];
						$orgGroups['groups_global'][$groupIndex]['users'][$memberIndex]['login'] = $member['login'];
					}
				}
				$orgGroups['groups_global'][$groupIndex]['user_count'] = isset($orgGroups['groups_global'][$groupIndex]['users']) ? count($orgGroups['groups_global'][$groupIndex]['users']) : 0;
			}
			apc_store('connect.orgGroupsExtended.' . $feide_org, $orgGroups, CACHE_60MIN_TTL);
		}
		$orgGroups = apc_fetch('connect.orgGroupsExtended.' . $feide_org);

		return array('status'              => TRUE,
		             'meta'                => array('method'      => __FUNCTION__,
		                                            'org'         => $feide_org,
		                                            'description' => $description,
		                                            'cached_sec'  => CACHE_60MIN_TTL
		             ),
		             'groups_org_count'    => count($orgGroups['groups_org']),
		             'groups_global_count' => count($orgGroups['groups_global']),
		             'groups_org'          => $orgGroups['groups_org'],
		             'groups_global'       => $orgGroups['groups_global']
		);
	}