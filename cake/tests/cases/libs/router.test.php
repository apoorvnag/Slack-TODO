<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
	require_once LIBS.'router.php';
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs
 */
class RouterTest extends UnitTestCase {

	function testReturnedInstanceReference() {
		$router =& Router::getInstance();
		$router->testVar = 'test';
		$this->assertIdentical($router, Router::getInstance());
		unset($router->testVar);
	}

	function testRouteWriting() {
		$router =& Router::getInstance();

		$router->routes = array();
		$router->connect('/');
		$this->assertEqual($router->routes[0][0], '/');
		$this->assertEqual($router->routes[0][1], '/^[\/]*$/');
		$this->assertEqual($router->routes[0][2], array());

		$router->routes = array();
		$router->connect('/', array('controller' => 'testing'));
		$this->assertTrue(is_array($router->routes[0][3]) && !empty($router->routes[0][3]));
		$this->assertEqual($router->routes[0][3]['controller'], 'testing');
		$this->assertEqual($router->routes[0][3]['action'], 'index');
		$this->assertEqual(count($router->routes[0][3]), 3);

		$router->routes = array();
		$router->connect('/:controller', array('controller' => 'testing2'));
		$this->assertTrue(is_array($router->routes[0][3]) && !empty($router->routes[0][3]), '/');
		$this->assertEqual($router->routes[0][3]['controller'], 'testing2');
		$this->assertEqual($router->routes[0][3]['action'], 'index');
		$this->assertEqual(count($router->routes[0][3]), 3);

		$router->routes = array();
		$router->connect('/:controller/:action', array('controller' => 'testing3'));
		$this->assertEqual($router->routes[0][0], '/:controller/:action');
		$this->assertEqual($router->routes[0][1], '#^(?:\/([^\/]+))?(?:\/([^\/]+))?[\/]*$#');
		$this->assertEqual($router->routes[0][2], array('controller', 'action'));
		$this->assertEqual($router->routes[0][3], array('controller' => 'testing3', 'action' => 'index', 'plugin' => null));

		$router->routes = array();
		$router->connect('/:controller/:action/:id', array('controller' => 'testing4', 'id' => null), array('id' => $router->__named['ID']));
		$this->assertEqual($router->routes[0][0], '/:controller/:action/:id');
		$this->assertEqual($router->routes[0][1], '#^(?:\/([^\/]+))?(?:\/([^\/]+))?(?:\/([0-9]+))?[\/]*$#');
		$this->assertEqual($router->routes[0][2], array('controller', 'action', 'id'));

		$router->routes = array();
		$router->connect('/:controller/:action/:id', array('controller' => 'testing4'), array('id' => $router->__named['ID']));
		$this->assertEqual($router->routes[0][1], '#^(?:\/([^\/]+))?(?:\/([^\/]+))?(?:\/([0-9]+))[\/]*$#');
	}

	function testRouterIdentity() {
		$router =& Router::getInstance();
		$this->vars = get_object_vars($router);

		$router->routes = $router->__paths = $router->__params = $router->__currentRoute = array();
		$router->__parseExtensions = false;
		$router2 = new Router();
		$this->assertEqual(get_object_vars($router), get_object_vars($router2));
	}

	function testUrlGeneration() {
		$router =& Router::getInstance();
		foreach ($this->vars as $var => $val) {
			$router->{$var} = $val;
		}
		$router->routes = array();

		$router->connect('/', array('controller'=>'pages', 'action'=>'display', 'home'));
		$out = $router->url(array('controller'=>'pages', 'action'=>'display', 'home'));
		$this->assertEqual($out, '/');

		$router->connect('/pages/*', array('controller'=>'pages', 'action'=>'display'));
		$out = $router->url(array('controller'=>'pages', 'action'=>'display', 'about'));
		$expected = '/pages/about';
		$this->assertEqual($out, $expected);


		$router->connect('/:plugin/:controller/*', array('plugin'=>'cake_plugin', 'controller'=>'posts', 'action'=>'view', '1'));
		$out = $router->url(array('plugin'=>'cake_plugin', 'controller'=>'posts', '1'));
		$expected = '/cake_plugin/posts/';
		$this->assertEqual($out, $expected);

		$router->connect('/:controller/:action/:id', array(), array('id' => '1'));
		$out = $router->url(array('controller'=>'posts', 'action'=>'view', '1'));
		$expected = '/posts/view/1';
		$this->assertEqual($out, $expected);

		$router->connect('/:controller/:id', array('action' => 'view'), array('id' => '1'));
		$out = $router->url(array('controller'=>'posts', '1'));
		$expected = '/posts/1';
		$this->assertEqual($out, $expected);

		$out = $router->url(array('controller' => 'posts', 'action'=>'index', '0'));
		$expected = '/posts/index/0';
		$this->assertEqual($out, $expected);

	}

	function testExtensionParsingSetting() {
		if (PHP5) {
			$router = Router::getInstance();
			$router->reload();
			$this->assertFalse($router->__parseExtensions);

			$router->parseExtensions();
			$this->assertTrue($router->__parseExtensions);
		}
	}
}
?>