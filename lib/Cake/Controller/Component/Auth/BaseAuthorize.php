<?php
/**
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
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Abstract base authorization adapter for AuthComponent.
 *
 * @package cake.libs.controller.components.auth
 * @since 2.0
 * @see AuthComponent::$authenticate
 */
abstract class BaseAuthorize {
/**
 * Controller for the request.
 *
 * @var Controller
 */
	protected $_Controller = null;

/**
 * Component collection instance for getting more components.
 *
 * @var ComponentCollection
 */
	protected $_Collection;
	
/**
 * Settings for authorize objects.
 *
 * - `actionPath` - The path to ACO nodes that contains the nodes for controllers.  Used as a prefix
 *    when calling $this->action();
 * - `actionMap` - Action -> crud mappings. Used by authorization objects that want to map actions to CRUD roles.
 *
 * @var array
 */
	public $settings = array(
		'actionPath' => null,
		'actionMap' => array(
			'index' => 'read',
			'add' => 'create',
			'edit' => 'update',
			'view' => 'read',
			'delete' => 'delete',
			'remove' => 'delete'
		)
	);

/**
 * Constructor
 *
 * @param Controller $controller The controller for this request.
 * @param string $settings An array of settings.  This class does not use any settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_Collection = $collection;
		$controller = $collection->getController();
		$this->controller($controller);
		$this->settings = Set::merge($this->settings, $settings);
	}

/**
 * Checks user authorization.
 *
 * @param array $user Active user data
 * @param CakeRequest $request 
 * @return boolean
 */
	abstract public function authorize($user, CakeRequest $request);

/**
 * Accessor to the controller object.
 *
 * @param mixed $controller null to get, a controller to set.
 * @return mixed.
 */
	public function controller($controller = null) {
		if ($controller) {
			if (!$controller instanceof Controller) {
				throw new CakeException(__d('cake', '$controller needs to be an instance of Controller'));
			}
			$this->_Controller = $controller;
			return true;
		}
		return $this->_Controller;
	}

/**
 * Get the action path for a given request.  Primarily used by authorize objects
 * that need to get information about the plugin, controller, and action being invoked.
 *
 * @param CakeRequest $request The request a path is needed for.
 * @return string the action path for the given request.
 */
	public function action($request, $path = '/:plugin/:controller/:action') {
		$plugin = empty($request['plugin']) ? null : Inflector::camelize($request['plugin']) . '/';
		return str_replace(
			array(':controller', ':action', ':plugin/'),
			array(Inflector::camelize($request['controller']), $request['action'], $plugin),
			$this->settings['actionPath'] . $path
		);
	}

/**
 * Maps crud actions to actual controller names.  Used to modify or get the current mapped actions.
 *
 * @param mixed $map Either an array of mappings, or undefined to get current values.
 * @return mixed Either the current mappings or null when setting.
 */
	public function mapActions($map = array()) {
		if (empty($map)) {
			return $this->settings['actionMap'];
		}
		$crud = array('create', 'read', 'update', 'delete');
		foreach ($map as $action => $type) {
			if (in_array($action, $crud) && is_array($type)) {
				foreach ($type as $typedAction) {
					$this->settings['actionMap'][$typedAction] = $action;
				}
			} else {
				$this->settings['actionMap'][$action] = $type;
			}
		}
	}
}