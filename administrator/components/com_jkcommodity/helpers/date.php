<?php

/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JKHelperDate {

	function getFirstWeekday($month, $year) {
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
	
	static function getWeekdaysCount($start, $end){
		$iter  = 24 * 60 * 60;
		$count = 0;

		for($i = $start; $i <= $end; $i=$i+$iter){
		    if(date('D',$i) != 'Sat' && date('D',$i) != 'Sun'){
		        $count++;
		    }
		}
		return $count;
	}
	
	static function getYearPeriod($start, $end) {
		$year1 = JHtml::date($start, 'Y');
		$year2 = JHtml::date($end, 'Y');
		
		$count = $year2 - $year1;
		$i = 0;
		$periods	= array();
		do {
			$year = $year1+$i;
			$unix = strtotime($year.'-01-01');

			$period			= new stdClass();
			$period->unix	= $unix;
			$period->sdate	= $year;
			$period->ldate	= $year;
			$period->mysql	= JHtml::date($unix, 'Y-01-01');

			$periods[]		= $period;

			$i++;
		} while ($i <= $count);

		
		return $periods;
		
	}
	
	static function getMonthPeriod($start, $end) {
		$interval	= round(($end - $start) / (30*24*60*60));
		$start		= JHtml::date($start, 'Y-m-01');
		$start		= strtotime($start);
		$periods	= array();

		for($i=0; $i<=$interval; $i++) {
			$period			= new stdClass();
			$period->unix	= strtotime("+$i month", $start);
			$period->sdate	= JHtml::date($period->unix, 'm/Y');
			$period->ldate	= JHtml::date($period->unix, 'M Y');
			$period->full 	= JHtml::date($period->unix, 'F Y');
			$period->mysql	= JHtml::date($period->unix, 'Y-m-01');
			$periods[]		= $period;
		}

		return $periods;
	}
	
	
	static function getWeekPeriod($start, $end) {
		$start = strtotime('this week', $start);
		$end = strtotime('this week', $end);
		$diff = floor(($end - $start)/(7*24*60*60));
		$period = array();
		for($i=0;$i<=$diff;$i++) {
			$unix = strtotime("+$i week", $start);
			$number = JKHelperNumber::toRoman(ceil(JHtml::date($unix, 'j') / 7)); 
			$timestamp = new stdClass();
			$timestamp->unix = $unix;
			$timestamp->sdate = JHtml::date($unix, 'M Y') . ' ('.$number.')';
			$timestamp->ldate = JHtml::date($unix, 'F Y') . ' ('.$number.')';
			$timestamp->mysql = JHtml::date($unix, 'Y-m-d');
			$period[] = $timestamp;
		}
		return $period;
	}
	
	static function getDayPeriod($start, $end) {
		$diff = floor(($end - $start)/(24*60*60));
		$period = array();
		for($i=0;$i<=$diff;$i++) {
			$cur_date = strtotime("+$i day", $start);
			if(in_array(date('w', $cur_date), array(0,6))) continue;
			$timestamp = new stdClass();
			$timestamp->unix = $cur_date;
			$timestamp->sdate = JHtml::date($cur_date, 'd/m/Y');
			$timestamp->ldate = JHtml::date($cur_date, 'd M Y');
			$period[] = $timestamp;
		}
		return $period;
	}

	static function getDayPeriod2($end, $count) {
		$start = strtotime("-".($count * 2)." day", $end);
		//$holidays = self::getHolidays($start, $end);

		$period = array();
		$i = 0;
		$j = 0;
		do {
			$unix = strtotime("-$j day", $end);
			$j++;
			//Skip Weekend
			if(in_array(JHtml::date($unix, 'w'), array(0,6))) continue;
			
			//Skip Holiday
			//if(in_array($unix, $holidays)) continue;

			$timestamp			= new stdClass();
			$timestamp->unix	= $unix;
			$timestamp->sdate	= JHtml::date($unix, 'd/m/Y');
			$timestamp->sdate2	= JHtml::date($unix, 'd M');
			$timestamp->sdate3	= JHtml::date($unix, 'd-m-Y');
			$timestamp->ldate	= JHtml::date($unix, 'd F Y');
			$timestamp->mysql 	= JHtml::date($unix, 'Y-m-d');
			$period[]			= $timestamp;

			$i++;
		} while ($i < $count);

		$period = array_reverse($period);
		return $period;
	}
}
