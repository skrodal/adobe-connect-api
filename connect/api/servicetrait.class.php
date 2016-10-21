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
			$response['access'] = ['orgadmin' => false, 'superadmin' => false, 'role' => 'Gjest', 'desc' => 'Begrenset tilgang til informasjon'];
			$url = $this->dataporten->groupInvitationURL();
			if($url !== false){
				$response['access']['orgadmin'] = true;
				$response['access']['group-invitation'] = $url;
				$response['access']['role'] = 'OrgAdmin';
				$response['access']['desc'] = 'Tilgang til informasjon om din organisasjon og globale tall)';
			}
			if($this->dataporten->isSuperAdmin()){
				$response['access']['superadmin'] = true;
				$response['access']['role'] = 'SuperAdmin';
				$response['access']['desc'] = 'Sjef! Du har tilgang til alt :)';
			}

			return $response;
		}
	}