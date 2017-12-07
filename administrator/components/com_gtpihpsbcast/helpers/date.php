<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class GTHelperDate {

	function getFirstWeekday($date) {
		$month = JHtml::date($date, 'n');
		$year = JHtml::date($date, 'Y');
		$unix = mktime(0, 0, 0, $month, 1, $year);
		$num = date("N", $unix);
		if (in_array($num, array(6, 7))) {
			$unix = $unix + ((8-$num) * 24 * 60 * 60);
		}
		return $unix;
	}
	
	function getLastWeekday($month, $year) {
		$unix = mktime(0, 0, 0, $month, 1, $year);
		$unix = mktime(0, 0, 0, $month, date('t', $unix), $year);
		$num = date("N", $unix);
		if (in_array($num, array(6, 7))) {
			$unix = $unix - (($num-5) * 24 * 60 * 60);
		}
		return $unix;
	}
	function getYearPeriod($start, $end) {
		$year1 = date('Y', $start);
		$year2 = date('Y', $end);
		
		$period = array();
		$y = $year1;
		while(!($y == $year2+1)) {
			$cur_date = mktime(0,0,0,1,1,$y);
			$timestamp = new stdClass();
			$jom_date = JFactory::getDate($cur_date);
			$timestamp->unix = $cur_date;
			$timestamp->sdate = $jom_date->format('Y', true);
			$timestamp->ldate = $jom_date->format('Y', true);
			$period[] = $timestamp;
			$y++;
		}
		return $period;
		
	}
	function getMonthPeriod($start, $end) {
		$interval	= round(($end - $start) / (30*24*60*60));
		$start		= JHtml::date($start, 'Y-m-01');
		$periods	= array();

		for($i=0; $i<=$interval; $i++) {
			$period			= new stdClass();
			$period->unix	= JHtml::date($start .'+'.$i.'month', 'U');
			$period->sdate	= JHtml::date($start .'+'.$i.'month', 'm/Y');
			$period->ldate	= JHtml::date($start .'+'.$i.'month', 'M Y');
			$periods[]		= $period;
		}

		return $periods;
	}
	
	
	function getWeekPeriodOld($start, $end) {		
		// Set time elements
		$end2 = strtotime("+1 week", $end);
		list($week1,$month1,$year1) = explode('-',date('W-m-Y',$start));
		list($week2,$month2,$year2) = explode('-',date('W-m-Y',$end2));
		$week_rom = array('I','II','III','IV','V');
		
		// Set initial value
		$period = array();
		list($w,$m,$y) = array($week1,$month1,$year1);
		$week_num = 0;
		// Iterate while week, month, year not in the end date
		while(!($w == $week2 && $m == $month2 && $y == $year2)) {
			// Set first week and last week of the month
			$first_week = self::getFirstWeekday($m, $y);
			$last_week = self::getLastWeekday($m, $y);
			// Set week position
			$cur_week_num = $w - intval(date('W', $first_week));
			// Check if week position invalid 
			if($cur_week_num < 0) {
				// Check if it is in January 
				if($m == 1) {
					$week_num = $w;
				} else {
					$week_num = $week_num+1;
				}
			} else {
				$week_num = $cur_week_num;
			}
			if($week_num == 0) {
				$cur_date = $first_week;
			} else {
				if($w == 1 && $m == 12) {
					$cur_date = strtotime($y+1 .'W'.sprintf('%02d',$w));
				} else {
					$cur_date = strtotime($y.'W'.sprintf('%02d',$w));
				}
			}

			$timestamp = new stdClass();
			$jom_date = JFactory::getDate($cur_date);
			$timestamp->unix = $cur_date;
			$timestamp->sdate = $jom_date->format('M Y', true). ' ( '. $week_rom[$week_num] . ' )';
			$timestamp->ldate = $jom_date->format('F Y', true). ' ( '. $week_rom[$week_num] . ' )';
			$period[] = $timestamp;
			if($w == date('W', $last_week)) {
				$m++;
				if($m > 12) {
					$m = 1;
					$y++;
				}
				$w = intval(date('W', GTHelperDate::getFirstWeekday($m, $y)));
			} else {
				$w = intval(date('W', strtotime("+1 week", $cur_date)));
			}
		}
		return $period;
	}
	
	function getDayPeriod($start, $end) {
		$diff = floor(($end - $start)/(24*60*60));
		$period = array();
		for($i=0;$i<=$diff;$i++) {
			$unix = strtotime("+$i day", $start);

			//Skip Weekend
			if(in_array(JHtml::date($unix, 'w'), array(0,6))) continue;

			$timestamp = new stdClass();
			$timestamp->unix = $unix;
			$timestamp->sdate = JHtml::date($unix, 'd/m/Y');
			$timestamp->ldate = JHtml::date($unix, 'd F Y');
			$period[] = $timestamp;
		}
		return $period;
	}

	function getWeekPeriod($start, $end) {
		$start = strtotime('this week', $start);
		$end = strtotime('this week', $end);
		$diff = floor(($end - $start)/(7*24*60*60));
		$period = array();
		for($i=0;$i<=$diff;$i++) {
			$unix = strtotime("+$i week", $start);
			$number = GTHelperNumber::toRoman(ceil(JHtml::date($unix, 'j') / 7)); 
			$timestamp = new stdClass();
			$timestamp->unix = $unix;
			$timestamp->sdate = JHtml::date($unix, 'M Y') . ' ('.$number.')';
			$timestamp->ldate = JHtml::date($unix, 'F Y') . ' ('.$number.')';
			$period[] = $timestamp;
		}
		return $period;
	}

	function getWeekNumber($date, $rollover = 'Monday') {
		$cut = substr($date, 0, 8);
		$daylen = 86400;

		$timestamp = strtotime($date);
		$first = strtotime($cut . "00");
		$elapsed = ($timestamp - $first) / $daylen;

		$i = 1;
		$weeks = 1;

		for($i; $i<=$elapsed; $i++)
		{
			$dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
			$daytimestamp = strtotime($dayfind);

			$day = strtolower(date("l", $daytimestamp));

			if($day == strtolower($rollover))  $weeks ++;
		}

		return $weeks;
	}
}