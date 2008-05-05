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
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs.view.helpers
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

uses('view'.DS.'helpers'.DS.'app_helper', 'controller'.DS.'controller', 'model'.DS.'model', 'view'.DS.'helper', 'view'.DS.'helpers'.DS.'time');

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.view.helpers
 */
class TimeTest extends UnitTestCase {

	function setUp() {
		$this->Time = new TimeHelper();
	}

	function testToQuarter() {
		$result = $this->Time->toQuarter('2007-12-25');
		$this->assertEqual($result, 4);

		$result = $this->Time->toQuarter('2007-9-25');
		$this->assertEqual($result, 3);

		$result = $this->Time->toQuarter('2007-3-25');
		$this->assertEqual($result, 1);

		$result = $this->Time->toQuarter('2007-3-25', true);
		$this->assertEqual($result, array('2007-01-01', '2007-03-31'));

		$result = $this->Time->toQuarter('2007-5-25', true);
		$this->assertEqual($result, array('2007-04-01', '2007-06-30'));

		$result = $this->Time->toQuarter('2007-8-25', true);
		$this->assertEqual($result, array('2007-07-01', '2007-09-30'));

		$result = $this->Time->toQuarter('2007-12-25', true);
		$this->assertEqual($result, array('2007-10-01', '2007-12-31'));
	}

	function testTimeAgoInWords() {
		$result = $this->Time->timeAgoInWords(strtotime('4 months, 2 weeks, 3 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '4 months, 2 weeks, 3 days');

		$result = $this->Time->timeAgoInWords(strtotime('4 months, 2 weeks, 2 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '4 months, 2 weeks, 2 days');

		$result = $this->Time->timeAgoInWords(strtotime('4 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '4 months, 2 weeks, 1 day');
	
		$result = $this->Time->timeAgoInWords(strtotime('3 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 2 weeks, 1 day');

		$result = $this->Time->timeAgoInWords(strtotime('3 months, 2 weeks'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 2 weeks');

		$result = $this->Time->timeAgoInWords(strtotime('3 months, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 1 week, 6 days');

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 2 weeks, 1 day');

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 weeks'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 2 weeks');

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 1 week, 6 days');

		$result = $this->Time->timeAgoInWords(strtotime('1 month, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '1 month, 1 week, 6 days');

		for($i = 0; $i < 200; $i ++) {
			$years = rand(0, 3);
			$months = rand(0, 11);
			$weeks = rand(0, 3);
			$days = rand(0, 6);
			$hours = 0;
			$minutes = 0;
			$seconds = 0;
			$relative_date = '';

			if ($years > 0) {
				// years and months and days
				$relative_date .= ($relative_date ? ', -' : '-') . $years . ' year' . ($years > 1 ? 's' : '');
				$relative_date .= $months > 0 ? ($relative_date ? ', -' : '-') . $months . ' month' . ($months > 1 ? 's' : '') : '';
				$relative_date .= $weeks > 0 ? ($relative_date ? ', -' : '-') . $weeks . ' week' . ($weeks > 1 ? 's' : '') : '';	
				$relative_date .= $days > 0 ? ($relative_date ? ', -' : '-') . $days . ' day' . ($days > 1 ? 's' : '') : '';						
			} elseif (abs($months) > 0) {
				// months, weeks and days
				$relative_date .= ($relative_date ? ', -' : '-') . $months . ' month' . ($months > 1 ? 's' : '');
				$relative_date .= $weeks > 0 ? ($relative_date ? ', -' : '-') . $weeks . ' week' . ($weeks > 1 ? 's' : '') : '';
				$relative_date .= $days > 0 ? ($relative_date ? ', -' : '-') . $days . ' day' . ($days > 1 ? 's' : '') : '';
			} elseif (abs($weeks) > 0) {
				// weeks and days
				$relative_date .= ($relative_date ? ', -' : '-') . $weeks . ' week' . ($weeks > 1 ? 's' : '');
				$relative_date .= $days > 0 ? ($relative_date ? ', -' : '-') . $days . ' day' . ($days > 1 ? 's' : '') : '';
			} elseif (abs($days) > 0) {
				// days and hours
				$relative_date .= ($relative_date ? ', -' : '-') . $days . ' day' . ($days > 1 ? 's' : '');
				$relative_date .= $hours > 0 ? ($relative_date ? ', -' : '-') . $hours . ' hour' . ($hours > 1 ? 's' : '') : '';
			} elseif (abs($hours) > 0) {
				// hours and minutes
				$relative_date .= ($relative_date ? ', -' : '-') . $hours . ' hour' . ($hours > 1 ? 's' : '');
				$relative_date .= $minutes > 0 ? ($relative_date ? ', -' : '-') . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '';
			} elseif (abs($minutes) > 0) {
				// minutes only
				$relative_date .= ($relative_date ? ', -' : '-') . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
			} else {
				// seconds only
				$relative_date .= ($relative_date ? ', -' : '-') . $seconds . ' second' . ($seconds != 1 ? 's' : '');
			}

			if (date('j/n/y', strtotime($relative_date)) != '1/1/70') {
				$result = $this->Time->timeAgoInWords(strtotime($relative_date), array('end' => '8 years'), true);
				if ($relative_date == '0 seconds') {
					$relative_date = '0 seconds ago';
				}

				$relative_date = str_replace('-', '', $relative_date) . ' ago';
				$this->assertEqual($result, $relative_date);

			}
		}

		for ($i = 0; $i < 200; $i ++) {
			$years = rand(0, 3);
			$months = rand(0, 11);
			$weeks = rand(0, 3);
			$days = rand(0, 6);
			$hours = 0;
			$minutes = 0;
			$seconds = 0;

			$relative_date = '';

			if ($years > 0) {
				// years and months and days
				$relative_date .= ($relative_date ? ', ' : '') . $years . ' year' . ($years > 1 ? 's' : '');
				$relative_date .= $months > 0 ? ($relative_date ? ', ' : '') . $months . ' month' . ($months > 1 ? 's' : '') : '';
				$relative_date .= $weeks > 0 ? ($relative_date ? ', ' : '') . $weeks . ' week' . ($weeks > 1 ? 's' : '') : '';	
				$relative_date .= $days > 0 ? ($relative_date ? ', ' : '') . $days . ' day' . ($days > 1 ? 's' : '') : '';
			} elseif (abs($months) > 0) {
				// months, weeks and days
				$relative_date .= ($relative_date ? ', ' : '') . $months . ' month' . ($months > 1 ? 's' : '');
				$relative_date .= $weeks > 0 ? ($relative_date ? ', ' : '') . $weeks . ' week' . ($weeks > 1 ? 's' : '') : '';
				$relative_date .= $days > 0 ? ($relative_date ? ', ' : '') . $days . ' day' . ($days > 1 ? 's' : '') : '';
			} elseif (abs($weeks) > 0) {
				// weeks and days
				$relative_date .= ($relative_date ? ', ' : '') . $weeks . ' week' . ($weeks > 1 ? 's' : '');
				$relative_date .= $days > 0 ? ($relative_date ? ', ' : '') . $days . ' day' . ($days > 1 ? 's' : '') : '';
			} elseif (abs($days) > 0) {
				// days and hours
				$relative_date .= ($relative_date ? ', ' : '') . $days . ' day' . ($days > 1 ? 's' : '');
				$relative_date .= $hours > 0 ? ($relative_date ? ', ' : '') . $hours . ' hour' . ($hours > 1 ? 's' : '') : '';
			} elseif (abs($hours) > 0) {
				// hours and minutes
				$relative_date .= ($relative_date ? ', ' : '') . $hours . ' hour' . ($hours > 1 ? 's' : '');
				$relative_date .= $minutes > 0 ? ($relative_date ? ', ' : '') . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '';
			} elseif (abs($minutes) > 0) {
				// minutes only
				$relative_date .= ($relative_date ? ', ' : '') . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
			} else {
				// seconds only
				$relative_date .= ($relative_date ? ', ' : '') . $seconds . ' second' . ($seconds != 1 ? 's' : '');
			}

			if (date('j/n/y', strtotime($relative_date)) != '1/1/70') {
				// echo $relative_date."<br />";
				$result = $this->Time->timeAgoInWords(strtotime($relative_date), array('end' => '8 years'), true);
				if ($relative_date == '0 seconds') {
					$relative_date = '0 seconds ago';
				}

				$relative_date = str_replace('-', '', $relative_date) . '';
				$this->assertEqual($result, $relative_date);
			}
		}
		
		$result = $this->Time->timeAgoInWords(strtotime('-2 years, -5 months, -2 days'), array('end' => '3 years'), true);
		$this->assertEqual($result, '2 years, 5 months, 2 days ago');

		$result = $this->Time->timeAgoInWords('2007-9-25');
		$this->assertEqual($result, 'on 25/9/07');

		$result = $this->Time->timeAgoInWords('2007-9-25', 'Y-m-d');
		$this->assertEqual($result, 'on 2007-09-25');

		$result = $this->Time->timeAgoInWords('2007-9-25', 'Y-m-d', true);
		$this->assertEqual($result, 'on 2007-09-25');

		$result = $this->Time->timeAgoInWords(strtotime('-2 weeks, -2 days'), 'Y-m-d', false);
		$this->assertEqual($result, '2 weeks, 2 days ago');

		$result = $this->Time->timeAgoInWords(strtotime('2 weeks, 2 days'), 'Y-m-d', true);
		$this->assertPattern('/^2 weeks, [1|2] day(s)?$/', $result);

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 days'), array('end' => '1 month'));
		$this->assertEqual($result, 'on ' . date('j/n/y', strtotime('2 months, 2 days')));

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 days'), array('end' => '3 month'));
		$this->assertPattern('/2 months/', $result);

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 12 days'), array('end' => '3 month'));
		$this->assertPattern('/2 months, 1 week/', $result);

		$result = $this->Time->timeAgoInWords(strtotime('3 months, 5 days'), array('end' => '4 month'));
		$this->assertEqual($result, '3 months, 5 days');

		$result = $this->Time->timeAgoInWords(strtotime('-2 months, -2 days'), array('end' => '3 month'));
		$this->assertEqual($result, '2 months, 2 days ago');

		$result = $this->Time->timeAgoInWords(strtotime('-2 months, -2 days'), array('end' => '3 month'));
		$this->assertEqual($result, '2 months, 2 days ago');

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 days'), array('end' => '3 month'));
		$this->assertPattern('/2 months/', $result);

		$result = $this->Time->timeAgoInWords(strtotime('2 months, 2 days'), array('end' => '1 month', 'format' => 'Y-m-d'));
		$this->assertEqual($result, 'on ' . date('Y-m-d', strtotime('2 months, 2 days')));

		$result = $this->Time->timeAgoInWords(strtotime('-2 months, -2 days'), array('end' => '1 month', 'format' => 'Y-m-d'));
		$this->assertEqual($result, 'on ' . date('Y-m-d', strtotime('-2 months, -2 days')));

		$result = $this->Time->timeAgoInWords(strtotime('-13 months, -5 days'), array('end' => '2 years'));
		$this->assertEqual($result, '1 year, 1 month, 5 days ago');
	}

	function testRelative() {
		$result = $this->Time->relativeTime('-1 week');
		$this->assertEqual($result, '1 week ago');
		$result = $this->Time->relativeTime('+1 week');
		$this->assertEqual($result, '1 week');
	}

	function testOfNice() {
		$time = time() + 2 * DAY;
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->Time->nice($time));

		$time = time() - 2 * DAY;
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->Time->nice($time));

		$time = time();
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->Time->nice($time));

		$time = 0;
		$this->assertEqual(date('D, M jS Y, H:i', time()), $this->Time->nice($time));

		$time = null;
		$this->assertEqual(date('D, M jS Y, H:i', time()), $this->Time->nice($time));
	}
	
	function testOfNiceShort() {
		$time = time() + 2 * DAY;
		if (date('Y', $time) == date('Y')) {
			$this->assertEqual(date('M jS, H:i', $time), $this->Time->niceShort($time));
		} else {
			$this->assertEqual(date('M jSY, H:i', $time), $this->Time->niceShort($time));
		}

		$time = time();
		$this->assertEqual('Today, '.date('H:i', $time), $this->Time->niceShort($time));

		$time = time() - DAY;
		$this->assertEqual('Yesterday, '.date('H:i', $time), $this->Time->niceShort($time));
	}
	
	function testOfDaysAsSql() {
		$begin = time();
		$end = time() + DAY;
		$field = 'my_field';
		$expected = '(my_field >= \''.date('Y-m-d', $begin).' 00:00:00\') AND (my_field <= \''.date('Y-m-d', $end).' 23:59:59\')';
		$this->assertEqual($expected, $this->Time->daysAsSql($begin, $end, $field));
	}

	function testOfDayAsSql() {
		$time = time();
		$field = 'my_field';
		$expected = '(my_field >= \''.date('Y-m-d', $time).' 00:00:00\') AND (my_field <= \''.date('Y-m-d', $time).' 23:59:59\')';
		$this->assertEqual($expected, $this->Time->dayAsSql($time, $field));
	}

	function testToUnix() {
		$this->assertEqual(time(), $this->Time->toUnix(time()));
		$this->assertEqual(strtotime('+1 day'), $this->Time->toUnix('+1 day'));
		$this->assertEqual(strtotime('+0 days'), $this->Time->toUnix('+0 days'));
		$this->assertEqual(strtotime('-1 days'), $this->Time->toUnix('-1 days'));
		$this->assertEqual(false, $this->Time->toUnix(''));
		$this->assertEqual(false, $this->Time->toUnix(null));
	}

	function testOfToAtom() {
		$this->assertEqual(date('Y-m-d\TH:i:s\Z'), $this->Time->toAtom(time()));
	}

	function testOfToRss() {
		$this->assertEqual(date('r'), $this->Time->toRss(time()));
	}

	function testOfFormat() {
		$format = 'D-M-Y';
		$arr = array(time(), strtotime('+1 days'), strtotime('+1 days'), strtotime('+0 days'));
		foreach ($arr as $val) {
			$this->assertEqual(date($format, $val), $this->Time->format($format, $val));
		}
	}

	function testOfGmt() {
		$hour = 3;
		$min = 4;
		$sec = 2;
		$month = 5;
		$day = 14;
		$year = 2007;
		$time = mktime($hour, $min, $sec, $month, $day, $year);
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEqual($expected, $this->Time->gmt(date('Y-n-j G:i:s', $time)));

		$hour = date('H');
		$min = date('i');
		$sec = date('s');
		$month = date('m');
		$day = date('d');
		$year = date('Y');
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEqual($expected, $this->Time->gmt(null));
	}

	function testOfIsToday() {
		$result = $this->Time->isToday('+1 day');
		$this->assertFalse($result);
		$result = $this->Time->isToday('+1 days');
		$this->assertFalse($result);
		$result = $this->Time->isToday('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isToday('-1 day');
		$this->assertFalse($result);
	}
	
	function testOfIsThisWeek() {
		switch (date('D')) {
			case 'Mon' :
				for ($i = 0; $i < 6; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+7 days"));
				$this->assertFalse($this->Time->isThisWeek("-1 days"));
				break;
			case 'Tue' :
				for ($i = -1; $i < 5; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+6 days"));
				$this->assertFalse($this->Time->isThisWeek("-2 days"));
				break;
			case 'Wed' :
				for ($i = -2; $i < 5; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+5 days"));
				$this->assertFalse($this->Time->isThisWeek("-3 days"));
				break;
			case 'Thu' :
				for ($i = -3; $i < 4; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+4 days"));
				$this->assertFalse($this->Time->isThisWeek("-4 days"));
				break;
			case 'Fri' :
				for ($i = -4; $i < 3; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+3 days"));
				$this->assertFalse($this->Time->isThisWeek("-5 days"));
				break;
			case 'Sat' :
				for ($i = -5; $i < 2; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+2 days"));
				$this->assertFalse($this->Time->isThisWeek("-6 days"));
				break;
			case 'Sun' :
				for ($i = -6; $i < 1; $i++) { 
					$this->assertTrue($this->Time->isThisWeek("+$i days"));
				}
				$this->assertFalse($this->Time->isThisWeek("+1 days"));
				$this->assertFalse($this->Time->isThisWeek("-7 days"));
				break;
		}
	}
	
	function testOfIsThisMonth() {
		$result = $this->Time->isThisMonth('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isThisMonth($time = mktime(0, 0, 0, date('m'), rand(1, 28), date('Y')));
		$this->assertTrue($result);
		$result = $this->Time->isThisMonth(mktime(0, 0, 0, date('m'), rand(1, 28), date('Y')-rand(1, 12)));
		$this->assertFalse($result);
		$result = $this->Time->isThisMonth(mktime(0, 0, 0, date('m'), rand(1, 28), date('Y')+rand(1, 12)));
		$this->assertFalse($result);

	}
	
	function testOfIsThisYear() {
		$result = $this->Time->isThisYear('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isThisYear(mktime(0, 0, 0, rand(1, 12), rand(1, 28), date('Y')));
		$this->assertTrue($result);
	}
	
	function testOfWasYesterday() {
		$result = $this->Time->wasYesterday('+1 day');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('+1 days');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('+0 day');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('-1 day');
		$this->assertTrue($result);
		$result = $this->Time->wasYesterday('-1 days');
		$this->assertTrue($result);
		$result = $this->Time->wasYesterday('-2 days');
		$this->assertFalse($result);
	}
	
	function testOfIsTomorrow() {
		$result = $this->Time->isTomorrow('+1 day');
		$this->assertTrue($result);
		$result = $this->Time->isTomorrow('+1 days');
		$this->assertTrue($result);
		$result = $this->Time->isTomorrow('+0 day');
		$this->assertFalse($result);
		$result = $this->Time->isTomorrow('-1 day');
		$this->assertFalse($result);
	}

	function testOfWasWithinLast() {
		$this->assertTrue($this->Time->wasWithinLast('1 day', '-1 day'));
		$this->assertTrue($this->Time->wasWithinLast('1 week', '-1 week'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('1 second', '-1 second'));
		$this->assertTrue($this->Time->wasWithinLast('1 minute', '-1 minute'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('1 month', '-1 month'));
		$this->assertTrue($this->Time->wasWithinLast('1 day', '-1 day'));
		
		$this->assertTrue($this->Time->wasWithinLast('1 week', '-1 day'));
		$this->assertTrue($this->Time->wasWithinLast('2 week', '-1 week'));
		$this->assertFalse($this->Time->wasWithinLast('1 second', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('10 minutes', '-1 second'));
		$this->assertTrue($this->Time->wasWithinLast('23 minutes', '-1 minute'));
		$this->assertFalse($this->Time->wasWithinLast('0 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('13 month', '-1 month'));
		$this->assertTrue($this->Time->wasWithinLast('2 days', '-1 day'));
		
		$this->assertFalse($this->Time->wasWithinLast('1 week', '-2 weeks'));
		$this->assertFalse($this->Time->wasWithinLast('1 second', '-2 seconds'));
		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->Time->wasWithinLast('1 hour', '-2 hours'));
		$this->assertFalse($this->Time->wasWithinLast('1 month', '-2 months'));
		$this->assertFalse($this->Time->wasWithinLast('1 year', '-2 years'));
		
		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 weeks'));
		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->Time->wasWithinLast('0 days', '-2 days'));
		$this->assertTrue($this->Time->wasWithinLast('1 hour', '-20 seconds'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-60 minutes -30 seconds'));
		$this->assertTrue($this->Time->wasWithinLast('3 years', '-2 months'));
		$this->assertTrue($this->Time->wasWithinLast('5 months', '-4 months'));

		$this->assertTrue($this->Time->wasWithinLast('5 ', '-3 days'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-1 hour'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-1 minute'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-23 hours -59 minutes -59 seconds'));
	}

	function tearDown() {
		unset($this->Time);
	}
}
?>