<?php
/**
 * Error Handling Controller
 *
 * Controller used by ErrorHandler to render error views.
 *
 * @package       Cake.Controller
 */
class CakeErrorController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'CakeError';

/**
 * Uses Property
 *
 * @var array
 */
	public $uses = array();

/**
 * __construct
 *
 * @param CakeRequest $request
 * @param CakeResponse $response
 * @return void
 */
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		$this->constructClasses();
		$this->Components->trigger('initialize', array(&$this));
		$this->_set(array('cacheAction' => false, 'viewPath' => 'Errors'));
	}

/**
 * Escapes the viewVars.
 *
 * @return void
 */
	public function beforeRender() {
		parent::beforeRender();
		foreach ($this->viewVars as $key => $value) {
			if (!is_object($value)){
				$this->viewVars[$key] = h($value);
			}
		}
	}
}
