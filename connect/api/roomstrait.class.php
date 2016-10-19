<?php

	namespace Connect\Api;

	trait RoomsTrait {
		/**
		 * Total count or number of rooms created in the last $days
		 *
		 * @param null $days
		 *
		 * @return int
		 */
		public function roomsCount($days = NULL) {
			$request = ['action' => 'report-bulk-objects', 'filter-type' => 'meeting'];
			if(!is_null($days)) {
				$now                               = date(DATE_ATOM, mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y")));
				$then                              = date(DATE_ATOM, mktime(0, 0, 0, date("m"), date("d") - (int)$days, date("Y")));
				$request['filter-gt-date-created'] = $then;
				$request['filter-lt-date-created'] = $now;
			}
			$response = $this->callConnectApi($request);

			return count($response->{'report-bulk-objects'}->row);

		}
	}