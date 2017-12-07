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

class GTHelperNumber
{

	public static function format($number) {
		$decimal_symbol = ',';
		$digit_group_symbol = '.';
		$num = is_numeric($number) ? number_format($number, 0, $decimal_symbol, $digit_group_symbol) : $number;
		return $num;
	}

	public static function toRoman($num) {
		$n = intval($num);
		$res = '';
		// roman_numerals array 
		$roman_numerals = array(
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1);

		foreach ($roman_numerals as $roman => $number) {
			// divide to get  matches
			$matches = intval($n / $number);
			// assign the roman char * $matches
			$res .= str_repeat($roman, $matches);
			// substract from the number *
			$n = $n % $number;
		}
		return $res;
	}

}
