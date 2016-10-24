<?php

	namespace Connect\Api;

	/**
	 * Note: In this API, we refer to rooms/meetings differently than Adobe Connect Web Services:
	 *
	 * This API     |   Adobe Connect Web Services
	 * -------------------------------------------
	 * Meeting      |   Session
	 * Room         |   Meeting
	 */
	trait MeetingsTrait {

		/**
		 * Active meetings right now
		 * @return int
		 */
		public function meetingsActiveCount() {
			$response = $this->callConnectApi(['action' => 'report-active-meetings']);

			return isset($response->{'report-active-meetings'}->sco) ? count($response->{'report-active-meetings'}->sco) : 0;
		}

		/**
		 * NOTE: The call returns only users who logged in to the meeting as participants, not users who entered as guests.
		 *
		 * Large range will produce large amounts of data - handle with care...
		 *
		 * @param $start_timestamp
		 * @param $end_timestamp
		 *
		 * @return array
		 */
		public function meetingsStatsInPeriod($start_timestamp, $end_timestamp, $org = null) {
			// Convert timestamps for something eadible for Adobe Connect
			$range_start = date(DATE_ATOM, (int)$start_timestamp);
			$range_end   = date(DATE_ATOM, (int)$end_timestamp);
			//
			$request = ['action'                 => 'report-bulk-consolidated-transactions',
			            'filter-type'            => 'meeting',
			            'filter-gt-date-created' => $range_start,
			            'filter-lt-date-created' => $range_end,
			            'sort-date-closed'       => 'asc'
			];

			if(!is_null($org)){
				if(!$this->dataporten->isSuperAdmin) {
					// If not SuperAdmin, default back to logged in user's org
					$org = $this->dataporten->userOrgId();
				}
				// Add org-filter
				$request['filter-like-login'] = $org;
			}

			// Make the call
			$apiResponse    = $this->callConnectApi($request);

			$uniqueRoomAndUserCount['from_timestamp'] = (int)$start_timestamp;
			$uniqueRoomAndUserCount['to_timestamp']   = (int)$end_timestamp;
			$uniqueRoomAndUserCount['sessions']       = 0;
			$uniqueRoomAndUserCount['rooms']          = [];
			$uniqueRoomAndUserCount['users']          = [];
			$uniqueRoomAndUserCount['duration_sec']   = 0;

			$roomAndUserCountByDate = [];

			// Collect info from each meeting
			foreach($apiResponse->{'report-bulk-consolidated-transactions'}->row as $meeting) {
				// Only count completed meetings
				if(isset($meeting->{'date-closed'})) {
					//
					$meetingDate = date("d.m.Y", strtotime((string)$meeting->{'date-closed'}));
					if(!isset($roomAndUserCountByDate[$meetingDate])) {
						$roomAndUserCountByDate[$meetingDate]['duration_sec'] = 0;
						$roomAndUserCountByDate[$meetingDate]['sessions']     = 0;
						$roomAndUserCountByDate[$meetingDate]['rooms']        = [];
						$roomAndUserCountByDate[$meetingDate]['users']        = [];
					}
					// Keep track of sessions per date
					$roomAndUserCountByDate[$meetingDate]['sessions']++;

					// Duration of this session
					$meetingDuration = abs(strtotime($meeting->{'date-closed'}) - strtotime($meeting->{'date-created'}));
					// Add to total for this date
					$roomAndUserCountByDate[$meetingDate]['duration_sec'] += $meetingDuration;
					// ... and the total for period
					$uniqueRoomAndUserCount['duration_sec'] += $meetingDuration;

					// User
					$login = (string)$meeting->attributes()->{'principal-id'};
					// Count users for this date
					$roomAndUserCountByDate[$meetingDate]['users'][$login] = true;
					// ...and the total for period
					$uniqueRoomAndUserCount['users'][$login] = true;

					// Room
					$room = (string)$meeting->attributes()->{'sco-id'};
					// Count rooms for this date
					$roomAndUserCountByDate[$meetingDate]['rooms'][$room] = true;
					// ...and the total for period
					$uniqueRoomAndUserCount['rooms'][$room] = true;

				}
			}

			// Ditch details, only want counts per date
			foreach($roomAndUserCountByDate as $key => $dateObj) {
				$roomAndUserCountByDate[$key]['rooms'] = count($roomAndUserCountByDate[$key]['rooms']);
				$roomAndUserCountByDate[$key]['users'] = count($roomAndUserCountByDate[$key]['users']);
			}


			// Totals summary
			$uniqueRoomAndUserCount['sessions']     = count($apiResponse->{'report-bulk-consolidated-transactions'}->row);
			$uniqueRoomAndUserCount['rooms']        = count($uniqueRoomAndUserCount['rooms']);
			$uniqueRoomAndUserCount['users']        = count($uniqueRoomAndUserCount['users']);
			$uniqueRoomAndUserCount['duration_sec'] = $uniqueRoomAndUserCount['duration_sec'];

			$response['requested_org'] = is_null($org) ? 'Alle' : $org;
			$response['summary'] = $uniqueRoomAndUserCount;
			$response['daily'] = $roomAndUserCountByDate;

			return $response;
		}

	}