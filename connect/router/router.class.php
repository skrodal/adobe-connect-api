<?php
	/**
	 * Required scope: admin (gk_adobe-connect_admin)
	 *    - Class Auth\Dataporten checks for required scope and returns Response::error(403) if missing
	 *
	 * @author Simon Skrødal
	 * @since  October 2016
	 */

	namespace Connect\Router;

	use Connect\Api\Connect;
	use Connect\Auth\Dataporten;
	use Connect\Conf\Config;
	use Connect\Utils\Response;
	use Connect\Vendor\AltoRouter;


	class Router {

		private $altoRouter, $connect, $dataporten;

		function __construct() {
			### ALTO ROUTER
			$this->altoRouter = new AltoRouter();
			$this->altoRouter->setBasePath(Config::get('altoRouter')['api_base_path']);
			$this->altoRouter->addMatchTypes(array('user' => '[0-9A-Za-z.@]++', 'org' => '[0-9A-Za-z.]++'));
			### DATAPORTEN
			$this->dataporten = new Dataporten();
			### CONNECT
			$this->connect = new Connect($this->dataporten);
			### ROUTES
			$this->declareGroupsRoutes();
			$this->declareMeetingsRoutes();
			$this->declareOrgsRoutes();
			$this->declareRoomsRoutes();
			$this->declareServiceRoutes();
			$this->declareUsersRoutes();

			if($this->dataporten->isSuperAdmin()) {
				$this->declareDevRoutes();
			}
			// Activate routes
			$this->matchRoutes();

		}

		/**
		 * Implemented in Groups trait
		 */
		private function declareGroupsRoutes() {
			$this->altoRouter->addRoutes([
				//
				array('GET', '/groups/orgs/', function () {
					Response::result($this->connect->groupsList(true));
				}, 'Sorted list of all organisations (groups) in the system'),
				array('GET', '/groups/', function () {
					Response::result($this->connect->groupsList());
				}, 'Sorted list of all groups in the system'),
			]);
		}

		/**
		 * Implemented in Meetings trait
		 */
		private function declareMeetingsRoutes() {
			$this->altoRouter->addRoutes([
				//
				array('GET', '/meetings/active/count/', function () {
					Response::result($this->connect->meetingsActiveCount());
				}, 'Number of active meetings on the server right now'),
				array('GET', '/meetings/stats/from/[i:from]/to/[i:to]/', function ($from, $to) {
					Response::result($this->connect->meetingsStatsInPeriod($from, $to));
				}, 'Meeting stats (users, rooms, sessions, duration) within a time period (defined by timestamps)'),
			]);
		}

		/**
		 * Implemented in Orgs trait
		 */
		private function declareOrgsRoutes() {
			$this->altoRouter->addRoutes([
				//
				array('GET', '/orgs/users/count/', function () {
					Response::result($this->connect->orgsUserCount());
				}, 'Number of users per org')
			]);
		}

		/**
		 * Implemented in Rooms trait
		 */
		private function declareRoomsRoutes() {
			$this->altoRouter->addRoutes([
				//
				array('GET', '/rooms/count/', function () {
					Response::result($this->connect->roomsCount());
				}, 'Total number of rooms on the service'),
				array('GET', '/rooms/new-since-days/[i:since_days]/count/', function ($since_days) {
					Response::result($this->connect->roomsCount($since_days));
				}, 'Total number of new rooms in the last days'),
			]);
		}

		/**
		 * Implemented in Service trait
		 */
		private function declareServiceRoutes() {
			$this->altoRouter->addRoutes([
				// List all routes
				array('GET', '/', function () {
					Response::result($this->altoRouter->getRoutes());
				}, 'All available routes.'),
				//
				array('GET', '/service/version/', function () {
					Response::result($this->connect->serviceVersion());
				}, 'Adobe Connect version.'),
			]);
		}

		/**
		 * Implemented in Users trait
		 */
		private function declareUsersRoutes() {
			$this->altoRouter->addRoutes([
				// List all routes
				array('GET', '/me/', function () {
					Response::result($this->connect->userInfo());
				}, 'Account details pertaining to logged on user.'),

				array('GET', '/users/[user:userName]/', function ($userName) {
					$response = $this->connect->userInfo($userName);
					Response::result($response);
				}, 'Account details pertaining to a specific user.'),
				array('GET', '/users/count/', function () {
					$response = $this->connect->usersCount();
					Response::result($response);
				}, 'Total number of user accounts.'),
				//
				array('GET', '/users/[org:orgName]/count/', function ($orgName) {
					$response = $this->connect->usersCount($orgName);
					Response::result($response);
				}, 'Total number of user accounts pertaining to a specific org.')
			]);
		}


		/**
		 * DEV PATHS - ONLY AVAILABLE IF LOGGED ON USER IS FROM UNINETT
		 */
		private function declareDevRoutes() {
			$this->altoRouter->addRoutes([
				array('GET', '/dev/', function () {
					$response = NULL;
					Response::result($response);
				}, 'Dev route to test.'),
			]);
		}

		private function matchRoutes() {
			$match = $this->altoRouter->match();

			if($match && is_callable($match['target'])) {
				call_user_func_array($match['target'], $match['params']);
			} else {
				Response::error(404, "Requested resource does not exist.");
			}
		}
	}
