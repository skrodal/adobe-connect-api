<?php

	namespace Connect\Api;

	trait ServiceTrait {

		/**
		 * @return string
		 */
		public function getVersion(){
			$apiCommonInfo = $this->callConnectApi(array('action' => 'common-info'));
			return (string)$apiCommonInfo->common->version;
		}
	}