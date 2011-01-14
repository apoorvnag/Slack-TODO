<?php
/**
 * Helpers collection is used as a registry for loaded helpers and handles loading
 * and constructing helper class objects.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake.libs.view
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Core', 'ObjectCollection');

class HelperCollection extends ObjectCollection {

/**
 * View object to use when making helpers.
 *
 * @var View
 */
	protected $_View;

/**
 * Constructor
 *
 * @return void
 */
	public function __construct(View $view) {
		$this->_View = $view;
	}

/**
 * Loads/constructs a helper.  Will return the instance in the registry if it already exists.
 * By setting `$enable` to false you can disable callbacks for a helper.  Alternatively you 
 * can set `$settings['enabled'] = false` to disable callbacks.  This alias is provided so that when
 * declaring $helpers arrays you can disable callbacks on helpers.
 *
 * You can alias your helper as an existing helper by setting the 'alias' key, i.e.,
 * {{{
 * public $components = array(
 *   'AliasedHtml' => array(
 *     'alias' => 'Html'
 *   );
 * );
 * }}}
 * All calls to the `Html` helper would use `AliasedHtml` instead.
 * 
 * @param string $helper Helper name to load
 * @param array $settings Settings for the helper.
 * @return Helper A helper object, Either the existing loaded helper or a new one.
 * @throws MissingHelperFileException, MissingHelperClassException when the helper could not be found
 */
	public function load($helper, $settings = array()) {
		list($plugin, $name) = pluginSplit($helper, true);

		$alias = $name;
		if (isset($settings['className'])) {
			$name = $settings['className'];
		}
		
		if (isset($this->_loaded[$alias])) {
			return $this->_loaded[$alias];
		}
		$helperClass = $name . 'Helper';
		if (!class_exists($helperClass)) {
			if (!App::import('Helper', $helper)) {
				throw new MissingHelperFileException(array(
					'class' => $helperClass,
					'file' => Inflector::underscore($name) . '.php'
				));
			}
			if (!class_exists($helperClass)) {
				throw new MissingHelperClassException(array(
					'class' => $helperClass,
					'file' => Inflector::underscore($name) . '.php'
				));
			}
		}
		$this->_loaded[$alias] = new $helperClass($this->_View, $settings);

		$vars = array('request', 'theme', 'plugin');
		foreach ($vars as $var) {
			$this->_loaded[$alias]->{$var} = $this->_View->{$var};
		}
		$enable = isset($settings['enabled']) ? $settings['enabled'] : true;
		if ($enable === true) {
			$this->_enabled[] = $alias;
		}
		return $this->_loaded[$alias];
	}

}