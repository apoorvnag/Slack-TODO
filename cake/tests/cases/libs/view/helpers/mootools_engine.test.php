<?php
/**
 * MooEngineTestCase
 *
 *
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2006-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright       Copyright 2006-2008, Cake Software Foundation, Inc.
 * @link            http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package         cake.tests
 * @subpackage      cake.tests.cases.views.helpers
 * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Helper', array('Html', 'Js', 'MootoolsEngine'));

class MooEngineHelperTestCase extends CakeTestCase {
/**
 * startTest
 *
 * @return void
 **/
	function startTest() {
		$this->Moo =& new MootoolsEngineHelper();
	}
/**
 * end test
 *
 * @return void
 **/
	function endTest() {
		unset($this->Moo);
	}
/**
 * test selector method
 *
 * @return void
 **/
	function testSelector() {
		$result = $this->Moo->get('#content');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$('content')");
		
		$result = $this->Moo->get('a .remove');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$$('a .remove')");
		
		$result = $this->Moo->get('document');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$(document)");
		
		$result = $this->Moo->get('window');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$(window)");
		
		$result = $this->Moo->get('ul');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$$('ul')");
		
		$result = $this->Moo->get('#some_long-id.class');
		$this->assertEqual($result, $this->Moo);
		$this->assertEqual($this->Moo->selection, "$$('#some_long-id.class')");
	}
/**
 * test event binding
 *
 * @return void
 **/
	function testEvent() {
		$result = $this->Moo->get('#myLink')->event('click', 'doClick', array('wrap' => false));
		$expected = "$('myLink').addEvent('click', doClick);";
		$this->assertEqual($result, $expected);

		$result = $this->Moo->get('#myLink')->event('click', 'this.setStyle("display", "");', array('stop' => false));
		$expected = "$('myLink').addEvent('click', function (event) {this.setStyle(\"display\", \"\");});";
		$this->assertEqual($result, $expected);

		$result = $this->Moo->get('#myLink')->event('click', 'this.setStyle("display", "none");');
		$expected = "\$('myLink').addEvent('click', function (event) {this.setStyle(\"display\", \"none\");\nreturn false;});";
		$this->assertEqual($result, $expected);
	}
/**
 * test dom ready event creation
 *
 * @return void
 **/
	function testDomReady() {

	}
/**
 * test Each method
 *
 * @return void
 **/
	function testEach() {

	}
/**
 * test Effect generation
 *
 * @return void
 **/
	function testEffect() {

	}
/**
 * Test Request Generation
 *
 * @return void
 **/
	function testRequest() {

	}
}
?>