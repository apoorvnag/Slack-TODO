<?php
/**
 * Deals with Collections of objects.  Keeping registries of those objects,
 * loading and constructing new objects and triggering callbacks.
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
 * @subpackage    cake.cake.libs.view
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
abstract class ObjectCollection {
/**
 * Lists the currently-attached objects
 *
 * @var array
 * @access private
 */
	protected $_attached = array();

/**
 * Lists the currently-attached objects which are disabled
 *
 * @var array
 * @access private
 */
	protected $_disabled = array();

/**
 * Enables callbacks on a behavior or array of behaviors
 *
 * @param mixed $name CamelCased name of the behavior(s) to enable (string or array)
 * @return void
 */
	public function enable($name) {
		$this->_disabled = array_diff($this->_disabled, (array)$name);
	}

/**
 * Disables callbacks on a object or array of objects.  Public object methods are still
 * callable as normal.
 *
 * @param mixed $name CamelCased name of the objects(s) to disable (string or array)
 * @return void
 */
	public function disable($name) {
		foreach ((array)$name as $object) {
			if (in_array($object, $this->_attached) && !in_array($object, $this->_disabled)) {
				$this->_disabled[] = $object;
			}
		}
	}

/**
 * Gets the list of currently-enabled objects, or, the current status of a single objects
 *
 * @param string $name Optional.  The name of the object to check the status of.  If omitted,
 *   returns an array of currently-enabled object
 * @return mixed If $name is specified, returns the boolean status of the corresponding object.
 *   Otherwise, returns an array of all enabled objects.
 */
	public function enabled($name = null) {
		if (!empty($name)) {
			return (in_array($name, $this->_attached) && !in_array($name, $this->_disabled));
		}
		return array_diff($this->_attached, $this->_disabled);
	}

/**
 * Gets the list of attached behaviors, or, whether the given behavior is attached
 *
 * @param string $name Optional.  The name of the behavior to check the status of.  If omitted,
 *   returns an array of currently-attached behaviors
 * @return mixed If $name is specified, returns the boolean status of the corresponding behavior.
 *    Otherwise, returns an array of all attached behaviors.
 */
	public function attached($name = null) {
		if (!empty($name)) {
			return (in_array($name, $this->_attached));
		}
		return $this->_attached;
	}

}