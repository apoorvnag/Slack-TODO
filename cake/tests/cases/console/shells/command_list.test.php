<?php
/**
 * CommandList file
 *
 * PHP 5
 *
 * CakePHP :  Rapid Development Framework (http://cakephp.org)
 * Copyright 2006-2010, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2006-2010, Cake Software Foundation, Inc.
 * @link          http://cakephp.org CakePHP Project
 * @package       cake
 * @subpackage    cake.tests.cases.console.libs
 * @since         CakePHP v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Shell', 'CommandList', false);

require_once CAKE . 'console' .  DS . 'shell_dispatcher.php';

class TestStringOutput extends ConsoleOutput {
	public $output = '';

	protected function _write($message) {
		$this->output .= $message;
	}
}

class CommandListTest extends CakeTestCase {
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		App::build(array(
			'plugins' => array(
				TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'plugins' . DS
			),
			'shells' => array(
				CORE_PATH ? 
					CORE_PATH . CAKE . 'console' . DS . 'shells' . DS : 
					CAKE_CORE_INCLUDE_PATH . DS . 'cake' . DS . 'console' . DS . 'shells' .DS, 
				TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'console' . DS . 'shells' . DS
			)
		), true);
		App::objects('plugin', null, false);

		$this->Dispatcher = $this->getMock(
			'ShellDispatcher', 
			array('_stop', '_initEnvironment', 'dispatch')
		);
		$out = new TestStringOutput();
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Shell = $this->getMock(
			'CommandListShell',
			array('in', '_stop', 'clear'),
			array(&$this->Dispatcher, $out, $out, $in)
		);
	}

/**
 * teardown
 *
 * @return void
 */
	function tearDown() {
		parent::tearDown();
		unset($this->Dispatcher, $this->Shell);
	}

/**
 * test that main finds core shells.
 *
 * @return void
 */
	function testMain() {
		$this->Shell->main();
		$output = $this->Shell->stdout->output;
;
		$expected = "/example \[.*TestPlugin, TestPluginTwo.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/welcome \[.*TestPluginTwo.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/acl \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/api \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/bake \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/console \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/i18n \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/schema \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/testsuite \[.*CORE.*\]/";
		$this->assertPattern($expected, $output);

		$expected = "/sample \[.*app.*\]/";
		$this->assertPattern($expected, $output);
	}

/**
 * test xml output.
 *
 * @return void
 */
	function testMainXml() {
		$this->Shell->params['xml'] = true;
		$this->Shell->main();

		$output = $this->Shell->stdout->output;

		$find = '<shell name="sample" call_as="sample" provider="app" help="sample -h"/>';
		$this->assertContains($find, $output);

		$find = '<shell name="bake" call_as="bake" provider="CORE" help="bake -h"/>';
		$this->assertContains($find, $output);
		
		$find = '<shell name="welcome" call_as="test_plugin_two.welcome" provider="TestPluginTwo" help="test_plugin_two.welcome -h"/>';
		$this->assertContains($find, $output);
	}
}
