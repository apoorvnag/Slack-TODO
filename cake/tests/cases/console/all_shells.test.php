<?php
/**
 * AllShellsTest file
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
 * @subpackage    cake.tests.cases
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * AllShellsTest class
 *
 * This test group will run all top level shell classes.
 *
 * @package       cake
 * @subpackage    cake.tests.cases.console
 */
class AllShellsTest extends PHPUnit_Framework_TestSuite {

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All shell classes');

		$path = CORE_TEST_CASES . DS . 'console' . DS . 'libs' . DS;

		$suite->addTestFile(CORE_TEST_CASES . DS . 'console' . DS . 'cake.test.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'console' . DS . 'console_error_handler.test.php');
		$tasks = array('acl', 'api', 'bake', 'schema', 'shell');
		foreach ($tasks as $task) {
			$suite->addTestFile($path . $task . '.test.php');
		}
		return $suite;
	}
}