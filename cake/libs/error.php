<?php
/**
 * Error handler
 *
 * Provides Error Capturing for Framework errors.
 *
 * PHP versions 4 and 5
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
 * @since         CakePHP(tm) v 0.10.5.1732
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Error Handler.
 *
 * Captures and handles all cakeError() calls.
 * Displays helpful framework errors when debug > 1.
 * When debug < 1 cakeError() will render 404 or 500 errors.
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class ErrorHandler {

/**
 * Controller instance.
 *
 * @var Controller
 * @access public
 */
	public $controller = null;

/**
 * Class constructor.
 *
 * @param string $method Method producing the error
 * @param array $messages Error messages
 */
	function __construct(Exception $exception) {
		static $__previousError = null;
		App::import('Core', 'Sanitize');
		App::import('Controller', 'CakeError');

		if ($__previousError != $exception) {
			$__previousError = $exception;
			$this->controller = new CakeErrorController();
		} else {
			$this->controller = new Controller();
			$this->controller->viewPath = 'errors';
		}

		if (method_exists($this->controller, 'apperror')) {
			return $this->controller->appError($exception);
		}
		$method = Inflector::variable(str_replace('Exception', '', get_class($exception)));

		if (!in_array($method, get_class_methods($this))) {
			$method = 'error';
		}
		if ($method !== 'error') {
			if (Configure::read('debug') == 0) {
				$parentClass = get_parent_class($this);
				if ($parentClass != 'ErrorHandler') {
					$method = 'error404';
				}
				$parentMethods = get_class_methods($parentClass);
				if (in_array($method, $parentMethods)) {
					$method = 'error404';
				}
				if (isset($code) && $code == 500) {
					$method = 'error500';
				}
			}
		}
		call_user_func_array(array($this, $method), array($exception));
	}

/**
 * Set as the default exception handler by the CakePHP bootstrap process.
 * If you wish you use a custom default exception handler use set_exception_handler()
 * in your app/config/bootstrap.php.
 *
 * @return void
 * @see http://php.net/manual/en/function.set-exception-handler.php
 */
	public static function handleException(Exception $exception) {
		$error = new ErrorHandler($exception);
	}

/**
 * Displays an error page (e.g. 404 Not found).
 *
 * @param array $params Parameters for controller
 */
	public function error($params) {
		extract($params, EXTR_OVERWRITE);
		$this->controller->set(array(
			'code' => $code,
			'name' => $name,
			'message' => $message,
			'title' => $code . ' ' . $name
		));
		$this->_outputMessage('error404');
	}

/**
 * Convenience method to display a 404 page.
 *
 * @param array $params Parameters for controller
 */
	public function error404($exception) {
		if (!isset($url)) {
			$url = $this->controller->here;
		}
		$url = Router::normalize($url);
		$this->controller->response->statusCode(404);
		$this->controller->set(array(
			'code' => '404',
			'name' => __('Not Found'),
			'message' => h($url),
			'base' => $this->controller->request->base
		));
		$this->_outputMessage('error404');
	}

/**
 * Convenience method to display a 500 page.
 *
 * @param array $params Parameters for controller
 */
	public function error500($params) {
		extract($params, EXTR_OVERWRITE);

		if (!isset($url)) {
			$url = $this->controller->request->here;
		}
		$url = Router::normalize($url);
		$this->controller->header("HTTP/1.0 500 Internal Server Error");
		$this->controller->set(array(
			'code' => '500',
			'name' => __('An Internal Error Has Occurred'),
			'message' => h($url),
			'base' => $this->controller->request->base
		));
		$this->_outputMessage('error500');
	}
/**
 * Renders the Missing Controller web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingController($params) {
		extract($params, EXTR_OVERWRITE);

		$controllerName = str_replace('Controller', '', $className);
		$this->controller->set(array(
			'controller' => $className,
			'controllerName' => $controllerName,
			'title' => __('Missing Controller')
		));
		$this->_outputMessage('missingController');
	}

/**
 * Renders the Missing Action web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingAction($params) {
		extract($params, EXTR_OVERWRITE);

		$controllerName = str_replace('Controller', '', $className);
		$this->controller->set(array(
			'controller' => $className,
			'controllerName' => $controllerName,
			'action' => $action,
			'title' => __('Missing Method in Controller')
		));
		$this->_outputMessage('missingAction');
	}

/**
 * Renders the Private Action web page.
 *
 * @param array $params Parameters for controller
 */
	public function privateAction($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'controller' => $className,
			'action' => $action,
			'title' => __('Trying to access private method in class')
		));
		$this->_outputMessage('privateAction');
	}

/**
 * Renders the Missing Table web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingTable($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->header("HTTP/1.0 500 Internal Server Error");
		$this->controller->set(array(
			'code' => '500',
			'model' => $className,
			'table' => $table,
			'title' => __('Missing Database Table')
		));
		$this->_outputMessage('missingTable');
	}

/**
 * Renders the Missing Database web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingDatabase($params = array()) {
		$this->controller->header("HTTP/1.0 500 Internal Server Error");
		$this->controller->set(array(
			'code' => '500',
			'title' => __('Scaffold Missing Database Connection')
		));
		$this->_outputMessage('missingScaffolddb');
	}

/**
 * Renders the Missing View web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingView($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'controller' => $className,
			'action' => $action,
			'file' => $file,
			'title' => __('Missing View')
		));
		$this->_outputMessage('missingView');
	}

/**
 * Renders the Missing Layout web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingLayout($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->layout = 'default';
		$this->controller->set(array(
			'file' => $file,
			'title' => __('Missing Layout')
		));
		$this->_outputMessage('missingLayout');
	}

/**
 * Renders the Database Connection web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingConnection($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->header("HTTP/1.0 500 Internal Server Error");
		$this->controller->set(array(
			'code' => '500',
			'model' => $className,
			'title' => __('Missing Database Connection')
		));
		$this->_outputMessage('missingConnection');
	}

/**
 * Renders the Missing Helper file web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingHelperFile($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'helperClass' => Inflector::camelize($helper) . "Helper",
			'file' => $file,
			'title' => __('Missing Helper File')
		));
		$this->_outputMessage('missingHelperFile');
	}

/**
 * Renders the Missing Helper class web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingHelperClass($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'helperClass' => Inflector::camelize($helper) . "Helper",
			'file' => $file,
			'title' => __('Missing Helper Class')
		));
		$this->_outputMessage('missingHelperClass');
	}

/**
 * Renders the Missing Behavior file web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingBehaviorFile($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'behaviorClass' => Inflector::camelize($behavior) . "Behavior",
			'file' => $file,
			'title' => __('Missing Behavior File')
		));
		$this->_outputMessage('missingBehaviorFile');
	}

/**
 * Renders the Missing Behavior class web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingBehaviorClass($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'behaviorClass' => Inflector::camelize($behavior) . "Behavior",
			'file' => $file,
			'title' => __('Missing Behavior Class')
		));
		$this->_outputMessage('missingBehaviorClass');
	}

/**
 * Renders the Missing Component file web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingComponentFile($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'controller' => $className,
			'component' => $component,
			'file' => $file,
			'title' => __('Missing Component File')
		));
		$this->_outputMessage('missingComponentFile');
	}

/**
 * Renders the Missing Component class web page.
 *
 * @param array $params Parameters for controller
 */
	public function missingComponentClass($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'controller' => $className,
			'component' => $component,
			'file' => $file,
			'title' => __('Missing Component Class')
		));
		$this->_outputMessage('missingComponentClass');
	}

/**
 * Renders the Missing Model class web page.
 *
 * @param unknown_type $params Parameters for controller
 */
	public function missingModel($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'model' => $className,
			'title' => __('Missing Model')
		));
		$this->_outputMessage('missingModel');
	}

/**
 * Output message
 *
 */
	protected function _outputMessage($template) {
		$this->controller->render($template);
		$this->controller->afterFilter();
		$this->controller->response->send();
	}
}
