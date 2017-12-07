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

class GTHelperArray
{

	public function handleNull($array) {
		foreach ($array as $key => $value) {
			$array[$key] = is_null($value) || $value == '' ? 'N/A' : $value;
		}
		return $array;
	}

	public function handleItem($array) {
		foreach ($array as $key => $value) {
			$array[$key] = reset(explode(':', $value));
		}
		return $array;
	}

	public function toJSON($array, $exclude = array()) {
		if(!count($array)) return null;

		$json = array();
		foreach($array as $k => $fields) {
			foreach ($fields as $field => $value) {
				if(in_array($field, $exclude)) continue;
				$json[$field][$k] = $value; 
			}
		}
		return json_encode($json);
	}

	public function toOption($array) {
		$options = array();
		foreach ($array as $value => $text) {
			$option = new stdClass();
			$option->text = $text;
			$option->value = $value;

			$options[] = $option;
		}

		return $options;
	}
}