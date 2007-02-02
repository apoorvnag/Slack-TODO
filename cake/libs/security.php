<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs
 * @since			CakePHP(tm) v .0.10.0.1233
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class Security extends Object{
/**
  * Enter description here...
  *
  * @return unknown
  */
	function &getInstance() {
		static $instance = array();
	 	if (!$instance) {
	 		$instance[0] =& new Security;
	 	}
	 	return $instance[0];
	}
/**
  * Enter description here...
  *
  * @return unknown
  */
	function inactiveMins() {
		$_this =& Security::getInstance();
		switch(CAKE_SECURITY) {
			case 'high':
				return 10;
			break;
			case 'medium':
				return 100;
			break;
			case 'low':
			default:
				return 300;
				break;
		}
	}
/**
  * Enter description here...
  *
  * @return unknown
  */
	function generateAuthKey() {
		$_this =& Security::getInstance();
		return $_this->hash(uniqid(rand(), true));
	}
/**
 * Enter description here...
 *
 * @param unknown_type $authKey
 * @return unknown
 */
	function validateAuthKey($authKey) {
		$_this =& Security::getInstance();
		return true;
	}
/**
 * Enter description here...
 *
 * @param unknown_type $string
 * @param unknown_type $type
 * @return unknown
 */
	function hash($string, $type = 'sha1') {
		$_this =& Security::getInstance();
		$type = strtolower($type);
		if ($type == 'sha1') {
			if (function_exists('sha1')) {
				$return = sha1($string);
				return $return;
			} else {
				$type = 'sha256';
			}
		}

		if ($type == 'sha256') {
			if (function_exists('mhash')) {
				$return = bin2hex(mhash(MHASH_SHA256, $string));
				return $return;
			} else {
				$type = 'md5';
	 		}
		}

		if ($type == 'md5') {
			$return = md5($string);
			return $return;
		}
	}
/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $key
 * @return unknown
 */
	function cipher($text, $key) {
		$_this =& Security::getInstance();
		if (!defined('CIPHER_SEED')) {
			//This is temporary will change later
			define('CIPHER_SEED', '76859309657453542496749683645');
		}
		srand (CIPHER_SEED);
		$out = '';

		for($i = 0; $i < strlen($text); $i++) {
			for($j = 0; $j < ord(substr($key, $i % strlen($key), 1)); $j++) {
				$toss = rand(0, 255);
			}
			$mask = rand(0, 255);
			$out .= chr(ord(substr($text, $i, 1)) ^ $mask);
		}
		return $out;
	}
}
?>