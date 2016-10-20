<?php

	namespace Connect\Api;

	trait RoomsTrait {
		/**
		 * Count total number of rooms on the service.
		 * @return int
		 */
		public function roomsCount() {
			$request  = ['action' => 'report-bulk-objects', 'filter-type' => 'meeting'];
			$response = $this->callConnectApi($request);

			return count($response->{'report-bulk-objects'}->row);
		}

		/**
		 * Count of rooms created within a set time frame.
		 *
		 * @param $start_timestamp
		 * @param $end_timestamp
		 *
		 * @return int
		 */
		public function roomsPeriodCount($start_timestamp, $end_timestamp) {
			$request                           = ['action' => 'report-bulk-objects', 'filter-type' => 'meeting'];
			$request['filter-gt-date-created'] = date(DATE_ATOM, (int)$start_timestamp);
			$request['filter-lt-date-created'] = date(DATE_ATOM, (int)$end_timestamp);
			$response                          = $this->callConnectApi($request);

			return count($response->{'report-bulk-objects'}->row);
		}

		/**
		 * Rooms created on the service within a set time frame.
		 *
		 * Not wired to a route, but used to assist other functions.
		 *
		 * @param $start_timestamp
		 * @param $end_timestamp
		 *
		 * @return mixed
		 */
		public function roomsPeriod($start_timestamp, $end_timestamp) {
			$request                           = ['action' => 'report-bulk-objects', 'filter-type' => 'meeting'];
			$request['filter-gt-date-created'] = date(DATE_ATOM, (int)$start_timestamp);
			$request['filter-lt-date-created'] = date(DATE_ATOM, (int)$end_timestamp);
			$response                          = $this->callConnectApi($request);

			return $response->{'report-bulk-objects'};
		}


		public function roomsUser($username = NULL) {
			if(!$this->dataporten->isSuperAdmin() || is_null($username)) {
				$username = $this->dataporten->feideUsername();
			}
			$folderId = $this->roomsUserFolderID($username);

			$response = callConnectApi(array('action'      => 'sco-contents',
			                                 'sco-id'      => $folderId,
			                                 'filter-rows' => 1,
			                                 'filter-type' => 'meeting'
			));

			$rooms = [];
			if(isset($response->scos->sco)) {
				// Fix when only one room; associative array is returned in sco instead of array index [0]
				// $roomsResponse['scos']['sco'] = fix_assoc($roomsResponse['scos']['sco']);
				$i = 0;
				foreach($response->scos->sco as $room) {
					$timestamp_created        = strtotime($room->{'date-created'});
					$timestamp_modified       = strtotime($room->{'date-modified'});
					$rooms[$i]['id']          = (string)$room->attributes()->{'sco-id'};
					$rooms[$i]['name']        = (string)$room->name;
					$rooms[$i]['description'] = (string)$room->description;
					$rooms[$i]['url-path']    = $room->{'url-path'};
					$rooms[$i]['created']     = strtolower(date("d.m.Y, H:i", $timestamp_created));
					$rooms[$i]['modified']    = strtolower(date("d.m.Y, H:i", $timestamp_modified));
					$i++;
				}
			}
			return $rooms;
		}

		/**
		 * Helper function: Get user's Meetings Folder ID
		 *
		 * @param null $username
		 *
		 * @return mixed
		 */
		private function roomsUserFolderID($username = NULL) {
			if(!$this->dataporten->isSuperAdmin() || is_null($username)) {
				$username = $this->dataporten->feideUsername();
			}
			$response = callConnectApi(['action'      => 'sco-search-by-field',
			                            'query'       => $username,
			                            'field'       => 'name',
			                            'filter-type' => 'folder'
			]);
			if(isset($response->{'sco-search-by-field-info'}->sco)) {
				foreach($response->{'sco-search-by-field-info'}->sco as $folder) {
					if(strcasecmp((string)$folder->{'folder-name'}, "User Meetings") == 0) {
						return (string)$folder->sco->attributes()->{'sco-id'};
					}
				}
				Response::error(404, "No meetings folder found for user $username");
			} else {
				Response::error(404, "No meetings folder found for user $username");
			}
		}
	}
