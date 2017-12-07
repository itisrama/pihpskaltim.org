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
	static function format($date_str, $format) {
		$avoids = array('', '0000-00-00', '0000-00-00 00:00:00');
		if(in_array(trim($date_str), $avoids)) {
			return null;
		}

		$date = JHtml::date($date_str, $format);
		return $date;
	}

	static function getFirstWeekday($date) {
		$month = JHtml::date($date, 'n');
		$year = JHtml::date($date, 'Y');
		$unix = mktime(0, 0, 0, $month, 1, $year);
		$num = date("N", $unix);
		if (in_array($num, array(6, 7))) {
			$unix = $unix + ((8-$num) * 24 * 60 * 60);
		}
		return $unix;
	}
	
	static function getLastWeekday($month, $year) {
		$unix = mktime(0, 0, 0, $month, 1, $year);
		$unix = mktime(0, 0, 0, $month, date('t', $unix), $year);
		$num = date("N", $unix);
		if (in_array($num, array(6, 7))) {
			$unix = $unix - (($num-5) * 24 * 60 * 60);
		}
		return $unix;
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
	
	
	static function getWeekPeriodOld($start, $end) {		
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
	
	static function getHolidays($start, $end, $convUnix = true){
		$start	= JHtml::date($start, 'Y-m-d');
		$end	= JHtml::date($end, 'Y-m-d');
		$start	= JHtml::date($start, 'Y-m-01');
		$end	= JHtml::date($end.' + 1 month', 'Y-m-01');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('start', 'end')));
		$query->from('#__gtpihps_holidays');
		$query->where($db->quoteName('published').' = 1');
		$query->where('('.
			$db->quoteName('start').' BETWEEN '.$db->quote($start).' AND '.$db->quote($end).' OR '.
			$db->quoteName('end').' BETWEEN '.$db->quote($start).' AND '.$db->quote($end).
		')');
		
		$db->setQuery($query);
		
		$items = $db->loadObjectList();
		
		$holidays = array();
		foreach($items as $item){
			$start  = strtotime($item->start);
			$end    = strtotime($item->end);
			
			$diff = round($end - $start)/(24*60*60);

			while($start <= $end) {				
				$holidays[$start] = $convUnix ? $start : JHtml::date($start, 'Y-m-d');
				$start = strtotime("+1 day", $start);
			}
		}

		//echo nl2br(str_replace('#__', 'pihps_', $query));
		sort($holidays);
		return $holidays;
	}

	static function isHoliday($date){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'name')));
		$query->from('#__gtpihps_holidays');
		$query->where($db->quoteName('published').' = 1');
		$query->where($db->quote($date).' BETWEEN '.$db->quoteName('start').' AND '.$db->quoteName('end'));
		
		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__', 'pihps_', $query));
		$holiday = $db->loadObject();

		if(!$holiday->id) {
			return false;
		} else {
			return $holiday->name;
		}
	}
	
	static function getDayPeriod($start, $end) {
		$holidays = self::getHolidays($start, $end);
		//echo"<pre>";var_dump($holidays);echo"</pre>";
	
		$diff = floor(($end - $start)/(24*60*60));
		$period = array();
		for($i=0;$i<=$diff;$i++) {
			$unix = strtotime("+$i day", $start);

			//Skip Weekend
			if(in_array(JHtml::date($unix, 'w'), array(0,6))) continue;
			
			//Skip Holiday
			if(in_array($unix, $holidays)) continue;

			$timestamp = new stdClass();
			$timestamp->unix = $unix;
			$timestamp->sdate = JHtml::date($unix, 'd/m/Y');
			$timestamp->sdate2 = JHtml::date($unix, 'd M');
			$timestamp->sdate3 = JHtml::date($unix, 'd-m-Y');
			$timestamp->ldate = JHtml::date($unix, 'd F Y');
			$timestamp->mysql = JHtml::date($unix, 'Y-m-d');
			$period[] = $timestamp;
		}
		//echo"<pre>";var_dump($period);echo"</pre>";
		return $period;
	}

	static function getCountPeriod($end, $count, $type = 'day') {
		$start		= strtotime('-'.$count.' '.$type, $end);
		$holidays	= self::getHolidays($start, $end);
		$period		= array();
		$unix		= 0;

		$i = 0;
		$j = 0;
		
		do {
			if($type != 'day') {
				$newunix = strtotime("-$j $type", $end); $j++;
				if($unix > 0 && $unix <= $newunix) {
					continue;
				}
				$is_holiday	= true;
				while ($is_holiday) {
					//Skip Weekend
					if(in_array(JHtml::date($newunix, 'w'), array(0,6))) {
						$newunix = strtotime("+1 day", $newunix);
						continue;
					}
					
					//Skip Holiday
					if(in_array($newunix, $holidays)) {
						$newunix = strtotime("+1 day", $newunix);
						continue;
					}
					$is_holiday = false;
				}
				if($unix > 0 && $unix == $newunix) {
					continue;
				}
				$unix = $newunix;
			} else {
				$unix = strtotime("-$j day", $end); $j++;
				//Skip Weekend
				if(in_array(JHtml::date($unix, 'w'), array(0,6))) continue;
				
				//Skip Holiday
				if(in_array($unix, $holidays)) continue;
			}

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

		sort($period);
		return $period;
	}

	static function getBetweenPeriod($start, $end, $type = 'day') {
		$holidays	= self::getHolidays($start, $end);
		$unix		= 0;
		$period		= array();
		$i			= 0;

		while($unix < $end) {
			if($type != 'day') {
				$newunix = strtotime("+$i $type", $start); $i++;
				if($unix > 0 && $unix >= $newunix) {
					continue;
				}
				$unix = $newunix;
				$is_holiday = true;
				while ($is_holiday) {
					//Skip Weekend
					if(in_array(JHtml::date($unix, 'w'), array(0,6))) {
						$unix = strtotime("+1 day", $unix);
						continue;
					}
					//Skip Holiday
					if(in_array($unix, $holidays)) {
						$unix = strtotime("+1 day", $unix);
						continue;
					}
					$is_holiday = false;
				}
			} else {
				$unix = strtotime("+$i day", $start); $i++;

				//Skip Weekend
				if(in_array(JHtml::date($unix, 'w'), array(0,6))) continue;
				
				//Skip Holiday
				if(in_array($unix, $holidays)) continue;
			}

			if($unix > $end) continue;

			$timestamp			= new stdClass();
			$timestamp->unix	= $unix;
			$timestamp->sdate	= JHtml::date($unix, 'd/m/Y');
			$timestamp->sdate2	= JHtml::date($unix, 'd M');
			$timestamp->sdate3	= JHtml::date($unix, 'd-m-Y');
			$timestamp->ldate	= JHtml::date($unix, 'd F Y');
			$timestamp->mysql 	= JHtml::date($unix, 'Y-m-d');
			$period[]			= $timestamp;
		}

		return $period;
	}

	static function getWeekPeriod($start, $end) {
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
			$timestamp->mysql = JHtml::date($unix, 'Y-m-d');
			$period[] = $timestamp;
		}
		return $period;
	}

	static function getWeekNumber($date, $rollover = 'Monday') {
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

	static function weekOfMonth($date) {
		//Get the first day of the month.
		$firstOfMonth = JHtml::date($date, "Y-m-01");

		//Apply above formula.
		return JHtml::date($date, 'W') - JHtml::date($firstOfMonth, 'W') + 1;
	}

	static function convertFormat($format) {
		return strtr($format,
			array('d' => 'j', 'dd' => 'd', 'DD' => 'l', 'D' => 'D', 'mm' => 'm', 'm' => 'n', 'MM' => 'F', 'M' => 'M', 'yyyy' => 'Y', 'yy' => 'y')
		);
	}

	public static function getDatePicker($name, $value, $attributes, $format = '%Y-%m-%d', $minView = 'days', $start = false, $end = 'now') {
		// Load JSs
		if(is_numeric(strpos($format, '%'))) {
			$format = str_replace(array('%', 'd', 'm', 'Y'), array('', 'dd', 'mm', 'yyyy'), $format);
		}
		$deflang	= explode('-', JComponentHelper::getParams('com_languages')->get('site'));
		$deflang	= reset($deflang);

		$start = $start ? JHtml::date($start, self::convertFormat($format)) : false;
		$end = $end ? JHtml::date($end, self::convertFormat($format)) : false;

		$params					= array();
		$params['format']		= $format;
		$params['language']		= $deflang;
		$params['startDate']	= $start;
		$params['endDate']		= $end;
		$params['minViewMode']	= $minView;
		$params 				= json_encode(array_filter($params));

		$document	= JFactory::getDocument();
		$document->addScript(GT_ADMIN_JS . '/datepicker/bootstrap-datepicker.js');
		$document->addScript(GT_ADMIN_JS . '/datepicker/locales/bootstrap-datepicker.' . $deflang . '.js');
		$document->addStylesheet(GT_ADMIN_CSS . '/bootstrap-datepicker3.min.css');
		$document->addScriptDeclaration("
			(function ($){
				$(document).ready(function (){
					$('#". $name ."_container').datepicker(".$params.")
				});
			})(jQuery);
		");

		$input = '<div class="input-group date" id="'.$name.'_container">';
		$input .= '<span class="input-group-addon btn btn-danger" onclick="jQuery(this).next().val(null)"><i class="fa fa-times"></i></span>';
		$input .= '<input type="text" class="form-control" readonly="" style="background-color:white" value="'.$value.'" id="'.$name.'" name="'.$name.'">';
		$input .= '<span class="input-group-addon btn btn-info"><i class="fa fa-calendar"></i></span></div>';

		return $input;
	}
}
