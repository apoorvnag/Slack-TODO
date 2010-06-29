<?php
/**
 * AclShell Test file
 *
 * PHP versions 4 and 5
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
 * @subpackage    cake.tests.cases.console.libs.tasks
 * @since         CakePHP v 1.2.0.7726
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Shell', 'Shell', false);

if (!defined('DISABLE_AUTO_DISPATCH')) {
	define('DISABLE_AUTO_DISPATCH', true);
}

if (!class_exists('ShellDispatcher')) {
	ob_start();
	$argv = false;
	require CAKE . 'console' .  DS . 'cake.php';
	ob_end_clean();
}

if (!class_exists('AclShell')) {
	require CAKE . 'console' .  DS . 'libs' . DS . 'acl.php';
}

/**
 * AclShellTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.console.libs.tasks
 */
class AclShellTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 * @access public
 */
	public $fixtures = array('core.aco', 'core.aro', 'core.aros_aco');

/**
 * startTest method
 *
 * @return void
 */
	public function startTest() {
		$this->_aclDb = Configure::read('Acl.database');
		$this->_aclClass = Configure::read('Acl.classname');

		Configure::write('Acl.database', 'test_suite');
		Configure::write('Acl.classname', 'DbAcl');

		$this->Dispatcher = $this->getMock(
			'ShellDispather', 
			array('getInput', 'stdout', 'stderr', '_stop', '_initEnvironment', 'dispatch')
		);
		$this->Task = $this->getMock(
			'AclShell',
			array('in', 'out', 'hr', 'createFile', 'error', 'err'),
			array(&$this->Dispatcher)
		);
		$this->Task->Acl = new AclComponent();

		$this->Task->params['datasource'] = 'test_suite';
	}

/**
 * endTest method
 *
 * @return void
 */
	public function endTest() {
		ClassRegistry::flush();
		Configure::write('Acl.database', $this->_aclDb);
		Configure::write('Acl.classname', $this->_aclClass);
	}

/**
 * test that model.foreign_key output works when looking at acl rows
 *
 * @return void
 */
	public function testViewWithModelForeignKeyOutput() {
		$this->Task->command = 'view';
		$this->Task->startup();
		$data = array(
			'parent_id' => null,
			'model' => 'MyModel',
			'foreign_key' => 2,
		);
		$this->Task->Acl->Aro->create($data);
		$this->Task->Acl->Aro->save();
		$this->Task->args[0] = 'aro';

		$this->Task->expects($this->at(0))->method('out')->with('Aro tree:');
		$this->Task->expects($this->at(2))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/\[1\] ROOT/'));

		$this->Task->expects($this->at(4))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/\[3\] Gandalf/'));
	
		$this->Task->expects($this->at(6))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/\[5\] MyModel\.2/'));

		$this->Task->view();
	}

/**
 * test view with an argument
 *
 * @return void
 */
	public function testViewWithArgument() {
		$this->Task->args = array('aro', 'admins');

		$this->Task->expects($this->at(0))->method('out')->with('Aro tree:');
		$this->Task->expects($this->at(2))->method('out')->with('  [2] admins');
		$this->Task->expects($this->at(3))->method('out')->with('    [3] Gandalf');
		$this->Task->expects($this->at(4))->method('out')->with('    [4] Elrond');

		$this->Task->view();
	}

/**
 * test the method that splits model.foreign key. and that it returns an array.
 *
 * @return void
 */
	public function testParsingModelAndForeignKey() {
		$result = $this->Task->parseIdentifier('Model.foreignKey');
		$expected = array('model' => 'Model', 'foreign_key' => 'foreignKey');

		$result = $this->Task->parseIdentifier('mySuperUser');
		$this->assertEqual($result, 'mySuperUser');

		$result = $this->Task->parseIdentifier('111234');
		$this->assertEqual($result, '111234');
	}

/**
 * test creating aro/aco nodes
 *
 * @return void
 */
	public function testCreate() {
		$this->Task->args = array('aro', 'root', 'User.1');
		$this->Task->expects($this->at(0))->method('out')->with("New Aro 'User.1' created.\n", true);
		$this->Task->expects($this->at(1))->method('out')->with("New Aro 'User.3' created.\n", true);
		$this->Task->expects($this->at(2))->method('out')->with("New Aro 'somealias' created.\n", true);

		$this->Task->create();

		$Aro = ClassRegistry::init('Aro');
		$Aro->cacheQueries = false;
		$result = $Aro->read();
		$this->assertEqual($result['Aro']['model'], 'User');
		$this->assertEqual($result['Aro']['foreign_key'], 1);
		$this->assertEqual($result['Aro']['parent_id'], null);
		$id = $result['Aro']['id'];

		$this->Task->args = array('aro', 'User.1', 'User.3');
		$this->Task->create();

		$Aro = ClassRegistry::init('Aro');
		$result = $Aro->read();
		$this->assertEqual($result['Aro']['model'], 'User');
		$this->assertEqual($result['Aro']['foreign_key'], 3);
		$this->assertEqual($result['Aro']['parent_id'], $id);

		$this->Task->args = array('aro', 'root', 'somealias');
		$this->Task->create();

		$Aro = ClassRegistry::init('Aro');
		$result = $Aro->read();
		$this->assertEqual($result['Aro']['alias'], 'somealias');
		$this->assertEqual($result['Aro']['model'], null);
		$this->assertEqual($result['Aro']['foreign_key'], null);
		$this->assertEqual($result['Aro']['parent_id'], null);
	}

/**
 * test the delete method with different node types.
 *
 * @return void
 */
	public function testDelete() {
		$this->Task->args = array('aro', 'AuthUser.1');
		$this->Task->expects($this->at(0))->method('out')
			->with("Aro deleted.\n", true);
		$this->Task->delete();

		$Aro = ClassRegistry::init('Aro');
		$result = $Aro->findById(3);
		$this->assertFalse($result);
	}

/**
 * test setParent method.
 *
 * @return void
 */
	public function testSetParent() {
		$this->Task->args = array('aro', 'AuthUser.2', 'root');
		$this->Task->setParent();

		$Aro = ClassRegistry::init('Aro');
		$result = $Aro->read(null, 4);
		$this->assertEqual($result['Aro']['parent_id'], null);
	}

/**
 * test grant
 *
 * @return void
 */
	public function testGrant() {
		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'create');
		$this->Task->expects($this->at(0))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/Permission granted/'), true);
		$this->Task->grant();

		$node = $this->Task->Acl->Aro->read(null, 4);
		$this->assertFalse(empty($node['Aco'][0]));
		$this->assertEqual($node['Aco'][0]['Permission']['_create'], 1);
	}

/**
 * test deny
 *
 * @return void
 */
	public function testDeny() {
		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'create');
		$this->Task->expects($this->at(0))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/Permission denied/'), true);
	
		$this->Task->deny();

		$node = $this->Task->Acl->Aro->read(null, 4);
		$this->assertFalse(empty($node['Aco'][0]));
		$this->assertEqual($node['Aco'][0]['Permission']['_create'], -1);
	}

/**
 * test checking allowed and denied perms
 *
 * @return void
 */
	public function testCheck() {
		$this->Task->expects($this->at(0))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/not allowed/'), true);
		$this->Task->expects($this->at(1))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/Permission granted/'), true);
		$this->Task->expects($this->at(2))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/is allowed/'), true);
		$this->Task->expects($this->at(3))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/not allowed/'), true);

		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', '*');
		$this->Task->check();

		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'create');
		$this->Task->grant();

		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'create');
		$this->Task->check();

		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', '*');
		$this->Task->check();
	}

/**
 * test inherit and that it 0's the permission fields.
 *
 * @return void
 */
	public function testInherit() {
		$this->Task->expects($this->at(0))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/Permission granted/'), true);
		$this->Task->expects($this->at(1))->method('out')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/Permission inherited/'), true);
		
		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'create');
		$this->Task->grant();

		$this->Task->args = array('AuthUser.2', 'ROOT/Controller1', 'all');
		$this->Task->inherit();

		$node = $this->Task->Acl->Aro->read(null, 4);
		$this->assertFalse(empty($node['Aco'][0]));
		$this->assertEqual($node['Aco'][0]['Permission']['_create'], 0);
	}

/**
 * test getting the path for an aro/aco
 *
 * @return void
 */
	public function testGetPath() {
		$this->Task->args = array('aro', 'AuthUser.2');
		$this->Task->expects($this->at(2))->method('out')->with('[1] ROOT');
		$this->Task->expects($this->at(3))->method('out')->with('  [2] admins');
		$this->Task->expects($this->at(4))->method('out')->with('    [4] Elrond');
		$this->Task->getPath();
	}

/**
 * test that initdb makes the correct call.
 *
 * @return void
 */
	function testInitDb() {
		$this->Task->Dispatch->expects($this->once())->method('dispatch');
		$this->Task->initdb();

		$this->assertEqual($this->Task->Dispatch->args, array('schema', 'create', 'DbAcl'));
	}
}
