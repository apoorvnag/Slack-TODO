<?php
/**
 * CakeTextReporter contains reporting features used for plain text based output
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.tests.libs.reporter
 * @since         CakePHP(tm) v 1.3
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */

include_once dirname(__FILE__) . DS . 'cake_base_reporter.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'DEFAULT');

/**
 * CakeTextReporter contains reporting features used for plain text based output
 *
 * @package cake
 * @subpackage cake.tests.lib
 */
class CakeTextReporter extends CakeBaseReporter {

/**
 * Sets the text/plain header if the test is not a CLI test.
 *
 * @return void
 */
	public function paintDocumentStart() {
		if (!headers_sent()) {
			header('Content-type: text/plain');
		}
	}

/**
 * Paints a pass
 *
 * @return void
 */
	public function paintPass() {
		echo '.';
	}

/**
 * Paints a failing test.
 *
 * @param $message PHPUnit_Framework_AssertionFailedError $message Failure object displayed in
 *   the context of the other tests.
 * @return void
 */
	public function paintFail($message) {
		$context = $message->getTrace();
		$realContext = $context[3];
		$context = $context[2];

		printf(
			"FAIL on line %s\n%s in\n%s %s()\n\n", 
			$context['line'], $message->toString(), $context['file'], $realContext['function']
		);
	}

/**
 * Paints the end of the test with a summary of
 * the passes and failures.
 *
 * @param PHPUnit_Framework_TestResult $result Result object
 * @return void
 */
	public function paintFooter($result) {
		if ($result->failureCount() + $result->errorCount() == 0) {
			echo "\nOK\n";
		} else {
			echo "FAILURES!!!\n";
		}

		echo "Test cases run: " . $result->count() . 
			"/" . ($result->count() - $result->skippedCount()) .
			', Passes: ' . $this->numAssertions .
			', Failures: ' . $result->failureCount() .
			', Exceptions: ' . $result->errorCount() . "\n";

		echo 'Time taken by tests (in seconds): ' . $result->time() . "\n";
		if (function_exists('memory_get_peak_usage')) {
			echo 'Peak memory use: (in bytes): ' . number_format(memory_get_peak_usage()) . "\n";
		}
		if (isset($this->params['codeCoverage']) && $this->params['codeCoverage']) {
			$coverage = $result->getCodeCoverageInformation();
			echo $this->paintCoverage($coverage);
		}
	}

/**
 * Paints the title only.
 *
 * @param string $test_name Name class of test.
 * @return void
 */
	public function paintHeader() {
		$this->paintDocumentStart();
		flush();
	}

/**
 * Paints a PHP exception.
 *
 * @param Exception $exception Exception to describe.
 * @return void
 */
	public function paintException($exception) {
		$message = 'Unexpected exception of type [' . get_class($exception) .
			'] with message ['. $exception->getMessage() .
			'] in ['. $exception->getFile() .
			' line ' . $exception->getLine() . ']';
		echo $message . "\n\n";
	}

/**
 * Prints the message for skipping tests.
 *
 * @param string $message Text of skip condition.
 * @return void
 */
	public function paintSkip($message) {
		parent::paintSkip($message);
		echo "Skip: $message\n";
	}

/**
 * Paints formatted text such as dumped variables.
 *
 * @param string $message Text to show.
 * @return void
 */
	public function paintFormattedMessage($message) {
		echo "$message\n";
		flush();
	}

/**
 * Generate a test case list in plain text.
 * Creates as series of url's for tests that can be run.
 * One case per line.
 *
 * @return void
 */
	public function testCaseList() {
		$testCases = parent::testCaseList();
		$app = $this->params['app'];
		$plugin = $this->params['plugin'];

		$buffer = "Core Test Cases:\n";
		$urlExtra = '';
		if ($app) {
			$buffer = "App Test Cases:\n";
			$urlExtra = '&app=true';
		} elseif ($plugin) {
			$buffer = Inflector::humanize($plugin) . " Test Cases:\n";
			$urlExtra = '&plugin=' . $plugin;
		}

		if (1 > count($testCases)) {
			$buffer .= "EMPTY";
			echo $buffer;
		}

		foreach ($testCases as $testCaseFile => $testCase) {
			$buffer .= $_SERVER['SERVER_NAME'] . $this->baseUrl() ."?case=" . $testCase . "&output=text"."\n";
		}

		$buffer .= "\n";
		echo $buffer;
	}

/**
 * Generates a Text summary of the coverage data.
 *
 * @param array $coverage Array of coverage data.
 * @return string
 */
	public function paintCoverage($coverage) {
		$file = dirname(dirname(__FILE__)) . '/coverage/text_coverage_report.php';
		include $file;
		$reporter = new TextCoverageReport($coverage, $this);
		echo $reporter->report();
	}

}
