<?php
/**
 * Send mail using mail() function
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake.libs.email
 * @since         CakePHP(tm) v 2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Mail class
 *
 * @package       cake.libs.email
 */
class MailTransport extends AbstractTransport {

/**
 * Send mail
 *
 * @params object $email CakeEmail
 * @return boolean
 */
	public function send(CakeEmail $email) {
		$eol = Configure::read('Email.Mail.EOL');
		if (!$eol) {
			$eol = PHP_EOL;
		}
		$headers = $email->getHeaders(array_fill_keys(array('from', 'replyTo', 'readReceipt', 'returnPath', 'to', 'cc', 'bcc'), true));
		$to = $headers['To'];
		unset($headers['To']);
		$header = $this->_headersToString($headers, $eol);
		$message = implode($eol, $email->message());
		if (ini_get('safe_mode')) {
			return @mail($to, $email->subject(), $message, $header);
		}
		return @mail($to, $email->subject(), $message, $header, (string)Configure::read('Email.Mail.AdditionalParameters'));
	}

}
