<?php
/* SVN FILE: $Id$ */

/**
 * ConfigureTest file
 *
 * Holds several tests
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
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 * @since         CakePHP(tm) v 1.2.0.5432
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
App::import('Core', 'Configure');

/**
 * ConfigureTest
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 */
class ConfigureTest extends CakeTestCase {

/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function setUp() {
		$this->_cacheDisable = Configure::read('Cache.disable');
		$this->_debug = Configure::read('debug');

		Configure::write('Cache.disable', true);
	}

/**
 * endTest
 *
 * @access public
 * @return void
 */
	function endTest() {
		App::build();
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_core_paths')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_core_paths');
		}
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_dir_map')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_dir_map');
		}
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_file_map')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_file_map');
		}
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_object_map')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'cake_core_object_map');
		}
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'test.config.php')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'test.config.php');
		}
		if (file_exists(TMP . 'cache' . DS . 'persistent' . DS . 'test.php')) {
			unlink(TMP . 'cache' . DS . 'persistent' . DS . 'test.php');
		}
		Configure::write('debug', $this->_debug);
		Configure::write('Cache.disable', $this->_cacheDisable);
	}

/**
 * testRead method
 *
 * @access public
 * @return void
 */
	function testRead() {
		$expected = 'ok';
		Configure::write('level1.level2.level3_1', $expected);
		Configure::write('level1.level2.level3_2', 'something_else');
		$result = Configure::read('level1.level2.level3_1');
		$this->assertEqual($expected, $result);

		$result = Configure::read('level1.level2.level3_2');
		$this->assertEqual($result, 'something_else');

		$result = Configure::read('debug');
		$this->assertTrue($result >= 0);
	}

/**
 * testWrite method
 *
 * @access public
 * @return void
 */
	function testWrite() {
		Configure::write('SomeName.someKey', 'myvalue');
		$result = Configure::read('SomeName.someKey');
		$this->assertEqual($result, 'myvalue');

		Configure::write('SomeName.someKey', null);
		$result = Configure::read('SomeName.someKey');
		$this->assertEqual($result, null);

		$expected = array('One' => array('Two' => array('Three' => array('Four' => array('Five' => 'cool')))));
		Configure::write('Key', $expected);

		$result = Configure::read('Key');
		$this->assertEqual($expected, $result);

		$result = Configure::read('Key.One');
		$this->assertEqual($expected['One'], $result);

		$result = Configure::read('Key.One.Two');
		$this->assertEqual($expected['One']['Two'], $result);

		$result = Configure::read('Key.One.Two.Three.Four.Five');
		$this->assertEqual('cool', $result);
	}

/**
 * testSetErrorReporting Level
 *
 * @return void
 **/
	function testSetErrorReportingLevel() {
		Configure::write('log', false);

		Configure::write('debug', 0);
		$result = ini_get('error_reporting');
		$this->assertEqual($result, 0);

		Configure::write('debug', 2);
		$result = ini_get('error_reporting');
		$this->assertEqual($result, E_ALL & ~E_DEPRECATED);

		$result = ini_get('display_errors');
		$this->assertEqual($result, 1);

		Configure::write('debug', 0);
		$result = ini_get('error_reporting');
		$this->assertEqual($result, 0);
	}

/**
 * test that log and debug configure values interact well.
 *
 * @return void
 **/
	function testInteractionOfDebugAndLog() {
		Configure::write('log', false);

		Configure::write('debug', 0);
		$this->assertEqual(ini_get('error_reporting'), 0);
		$this->assertEqual(ini_get('display_errors'), 0);

		Configure::write('log', E_WARNING);
		Configure::write('debug', 0);
		$this->assertEqual(ini_get('error_reporting'), E_WARNING);
		$this->assertEqual(ini_get('display_errors'), 0);

		Configure::write('debug', 2);
		$this->assertEqual(ini_get('error_reporting'), E_ALL & ~E_DEPRECATED);
		$this->assertEqual(ini_get('display_errors'), 1);
	}

/**
 * testDelete method
 *
 * @access public
 * @return void
 */
	function testDelete() {
		Configure::write('SomeName.someKey', 'myvalue');
		$result = Configure::read('SomeName.someKey');
		$this->assertEqual($result, 'myvalue');

		Configure::delete('SomeName.someKey');
		$result = Configure::read('SomeName.someKey');
		$this->assertTrue($result === null);

		Configure::write('SomeName', array('someKey' => 'myvalue', 'otherKey' => 'otherValue'));

		$result = Configure::read('SomeName.someKey');
		$this->assertEqual($result, 'myvalue');

		$result = Configure::read('SomeName.otherKey');
		$this->assertEqual($result, 'otherValue');

		Configure::delete('SomeName');

		$result = Configure::read('SomeName.someKey');
		$this->assertTrue($result === null);

		$result = Configure::read('SomeName.otherKey');
		$this->assertTrue($result === null);
	}

/**
 * testLoad method
 *
 * @access public
 * @return void
 */
	function testLoad() {
		$result = Configure::load('non_existing_configuration_file');
		$this->assertFalse($result);

		$result = Configure::load('config');
		$this->assertTrue($result === null);
	}

/**
 * testStore method
 *
 * @access public
 * @return void
 */
	function testStoreAndLoad() {
		Configure::write('Cache.disable', false);

		$expected = array('data' => 'value');
		Configure::store('SomeExample', 'test', $expected);

		Configure::load('test');
		$config = Configure::read('SomeExample');
		$this->assertEqual($config, $expected);

		$expected = array('data' => array('first' => 'value', 'second' => 'value2'));
		Configure::store('AnotherExample', 'test.config', $expected);

		Configure::load('test.config');
		$config = Configure::read('AnotherExample');
		$this->assertEqual($config, $expected);
	}

/**
 * testVersion method
 *
 * @access public
 * @return void
 */
	function testVersion() {
		$result = Configure::version();
		$this->assertTrue(version_compare($result, '1.2', '>='));
	}
}

/**
 * AppImportTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 */
class AppImportTest extends UnitTestCase {

/**
 * testBuild method
 *
 * @access public
 * @return void
 */
	function testBuild() {
		$old = App::path('models');
		$expected = array(
			APP . 'models' . DS,
			APP,
			ROOT . DS . LIBS . 'model' . DS
		);
		$this->assertEqual($expected, $old);

		App::build(array('models' => array('/path/to/models/')));

		$new = App::path('models');

		$expected = array(
			APP . 'models' . DS,
			'/path/to/models/',
			APP,
			ROOT . DS . LIBS . 'model' . DS
		);
		$this->assertEqual($expected, $new);

		App::build(); //reset defaults
		$defaults = App::path('models');
		$this->assertEqual($old, $defaults);
	}

/**
 * testBuildWithReset method
 *
 * @access public
 * @return void
 */
	function testBuildWithReset() {
		$old = App::path('models');
		$expected = array(
			APP . 'models' . DS,
			APP,
			ROOT . DS . LIBS . 'model' . DS
		);
		$this->assertEqual($expected, $old);

		App::build(array('models' => array('/path/to/models/')), true);

		$new = App::path('models');

		$expected = array(
			'/path/to/models/'
		);
		$this->assertEqual($expected, $new);

		App::build(); //reset defaults
		$defaults = App::path('models');
		$this->assertEqual($old, $defaults);
	}

/**
 * testCore method
 *
 * @access public
 * @return void
 */
	function testCore() {
		$model = App::core('models');
		$this->assertEqual(array(ROOT . DS . LIBS . 'model' . DS), $model);

		$view = App::core('views');
		$this->assertEqual(array(ROOT . DS . LIBS . 'view' . DS), $view);

		$controller = App::core('controllers');
		$this->assertEqual(array(ROOT . DS . LIBS . 'controller' . DS), $controller);

	}

/**
 * testListObjects method
 *
 * @access public
 * @return void
 */
	function testListObjects() {
		$result = App::objects('class', TEST_CAKE_CORE_INCLUDE_PATH . 'libs');
		$this->assertTrue(in_array('Xml', $result));
		$this->assertTrue(in_array('Cache', $result));
		$this->assertTrue(in_array('HttpSocket', $result));

		$result = App::objects('behavior');
		$this->assertTrue(in_array('Tree', $result));

		$result = App::objects('controller');
		$this->assertTrue(in_array('Pages', $result));

		$result = App::objects('component');
		$this->assertTrue(in_array('Auth', $result));

		$result = App::objects('view');
		$this->assertTrue(in_array('Media', $result));

		$result = App::objects('helper');
		$this->assertTrue(in_array('Html', $result));

		$result = App::objects('model');
		$notExpected = array('AppModel', 'ModelBehavior', 'ConnectionManager',  'DbAcl', 'Model', 'CakeSchema');
		foreach ($notExpected as $class) {
			$this->assertFalse(in_array($class, $result));
		}

		$result = App::objects('file');
		$this->assertFalse($result);

		$result = App::objects('file', 'non_existing_configure');
		$expected = array();
		$this->assertEqual($result, $expected);

		$result = App::objects('NonExistingType');
		$this->assertFalse($result);
	}

/**
 * testClassLoading method
 *
 * @access public
 * @return void
 */
	function testClassLoading() {
		$file = App::import();
		$this->assertTrue($file);

		$file = App::import('Model', 'Model', false);
		$this->assertTrue($file);
		$this->assertTrue(class_exists('Model'));

		$file = App::import('Controller', 'Controller', false);
		$this->assertTrue($file);
		$this->assertTrue(class_exists('Controller'));

		$file = App::import('Component', 'Component', false);
		$this->assertTrue($file);
		$this->assertTrue(class_exists('Component'));

		$file = App::import('Shell', 'Shell', false);
		$this->assertTrue($file);
		$this->assertTrue(class_exists('Shell'));

		$file = App::import('Model', 'SomeRandomModelThatDoesNotExist', false);
		$this->assertFalse($file);

		$file = App::import('Model', 'AppModel', false);
		$this->assertTrue($file);
		$this->assertTrue(class_exists('AppModel'));

		$file = App::import('WrongType', null, true, array(), '');
		$this->assertTrue($file);

		$file = App::import('Model', 'NonExistingPlugin.NonExistingModel', false);
		$this->assertFalse($file);

		$file = App::import('Core', 'NonExistingPlugin.NonExistingModel', false);
		$this->assertFalse($file);

		$file = App::import('Model', array('NonExistingPlugin.NonExistingModel'), false);
		$this->assertFalse($file);

		$file = App::import('Core', array('NonExistingPlugin.NonExistingModel'), false);
		$this->assertFalse($file);

		$file = App::import('Core', array('NonExistingPlugin.NonExistingModel.AnotherChild'), false);
		$this->assertFalse($file);

		if (!class_exists('AppController')) {
			$classes = array_flip(get_declared_classes());

			if (PHP5) {
				$this->assertFalse(isset($classes['PagesController']));
				$this->assertFalse(isset($classes['AppController']));
			} else {
				$this->assertFalse(isset($classes['pagescontroller']));
				$this->assertFalse(isset($classes['appcontroller']));
			}

			$file = App::import('Controller', 'Pages');
			$this->assertTrue($file);
			$this->assertTrue(class_exists('PagesController'));

			$classes = array_flip(get_declared_classes());

			if (PHP5) {
				$this->assertTrue(isset($classes['PagesController']));
				$this->assertTrue(isset($classes['AppController']));
			} else {
				$this->assertTrue(isset($classes['pagescontroller']));
				$this->assertTrue(isset($classes['appcontroller']));
			}

			$file = App::import('Behavior', 'Containable');
			$this->assertTrue($file);
			$this->assertTrue(class_exists('ContainableBehavior'));

			$file = App::import('Component', 'RequestHandler');
			$this->assertTrue($file);
			$this->assertTrue(class_exists('RequestHandlerComponent'));

			$file = App::import('Helper', 'Form');
			$this->assertTrue($file);
			$this->assertTrue(class_exists('FormHelper'));

			$file = App::import('Model', 'NonExistingModel');
			$this->assertFalse($file);

			$file = App::import('Datasource', 'DboSource');
			$this->assertTrue($file);
			$this->assertTrue(class_exists('DboSource'));
		}

		App::build(array(
			'libs' => array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'libs' . DS),
			'plugins' => array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS)
		));

		$result = App::import('Controller', 'TestPlugin.Tests');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('TestPluginAppController'));
		$this->assertTrue(class_exists('TestsController'));

		$result = App::import('Lib', 'TestPlugin.TestPluginLibrary');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('TestPluginLibrary'));

		$result = App::import('Lib', 'Library');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('Library'));

		$result = App::import('Helper', 'TestPlugin.OtherHelper');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('OtherHelperHelper'));

		$result = App::import('Datasource', 'TestPlugin.TestSource');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('TestSource'));

		App::build();
	}

/**
 * testFileLoading method
 *
 * @access public
 * @return void
 */
	function testFileLoading () {
		$file = App::import('File', 'RealFile', false, array(), TEST_CAKE_CORE_INCLUDE_PATH  . 'config' . DS . 'config.php');
		$this->assertTrue($file);

		$file = App::import('File', 'NoFile', false, array(), TEST_CAKE_CORE_INCLUDE_PATH  . 'config' . DS . 'cake' . DS . 'config.php');
		$this->assertFalse($file);
	}
	// import($type = null, $name = null, $parent = true, $file = null, $search = array(), $return = false) {

/**
 * testFileLoadingWithArray method
 *
 * @access public
 * @return void
 */
	function testFileLoadingWithArray() {
		$type = array('type' => 'File', 'name' => 'SomeName', 'parent' => false,
				'file' => TEST_CAKE_CORE_INCLUDE_PATH  . DS . 'config' . DS . 'config.php');
		$file = App::import($type);
		$this->assertTrue($file);

		$type = array('type' => 'File', 'name' => 'NoFile', 'parent' => false,
				'file' => TEST_CAKE_CORE_INCLUDE_PATH  . 'config' . DS . 'cake' . DS . 'config.php');
		$file = App::import($type);
		$this->assertFalse($file);
	}

/**
 * testFileLoadingReturnValue method
 *
 * @access public
 * @return void
 */
	function testFileLoadingReturnValue () {
		$file = App::import('File', 'Name', false, array(), TEST_CAKE_CORE_INCLUDE_PATH  . 'config' . DS . 'config.php', true);
		$this->assertTrue($file);

		$this->assertTrue(isset($file['Cake.version']));

		$type = array('type' => 'File', 'name' => 'OtherName', 'parent' => false,
				'file' => TEST_CAKE_CORE_INCLUDE_PATH  . 'config' . DS . 'config.php', 'return' => true);
		$file = App::import($type);
		$this->assertTrue($file);

		$this->assertTrue(isset($file['Cake.version']));
	}

/**
 * testLoadingWithSearch method
 *
 * @access public
 * @return void
 */
	function testLoadingWithSearch () {
		$file = App::import('File', 'NewName', false, array(TEST_CAKE_CORE_INCLUDE_PATH ), 'config.php');
		$this->assertTrue($file);

		$file = App::import('File', 'AnotherNewName', false, array(LIBS), 'config.php');
		$this->assertFalse($file);
	}

/**
 * testLoadingWithSearchArray method
 *
 * @access public
 * @return void
 */
	function testLoadingWithSearchArray () {
		$type = array('type' => 'File', 'name' => 'RandomName', 'parent' => false, 'file' => 'config.php', 'search' => array(TEST_CAKE_CORE_INCLUDE_PATH ));
		$file = App::import($type);
		$this->assertTrue($file);

		$type = array('type' => 'File', 'name' => 'AnotherRandomName', 'parent' => false, 'file' => 'config.php', 'search' => array(LIBS));
		$file = App::import($type);
		$this->assertFalse($file);
	}

/**
 * testMultipleLoading method
 *
 * @access public
 * @return void
 */
	function testMultipleLoading() {
		$toLoad = array('I18n', 'CakeSocket');

		$classes = array_flip(get_declared_classes());
		$this->assertFalse(isset($classes['i18n']));
		$this->assertFalse(isset($classes['CakeSocket']));

		$load = App::import($toLoad);
		$this->assertTrue($load);

		$classes = array_flip(get_declared_classes());

		if (PHP5) {
			$this->assertTrue(isset($classes['I18n']));
		} else {
			$this->assertTrue(isset($classes['i18n']));
		}

		$load = App::import(array('I18n', 'SomeNotFoundClass', 'CakeSocket'));
		$this->assertFalse($load);

		$load = App::import($toLoad);
		$this->assertTrue($load);
	}

/**
 * This test only works if you have plugins/my_plugin set up.
 * plugins/my_plugin/models/my_plugin.php and other_model.php
 */

/*
	function testMultipleLoadingByType() {
		$classes = array_flip(get_declared_classes());
		$this->assertFalse(isset($classes['OtherPlugin']));
		$this->assertFalse(isset($classes['MyPlugin']));


		$load = App::import('Model', array('MyPlugin.OtherPlugin', 'MyPlugin.MyPlugin'));
		$this->assertTrue($load);

		$classes = array_flip(get_declared_classes());
		$this->assertTrue(isset($classes['OtherPlugin']));
		$this->assertTrue(isset($classes['MyPlugin']));
	}
*/
	function testLoadingVendor() {
		App::build(array(
			'plugins' => array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS),
			'vendors' => array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'vendors'. DS),
		), true);

		ob_start();
		$result = App::import('Vendor', 'TestPlugin.TestPluginAsset', array('ext' => 'css'));
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'this is the test plugin asset css file');

		ob_start();
		$result = App::import('Vendor', 'TestAsset', array('ext' => 'css'));
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'this is the test asset css file');

		$result = App::import('Vendor', 'TestPlugin.SamplePlugin');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('SamplePluginClassTestName'));

		$result = App::import('Vendor', 'ConfigureTestVendorSample');
		$this->assertTrue($result);
		$this->assertTrue(class_exists('ConfigureTestVendorSample'));

		ob_start();
		$result = App::import('Vendor', 'SomeName', array('file' => 'some.name.php'));
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'This is a file with dot in file name');

		ob_start();
		$result = App::import('Vendor', 'TestHello', array('file' => 'Test'.DS.'hello.php'));
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'This is the hello.php file in Test directory');

		ob_start();
		$result = App::import('Vendor', 'MyTest', array('file' => 'Test'.DS.'MyTest.php'));
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'This is the MyTest.php file');

		ob_start();
		$result = App::import('Vendor', 'Welcome');
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'This is the welcome.php file in vendors directory');

		ob_start();
		$result = App::import('Vendor', 'TestPlugin.Welcome');
		$text = ob_get_clean();
		$this->assertTrue($result);
		$this->assertEqual($text, 'This is the welcome.php file in test_plugin/vendors directory');
	}
}
?>