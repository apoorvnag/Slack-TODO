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
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs.controller.components
 * @since			CakePHP(tm) v 1.2.0.5435
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('controller' . DS . 'controller', 'controller' . DS . 'components' . DS .'request_handler');
/**
 * Short description for class.
 *
 * @package    cake.tests
 * @subpackage cake.tests.cases.libs.controller.components
 */
class RequestHandlerComponentTest extends CakeTestCase {

	function setUp() {
		$this->RequestHandler = new RequestHandlerComponent();
		$this->Controller = new Controller();
	}

	function testRenderAs() {
		$this->assertFalse(in_array('Xml', $this->Controller->helpers));
		$this->RequestHandler->renderAs($this->Controller, 'xml');
		$this->assertTrue(in_array('Xml', $this->Controller->helpers));
	}

	function testRequestTypes() {
		$this->assertFalse($this->RequestHandler->isFlash());
		$_SERVER['HTTP_USER_AGENT'] = 'Shockwave Flash';
		$this->assertTrue($this->RequestHandler->isFlash());
		unset($_SERVER['HTTP_USER_AGENT']);

		$this->assertFalse($this->RequestHandler->isAjax());
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->assertTrue($this->RequestHandler->isAjax());
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}

	function testClientPreference() {
		$this->assertNotEqual($this->RequestHandler->prefers(), 'rss');
		$this->RequestHandler->ext = 'rss';
		$this->assertEqual($this->RequestHandler->prefers(), 'rss');

		$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
		$_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
		$this->RequestHandler = new RequestHandlerComponent();
		$this->assertEqual($this->RequestHandler->prefers(), 'xml');
		$_SERVER['HTTP_ACCEPT'] = $accept;
	}

	function tearDown() {
		unset($this->RequestHandler);
		unset($this->Controller);
	}
}
?>