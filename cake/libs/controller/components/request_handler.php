<?php
/**
 * Request object for handling alternative HTTP requests
 *
 * Alternative HTTP requests can come from wireless units like mobile phones, palmtop computers,
 * and the like.  These units have no use for Ajax requests, and this Component can tell how Cake
 * should respond to the different needs of a handheld computer and a desktop machine.
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
 * @subpackage    cake.cake.libs.controller.components
 * @since         CakePHP(tm) v 0.10.4.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Request object for handling HTTP requests
 *
 * @package       cake
 * @subpackage    cake.cake.libs.controller.components
 * @link http://book.cakephp.org/view/1291/Request-Handling
 *
 */
class RequestHandlerComponent extends Object {

/**
 * The layout that will be switched to for Ajax requests
 *
 * @var string
 * @access public
 * @see RequestHandler::setAjax()
 */
	public $ajaxLayout = 'ajax';

/**
 * Determines whether or not callbacks will be fired on this component
 *
 * @var boolean
 * @access public
 */
	public $enabled = true;

/**
 * Holds the content-type of the response that is set when using
 * RequestHandler::respondAs()
 *
 * @var string
 * @access private
 */
	private $__responseTypeSet = null;

/**
 * Holds the copy of Controller::$request
 *
 * @var CakeRequest
 * @access public
 */
	public $request;

/**
 * Friendly content-type mappings used to set response types and determine
 * request types.  Can be modified with RequestHandler::setContent()
 *
 * @var array
 * @access private
 * @see RequestHandlerComponent::setContent
 */
	protected $_contentTypeMap = array(
		'javascript'	=> 'text/javascript',
		'js'			=> 'text/javascript',
		'json'			=> 'application/json',
		'css'			=> 'text/css',
		'html'			=> array('text/html', '*/*'),
		'text'			=> 'text/plain',
		'txt'			=> 'text/plain',
		'csv'			=> array('application/vnd.ms-excel', 'text/plain'),
		'form'			=> 'application/x-www-form-urlencoded',
		'file'			=> 'multipart/form-data',
		'xhtml'			=> array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
		'xhtml-mobile'	=> 'application/vnd.wap.xhtml+xml',
		'xml'			=> array('application/xml', 'text/xml'),
		'rss'			=> 'application/rss+xml',
		'atom'			=> 'application/atom+xml',
		'amf'			=> 'application/x-amf',
		'wap'			=> array(
			'text/vnd.wap.wml',
			'text/vnd.wap.wmlscript',
			'image/vnd.wap.wbmp'
		),
		'wml'			=> 'text/vnd.wap.wml',
		'wmlscript'		=> 'text/vnd.wap.wmlscript',
		'wbmp'			=> 'image/vnd.wap.wbmp',
		'pdf'			=> 'application/pdf',
		'zip'			=> 'application/x-zip',
		'tar'			=> 'application/x-tar'
	);

/**
 * The template to use when rendering the given content type.
 *
 * @var string
 * @access private
 */
	private $__renderType = null;

/**
 * Contains the file extension parsed out by the Router
 *
 * @var string
 * @access public
 * @see Router::parseExtensions()
 */
	public $ext = null;

/**
 * Initializes the component, gets a reference to Controller::$parameters, and
 * checks to see if a file extension has been parsed by the Router.  Or if the 
 * HTTP_ACCEPT_TYPE is set to a single value that is a supported extension and mapped type.
 * If yes, RequestHandler::$ext is set to that value
 *
 * @param object $controller A reference to the controller
 * @param array $settings Array of settings to _set().
 * @return void
 * @see Router::parseExtensions()
 */
	public function initialize(&$controller, $settings = array()) {
		$this->request = $controller->request;
		if (isset($controller->params['url']['ext'])) {
			$this->ext = $controller->params['url']['ext'];
		}
		if (empty($this->ext)) {
			$accepts = $this->request->accepts();
			$extensions = Router::extensions();
			if (count($accepts) == 1) {
				$mapped = $this->mapType($accepts[0]);
				if (in_array($mapped, $extensions)) {
					$this->ext = $mapped;
				}
			}
		}
		$this->_set($settings);
	}

/**
 * The startup method of the RequestHandler enables several automatic behaviors
 * related to the detection of certain properties of the HTTP request, including:
 *
 * - Disabling layout rendering for Ajax requests (based on the HTTP_X_REQUESTED_WITH header)
 * - If Router::parseExtensions() is enabled, the layout and template type are
 *   switched based on the parsed extension or Accept-Type header.  For example, if `controller/action.xml`
 *   is requested, the view path becomes `app/views/controller/xml/action.ctp`. Also if
 *   `controller/action` is requested with `Accept-Type: application/xml` in the headers
 *   the view path will become `app/views/controller/xml/action.ctp`.
 * - If a helper with the same name as the extension exists, it is added to the controller.
 * - If the extension is of a type that RequestHandler understands, it will set that
 *   Content-type in the response header.
 * - If the XML data is POSTed, the data is parsed into an XML object, which is assigned
 *   to the $data property of the controller, which can then be saved to a model object.
 *
 * @param object $controller A reference to the controller
 * @return void
 */
	public function startup(&$controller) {
		$controller->request->params['isAjax'] = $this->request->is('ajax');
		$isRecognized = (
			!in_array($this->ext, array('html', 'htm')) &&
			in_array($this->ext, array_keys($this->_contentTypeMap))
		);

		if (!empty($this->ext) && $isRecognized) {
			$this->renderAs($controller, $this->ext);
		} elseif ($this->request->is('ajax')) {
			$this->renderAs($controller, 'ajax');
		}

		if ($this->requestedWith('xml')) {
			if (!class_exists('XmlNode')) {
				App::import('Core', 'Xml');
			}
			$xml = new Xml(trim(file_get_contents('php://input')));

			if (count($xml->children) == 1 && is_object($dataNode = $xml->child('data'))) {
				$controller->data = $dataNode->toArray();
			} else {
				$controller->data = $xml->toArray();
			}
		}
	}

/**
 * Handles (fakes) redirects for Ajax requests using requestAction()
 *
 * @param object $controller A reference to the controller
 * @param mixed $url A string or array containing the redirect location
 * @param mixed HTTP Status for redirect
 */
	public function beforeRedirect(&$controller, $url, $status = null) {
		if (!$this->request->is('ajax')) {
			return;
		}
		foreach ($_POST as $key => $val) {
			unset($_POST[$key]);
		}
		if (is_array($url)) {
			$url = Router::url($url + array('base' => false));
		}
		if (!empty($status)) {
			$statusCode = $controller->httpCodes($status);
			$code = key($statusCode);
			$msg = $statusCode[$code];
			$controller->header("HTTP/1.1 {$code} {$msg}");
		}
		echo $this->requestAction($url, array('return', 'bare' => false));
		$this->_stop();
	}

/**
 * Returns true if the current HTTP request is Ajax, false otherwise
 *
 * @return boolean True if call is Ajax
 */
	public function isAjax() {
		return $this->request->is('ajax');
	}

/**
 * Returns true if the current HTTP request is coming from a Flash-based client
 *
 * @return boolean True if call is from Flash
 */
	public function isFlash() {
		return $this->request->is('flash');
	}

/**
 * Returns true if the current request is over HTTPS, false otherwise.
 *
 * @return bool True if call is over HTTPS
 */
	public function isSSL() {
		return $this->request->is('ssl');
	}

/**
 * Returns true if the current call accepts an XML response, false otherwise
 *
 * @return boolean True if client accepts an XML response
 */
	public function isXml() {
		return $this->prefers('xml');
	}

/**
 * Returns true if the current call accepts an RSS response, false otherwise
 *
 * @return boolean True if client accepts an RSS response
 */
	public function isRss() {
		return $this->prefers('rss');
	}

/**
 * Returns true if the current call accepts an Atom response, false otherwise
 *
 * @return boolean True if client accepts an RSS response
 */
	public function isAtom() {
		return $this->prefers('atom');
	}

/**
 * Returns true if user agent string matches a mobile web browser, or if the
 * client accepts WAP content.
 *
 * @return boolean True if user agent is a mobile web browser
 */
	function isMobile() {
		return $this->request->is('mobile') || $this->accepts('wap');
	}

/**
 * Returns true if the client accepts WAP content
 *
 * @return bool
 */
	public function isWap() {
		return $this->prefers('wap');
	}

/**
 * Returns true if the current call a POST request
 *
 * @return boolean True if call is a POST
 * @deprecated Use $this->request->is('post'); from your controller.
 */
	public function isPost() {
		return $this->request->is('post');
	}

/**
 * Returns true if the current call a PUT request
 *
 * @return boolean True if call is a PUT
 * @deprecated Use $this->request->is('put'); from your controller.
 */
	public function isPut() {
		return $this->request->is('put');
	}

/**
 * Returns true if the current call a GET request
 *
 * @return boolean True if call is a GET
 * @deprecated Use $this->request->is('get'); from your controller.
 */
	public function isGet() {
		return $this->request->is('get');
	}

/**
 * Returns true if the current call a DELETE request
 *
 * @return boolean True if call is a DELETE
 * @deprecated Use $this->request->is('delete'); from your controller.
 */
	public function isDelete() {
		return $this->request->is('delete');
	}

/**
 * Gets Prototype version if call is Ajax, otherwise empty string.
 * The Prototype library sets a special "Prototype version" HTTP header.
 *
 * @return string Prototype version of component making Ajax call
 */
	public function getAjaxVersion() {
		if (env('HTTP_X_PROTOTYPE_VERSION') != null) {
			return env('HTTP_X_PROTOTYPE_VERSION');
		}
		return false;
	}

/**
 * Adds/sets the Content-type(s) for the given name.  This method allows
 * content-types to be mapped to friendly aliases (or extensions), which allows
 * RequestHandler to automatically respond to requests of that type in the
 * startup method.
 *
 * @param string $name The name of the Content-type, i.e. "html", "xml", "css"
 * @param mixed $type The Content-type or array of Content-types assigned to the name,
 *    i.e. "text/html", or "application/xml"
 * @return void
 */
	public function setContent($name, $type = null) {
		$this->_contentTypeMap[$name] = $type;
	}

/**
 * Gets the server name from which this request was referred
 *
 * @return string Server address
 * @deprecated use $this->request->referer() from your controller instead
 */
	public function getReferer() {
		return $this->request->referer(false);
	}

/**
 * Gets remote client IP
 *
 * @return string Client IP address
 * @deprecated use $this->request->clientIp() from your controller instead.
 */
	public function getClientIP($safe = true) {
		return $this->request->clientIp($safe);
	}

/**
 * Determines which content types the client accepts.  Acceptance is based on
 * the file extension parsed by the Router (if present), and by the HTTP_ACCEPT
 * header. Unlike CakeRequest::accepts() this method deals entirely with mapped content types.
 *
 * Usage:
 *
 * `$this->RequestHandler->accepts(array('xml', 'html', 'json'));`
 *
 * Returns true if the client accepts any of the supplied types.
 *
 * `$this->RequestHandler->accepts('xml');`
 *
 * Returns true if the client accepts xml.
 *
 * @param mixed $type Can be null (or no parameter), a string type name, or an
 *   array of types
 * @return mixed If null or no parameter is passed, returns an array of content
 *   types the client accepts.  If a string is passed, returns true
 *   if the client accepts it.  If an array is passed, returns true
 *   if the client accepts one or more elements in the array.
 * @access public
 * @see RequestHandlerComponent::setContent()
 */
	function accepts($type = null) {
		$accepted = $this->request->accepts();

		if ($type == null) {
			return $this->mapType($accepted);
		} elseif (is_array($type)) {
			foreach ($type as $t) {
				$t = $this->mapAlias($t);
				if (in_array($t, $accepted)) {
					return true;
				}
			}
			return false;
		} elseif (is_string($type)) {
			$type = $this->mapAlias($type);
			return in_array($type, $accepted);
		}
		return false;
	}

/**
 * Determines the content type of the data the client has sent (i.e. in a POST request)
 *
 * @param mixed $type Can be null (or no parameter), a string type name, or an array of types
 * @return mixed If a single type is supplied a boolean will be returned.  If no type is provided
 *   The mapped value of CONTENT_TYPE will be returned. If an array is supplied the first type
 *   in the request content type will be returned.
 */
	public function requestedWith($type = null) {
		if (!$this->request->is('post') && !$this->request->is('put')) {
			return null;
		}
	
		list($contentType) = explode(';', env('CONTENT_TYPE'));
		if ($type == null) {
			return $this->mapType($contentType);
		} elseif (is_array($type)) {
			foreach ($type as $t) {
				if ($this->requestedWith($t)) {
					return $t;
				}
			}
			return false;
		} elseif (is_string($type)) {
			return ($type == $this->mapType($contentType));
		}
	}

/**
 * Determines which content-types the client prefers.  If no parameters are given,
 * the content-type that the client most likely prefers is returned.  If $type is
 * an array, the first item in the array that the client accepts is returned.
 * Preference is determined primarily by the file extension parsed by the Router
 * if provided, and secondarily by the list of content-types provided in
 * HTTP_ACCEPT.
 *
 * @param mixed $type An optional array of 'friendly' content-type names, i.e.
 *   'html', 'xml', 'js', etc.
 * @return mixed If $type is null or not provided, the first content-type in the
 *    list, based on preference, is returned.
 * @access public
 * @see RequestHandlerComponent::setContent()
 */
	function prefers($type = null) {
		$accepts = $this->accepts();

		if ($type == null) {
			if (empty($this->ext)) {
				if (is_array($accepts)) {
					return $accepts[0];
				}
				return $accepts;
			}
			return $this->ext;
		}

		$types = (array)$type;

		if (count($types) === 1) {
			if (!empty($this->ext)) {
				return ($types[0] == $this->ext);
			}
			return ($types[0] == $accepts[0]);
		}

	
		$intersect = array_values(array_intersect($accepts, $types));
		if (empty($intersect)) {
			return false;
		}
		return $intersect[0];
	}

/**
 * Sets the layout and template paths for the content type defined by $type.
 *
 * @param object $controller A reference to a controller object
 * @param string $type Type of response to send (e.g: 'ajax')
 * @return void
 * @access public
 * @see RequestHandlerComponent::setContent()
 * @see RequestHandlerComponent::respondAs()
 */
	function renderAs(&$controller, $type) {
		$options = array('charset' => 'UTF-8');

		if (Configure::read('App.encoding') !== null) {
			$options = array('charset' => Configure::read('App.encoding'));
		}

		if ($type == 'ajax') {
			$controller->layout = $this->ajaxLayout;
			return $this->respondAs('html', $options);
		}
		$controller->ext = '.ctp';

		if (empty($this->__renderType)) {
			$controller->viewPath .= DS . $type;
		} else {
			$remove = preg_replace("/([\/\\\\]{$this->__renderType})$/", DS . $type, $controller->viewPath);
			$controller->viewPath = $remove;
		}
		$this->__renderType = $type;
		$controller->layoutPath = $type;

		if (isset($this->_contentTypeMap[$type])) {
			$this->respondAs($type, $options);
		}

		$helper = ucfirst($type);
		$isAdded = (
			in_array($helper, $controller->helpers) ||
			array_key_exists($helper, $controller->helpers)
		);

		if (!$isAdded) {
			if (App::import('Helper', $helper)) {
				$controller->helpers[] = $helper;
			}
		}
	}

/**
 * Sets the response header based on type map index name.  If DEBUG is greater than 2, the header
 * is not set.
 *
 * @param mixed $type Friendly type name, i.e. 'html' or 'xml', or a full content-type,
 *    like 'application/x-shockwave'.
 * @param array $options If $type is a friendly type name that is associated with
 *    more than one type of content, $index is used to select which content-type to use.
 * @return boolean Returns false if the friendly type name given in $type does
 *    not exist in the type map, or if the Content-type header has
 *    already been set by this method.
 * @access public
 * @see RequestHandlerComponent::setContent()
 */
	function respondAs($type, $options = array()) {
		if (!array_key_exists($type, $this->_contentTypeMap) && strpos($type, '/') === false) {
			return false;
		}
		$defaults = array('index' => 0, 'charset' => null, 'attachment' => false);
		$options = array_merge($defaults, $options);

		if (strpos($type, '/') === false && isset($this->_contentTypeMap[$type])) {
			$cType = null;
			if (is_array($this->_contentTypeMap[$type]) && isset($this->_contentTypeMap[$type][$options['index']])) {
				$cType = $this->_contentTypeMap[$type][$options['index']];
			} elseif (is_array($this->_contentTypeMap[$type]) && isset($this->_contentTypeMap[$type][0])) {
				$cType = $this->_contentTypeMap[$type][0];
			} elseif (isset($this->_contentTypeMap[$type])) {
				$cType = $this->_contentTypeMap[$type];
			} else {
				return false;
			}

			if (is_array($cType)) {
				if ($this->prefers($cType)) {
					$cType = $this->prefers($cType);
				} else {
					$cType = $cType[0];
				}
			}
		} else {
			$cType = $type;
		}

		if ($cType != null) {
			$header = 'Content-type: ' . $cType;

			if (!empty($options['charset'])) {
				$header .= '; charset=' . $options['charset'];
			}
			if (!empty($options['attachment'])) {
				$this->_header("Content-Disposition: attachment; filename=\"{$options['attachment']}\"");
			}
			if (Configure::read('debug') < 2 && !defined('CAKEPHP_SHELL')) {
				$this->_header($header);
			}
			$this->__responseTypeSet = $cType;
			return true;
		}
		return false;
	}

/**
 * Wrapper for header() so calls can be easily tested.
 *
 * @param string $header The header to be sent.
 * @return void
 */
	protected function _header($header) {
		header($header);
	}

/**
 * Returns the current response type (Content-type header), or null if none has been set
 *
 * @return mixed A string content type alias, or raw content type if no alias map exists,
 *    otherwise null
 */
	public function responseType() {
		if ($this->__responseTypeSet == null) {
			return null;
		}
		return $this->mapType($this->__responseTypeSet);
	}

/**
 * Maps a content-type back to an alias
 *
 * @param mixed $type Either a string content type to map, or an array of types.
 * @return mixed Aliases for the types provided.
 */
	public function mapType($ctype) {
		if (is_array($ctype)) {
			return array_map(array($this, 'mapType'), $ctype);
		}
		$keys = array_keys($this->_contentTypeMap);
		$count = count($keys);

		foreach ($this->_contentTypeMap as $alias => $types) {
			if (is_array($types) && in_array($ctype, $types)) {
				return $alias;
			} elseif (is_string($types) && $types == $ctype) {
				return $alias;
			}
		}
		return null;
	}

/**
 * Maps a content type alias back to its mime-type(s)
 *
 * @param mixed $alias String alias to convert back into a content type. Or an array of aliases to map.
 * @return mixed Null on an undefined alias.  String value of the mapped alias type.  If an
 *   alias maps to more than one content type, the first one will be returned.
 */
	public function mapAlias($alias) {
		if (is_array($alias)) {
			return array_map(array($this, 'mapAlias'), $alias);
		}
		if (isset($this->_contentTypeMap[$alias])) {
			$types = $this->_contentTypeMap[$alias];
			if (is_array($types)) {
				return $types[0];
			}
			return $types;
		}
		return null;
	}

}
