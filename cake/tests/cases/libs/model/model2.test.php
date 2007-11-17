<?php
/* SVN FILE: $Id: model.test.php 5912 2007-10-28 04:18:18Z gwoo $ */
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
 * @subpackage		cake.tests.cases.libs.model
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 5912 $
 * @modifiedby		$LastChangedBy: gwoo $
 * @lastmodified	$Date: 2007-10-28 00:18:18 -0400 (Sun, 28 Oct 2007) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}
uses('model'.DS.'model', 'model'.DS.'datasources'.DS.'datasource', 'model'.DS.'datasources'.DS.'dbo_source',
	'model'.DS.'datasources'.DS.'dbo'.DS.'dbo_mysql');

if (!class_exists('AppModel')) {
	loadModel();
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Test extends Model {
	var $useTable = false;
	var $name = 'Test';

	function schema() {
		return array(
			'id'=> array('type' => 'integer', 'null' => '', 'default' => '1', 'length' => '8', 'key'=>'primary'),
			'name'=> array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
			'email'=> array('type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'),
			'notes'=> array('type' => 'text', 'null' => '1', 'default' => 'write some notes here', 'length' => ''),
			'created'=> array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
			'updated'=> array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null));
	}
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class TestValidate extends Model {
	var $useTable = false;
	var $name = 'TestValidate';

	function validateNumber($value, $options) {
		$options = am(array('min' => 0, 'max' => 100), $options);
		$valid = ($value >= $options['min'] && $value <= $options['max']);
		return $valid;
	}

	function validateTitle($title) {
		if (!empty($title) && strpos(low($title), 'title-') === 0) {
			return true;
		}
		return false;
	}

	function schema() {
		return array(
			'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
			'title' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
			'body' => array('type' => 'string', 'null' => '1', 'default' => '', 'length' => ''),
			'number' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
			'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
			'modified' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
		);
	}
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class User extends CakeTestModel {
	var $name = 'User';
	var $validate = array('user' => VALID_NOT_EMPTY, 'password' => VALID_NOT_EMPTY);
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Article extends CakeTestModel {
	var $name = 'Article';
	var $belongsTo = array('User');
	var $hasMany = array('Comment' => array('className'=>'Comment', 'dependent' => true));
	var $hasAndBelongsToMany = array('Tag');
	var $validate = array('user_id' => VALID_NUMBER, 'title' => array('allowEmpty' => false, 'rule' => VALID_NOT_EMPTY), 'body' => VALID_NOT_EMPTY);

	function titleDuplicate ($title) {
		if ($title === 'My Article Title') {
			return false;
		}
		return true;
	}
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class ArticleFeatured extends CakeTestModel {
	var $name = 'ArticleFeatured';
	var $belongsTo = array('User', 'Category');
	var $hasOne = array('Featured');
	var $hasMany = array('Comment' => array('className'=>'Comment', 'dependent' => true));
	var $hasAndBelongsToMany = array('Tag');
	var $validate = array('user_id' => VALID_NUMBER, 'title' => VALID_NOT_EMPTY, 'body' => VALID_NOT_EMPTY);
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Featured extends CakeTestModel {
	var $name = 'Featured';
	var $belongsTo = array(
		'ArticleFeatured'=> array('className' => 'ArticleFeatured'),
		'Category'=> array('className' => 'Category')
	);
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Tag extends CakeTestModel {
	var $name = 'Tag';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class ArticlesTag extends CakeTestModel {
	var $name = 'ArticlesTag';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class ArticleFeaturedsTag extends CakeTestModel {
	var $name = 'ArticleFeaturedsTag';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Comment extends CakeTestModel {
	var $name = 'Comment';
	var $belongsTo = array('Article', 'User');
	var $hasOne = array('Attachment' => array('className'=>'Attachment', 'dependent' => true));
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Attachment extends CakeTestModel {
	var $name = 'Attachment';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Category extends CakeTestModel {
	var $name = 'Category';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class CategoryThread extends CakeTestModel {
	var $name = 'CategoryThread';
	var $belongsTo = array('ParentCategory' => array('className' => 'CategoryThread', 'foreignKey' => 'parent_id'));
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Apple extends CakeTestModel {
	var $name = 'Apple';
	var $validate = array('name' => VALID_NOT_EMPTY);
	var $hasOne = array('Sample');
	var $hasMany = array('Child' => array('className' => 'Apple', 'dependent' => true));
	var $belongsTo = array('Parent' => array('className' => 'Apple', 'foreignKey' => 'apple_id'));
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Sample extends CakeTestModel {
	var $name = 'Sample';
	var $belongsTo = 'Apple';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class AnotherArticle extends CakeTestModel {
	var $name = 'AnotherArticle';
	var $hasMany = 'Home';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Advertisement extends CakeTestModel {
	var $name = 'Advertisement';
	var $hasMany = 'Home';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Home extends CakeTestModel {
	var $name = 'Home';
	var $belongsTo = array('AnotherArticle', 'Advertisement');
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Post extends CakeTestModel {
	var $name = 'Post';
	var $belongsTo = array('Author');
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Author extends CakeTestModel {
	var $name = 'Author';
	var $hasMany = array('Post');

	function afterFind($results) {
		$results[0]['Author']['test'] = 'working';
		return $results;
	}
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Project extends CakeTestModel {
	var $name = 'Project';
	var $hasMany = array('Thread');
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Thread extends CakeTestModel {
	var $name = 'Thread';
	var $hasMany = array('Message');
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Message extends CakeTestModel {
	var $name = 'Message';
	var $hasOne = array('Bid');
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class Bid extends CakeTestModel {
	var $name = 'Bid';
	var $belongsTo = array('Message');
}
class NodeAfterFind extends CakeTestModel {
	var $name = 'NodeAfterFind';
	var $validate = array('name' => VALID_NOT_EMPTY);
	var $useTable = 'apples';
	var $hasOne = array('Sample' => array('className' => 'NodeAfterFindSample'));
	var $hasMany = array('Child' => array('className' => 'NodeAfterFind', 'dependent' => true));
	var $belongsTo = array('Parent' => array('className' => 'NodeAfterFind', 'foreignKey' => 'apple_id'));

	function afterFind($results) {
		return $results;
	}
}
class NodeAfterFindSample extends CakeTestModel {
	var $name = 'NodeAfterFindSample';
	var $useTable = 'samples';
	var $belongsTo = 'NodeAfterFind';
}
class NodeNoAfterFind extends CakeTestModel {
	var $name = 'NodeAfterFind';
	var $validate = array('name' => VALID_NOT_EMPTY);
	var $useTable = 'apples';
	var $hasOne = array('Sample' => array('className' => 'NodeAfterFindSample'));
	var $hasMany = array('Child' => array( 'className' => 'NodeAfterFind', 'dependent' => true));
	var $belongsTo = array('Parent' => array('className' => 'NodeAfterFind', 'foreignKey' => 'apple_id'));
}
class ModelA extends CakeTestModel {
	var $name = 'ModelA';
	var $useTable = 'apples';
	var $hasMany = array('ModelB', 'ModelC');
}
class ModelB extends CakeTestModel {
	var $name = 'ModelB';
	var $useTable = 'messages';
	var $hasMany = array('ModelD');
}
class ModelC extends CakeTestModel {
	var $name = 'ModelC';
	var $useTable = 'bids';
	var $hasMany = array('ModelD');
}
class ModelD extends CakeTestModel {
	var $useTable = 'threads';
}
class Portfolio extends CakeTestModel {
	var $name = 'Portfolio';
	var $hasAndBelongsToMany = array('Item');
}
class Item extends CakeTestModel {
	var $name = 'Item';
	var $belongsTo = array('Syfile');
	var $hasAndBelongsToMany = array('Portfolio');
}
class ItemsPortfolio extends CakeTestModel {
	var $name = 'ItemsPortfolio';
}
class Syfile extends CakeTestModel {
	var $name = 'Syfile';
	var $belongsTo = array('Image');
}
class Image extends CakeTestModel {
	var $name = 'Image';
}
class DeviceType extends CakeTestModel {
	var $name = 'DeviceType';
	var $order = array('DeviceType.order' => 'ASC');
	var $belongsTo = array(
		'DeviceTypeCategory', 'FeatureSet', 'ExteriorTypeCategory',
		'Image' => array('className' => 'Document'),
		'Extra1' => array('className' => 'Document'),
		'Extra2' => array('className' => 'Document'));
	var $hasMany = array('Device' => array('order' => array('Device.id' => 'ASC')));
}
class DeviceTypeCategory extends CakeTestModel {
	var $name = 'DeviceTypeCategory';
}
class FeatureSet extends CakeTestModel {
	var $name = 'FeatureSet';
}
class ExteriorTypeCategory extends CakeTestModel {
	var $name = 'ExteriorTypeCategory';
	var $belongsTo = array('Image' => array('className' => 'Device'));
}
class Document extends CakeTestModel {
	var $name = 'Document';
	var $belongsTo = array('DocumentDirectory');
}
class Device extends CakeTestModel {
	var $name = 'Device';
}
class DocumentDirectory extends CakeTestModel {
	var $name = 'DocumentDirectory';
}
class PrimaryModel extends CakeTestModel {
	var $name = 'PrimaryModel';
}
class SecondaryModel extends CakeTestModel {
	var $name = 'SecondaryModel';
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class ModelTest extends CakeTestCase {

	var $fixtures = array(
		'core.category', 'core.category_thread', 'core.user', 'core.article', 'core.featured', 'core.article_featureds_tags',
		'core.article_featured', 'core.articles', 'core.tag', 'core.articles_tag', 'core.comment', 'core.attachment',
		'core.apple', 'core.sample', 'core.another_article', 'core.advertisement', 'core.home', 'core.post', 'core.author',
		'core.project', 'core.thread', 'core.message', 'core.bid',
		'core.portfolio', 'core.item', 'core.items_portfolio', 'core.syfile', 'core.image',
		'core.device_type', 'core.device_type_category', 'core.feature_set', 'core.exterior_type_category', 'core.document', 'core.device', 'core.document_directory',
		'core.primary_model', 'core.secondary_model'
	);

	function start() {
		parent::start();
		$this->debug = Configure::read('debug');
		Configure::write('debug', 2);
	}

	function end() {
		parent::end();
		Configure::write('debug', $this->debug);
	}

	function testNormalizeFindParams() {
		$this->model =& new Article();

		$result = $this->model->normalizeFindParams('fields', array(
			'title', 'body', 'published', 'Article.id',
			'User', 'Comment.id', 'Comment.comment', 'Comment.User.password', 'Comment.Article',
			'Tag' => array('id', 'tag'))
		);

		$expected = array(
			'Article' => array(
				'fields'	=> array('id', 'title', 'body', 'published'),
				'User'		=> array('fields' => null),
				'Comment'	=> array(
					'fields' => array('id', 'comment'),
					'User'	=> array('fields' => array('password')),
					'Article' => array('fields' => null)
				),
				'Tag'		=> array('fields' => array('id', 'tag'))
			)
		);
		$this->assertEqual($result, $expected);
	}


	function endTest() {
		ClassRegistry::flush();
	}
}
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.model
 */
class ValidationTest extends CakeTestModel {
	var $name = 'ValidationTest';
	var $useTable = false;

	var $validate = array(
		'title' => VALID_NOT_EMPTY,
		'published' => 'customValidationMethod',
		'body' => array(
			VALID_NOT_EMPTY,
			'/^.{5,}$/s' => 'no matchy',
			'/^[0-9A-Za-z \\.]{1,}$/s'
		)
	);

	function customValidationMethod($data) {
		return $data === 1;
	}

	function schema() {
		return array();
	}
}

?>