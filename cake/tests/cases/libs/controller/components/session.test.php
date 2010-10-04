<?php
/**
 * SessionComponentTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 * @since         CakePHP(tm) v 1.2.0.5436
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Controller', 'Controller', false);
App::import('Component', 'Session');

/**
 * SessionTestController class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class SessionTestController extends Controller {

/**
 * uses property
 *
 * @var array
 * @access public
 */
	public $uses = array();

/**
 * session_id method
 *
 * @return string
 */
	public function session_id() {
		return $this->Session->id();
	}
}

/**
 * OrangeSessionTestController class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class OrangeSessionTestController extends Controller {

/**
 * uses property
 *
 * @var array
 * @access public
 */
	public $uses = array();

/**
 * session_id method
 *
 * @return string
 */
	public function session_id() {
		return $this->Session->id();
	}
}

/**
 * SessionComponentTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.controller.components
 */
class SessionComponentTest extends CakeTestCase {

	protected static $_sessionBackup;

/**
 * fixtures
 *
 * @var string
 */
	public $fixtures = array('core.session');

/**
 * test case startup
 *
 * @return void
 */
	public static function setupBeforeClass() {
		self::$_sessionBackup = Configure::read('Session');
		Configure::write('Session', array(
			'defaults' => 'php',
			'timeout' => 100,
			'cookie' => 'test'
		));
	}

/**
 * cleanup after test case.
 *
 * @return void
 */
	public static function teardownAfterClass() {
		Configure::write('Session', self::$_sessionBackup);
	}

/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function setUp() {
		parent::setUp();
		$_SESSION = null;
		$this->ComponentCollection = new ComponentCollection();
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
		parent::tearDown();
		CakeSession::destroy();
	}

/**
 * ensure that session ids don't change when request action is called.
 *
 * @access public
 * @return void
 */
	function testSessionIdConsistentAcrossRequestAction() {
		$Session = new SessionComponent($this->ComponentCollection);
		$this->assertTrue(isset($_SESSION));

		$Object = new Object();
		$Session = new SessionComponent($this->ComponentCollection);
		$expected = $Session->id();

		$result = $Object->requestAction('/session_test/session_id');
		$this->assertEqual($result, $expected);

		$result = $Object->requestAction('/orange_session_test/session_id');
		$this->assertEqual($result, $expected);
	}

/**
 * testSessionValid method
 *
 * @access public
 * @return void
 */
	function testSessionValid() {
		$Session = new SessionComponent($this->ComponentCollection);

		$this->assertTrue($Session->valid());

		Configure::write('Session.checkAgent', true);
		$Session->userAgent('rweerw');
		$this->assertFalse($Session->valid());

		$Session = new SessionComponent($this->ComponentCollection);
		$Session->time = $Session->read('Config.time') + 1;
		$this->assertFalse($Session->valid());
	}

/**
 * testSessionError method
 *
 * @access public
 * @return void
 */
	function testSessionError() {
		$Session = new SessionComponent($this->ComponentCollection);
		$this->assertFalse($Session->error());
	}

/**
 * testSessionReadWrite method
 *
 * @access public
 * @return void
 */
	function testSessionReadWrite() {
		$Session = new SessionComponent($this->ComponentCollection);

		$this->assertNull($Session->read('Test'));

		$this->assertTrue($Session->write('Test', 'some value'));
		$this->assertEqual($Session->read('Test'), 'some value');
		$this->assertFalse($Session->write('Test.key', 'some value'));
		$Session->delete('Test');

		$this->assertTrue($Session->write('Test.key.path', 'some value'));
		$this->assertEqual($Session->read('Test.key.path'), 'some value');
		$this->assertEqual($Session->read('Test.key'), array('path' => 'some value'));
		$this->assertTrue($Session->write('Test.key.path2', 'another value'));
		$this->assertEqual($Session->read('Test.key'), array('path' => 'some value', 'path2' => 'another value'));
		$Session->delete('Test');

		$array = array('key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3');
		$this->assertTrue($Session->write('Test', $array));
		$this->assertEqual($Session->read('Test'), $array);
		$Session->delete('Test');

		$this->assertFalse($Session->write(array('Test'), 'some value'));
		$this->assertTrue($Session->write(array('Test' => 'some value')));
		$this->assertEqual($Session->read('Test'), 'some value');
		$Session->delete('Test');
	}

/**
 * testSessionDelete method
 *
 * @access public
 * @return void
 */
	function testSessionDelete() {
		$Session = new SessionComponent($this->ComponentCollection);

		$this->assertFalse($Session->delete('Test'));

		$Session->write('Test', 'some value');
		$this->assertTrue($Session->delete('Test'));
	}

/**
 * testSessionCheck method
 *
 * @access public
 * @return void
 */
	function testSessionCheck() {
		$Session = new SessionComponent($this->ComponentCollection);

		$this->assertFalse($Session->check('Test'));

		$Session->write('Test', 'some value');
		$this->assertTrue($Session->check('Test'));
		$Session->delete('Test');
	}

/**
 * testSessionFlash method
 *
 * @access public
 * @return void
 */
	function testSessionFlash() {
		$Session = new SessionComponent($this->ComponentCollection);

		$this->assertNull($Session->read('Message.flash'));

		$Session->setFlash('This is a test message');
		$this->assertEqual($Session->read('Message.flash'), array('message' => 'This is a test message', 'element' => 'default', 'params' => array()));

		$Session->setFlash('This is a test message', 'test', array('name' => 'Joel Moss'));
		$this->assertEqual($Session->read('Message.flash'), array('message' => 'This is a test message', 'element' => 'test', 'params' => array('name' => 'Joel Moss')));

		$Session->setFlash('This is a test message', 'default', array(), 'myFlash');
		$this->assertEqual($Session->read('Message.myFlash'), array('message' => 'This is a test message', 'element' => 'default', 'params' => array()));

		$Session->setFlash('This is a test message', 'non_existing_layout');
		$this->assertEqual($Session->read('Message.myFlash'), array('message' => 'This is a test message', 'element' => 'default', 'params' => array()));

		$Session->delete('Message');
	}

/**
 * testSessionId method
 *
 * @access public
 * @return void
 */
	function testSessionId() {
		unset($_SESSION);
		$Session = new SessionComponent($this->ComponentCollection);
		$this->assertEquals(session_id(), $Session->id());
	}

/**
 * testSessionDestroy method
 *
 * @access public
 * @return void
 */
	function testSessionDestroy() {
		$Session = new SessionComponent($this->ComponentCollection);

		$Session->write('Test', 'some value');
		$this->assertEqual($Session->read('Test'), 'some value');
		$Session->destroy('Test');
		$this->assertNull($Session->read('Test'));
	}

}
