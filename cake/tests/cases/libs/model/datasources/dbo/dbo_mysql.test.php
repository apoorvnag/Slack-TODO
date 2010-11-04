<?php
/**
 * DboMysqlTest file
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
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Core', array('Model', 'DataSource', 'DboSource', 'DboMysql'));
App::import('Model', 'App');
require_once dirname(dirname(dirname(__FILE__))) . DS . 'models.php';

/**
 * DboMysqlTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs.model.datasources.dbo
 */
class DboMysqlTest extends CakeTestCase {
/**
 * autoFixtures property
 *
 * @var bool false
 * @access public
 */
	public $autoFixtures = false;

/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'core.apple', 'core.article', 'core.articles_tag', 'core.attachment', 'core.comment',
		'core.sample', 'core.tag', 'core.user', 'core.post', 'core.author', 'core.data_test',
		'core.binary_test'
	);

/**
 * The Dbo instance to be tested
 *
 * @var DboSource
 * @access public
 */
	public $Dbo = null;

/**
 * Sets up a Dbo class instance for testing
 *
 */
	public function setUp() {
		$this->Dbo = ConnectionManager::getDataSource('test');
		if ($this->Dbo->config['driver'] !== 'mysql') {
			$this->markTestSkipped('The MySQL extension is not available.');
		}
		$this->_debug = Configure::read('debug');
		Configure::write('debug', 1);
		$this->model = new MysqlTestModel();
	}

/**
 * Sets up a Dbo class instance for testing
 *
 */
	public function tearDown() {
		unset($this->model);
		ClassRegistry::flush();
		Configure::write('debug', $this->_debug);
	}

/**
 * Test Dbo value method
 *
 * @group quoting
 */
	public function testQuoting() {
		$result = $this->Dbo->fields($this->model);
		$expected = array(
			'`MysqlTestModel`.`id`',
			'`MysqlTestModel`.`client_id`',
			'`MysqlTestModel`.`name`',
			'`MysqlTestModel`.`login`',
			'`MysqlTestModel`.`passwd`',
			'`MysqlTestModel`.`addr_1`',
			'`MysqlTestModel`.`addr_2`',
			'`MysqlTestModel`.`zip_code`',
			'`MysqlTestModel`.`city`',
			'`MysqlTestModel`.`country`',
			'`MysqlTestModel`.`phone`',
			'`MysqlTestModel`.`fax`',
			'`MysqlTestModel`.`url`',
			'`MysqlTestModel`.`email`',
			'`MysqlTestModel`.`comments`',
			'`MysqlTestModel`.`last_login`',
			'`MysqlTestModel`.`created`',
			'`MysqlTestModel`.`updated`'
		);
		$this->assertEqual($result, $expected);

		$expected = 1.2;
		$result = $this->Dbo->value(1.2, 'float');
		$this->assertEqual($expected, $result);

		$expected = "'1,2'";
		$result = $this->Dbo->value('1,2', 'float');
		$this->assertEqual($expected, $result);

		$expected = "'4713e29446'";
		$result = $this->Dbo->value('4713e29446');

		$this->assertEqual($expected, $result);

		$expected = 'NULL';
		$result = $this->Dbo->value('', 'integer');
		$this->assertEqual($expected, $result);

		$expected = "'0'";
		$result = $this->Dbo->value('', 'boolean');
		$this->assertEqual($expected, $result);

		$expected = 10010001;
		$result = $this->Dbo->value(10010001);
		$this->assertEqual($expected, $result);

		$expected = "'00010010001'";
		$result = $this->Dbo->value('00010010001');
		$this->assertEqual($expected, $result);
	}

/**
 * test that localized floats don't cause trouble.
 *
 * @group quoting
 * @return void
 */
	function testLocalizedFloats() {
		$restore = setlocale(LC_ALL, null);
		setlocale(LC_ALL, 'de_DE');

		$result = $this->Dbo->value(3.141593, 'float');
		$this->assertEqual((string)$result, '3.141593');

		$result = $this->Dbo->value(3.141593);
		$this->assertEqual((string)$result, '3.141593');

		setlocale(LC_ALL, $restore);
	}

/**
 * testTinyintCasting method
 *
 *
 * @return void
 */
	function testTinyintCasting() {
		$this->Dbo->cacheSources = false;
		$tableName = 'tinyint_' . uniqid();
		$this->Dbo->rawQuery('CREATE TABLE ' . $this->Dbo->fullTableName($tableName) . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id));');

		$this->model = new CakeTestModel(array(
			'name' => 'Tinyint', 'table' => $tableName, 'ds' => 'test'
		));

		$result = $this->model->schema();
		$this->assertEqual($result['bool']['type'], 'boolean');
		$this->assertEqual($result['small_int']['type'], 'integer');

		$this->assertTrue((bool)$this->model->save(array('bool' => 5, 'small_int' => 5)));
		$result = $this->model->find('first');
		$this->assertIdentical($result['Tinyint']['bool'], '1');
		$this->assertIdentical($result['Tinyint']['small_int'], '5');
		$this->model->deleteAll(true);

		$this->assertTrue((bool)$this->model->save(array('bool' => 0, 'small_int' => 100)));
		$result = $this->model->find('first');
		$this->assertIdentical($result['Tinyint']['bool'], '0');
		$this->assertIdentical($result['Tinyint']['small_int'], '100');
		$this->model->deleteAll(true);

		$this->assertTrue((bool)$this->model->save(array('bool' => true, 'small_int' => 0)));
		$result = $this->model->find('first');
		$this->assertIdentical($result['Tinyint']['bool'], '1');
		$this->assertIdentical($result['Tinyint']['small_int'], '0');
		$this->model->deleteAll(true);

		$this->Dbo->rawQuery('DROP TABLE ' . $this->Dbo->fullTableName($tableName));
	}

/**
 * testIndexDetection method
 *
 * @group indices
 * @return void
 */
	public function testIndexDetection() {
		$this->Dbo->cacheSources = false;

		$name = $this->Dbo->fullTableName('simple');
		$this->Dbo->rawQuery('CREATE TABLE ' . $name . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id));');
		$expected = array('PRIMARY' => array('column' => 'id', 'unique' => 1));
		$result = $this->Dbo->index('simple', false);
		$this->Dbo->rawQuery('DROP TABLE ' . $name);
		$this->assertEqual($expected, $result);


		$name = $this->Dbo->fullTableName('with_a_key');
		$this->Dbo->rawQuery('CREATE TABLE ' . $name . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id), KEY `pointless_bool` ( `bool` ));');
		$expected = array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'pointless_bool' => array('column' => 'bool', 'unique' => 0),
		);
		$result = $this->Dbo->index('with_a_key', false);
		$this->Dbo->rawQuery('DROP TABLE ' . $name);
		$this->assertEqual($expected, $result);

		$name = $this->Dbo->fullTableName('with_two_keys');
		$this->Dbo->rawQuery('CREATE TABLE ' . $name . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id), KEY `pointless_bool` ( `bool` ), KEY `pointless_small_int` ( `small_int` ));');
		$expected = array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'pointless_bool' => array('column' => 'bool', 'unique' => 0),
			'pointless_small_int' => array('column' => 'small_int', 'unique' => 0),
		);
		$result = $this->Dbo->index('with_two_keys', false);
		$this->Dbo->rawQuery('DROP TABLE ' . $name);
		$this->assertEqual($expected, $result);

		$name = $this->Dbo->fullTableName('with_compound_keys');
		$this->Dbo->rawQuery('CREATE TABLE ' . $name . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id), KEY `pointless_bool` ( `bool` ), KEY `pointless_small_int` ( `small_int` ), KEY `one_way` ( `bool`, `small_int` ));');
		$expected = array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'pointless_bool' => array('column' => 'bool', 'unique' => 0),
			'pointless_small_int' => array('column' => 'small_int', 'unique' => 0),
			'one_way' => array('column' => array('bool', 'small_int'), 'unique' => 0),
		);
		$result = $this->Dbo->index('with_compound_keys', false);
		$this->Dbo->rawQuery('DROP TABLE ' . $name);
		$this->assertEqual($expected, $result);

		$name = $this->Dbo->fullTableName('with_multiple_compound_keys');
		$this->Dbo->rawQuery('CREATE TABLE ' . $name . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id), KEY `pointless_bool` ( `bool` ), KEY `pointless_small_int` ( `small_int` ), KEY `one_way` ( `bool`, `small_int` ), KEY `other_way` ( `small_int`, `bool` ));');
		$expected = array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'pointless_bool' => array('column' => 'bool', 'unique' => 0),
			'pointless_small_int' => array('column' => 'small_int', 'unique' => 0),
			'one_way' => array('column' => array('bool', 'small_int'), 'unique' => 0),
			'other_way' => array('column' => array('small_int', 'bool'), 'unique' => 0),
		);
		$result = $this->Dbo->index('with_multiple_compound_keys', false);
		$this->Dbo->rawQuery('DROP TABLE ' . $name);
		$this->assertEqual($expected, $result);
	}

/**
 * testBuildColumn method
 *
 * @return void
 */
	function testBuildColumn() {
		$restore = $this->Dbo->columns;
		$this->Dbo->columns = array('varchar(255)' => 1);
		$data = array(
			'name' => 'testName',
			'type' => 'varchar(255)',
			'default',
			'null' => true,
			'key',
			'comment' => 'test'
		);
		$result = $this->Dbo->buildColumn($data);
		$expected = '`testName`  DEFAULT NULL COMMENT \'test\'';
		$this->assertEqual($result, $expected);

		$data = array(
			'name' => 'testName',
			'type' => 'varchar(255)',
			'default',
			'null' => true,
			'key',
			'charset' => 'utf8',
			'collate' => 'utf8_unicode_ci'
		);
		$result = $this->Dbo->buildColumn($data);
		$expected = '`testName`  CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL';
		$this->assertEqual($result, $expected);
		$this->Dbo->columns = $restore;
	}

/**
 * MySQL 4.x returns index data in a different format,
 * Using a mock ensure that MySQL 4.x output is properly parsed.
 *
 * @group indices
 * @return void
 */
	function testIndexOnMySQL4Output() {
		$name = $this->Dbo->fullTableName('simple');

		$mockDbo = $this->getMock('DboMysql', array('connect', '_execute', 'getVersion'));
		$columnData = array(
			array('0' => array(
				'Table' => 'with_compound_keys',
				'Non_unique' => '0',
				'Key_name' => 'PRIMARY',
				'Seq_in_index' => '1',
				'Column_name' => 'id',
				'Collation' => 'A',
				'Cardinality' => '0',
				'Sub_part' => NULL,
				'Packed' => NULL,
				'Null' => '',
				'Index_type' => 'BTREE',
				'Comment' => ''
			)),
			array('0' => array(
				'Table' => 'with_compound_keys',
				'Non_unique' => '1',
				'Key_name' => 'pointless_bool',
				'Seq_in_index' => '1',
				'Column_name' => 'bool',
				'Collation' => 'A',
				'Cardinality' => NULL,
				'Sub_part' => NULL,
				'Packed' => NULL,
				'Null' => 'YES',
				'Index_type' => 'BTREE',
				'Comment' => ''
			)),
			array('0' => array(
				'Table' => 'with_compound_keys',
				'Non_unique' => '1',
				'Key_name' => 'pointless_small_int',
				'Seq_in_index' => '1',
				'Column_name' => 'small_int',
				'Collation' => 'A',
				'Cardinality' => NULL,
				'Sub_part' => NULL,
				'Packed' => NULL,
				'Null' => 'YES',
				'Index_type' => 'BTREE',
				'Comment' => ''
			)),
			array('0' => array(
				'Table' => 'with_compound_keys',
				'Non_unique' => '1',
				'Key_name' => 'one_way',
				'Seq_in_index' => '1',
				'Column_name' => 'bool',
				'Collation' => 'A',
				'Cardinality' => NULL,
				'Sub_part' => NULL,
				'Packed' => NULL,
				'Null' => 'YES',
				'Index_type' => 'BTREE',
				'Comment' => ''
			)),
			array('0' => array(
				'Table' => 'with_compound_keys',
				'Non_unique' => '1',
				'Key_name' => 'one_way',
				'Seq_in_index' => '2',
				'Column_name' => 'small_int',
				'Collation' => 'A',
				'Cardinality' => NULL,
				'Sub_part' => NULL,
				'Packed' => NULL,
				'Null' => 'YES',
				'Index_type' => 'BTREE',
				'Comment' => ''
			))
		);

		$mockDbo->expects($this->once())->method('getVersion')->will($this->returnValue('4.1'));
		$resultMock = $this->getMock('PDOStatement', array('fetch'));
		$mockDbo->expects($this->once())
			->method('_execute')
			->with('SHOW INDEX FROM ' . $name)
			->will($this->returnValue($resultMock));

		foreach ($columnData as $i => $data) {
			$resultMock->expects($this->at($i))->method('fetch')->will($this->returnValue((object) $data));
		}

		$result = $mockDbo->index($name, false);
		$expected = array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'pointless_bool' => array('column' => 'bool', 'unique' => 0),
			'pointless_small_int' => array('column' => 'small_int', 'unique' => 0),
			'one_way' => array('column' => array('bool', 'small_int'), 'unique' => 0),
		);
		$this->assertEqual($result, $expected);
	}

/**
 * testColumn method
 *
 * @return void
 */
	public function testColumn() {
		$result = $this->Dbo->column('varchar(50)');
		$expected = 'string';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('text');
		$expected = 'text';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('int(11)');
		$expected = 'integer';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('int(11) unsigned');
		$expected = 'integer';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('tinyint(1)');
		$expected = 'boolean';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('boolean');
		$expected = 'boolean';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('float');
		$expected = 'float';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('float unsigned');
		$expected = 'float';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('double unsigned');
		$expected = 'float';
		$this->assertEqual($result, $expected);

		$result = $this->Dbo->column('decimal(14,7) unsigned');
		$expected = 'float';
		$this->assertEqual($result, $expected);
	}

/**
 * testAlterSchemaIndexes method
 *
 * @group indices
 * @return void
 */
	function testAlterSchemaIndexes() {
		App::import('Model', 'CakeSchema');
		$this->Dbo->cacheSources = $this->Dbo->testing = false;

		$schema1 = new CakeSchema(array(
			'name' => 'AlterTest1',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
				'group1' => array('type' => 'integer', 'null' => true),
				'group2' => array('type' => 'integer', 'null' => true)
		)));
		$result = $this->Dbo->createSchema($schema1);
		$this->assertContains('`id` int(11) DEFAULT 0 NOT NULL,', $result);
		$this->assertContains('`name` varchar(50) NOT NULL,', $result);
		$this->assertContains('`group1` int(11) DEFAULT NULL', $result);
		$this->assertContains('`group2` int(11) DEFAULT NULL', $result);

		//Test that the string is syntactically correct
		$query = $this->Dbo->getConnection()->prepare($result);
		$this->assertEquals($result, $query->queryString);

		$schema2 = new CakeSchema(array(
			'name' => 'AlterTest2',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
				'group1' => array('type' => 'integer', 'null' => true),
				'group2' => array('type' => 'integer', 'null' => true),
				'indexes' => array(
					'name_idx' => array('column' => 'name', 'unique' => 0),
					'group_idx' => array('column' => 'group1', 'unique' => 0),
					'compound_idx' => array('column' => array('group1', 'group2'), 'unique' => 0),
					'PRIMARY' => array('column' => 'id', 'unique' => 1))
		)));

		$result = $this->Dbo->alterSchema($schema2->compare($schema1));
		$this->assertContains('ALTER TABLE `altertest`', $result);
		$this->assertContains('ADD KEY name_idx (`name`),', $result);
		$this->assertContains('ADD KEY group_idx (`group1`),', $result);
		$this->assertContains('ADD KEY compound_idx (`group1`, `group2`),', $result);
		$this->assertContains('ADD PRIMARY KEY  (`id`);', $result);

		//Test that the string is syntactically correct
		$query = $this->Dbo->getConnection()->prepare($result);
		$this->assertEquals($result, $query->queryString);

		// Change three indexes, delete one and add another one
		$schema3 = new CakeSchema(array(
			'name' => 'AlterTest3',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
				'group1' => array('type' => 'integer', 'null' => true),
				'group2' => array('type' => 'integer', 'null' => true),
				'indexes' => array(
					'name_idx' => array('column' => 'name', 'unique' => 1),
					'group_idx' => array('column' => 'group2', 'unique' => 0),
					'compound_idx' => array('column' => array('group2', 'group1'), 'unique' => 0),
					'id_name_idx' => array('column' => array('id', 'name'), 'unique' => 0))
		)));

		$result = $this->Dbo->alterSchema($schema3->compare($schema2));
		$this->assertContains('ALTER TABLE `altertest`', $result);
		$this->assertContains('DROP PRIMARY KEY,', $result);
		$this->assertContains('DROP KEY name_idx,', $result);
		$this->assertContains('DROP KEY group_idx,', $result);
		$this->assertContains('DROP KEY compound_idx,', $result);
		$this->assertContains('ADD KEY id_name_idx (`id`, `name`),', $result);
		$this->assertContains('ADD UNIQUE KEY name_idx (`name`),', $result);
		$this->assertContains('ADD KEY group_idx (`group2`),', $result);
		$this->assertContains('ADD KEY compound_idx (`group2`, `group1`);', $result);

		$query = $this->Dbo->getConnection()->prepare($result);
		$this->assertEquals($result, $query->queryString);

		// Compare us to ourself.
		$this->assertEqual($schema3->compare($schema3), array());

		// Drop the indexes
		$result = $this->Dbo->alterSchema($schema1->compare($schema3));

		$this->assertContains('ALTER TABLE `altertest`', $result);
		$this->assertContains('DROP KEY name_idx,', $result);
		$this->assertContains('DROP KEY group_idx,', $result);
		$this->assertContains('DROP KEY compound_idx,', $result);
		$this->assertContains('DROP KEY id_name_idx;', $result);

		$query = $this->Dbo->getConnection()->prepare($result);
		$this->assertEquals($result, $query->queryString);
	}

/**
 * test saving and retrieval of blobs
 *
 * @return void
 */
	function testBlobSaving() {
		$this->loadFixtures('BinaryTest');
		$this->Dbo->cacheSources = false;
		$data = "GIF87ab 
		 Ò   4A¿¿¿ˇˇˇ   ,    b 
		  ¢îè©ÀÌ#¥⁄ã≥ﬁ:¯Ü‚Héá¶jV∂ÓúÎL≥çÀóËıÎ…>ï ≈ vFE%ÒâLFI<†µw˝±≈£7˘ç^H“≤«>ÉÃ¢*∑Ç nÖA•Ù|ﬂêèj£:=ÿ6óUàµ5'∂®àA¬ñ∆ˆGE(gt’≈àÚyÁó«7	‚VìöÇ√˙Ç™
		k”:;kÀAõ{*¡€Î˚˚[  ;;";

		$model = new CakeTestModel(array('name' => 'BinaryTest', 'ds' => 'test'));
		$model->save(compact('data'));

		$result = $model->find('first');
		$this->assertEqual($result['BinaryTest']['data'], $data);
	}

/**
 * test altering the table settings with schema.
 *
 * @return void
 */
	function testAlteringTableParameters() {
		App::import('Model', 'CakeSchema');
		$this->Dbo->cacheSources = $this->Dbo->testing = false;

		$schema1 = new CakeSchema(array(
			'name' => 'AlterTest1',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
				'tableParameters' => array(
					'charset' => 'latin1',
					'collate' => 'latin1_general_ci',
					'engine' => 'MyISAM'
				)
			)
		));
		$this->Dbo->rawQuery($this->Dbo->createSchema($schema1));
		$schema2 = new CakeSchema(array(
			'name' => 'AlterTest1',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
				'tableParameters' => array(
					'charset' => 'utf8',
					'collate' => 'utf8_general_ci',
					'engine' => 'InnoDB'
				)
			)
		));
		$result = $this->Dbo->alterSchema($schema2->compare($schema1));
		$this->assertContains('DEFAULT CHARSET=utf8', $result);
		$this->assertContains('ENGINE=InnoDB', $result);
		$this->assertContains('COLLATE=utf8_general_ci', $result);

		$this->Dbo->rawQuery($result);
		$result = $this->Dbo->listDetailedSources('altertest');
		$this->assertEqual($result['Collation'], 'utf8_general_ci');
		$this->assertEqual($result['Engine'], 'InnoDB');
		$this->assertEqual($result['charset'], 'utf8');

		$this->Dbo->rawQuery($this->Dbo->dropSchema($schema1));
	}

/**
 * test alterSchema on two tables.
 *
 * @return void
 */
	function testAlteringTwoTables() {
		$schema1 = new CakeSchema(array(
			'name' => 'AlterTest1',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
			),
			'other_table' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'name' => array('type' => 'string', 'null' => false, 'length' => 50),
			)
		));
		$schema2 = new CakeSchema(array(
			'name' => 'AlterTest1',
			'connection' => 'test',
			'altertest' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'field_two' => array('type' => 'string', 'null' => false, 'length' => 50),
			),
			'other_table' => array(
				'id' => array('type' => 'integer', 'null' => false, 'default' => 0),
				'field_two' => array('type' => 'string', 'null' => false, 'length' => 50),
			)
		));
		$result = $this->db->alterSchema($schema2->compare($schema1));
		$this->assertEqual(2, substr_count($result, 'field_two'), 'Too many fields');
	}

/**
 * testReadTableParameters method
 *
 * @access public
 * @return void
 */
	function testReadTableParameters() {
		$this->Dbo->cacheSources = $this->Dbo->testing = false;
		$tableName = 'tinyint_' . uniqid();
		$this->Dbo->rawQuery('CREATE TABLE ' . $this->Dbo->fullTableName($tableName) . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
		$result = $this->Dbo->readTableParameters($tableName);
		$this->Dbo->rawQuery('DROP TABLE ' . $this->Dbo->fullTableName($tableName));
		$expected = array(
			'charset' => 'utf8',
			'collate' => 'utf8_unicode_ci',
			'engine' => 'InnoDB');
		$this->assertEqual($result, $expected);

		$this->Dbo->rawQuery('CREATE TABLE ' . $this->Dbo->fullTableName($tableName) . ' (id int(11) AUTO_INCREMENT, bool tinyint(1), small_int tinyint(2), primary key(id)) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_general_ci;');
		$result = $this->Dbo->readTableParameters($tableName);
		$this->Dbo->rawQuery('DROP TABLE ' . $this->Dbo->fullTableName($tableName));
		$expected = array(
			'charset' => 'cp1250',
			'collate' => 'cp1250_general_ci',
			'engine' => 'MyISAM');
		$this->assertEqual($result, $expected);
	}

/**
 * testBuildTableParameters method
 *
 * @access public
 * @return void
 */
	function testBuildTableParameters() {
		$this->Dbo->cacheSources = $this->Dbo->testing = false;
		$data = array(
			'charset' => 'utf8',
			'collate' => 'utf8_unicode_ci',
			'engine' => 'InnoDB');
		$result = $this->Dbo->buildTableParameters($data);
		$expected = array(
			'DEFAULT CHARSET=utf8',
			'COLLATE=utf8_unicode_ci',
			'ENGINE=InnoDB');
		$this->assertEqual($result, $expected);
	}

/**
 * testBuildTableParameters method
 *
 * @access public
 * @return void
 */
	function testGetCharsetName() {
		$this->Dbo->cacheSources = $this->Dbo->testing = false;
		$result = $this->Dbo->getCharsetName('utf8_unicode_ci');
		$this->assertEqual($result, 'utf8');
		$result = $this->Dbo->getCharsetName('cp1250_general_ci');
		$this->assertEqual($result, 'cp1250');
	}

/**
 * test that changing the virtualFieldSeparator allows for __ fields.
 *
 * @return void
 */
	function testVirtualFieldSeparators() {
		$this->loadFixtures('BinaryTest');
		$model = new CakeTestModel(array('table' => 'binary_tests', 'ds' => 'test', 'name' => 'BinaryTest'));
		$model->virtualFields = array(
			'other__field' => 'SUM(id)'
		);
		
		$this->db->virtualFieldSeparator = '_$_';
		$result = $this->db->fields($model, null, array('data', 'other__field'));
		$expected = array('`BinaryTest`.`data`', '(SUM(id)) AS  `BinaryTest_$_other__field`');
		$this->assertEqual($result, $expected);
	}

/**
 * test that a describe() gets additional fieldParameters
 *
 * @return void
 */
	function testDescribeGettingFieldParameters() {
		$schema = new CakeSchema(array(
			'connection' => 'test',
			'testdescribes' => array(
				'id' => array('type' => 'integer', 'key' => 'primary'),
				'stringy' => array(
					'type' => 'string',
					'null' => true,
					'charset' => 'cp1250',
					'collate' => 'cp1250_general_ci',
				),
				'other_col' => array(
					'type' => 'string',
					'null' => false,
					'charset' => 'latin1',
					'comment' => 'Test Comment'
				)
			)
		));

		$this->db->execute($this->db->createSchema($schema));
		$model = new CakeTestModel(array('table' => 'testdescribes', 'name' => 'Testdescribes'));
		$result = $this->db->describe($model);
		$this->db->execute($this->db->dropSchema($schema));

		$this->assertEqual($result['stringy']['collate'], 'cp1250_general_ci');
		$this->assertEqual($result['stringy']['charset'], 'cp1250');
		$this->assertEqual($result['other_col']['comment'], 'Test Comment');
	}

/**
 * Tests that listSources method sends the correct query and parses the result accordingly
 * @return void
 */
	public function testListSources() {
		$db = $this->getMock('DboMysql', array('connect', '_execute'));
		$queryResult = $this->getMock('PDOStatement');
		$db->expects($this->once())
			->method('_execute')
			->with('SHOW TABLES FROM cake')
			->will($this->returnValue($queryResult));
		$queryResult->expects($this->at(0))
			->method('fetch')
			->will($this->returnValue(array('cake_table')));
		$queryResult->expects($this->at(1))
			->method('fetch')
			->will($this->returnValue(array('another_table')));
		$queryResult->expects($this->at(2))
			->method('fetch')
			->will($this->returnValue(null));

		$tables = $db->listSources();
		$this->assertEqual($tables, array('cake_table', 'another_table'));
	}

/**
 * Tests that getVersion method sends the correct query for getting the mysql version
 * @return void
 */
	public function testGetVersion() {
		$version = $this->Dbo->getVersion();
		$this->assertTrue(is_string($version));
	}

/**
 * Tests that getVersion method sends the correct query for getting the client encoding
 * @return void
 */
	public function testGetEncoding() {
		$db = $this->getMock('DboMysql', array('connect', '_execute'));
		$queryResult = $this->getMock('PDOStatement');

		$db->expects($this->once())
			->method('_execute')
			->with('SHOW VARIABLES LIKE ?', array('character_set_client'))
			->will($this->returnValue($queryResult));
		$result = new StdClass;
		$result->Value = 'utf-8';
		$queryResult->expects($this->once())
			->method('fetchObject')
			->will($this->returnValue($result));

		$encoding = $db->getEncoding();
		$this->assertEqual('utf-8', $encoding);
	}

/**
 * testFieldDoubleEscaping method
 *
 * @access public
 * @return void
 */
	function testFieldDoubleEscaping() {
		$test = $this->getMock('DboMysql', array('connect', '_execute', 'execute'));
		$this->Model = $this->getMock('Article2', array('getDataSource'));
		$this->Model->alias = 'Article';
		$this->Model->expects($this->any())
			->method('getDataSource')
			->will($this->returnValue($test));

		$this->assertEqual($this->Model->escapeField(), '`Article`.`id`');
		$result = $test->fields($this->Model, null, $this->Model->escapeField());
		$this->assertEqual($result, array('`Article`.`id`'));

		$test->expects($this->at(0))->method('execute')
			->with('SELECT `Article`.`id` FROM `articles` AS `Article`   WHERE 1 = 1');

		$result = $test->read($this->Model, array(
			'fields' => $this->Model->escapeField(),
			'conditions' => null,
			'recursive' => -1
		));

		$test->startQuote = '[';
		$test->endQuote = ']';
		$this->assertEqual($this->Model->escapeField(), '[Article].[id]');

		$result = $test->fields($this->Model, null, $this->Model->escapeField());
		$this->assertEqual($result, array('[Article].[id]'));

		$test->expects($this->at(0))->method('execute')
			->with('SELECT [Article].[id] FROM [' . $test->fullTableName('articles', false) . '] AS [Article]   WHERE 1 = 1');
		$result = $test->read($this->Model, array(
			'fields' => $this->Model->escapeField(),
			'conditions' => null,
			'recursive' => -1
		));
	}

/**
 * testGenerateAssociationQuerySelfJoin method
 *
 * @return void
 */
	function testGenerateAssociationQuerySelfJoin() {
		$this->testDb = $this->getMock('DboMysql', array('connect', '_execute', 'execute'));
		$this->startTime = microtime(true);
		$this->Model = new Article2();
		$this->_buildRelatedModels($this->Model);
		$this->_buildRelatedModels($this->Model->Category2);
		$this->Model->Category2->ChildCat = new Category2();
		$this->Model->Category2->ParentCat = new Category2();

		$queryData = array();

		foreach ($this->Model->Category2->associations() as $type) {
			foreach ($this->Model->Category2->{$type} as $assoc => $assocData) {
				$linkModel = $this->Model->Category2->{$assoc};
				$external = isset($assocData['external']);

				if ($this->Model->Category2->alias == $linkModel->alias && $type != 'hasAndBelongsToMany' && $type != 'hasMany') {
					$result = $this->testDb->generateAssociationQuery($this->Model->Category2, $linkModel, $type, $assoc, $assocData, $queryData, $external, $null);
					$this->assertFalse(empty($result));
				} else {
					if ($this->Model->Category2->useDbConfig == $linkModel->useDbConfig) {
						$result = $this->testDb->generateAssociationQuery($this->Model->Category2, $linkModel, $type, $assoc, $assocData, $queryData, $external, $null);
						$this->assertFalse(empty($result));
					}
				}
			}
		}

		$query = $this->testDb->generateAssociationQuery($this->Model->Category2, $null, null, null, null, $queryData, false, $null);
		$this->assertPattern('/^SELECT\s+(.+)FROM(.+)`Category2`\.`group_id`\s+=\s+`Group`\.`id`\)\s+LEFT JOIN(.+)WHERE\s+1 = 1\s*$/', $query);

		$this->Model = new TestModel4();
		$this->Model->schema();
		$this->_buildRelatedModels($this->Model);

		$binding = array('type' => 'belongsTo', 'model' => 'TestModel4Parent');
		$queryData = array();
		$resultSet = null;
		$null = null;

		$params = &$this->_prepareAssociationQuery($this->Model, $queryData, $binding);

		$_queryData = $queryData;
		$result = $this->testDb->generateAssociationQuery($this->Model, $params['linkModel'], $params['type'], $params['assoc'], $params['assocData'], $queryData, $params['external'], $resultSet);
		$this->assertTrue($result);

		$expected = array(
			'conditions' => array(),
			'fields' => array(
				'`TestModel4`.`id`',
				'`TestModel4`.`name`',
				'`TestModel4`.`created`',
				'`TestModel4`.`updated`',
				'`TestModel4Parent`.`id`',
				'`TestModel4Parent`.`name`',
				'`TestModel4Parent`.`created`',
				'`TestModel4Parent`.`updated`'
			),
			'joins' => array(
				array(
					'table' => '`test_model4`',
					'alias' => 'TestModel4Parent',
					'type' => 'LEFT',
					'conditions' => '`TestModel4`.`parent_id` = `TestModel4Parent`.`id`'
				)
			),
			'order' => array(),
			'limit' => array(),
			'offset' => array(),
			'group' => array()
		);
		$this->assertEqual($queryData, $expected);

		$result = $this->testDb->generateAssociationQuery($this->Model, $null, null, null, null, $queryData, false, $null);
		$this->assertPattern('/^SELECT\s+`TestModel4`\.`id`, `TestModel4`\.`name`, `TestModel4`\.`created`, `TestModel4`\.`updated`, `TestModel4Parent`\.`id`, `TestModel4Parent`\.`name`, `TestModel4Parent`\.`created`, `TestModel4Parent`\.`updated`\s+/', $result);
		$this->assertPattern('/FROM\s+`test_model4` AS `TestModel4`\s+LEFT JOIN\s+`test_model4` AS `TestModel4Parent`/', $result);
		$this->assertPattern('/\s+ON\s+\(`TestModel4`.`parent_id` = `TestModel4Parent`.`id`\)\s+WHERE/', $result);
		$this->assertPattern('/\s+WHERE\s+1 = 1\s+$/', $result);

		$params['assocData']['type'] = 'INNER';
		$this->Model->belongsTo['TestModel4Parent']['type'] = 'INNER';
		$result = $this->testDb->generateAssociationQuery($this->Model, $params['linkModel'], $params['type'], $params['assoc'], $params['assocData'], $_queryData, $params['external'], $resultSet);
		$this->assertTrue($result);
		$this->assertEqual($_queryData['joins'][0]['type'], 'INNER');
	}

/**
 * buildRelatedModels method
 *
 * @param mixed $model
 * @access protected
 * @return void
 */
	function _buildRelatedModels(&$model) {
		foreach ($model->associations() as $type) {
			foreach ($model->{$type} as $assoc => $assocData) {
				if (is_string($assocData)) {
					$className = $assocData;
				} elseif (isset($assocData['className'])) {
					$className = $assocData['className'];
				}
				$model->$className = new $className();
				$model->$className->schema();
			}
		}
	}

/**
 * &_prepareAssociationQuery method
 *
 * @param mixed $model
 * @param mixed $queryData
 * @param mixed $binding
 * @access public
 * @return void
 */
	function &_prepareAssociationQuery(&$model, &$queryData, $binding) {
		$type = $binding['type'];
		$assoc = $binding['model'];
		$assocData = $model->{$type}[$assoc];
		$className = $assocData['className'];

		$linkModel = $model->{$className};
		$external = isset($assocData['external']);
		$queryData = $this->Dbo->__scrubQueryData($queryData);

		$result = array_merge(array('linkModel' => &$linkModel), compact('type', 'assoc', 'assocData', 'external'));
		return $result;
	}

/**
 * testGenerateInnerJoinAssociationQuery method
 *
 * @access public
 * @return void
 */
	function testGenerateInnerJoinAssociationQuery() {
		$test = $this->getMock('DboMysql', array('connect', '_execute', 'execute'));
		$this->Model = $this->getMock('TestModel9', array('getDataSource'));
		$this->Model->expects($this->any())
			->method('getDataSource')
			->will($this->returnValue($test));

		$this->Model->TestModel8 = $this->getMock('TestModel8', array('getDataSource'));
		$this->Model->TestModel8->expects($this->any())
			->method('getDataSource')
			->will($this->returnValue($test));

		$test->expects($this->at(0))->method('execute')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/`TestModel9` LEFT JOIN `test_model8`/'));

		$test->expects($this->at(1))->method('execute')
			->with(new PHPUnit_Framework_Constraint_PCREMatch('/`TestModel9` INNER JOIN `test_model8`/'));

		$test->read($this->Model, array('recursive' => 1));
		$this->Model->belongsTo['TestModel8']['type'] = 'INNER';
		$test->read($this->Model, array('recursive' => 1));
	}

/**
 * testGenerateAssociationQuerySelfJoinWithConditionsInHasOneBinding method
 *
 * @access public
 * @return void
 */
	function testGenerateAssociationQuerySelfJoinWithConditionsInHasOneBinding() {
		$this->Model = new TestModel8();
		$this->Model->schema();
		$this->_buildRelatedModels($this->Model);

		$binding = array('type' => 'hasOne', 'model' => 'TestModel9');
		$queryData = array();
		$resultSet = null;
		$null = null;

		$params = &$this->_prepareAssociationQuery($this->Model, $queryData, $binding);
		$_queryData = $queryData;
		$result = $this->Dbo->generateAssociationQuery($this->Model, $params['linkModel'], $params['type'], $params['assoc'], $params['assocData'], $queryData, $params['external'], $resultSet);
		$this->assertTrue($result);

		$result = $this->Dbo->generateAssociationQuery($this->Model, $null, null, null, null, $queryData, false, $null);
		$this->assertPattern('/^SELECT\s+`TestModel8`\.`id`, `TestModel8`\.`test_model9_id`, `TestModel8`\.`name`, `TestModel8`\.`created`, `TestModel8`\.`updated`, `TestModel9`\.`id`, `TestModel9`\.`test_model8_id`, `TestModel9`\.`name`, `TestModel9`\.`created`, `TestModel9`\.`updated`\s+/', $result);
		$this->assertPattern('/FROM\s+`test_model8` AS `TestModel8`\s+LEFT JOIN\s+`test_model9` AS `TestModel9`/', $result);
		$this->assertPattern('/\s+ON\s+\(`TestModel9`\.`name` != \'mariano\'\s+AND\s+`TestModel9`.`test_model8_id` = `TestModel8`.`id`\)\s+WHERE/', $result);
		$this->assertPattern('/\s+WHERE\s+(?:\()?1\s+=\s+1(?:\))?\s*$/', $result);
	}

/**
 * testGenerateAssociationQuerySelfJoinWithConditionsInBelongsToBinding method
 *
 * @access public
 * @return void
 */
	function testGenerateAssociationQuerySelfJoinWithConditionsInBelongsToBinding() {
		$this->Model = new TestModel9();
		$this->Model->schema();
		$this->_buildRelatedModels($this->Model);

		$binding = array('type' => 'belongsTo', 'model' => 'TestModel8');
		$queryData = array();
		$resultSet = null;
		$null = null;

		$params = &$this->_prepareAssociationQuery($this->Model, $queryData, $binding);
		$result = $this->Dbo->generateAssociationQuery($this->Model, $params['linkModel'], $params['type'], $params['assoc'], $params['assocData'], $queryData, $params['external'], $resultSet);
		$this->assertTrue($result);

		$result = $this->Dbo->generateAssociationQuery($this->Model, $null, null, null, null, $queryData, false, $null);
		$this->assertPattern('/^SELECT\s+`TestModel9`\.`id`, `TestModel9`\.`test_model8_id`, `TestModel9`\.`name`, `TestModel9`\.`created`, `TestModel9`\.`updated`, `TestModel8`\.`id`, `TestModel8`\.`test_model9_id`, `TestModel8`\.`name`, `TestModel8`\.`created`, `TestModel8`\.`updated`\s+/', $result);
		$this->assertPattern('/FROM\s+`test_model9` AS `TestModel9`\s+LEFT JOIN\s+`test_model8` AS `TestModel8`/', $result);
		$this->assertPattern('/\s+ON\s+\(`TestModel8`\.`name` != \'larry\'\s+AND\s+`TestModel9`.`test_model8_id` = `TestModel8`.`id`\)\s+WHERE/', $result);
		$this->assertPattern('/\s+WHERE\s+(?:\()?1\s+=\s+1(?:\))?\s*$/', $result);
	}
}
