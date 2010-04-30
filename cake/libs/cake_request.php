<?php
/**
 * A class that helps wrap Request information and particulars about a single request.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Core', 'Set');

class CakeRequest implements ArrayAccess {
/**
 * Array of parameters parsed from the url.
 *
 * @var array
 */
	public $params = array();

/**
 * Array of POST data.  Will contain form data as well as uploaded files.
 * Will only contain data from inputs that start with 'data'.  So
 * `<input name="some_input" />` will not end up in data. However,
 * `<input name="data[something]" />`
 *
 * @var array
 */
	public $data = array();

/**
 * Array of querystring arguments
 *
 * @var array
 */
	public $url = array();

/**
 * The built in detectors used with `is()` can be modified with `addDetector()`.
 *
 * There are several ways to specify a detector, see CakeRequest::addDetector() for the 
 * various formats and ways to define detectors.
 *
 * @var array
 */
	protected $_detectors = array(
		'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
		'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
		'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
		'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
		'head' => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
		'ssl' => array('env' => 'HTTPS', 'value' => 1),
		'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
		'flash' => array('env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/'),
		'mobile' => array('env' => 'HTTP_USER_AGENT', 'options' => array(
			'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'iPod', 'iPhone',
			'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'PalmOS', 'PalmSource',
			'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\.Browser',
			'webOS', 'Windows CE', 'Xiino'
		))
	);
/**
 * Constructor 
 *
 * @return void
 */
	public function __construct() {
		if (isset($_POST)) {
			$this->_processPost();
		}
		if (isset($_GET)) {
			$this->_processGet();
		}
		$this->_processFiles();
	}

/**
 * process the post data and set what is there into the object.
 *
 * @return void
 */
	protected function _processPost() {
		$this->params['form'] = $_POST;
		if (ini_get('magic_quotes_gpc') === '1') {
			$this->params['form'] = stripslashes_deep($this->params['form']);
		}
		if (env('HTTP_X_HTTP_METHOD_OVERRIDE')) {
			$this->params['form']['_method'] = env('HTTP_X_HTTP_METHOD_OVERRIDE');
		}
		if (isset($this->params['form']['_method'])) {
			if (!empty($_SERVER)) {
				$_SERVER['REQUEST_METHOD'] = $this->params['form']['_method'];
			} else {
				$_ENV['REQUEST_METHOD'] = $this->params['form']['_method'];
			}
			unset($this->params['form']['_method']);
		}
		if (isset($this->params['form']['data'])) {
			$this->data = $this->params['form']['data'];
			unset($this->params['form']['data']);
		}
	}

/**
 * Process the GET parameters and move things into the object.
 *
 * @return void
 */
	protected function _processGet() {
		if (ini_get('magic_quotes_gpc') === '1') {
			$url = stripslashes_deep($_GET);
		} else {
			$url = $_GET;
		}
		if (isset($this->params['url'])) {
			$this->url = array_merge($this->url, $url);
		} else {
			$this->url = $url;
		}
	}

/**
 * Process $_FILES and move things into the object.
 *
 * @return void
 */
	protected function _processFiles() {
		if (isset($_FILES) && is_array($_FILES)) {
			foreach ($_FILES as $name => $data) {
				if ($name != 'data') {
					$this->params['form'][$name] = $data;
				}
			}
		}

		if (isset($_FILES['data'])) {
			foreach ($_FILES['data'] as $key => $data) {
				foreach ($data as $model => $fields) {
					if (is_array($fields)) {
						foreach ($fields as $field => $value) {
							if (is_array($value)) {
								foreach ($value as $k => $v) {
									$this->data[$model][$field][$k][$key] = $v;
								}
							} else {
								$this->data[$model][$field][$key] = $value;
							}
						}
					} else {
						$this->data[$model][$key] = $fields;
					}
				}
			}
		}
	}

/**
 * Get the IP the client is using, or says they are using.
 *
 * @param boolean $safe Use safe = false when you think the user might manipulate their HTTP_CLIENT_IP
 *   header.  Setting $safe = false will will also look at HTTP_X_FORWARDED_FOR
 * @return void
 */
	public function getClientIp($safe = true) {
		if (!$safe && env('HTTP_X_FORWARDED_FOR') != null) {
			$ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
		} else {
			if (env('HTTP_CLIENT_IP') != null) {
				$ipaddr = env('HTTP_CLIENT_IP');
			} else {
				$ipaddr = env('REMOTE_ADDR');
			}
		}

		if (env('HTTP_CLIENTADDRESS') != null) {
			$tmpipaddr = env('HTTP_CLIENTADDRESS');

			if (!empty($tmpipaddr)) {
				$ipaddr = preg_replace('/(?:,.*)/', '', $tmpipaddr);
			}
		}
		return trim($ipaddr);
	}

/**
 * Returns the referer that referred this request.
 *
 * @param boolean $local Attempt to return a local address. Local addresses do not contain hostnames.
 * @return string The referring address for this request.
 */
	public function referer($local = false) {
		$ref = env('HTTP_REFERER');
		$base = '';
		if (defined('FULL_BASE_URL')) {
			$base = FULL_BASE_URL;
		}
		if (!empty($ref)) {
			if ($local && strpos($ref, $base) === 0) {
				$ref = substr($ref, strlen($base));
				if ($ref[0] != '/') {
					$ref = '/' . $ref;
				}
			}
			return $ref;
		}
		return '/';
	}

/**
 * Missing method handler, handles wrapping older style isAjax() type methods
 *
 * @param string $name The method called
 * @param array $params Array of parameters for the method call
 * @return mixed
 */
	public function __call($name, $params) {
		if (strpos($name, 'is') === 0) {
			$type = strtolower(substr($name, 2));
			return $this->is($type);
		}
	}

/**
 * Magic get method allows access to parsed routing parameters directly on the object.
 *
 * @param string $name The property being accessed.
 * @return mixed Either the value of the parameter or null.
 */
	public function __get($name) {
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return null;
	}

/**
 * Check whether or not a Request is a certain type.  Uses the built in detection rules
 * as well as additional rules defined with CakeRequest::addDetector().  Any detector can be called 
 * with `is($type)` or `is$Type()`.
 *
 * @param string $type The type of request you want to check.
 * @return boolean Whether or not the request is the type you are checking.
 */
	public function is($type) {
		$type = strtolower($type);
		if (!isset($this->_detectors[$type])) {
			return false;
		}
		$detect = $this->_detectors[$type];
		if (isset($detect['env'])) {
			if (isset($detect['value'])) {
				return env($detect['env']) == $detect['value'];
			}
			if (isset($detect['pattern'])) {
				return (bool)preg_match($detect['pattern'], env($detect['env']));
			}
			if (isset($detect['options'])) {
				$pattern = '/' . implode('|', $detect['options']) . '/i';
				return (bool)preg_match($pattern, env($detect['env']));
			}
		}
		if (isset($detect['callback']) && is_callable($detect['callback'])) {
			return call_user_func($detect['callback'], $this);
		}
		return false;
	}

/**
 * Add a new detector to the list of detectors that a request can use.
 * There are several different formats and types of detectors that can be set.
 *
 * ### Environment value comparison
 *
 * An environment value comparison, compares a value fetched from `env()` to a known value
 * the environment value is equality checked against the provided value.
 *
 * e.g `addDetector('post', array('env' => 'REQUEST_METHOD', 'value' => 'POST'))`
 *
 * ### Pattern value comparison
 *
 * Pattern value comparison allows you to compare a value fetched from `env()` to a regular expression.
 * 
 * e.g `addDetector('iphone', array('env' => 'HTTP_USER_AGENT', 'pattern' => '/iPhone/i'));
 *
 * ### Option based comparison
 *
 * Option based comparisons use a list of options to create a regular expression.  Subsequent calls
 * to add an already defined options detector will merge the options.
 *
 * e.g `addDetector('mobile', array('env' => 'HTTP_USER_AGENT', 'options' => array('Fennec')));`
 *
 * ### Callback detectors
 *
 * Callback detectors allow you to provide a 'callback' type to handle the check.  The callback will
 * recieve the request object as its only parameter.
 *
 * e.g `addDetector('custom', array('callback' => array('SomeClass', 'somemethod')));`
 *
 * @param string $name The name of the detector.
 * @param array $options  The options for the detector definition.  See above.
 * @return void
 */
	public function addDetector($name, $options) {
		if (isset($this->_detectors[$name]) && isset($options['options'])) {
			$options = Set::merge($this->_detectors[$name], $options);
		}
		$this->_detectors[$name] = $options;
	}

/**
 * Array access read implementation
 *
 * @param string $name Name of the key being accessed.
 * @return mixed
 */
	public function offsetGet($name) {
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return null;
	}

/**
 * Array access write implementation
 *
 * @param string $name Name of the key being written
 * @param mixed $value The value being written.
 * @return void
 */
	public function offsetSet($name, $value) {
		$this->params[$name] = $value;
	}

/**
 * Array access isset() implementation
 *
 * @param string $name thing to check.
 * @return boolean
 */
	public function offsetExists($name) {
		return isset($this->params[$name]);
	}

/**
 * Array access unset() implementation
 *
 * @param $name Name to unset.
 * @return void
 */
	public function offsetUnset($name) {
		unset($this->params[$name]);
	}
}