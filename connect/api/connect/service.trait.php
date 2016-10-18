<?php
	
	namespace Connect\Api\Connect;


	trait Service {

		/**
		 * @return string
		 */
		public function getVersion(){
			$apiCommonInfo = $this->callConnectApi(array('action' => 'common-info'));
			return (string)$apiCommonInfo->common->version;
		}
	}