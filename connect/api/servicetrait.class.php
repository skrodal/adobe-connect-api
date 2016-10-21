<?php
	namespace Connect\Api;

	trait ServiceTrait {

		/**
		 * @return string
		 */
		public function serviceVersion() {
			$response = $this->callConnectApi(array('action' => 'common-info'));

			return (string)$response->common->version;
		}

		/**
		 * Get invitation URL to the ConnectAdmin Dataporten group.
		 *
		 * Returns false if logged on user is not member of group.
		 * @return mixed
		 */
		public function serviceInvitationURL() {
			return $this->dataporten->groupInvitationURL();
		}
	}