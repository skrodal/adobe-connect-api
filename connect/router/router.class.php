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
			### DATAPORTEN
			$this->dataporten = new Dataporten();
			// Make all GET routes available
			$this->declareGetRoutes();
			if($this->dataporten->isSuperAdmin()) {
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
		private function declareGetRoutes() {
			$this->altoRouter->addRoutes([
				// List all routes
				array('GET', '/', function () {
					Response::result($this->altoRouter->getRoutes());
				}, 'All available routes.'),
				//
				array('GET', '/service/version/', function () {
					$this->connect = new Connect($this->dataporten);
					Response::result($this->connect->getVersion());
				}, 'Adobe Connect version.'),
			]);
		}


		/**
		 * DEV PATHS - ONLY AVAILABLE IF LOGGED ON USER IS FROM UNINETT
		 *
		 * @return string
		 */
		private function declareDevRoutes() {
			$this->altoRouter->addRoutes([
				array('GET', '/dev/', function () {
					$response = null;
					Response::result($response);
				}, 'Dev route to test.'),
			]);
		}


		private function matchRoutes() {
			$match = $this->altoRouter->match();

			if($match && is_callable($match['target'])) {
				call_user_func_array($match['target'], $match['params']);
			} else {
				Response::error(404, "URLen det spørres etter finnes ikke.");
			}
		}


	}
