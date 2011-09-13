<?php
/**
 * BasicMethodTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       Cake.Test.Case.Network.Http
 * @since         CakePHP(tm) v 2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('HttpSocket', 'Network/Http');
App::uses('BasicAuthentication', 'Network/Http');

/**
 * BasicMethodTest class
 *
 * @package       Cake.Test.Case.Network.Http
 */
class BasicAuthenticationTest extends CakeTestCase {

/**
 * testAuthentication method
 *
 * @return void
 */
	public function testAuthentication() {
		$http = new HttpSocket();
		$auth = array(
			'method' => 'Basic',
			'user' => 'mark',
			'pass' => 'secret'
		);

		BasicAuthentication::authentication($http, $auth);
		$this->assertEqual($http->request['header']['Authorization'], 'Basic bWFyazpzZWNyZXQ=');
	}

/**
 * testProxyAuthentication method
 *
 * @return void
 */
	public function testProxyAuthentication() {
		$http = new HttpSocket();
		$proxy = array(
			'method' => 'Basic',
			'user' => 'mark',
			'pass' => 'secret'
		);

		BasicAuthentication::proxyAuthentication($http, $proxy);
		$this->assertEqual($http->request['header']['Proxy-Authorization'], 'Basic bWFyazpzZWNyZXQ=');
	}

}
