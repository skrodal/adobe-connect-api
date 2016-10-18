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
			$this->declareServiceRoutes();
			$this->declareMeRoutes();
			$this->declareRoomsRoutes();
			$this->declareMeetingsRoutes();
			$this->declareUsersRoutes();

			if($this->dataporten->isSuperAdmin()) {
				$this->declareAdminRoutes();
				$this->declareDevRoutes();
			}
			// Activate routes
			$this->matchRoutes();

		}

		/**
		 * INFO ROUTES
		 *
		 * @return string
		 */
		private function declareServiceRoutes() {
			$this->altoRouter->addRoutes([
				// List all routes
				array('GET', '/', function () {
					Response::result($this->altoRouter->getRoutes());
				}, 'All available routes.'),
				//
				array('GET', '/service/version/', function () {
					Response::result($this->connect->getVersion());
				}, 'Adobe Connect version.'),
			]);
		}

		/**
		 *
		 */
		private function declareMeRoutes() {
			$this->altoRouter->addRoutes([
				// List all routes
				array('GET', '/me/', function () {
					Response::result($this->connect->getUserInfo());
				}, 'Account details pertaining to logged on user.')
			]);
		}

		private function declareRoomsRoutes() {

		}

		private function declareMeetingsRoutes() {

		}

		private function declareUsersRoutes() {
			$this->altoRouter->addRoutes([
				array('GET', '/users/count/', function () {
					$response = $this->connect->getUserCount();
					Response::result($response);
				}, 'Total number of user accounts.'),
			    //
				array('GET', '/users/[org:orgName]/count/', function ($orgName) {
					$response = $this->connect->getUserCount($orgName);
					Response::result($response);
				}, 'Total number of user accounts pertaining to a specific org.')
			]);
		}

		/**
		 *
		 */
		private function declareAdminRoutes() {
			$this->altoRouter->addRoutes([
				array('GET', '/users/[user:userName]/', function ($userName) {
					$response = $this->connect->getUserInfo($userName);
					Response::result($response);
				}, 'Account details pertaining to a specific user.')
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
