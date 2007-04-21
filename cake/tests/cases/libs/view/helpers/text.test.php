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
 * @subpackage		cake.tests.cases.libs.view.helpers
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
	require_once LIBS.'../app_helper.php';
	require_once LIBS.DS.'view'.DS.'helper.php';
	require_once LIBS.DS.'view'.DS.'helpers'.DS.'text.php';
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.view.helpers
 */
class TextTest extends UnitTestCase {
	var $helper = null;
	
	
	function setUp() {
		$this->helper = new TextHelper();
	}

	function testHighlight() {
		$text = 'This is a test text';
		$phrases = array('This', 'text');
		$result = $this->helper->highlight($text, $phrases, '<b>\1</b>');
		$expected = '<b>This</b> is a test <b>text</b>';
		$this->assertEqual($expected, $result);
	}
	
	function testHighlightCaseInsensitivity() {
		$text = 'This is a Test text';
		$expected = 'This is a <b>Test</b> text';
		
		$result = $this->helper->highlight($text, 'test', '<b>\1</b>');
		$this->assertEqual($expected, $result);
		
		$result = $this->helper->highlight($text, array('test'), '<b>\1</b>');
		$this->assertEqual($expected, $result);
	}

	function tearDown() {
		unset($this->helper);
	}
}

?>