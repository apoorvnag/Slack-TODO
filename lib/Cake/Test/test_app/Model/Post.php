<?php
/**
 * Test App Comment Model
 *
 *
 *
 * PHP 5
 *
 * CakePHP : Rapid Development Framework (http://cakephp.org)
 * Copyright 2006-2010, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2006-2010, Cake Software Foundation, Inc.
 * @link          http://cakephp.org CakePHP Project
 * @package       cake.libs.
 * @since         CakePHP v 1.2.0.7726
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Post extends AppModel {
	public $useTable = 'posts';
	public $name = 'Post';
	public $validate = array(
		'title' => array(
			'rule' => array('custom', '.*'),
			'allowEmpty' => true,
			'required' => false,
			'message' => 'Post title is required'
		),
		'body' => array(
			'first_rule' => array(
				'rule' => array('custom', '.*'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Post body is required'
			),
			'second_rule' => array(
				'rule' => array('custom', '.*'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Post body is super required'
			)
		),
	);
}
