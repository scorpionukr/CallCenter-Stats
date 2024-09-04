<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

class Ajam {

	/**
	 * Asterisk manager url address
	 *
	 * @var string
	 * @access private
	 */
	private $_urlraw = null;

	/**
	 * Asterisk manager user
	 *
	 * @var string
	 * @access private
	 */
	private $_usermanager;

	/**
	 * Asterisk manager secret
	 *
	 * @var string
	 * @access private
	 */
	private $_secretmanager;

	/**
	 * AJAM cookie file
	 *
	 * @var string
	 * @access private
	 */
	private $_cookiefile;

	/**
	 * AJAM authentication type
	 * plaintext or md5
	 *
	 * @var string
	 * @access private
	 */
	private $_authtype;

	/**
	 * Get result commands
	 *
	 * @var array
	 * @access private
	 */
	private $_result;

	/**
	 * Enable or disable debung commands
	 *
	 * Default: Disabled
	 *
	 * @var boolean
	 * @access private
	 */
	private $_debug;

	/**
	 * Ajam::setResult()
	 * Define variable $_result with a AJAM command result
	 *
	 * @access private
	 * @param array $data_arr Result of AJAM command
	 * @return void
	 */
	private function setResult($data_arr) {
		$this->_result = $data_arr;
	}

	/**
	 * Ajam::getResult()
	 *
	 * Get data of variable $_result
	 *
	 * @access public
	 * @return array
	 */
	public function getResult() {
		return $this->_result;
	}

	/**
	 * Ajam::__construct()
	 *
	 * Constructor method of class AJAM
	 *
	 * @access public
	 * @param aray $config_arr
	 * @return void
	 */
	public function __construct($config_arr) {
		// Check if phpversion is 5 or higher
		if (version_compare(PHP_VERSION, "5", "lt")) {
			die('Requires PHP 5 or higher');
		}
		// Verify if function curl_exists
		// its is needed by AJAM HTTP access
		if (!function_exists('curl_init')) {
			die('Php Curl module unavailable');
		}
		// Verify if the config variables is defined
		if (!$config_arr) {
			die('Error, please set the conection params');
		} else {
			$this->_urlraw = $config_arr['urlraw'];
			$this->_usermanager = $config_arr['admin'];
			$this->_secretmanager = $config_arr['secret'];
			$this->_authtype = $config_arr['authtype'];

			if (!$config_arr['cookiefile']) {
				$this->_cookiefile = 'ajam_cookie';
			} else {
				$this->_cookiefile = $config_arr['cookiefile'];
			}
			// Verify if cookie file exists
			// and have permissions to read/write
			if (file_exists($this->_cookiefile)) {
				if (!is_writable($this->_cookiefile)) {
					die("Change permission to read/write in file: $this->_cookiefile");
				}
				// If file not exists I try create it
			} else {
				if (@fopen($this->_cookiefile, 'w')) {
					@fclose($this->_cookiefile);
				} else {
					die("Change permission to read/write in file: $this->_cookiefile");
				}
			}
			if (isset($config_arr['debug']) && $config_arr['debug'] != false) {
				$this->_debug = true;
			}
		}
	}

	/**
	 * Ajam::parseRaw()
	 *
	 * Parser of result AJAM command
	 *
	 * @access private
	 * @param string $data Result of AJAM command
	 * @return array
	 */
	private function parseRaw($data) {
		$response = explode("\r\n", $data);
		if ($response[0] == "Response: Success") {
			$ret['Response'] = "Success";
		} else {
			$ret['Response'] = $response[0];
		}
		return $ret;
	}

	/**
	 * Ajam::doLogin()
	 *
	 * Make the AJAM login
	 *
	 * @access private
	 * @param string $authtype Use plaintext or md5
	 * @return boolean
	 */
	private function doLogin($authtype) {
		switch (strtolower($authtype)) {
		case "plaintext":

			$query = $this->buildQuery("Login", array('Username' => $this->_usermanager,
				'Secret' => $this->_secretmanager));

			$data = $this->getResponse("login", $query);
			$raw_result = $this->parseRaw($data);
			if (isset($raw_result['Response']) && $raw_result['Response'] == "Success") {
				if ($this->_debug) {
					print_r($raw_result);
					echo "Login success: Type: 'plaintext' \n";
					echo "User> $this->_usermanager  -- Pass> $this->_secretmanager \n";
				}
				return true;
			} else {
				die('Error in login process: check username, password, url');
				return false;
			}

		case "md5":
			$query = $this->buildQuery("Challenge", array('Authtype' => 'md5'));
			$data = $this->getResponse("command", $query);
			$raw_result = $this->parseRaw($data);

			if ($raw_result['Response'] != "Success") {
				return false;
			} else {
				$challenge = $raw_result['Test'][0]['Challenge'];

				$md5_key = md5($challenge . $this->_secretmanager);

				$query = $this->buildQuery("Login", array('AuthType' => 'MD5',
					'Username' => $this->_usermanager,
					'Key' => $md5_key));

				$data = $this->getResponse("login", $query);
				$raw_result = $this->parseRaw($data);
				if ($raw_result['Response'] == "Success") {
					if ($this->_debug) {
						print_r($raw_result);
						echo "Login success: Type: 'md5' \n";
						echo "User> $this->_usermanager  -- Pass> $this->_secretmanager \n";
					}
					return true;
				} else {
					die('Error in login process');
					return false;
				}
			}
		}
	}

	/**
	 * Ajam::getResponse()
	 *
	 * Execute HTTP post data to AJAM and get
	 * the command result
	 *
	 * @access private
	 * @param string $action_str Use login or command
	 * @param string $query_str Complete url query
	 * @return string
	 */
	function getResponse($action_str, $query_str) {
		switch (strtolower($action_str)) {
		case "login":
			$cur_resource = curl_init($query_str);
			@curl_setopt($cur_resource, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($cur_resource, CURLOPT_COOKIESESSION, 1);
			@curl_setopt($cur_resource, CURLOPT_COOKIEJAR, $this->_cookiefile);
			$data = curl_exec($cur_resource);
			@curl_close($cur_resource);
			break;
		case "command":
			$cur_resource = curl_init($query_str);
			@curl_setopt($cur_resource, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($cur_resource, CURLOPT_COOKIEFILE, $this->_cookiefile);
			$data = curl_exec($cur_resource);
			@curl_close($cur_resource);
			break;
		}
		return $data;
	}

	/**
	 * Ajam::checkCon()
	 *
	 * Verify if connection session
	 * is logged or no, this method
	 * use the AJAM ping command
	 *
	 * @access private
	 * @return boolean
	 */
	private function checkCon() {
		$query = $this->buildQuery("Ping", null);
		$data = $this->getResponse("command", $query);
		$raw_result = $this->parseRaw($data);
		if (isset($raw_result['Response']) && $raw_result['Response'] == 'Success') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Ajam::buildQuery()
	 *
	 * Build a complete URL AJAM query
	 * Ex.
	 * http://localhost:8088/asterisk/rawman?action=Ping
	 *
	 * @access private
	 * @param string $action_str AJAM Command
	 * @param array $params_arr Command params
	 * @return string
	 */
	private function buildQuery($action_str, $params_arr = array()) {
		$query = $this->_urlraw . "?action=" . $action_str;
		if ($params_arr) {
			foreach (array_keys($params_arr) as $key) {
				$query .= "&$key=" . $params_arr[$key];
			}
		}
		if ($this->_debug) {
			echo "QUERY $query \n";
		}
		// Replace blank space to %20 to be
		// a valid Url address
		return str_replace(' ', '%20', $query);
	}

	/**
	 * Ajam::doCommand()
	 *
	 * @access public
	 * @param string $action_str AJAM Command
	 * @param mixed-array/null $params_arr Command params
	 * @return array
	 */

	public function doCommand($action_str, $params_arr = array()) {
		if (!$this->checkCon()) {
			if ($this->_debug) {
				echo "Need login \n";
			}
			$this->doLogin($this->_authtype);
		}

		if (($action_str)) {
			$query = $this->buildQuery($action_str, $params_arr);
			$data = $this->getResponse("command", $query);
			//$raw_result = $this->parseRaw($data);

			if ($this->_debug) {
				print_r($data);
			}

			return $this->setResult($data);
		} else {
			die('Action/Command is required');
		}
	}

}

