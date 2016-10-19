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
	}