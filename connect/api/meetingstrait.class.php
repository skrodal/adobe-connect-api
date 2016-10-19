<?php

	namespace Connect\Api;

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
		 * @param $start_timestamp
		 * @param $end_timestamp
		 *
		 * @return array
		 */
		public function meetingsStatsInPeriod($start_timestamp, $end_timestamp) {
			$range_start                              = date(DATE_ATOM, (int)$start_timestamp);
			$range_end                                = date(DATE_ATOM, (int)$end_timestamp);
			$response                                 = $this->callConnectApi(['action'                 => 'report-bulk-consolidated-transactions',
			                                                                   'filter-type'            => 'meeting',
			                                                                   'filter-gt-date-created' => $range_start,
			                                                                   'filter-lt-date-created' => $range_end
			]);
			$uniqueRoomAndUserCount['from_timestamp'] = (int)$start_timestamp;
			$uniqueRoomAndUserCount['to_timestamp']   = (int)$end_timestamp;
			$uniqueRoomAndUserCount['sessions']       = 0;
			$uniqueRoomAndUserCount['rooms']          = [];
			$uniqueRoomAndUserCount['users']          = [];
			$uniqueRoomAndUserCount['duration_sec']   = 0;

			// Collect info from each meeting
			foreach($response->{'report-bulk-consolidated-transactions'}->row as $meeting) {
				// Only count completed meetings
				if(isset($meeting->{'date-closed'})) {
					// Add duration to total
					$meetingDuration = abs(strtotime($meeting->{'date-closed'}) - strtotime($meeting->{'date-created'}));
					$uniqueRoomAndUserCount['duration_sec'] += $meetingDuration;
					// username
					$login = (string)$meeting->attributes()->{'principal-id'};
					// room id
					$room = (string)$meeting->attributes()->{'sco-id'};
					// add sco as key (we use this to count total number of rooms involved in transactions)
					$uniqueRoomAndUserCount['rooms'][$room] = true;
					// add sco as key (we use this to count total number of users involved in transactions)
					$uniqueRoomAndUserCount['users'][$login] = true;
				}
			}

			$uniqueRoomAndUserCount['sessions']     = count($response->{'report-bulk-consolidated-transactions'}->row);
			$uniqueRoomAndUserCount['rooms']        = count($uniqueRoomAndUserCount['rooms']);
			$uniqueRoomAndUserCount['users']        = count($uniqueRoomAndUserCount['users']);
			$uniqueRoomAndUserCount['duration_sec'] = $uniqueRoomAndUserCount['duration_sec'];

			return $uniqueRoomAndUserCount;
		}


	}