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
		 * Get user (admin)roles and Dataporten group invitation (if member)
		 *
		 * @return mixed
		 */
		public function serviceAccessDetails() {
			$response = ['role' => 'Gjest', 'orgadmin' => false, 'superadmin' => false];
			$url = $this->dataporten->groupInvitationURL();
			if($url !== false){
				$response['orgadmin'] = true;
				$response['group-invitation'] = $url;
				$response['role'] = 'OrgAdmin';
			}
			if($this->dataporten->isSuperAdmin()){
				$response['superadmin'] = true;
				$response['role'] = 'SuperAdmin';
			}

			return $response;
		}
	}