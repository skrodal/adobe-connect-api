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
	}
