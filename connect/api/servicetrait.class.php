<?php
	namespace Connect\Api;

	trait ServiceTrait {

		/**
		 * @return string
		 */
		public function serviceVersion(){
			$response = $this->callConnectApi(array('action' => 'common-info'));
			return (string)$response->common->version;
		}

		/**
		 * TODO: Add URL to config
		 * Get invitation URL to the ConnectAdmin Dataporten group
		 * @return string
		 */
		public function serviceInvitationURL(){
			return "MÅ GJØRES!";
		}
	}