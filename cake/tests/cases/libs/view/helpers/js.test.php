<?php
/* SVN FILE: $Id$ */
/**
 * JsHelper Test Case
 *
 * TestCase for the JsHelper
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package       cake.tests
 * @subpackage    cake.tests.cases.libs.view.helpers
 * @since         CakePHP(tm) v 1.2.0.4206
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
App::import('Helper', 'Js');
App::import('Core', array('View', 'ClassRegistry'));

Mock::generate('Helper', 'TestJsEngineHelper', array('methodOne'));
Mock::generate('View', 'JsHelperView');

/**
 * JsHelper TestCase.
 *
 * @package       cake.tests
 * @subpackage    cake.tests.cases.libs.view.helpers
 */
class JsHelperTestCase extends CakeTestCase {
/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->Js = new JsHelper('JsBase');
		$this->Js->JsBaseEngine = new JsBaseEngineHelper();
	}
/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function endTest() {
		ClassRegistry::removeObject('view');
		unset($this->Js);
	}
/**
 * test object construction
 *
 * @return void
 **/
	function testConstruction() {
		$js = new JsHelper();
		$this->assertEqual($js->helpers, array('jqueryEngine')); 
		
		$js = new JsHelper(array('mootools'));
		$this->assertEqual($js->helpers, array('mootoolsEngine')); 
		
		$js = new JsHelper('prototype');
		$this->assertEqual($js->helpers, array('prototypeEngine'));
		
		$js = new JsHelper('MyPlugin.Dojo');
		$this->assertEqual($js->helpers, array('MyPlugin.DojoEngine'));
	}
/**
 * test that methods dispatch internally and to the engine class
 *
 * @return void
 **/
	function testMethodDispatching() {
		$js = new JsHelper(array('TestJs'));
		$js->TestJsEngine = new TestJsEngineHelper();
		$js->TestJsEngine->expectOnce('dispatchMethod', array('methodOne', array()));

		$js->methodOne();

		$js->TestEngine = new StdClass();
		$this->expectError();
		$js->someMethodThatSurelyDoesntExist();
	}
/**
 * test script tag generation
 *
 * @return void
 **/
	function testUses() {
		$result = $this->Js->uses('foo');
		$expected = array(
			'script' => array('type' => 'text/javascript', 'src' => 'js/foo.js')
		);
		$this->assertTags($result, $expected);
		
		$result = $this->Js->uses(array('foobar', 'bar'));
		$expected = array(
			array('script' => array('type' => 'text/javascript', 'src' => 'js/foobar.js')),
			'/script',
			array('script' => array('type' => 'text/javascript', 'src' => 'js/bar.js')),
			'/script',
		);
		$this->assertTags($result, $expected);

		$result = $this->Js->uses('jquery-1.3');
		$expected = array(
			'script' => array('type' => 'text/javascript', 'src' => 'js/jquery-1.3.js')
		);
		$this->assertTags($result, $expected);

		$result = $this->Js->uses('/plugin/js/jquery-1.3.2');
		$expected = array(
			'script' => array('type' => 'text/javascript', 'src' => '/plugin/js/jquery-1.3.2.js')
		);
		$this->assertTags($result, $expected);

		$result = $this->Js->uses('scriptaculous.js?load=effects');
		$expected = array(
			'script' => array('type' => 'text/javascript', 'src' => 'js/scriptaculous.js?load=effects')
		);
		$this->assertTags($result, $expected);
		
		$view = new JsHelperView();
		ClassRegistry::addObject('view', $view);

		$view->expectOnce('addScript');
		$result = $this->Js->uses('test', false);
		$this->assertNull($result);
	}
/**
 * test Min/pack version autofinding
 *
 * @return void
 **/
	function testMinPackAutoUse() {
		if ($this->skipIf(!is_writable(JS), 'webroot/js is not Writable, min/pack js testing is skipped')) {
			return;
		}
		Configure::write('debug', 0);
		touch(WWW_ROOT . 'js' . DS. '__cake_js_min_test.min.js');
		touch(WWW_ROOT . 'js' . DS. '__cake_js_pack_test.pack.js');

		$result = $this->Js->uses('__cake_js_min_test');
		$this->assertPattern('/__cake_js_min_test\.min\.js/', $result);

		$result = $this->Js->uses('__cake_js_pack_test');
		$this->assertPattern('/__cake_js_pack_test\.pack\.js/', $result);

		Configure::write('debug', 2);
		$result = $this->Js->uses('__cake_js_pack_test');
		$this->assertNoPattern('/pack\.js/', $result);

		unlink(WWW_ROOT . 'js' . DS. '__cake_js_min_test.min.js');
		unlink(WWW_ROOT . 'js' . DS. '__cake_js_pack_test.pack.js');
	}
/**
 * test timestamp enforcement
 *
 * @return void
 **/
	function testAssetTimestamping() {
		if ($this->skipIf(!is_writable(JS), 'webroot/js is not Writable, timestamp testing has been skipped')) {
			return;
		}

		Configure::write('Asset.timestamp', true);
		touch(WWW_ROOT . 'js' . DS. '__cake_js_test.js');
		$timestamp = substr(strtotime('now'), 0, 8);

		$result = $this->Js->uses('__cake_js_test', true, false);
		$this->assertPattern('/__cake_js_test.js\?' . $timestamp . '[0-9]{2}"/', $result, 'Timestamp value not found %s');

		Configure::write('debug', 0);
		$result = $this->Js->uses('__cake_js_test', true, false);
		$this->assertPattern('/__cake_js_test.js"/', $result);

		Configure::write('Asset.timestamp', 'force');
		$result = $this->Js->uses('__cake_js_test', true, false);
		$this->assertPattern('/__cake_js_test.js\?' . $timestamp . '[0-9]{2}"/', $result, 'Timestamp value not found %s');

		unlink(WWW_ROOT . 'js' . DS. '__cake_js_test.js');
		Configure::write('debug', 2);
	}
/**
 * test that scripts added with uses() are only ever included once.
 *
 * @return void
 **/
	function testUniqueScriptInsertion() {
		$result = $this->Js->uses('foo');
		$this->assertNotNull($result);
		
		$result = $this->Js->uses('foo');
		$this->assertNull($result, 'Script returned upon duplicate inclusion %s');
	}
}



/**
 * JsBaseEngine Class Test case
 *
 * @package cake.tests.view.helpers
 **/
class JsBaseEngineTestCase extends CakeTestCase {
/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->JsEngine = new JsBaseEngineHelper();
	}
/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function endTest() {
		ClassRegistry::removeObject('view');
		unset($this->JsEngine);
	}
/**
 * test escape string skills
 *
 * @return void
 **/
	function testEscaping() {
		$result = $this->JsEngine->escape('');
		$expected = '';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->escape('CakePHP' . "\n" . 'Rapid Development Framework');
		$expected = 'CakePHP\\nRapid Development Framework';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->escape('CakePHP' . "\r\n" . 'Rapid Development Framework' . "\r" . 'For PHP');
		$expected = 'CakePHP\\nRapid Development Framework\\nFor PHP';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->escape('CakePHP: "Rapid Development Framework"');
		$expected = 'CakePHP: \\"Rapid Development Framework\\"';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->escape('CakePHP: \'Rapid Development Framework\'');
		$expected = 'CakePHP: \\\'Rapid Development Framework\\\'';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->escape('my \\"string\\"');
		$expected = 'my \\\"string\\\"';
		$this->assertEqual($result, $expected);
	}
/**
 * test prompt() creation
 *
 * @return void
 **/
	function testPrompt() {
		$result = $this->JsEngine->prompt('Hey, hey you', 'hi!');
		$expected = 'prompt("Hey, hey you", "hi!");';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->prompt('"Hey"', '"hi"');
		$expected = 'prompt("\"Hey\"", "\"hi\"");';
		$this->assertEqual($result, $expected);
	}
/**
 * test alert generation
 *
 * @return void
 **/
	function testAlert() {
		$result = $this->JsEngine->alert('Hey there');
		$expected = 'alert("Hey there");';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->alert('"Hey"');
		$expected = 'alert("\"Hey\"");';
		$this->assertEqual($result, $expected);	
	}
/**
 * test confirm generation
 *
 * @return void
 **/
	function testConfirm() {
		$result = $this->JsEngine->confirm('Are you sure?');
		$expected = 'confirm("Are you sure?");';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->confirm('"Are you sure?"');
		$expected = 'confirm("\"Are you sure?\"");';
		$this->assertEqual($result, $expected);	
	}
/**
 * test Redirect 
 *
 * @return void
 **/
	function testRedirect() {
		$result = $this->JsEngine->redirect(array('controller' => 'posts', 'action' => 'view', 1));
		$expected = 'window.location = "/posts/view/1";';
		$this->assertEqual($result, $expected);
	}
/**
 * testObject encoding with non-native methods.
 *
 * @return void
 **/
	function testObject() {
		$this->JsEngine->useNative = false;

		$object = array('title' => 'New thing', 'indexes' => array(5, 6, 7, 8));
		$result = $this->JsEngine->object($object);
		$expected = '{"title":"New thing","indexes":[5,6,7,8]}';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->object(array('default' => 0));
		$expected = '{"default":0}';
		$this->assertEqual($result, $expected);

		$result = $this->JsEngine->object(array(
			'2007' => array(
				'Spring' => array('1' => array('id' => 1, 'name' => 'Josh'), '2' => array('id' => 2, 'name' => 'Becky')),
				'Fall' => array('1' => array('id' => 1, 'name' => 'Josh'), '2' => array('id' => 2, 'name' => 'Becky'))
			), '2006' => array(
				'Spring' => array('1' => array('id' => 1, 'name' => 'Josh'), '2' => array('id' => 2, 'name' => 'Becky')),
				'Fall' => array('1' => array('id' => 1, 'name' => 'Josh'), '2' => array('id' => 2, 'name' => 'Becky')
			))
		));
		$expected = '{"2007":{"Spring":{"1":{"id":1,"name":"Josh"},"2":{"id":2,"name":"Becky"}},"Fall":{"1":{"id":1,"name":"Josh"},"2":{"id":2,"name":"Becky"}}},"2006":{"Spring":{"1":{"id":1,"name":"Josh"},"2":{"id":2,"name":"Becky"}},"Fall":{"1":{"id":1,"name":"Josh"},"2":{"id":2,"name":"Becky"}}}}';
		$this->assertEqual($result, $expected);
	}
}

?>