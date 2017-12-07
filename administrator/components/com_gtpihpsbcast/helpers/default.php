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

class GTHelper {

	public function getInfo() {
		$xml = JPATH_COMPONENT_ADMINISTRATOR . DS . 'manifest.xml';
		$xml = JApplicationHelper::parseXMLInstallFile($xml);

		$info = new stdClass();
		$info->name			= $xml['name'];
		$info->type			= $xml['type'];
		$info->creationDate	= $xml['creationdate'];
		$info->creationYear	= array_pop(explode(' ', $xml['creationdate']));
		$info->author		= $xml['author'];
		$info->copyright	= $xml['copyright'];
		$info->authorEmail	= $xml['authorEmail'];
		$info->authorUrl	= $xml['authorUrl'];
		$info->version		= $xml['version'];
		$info->description	= $xml['description'];

		return $info;
	}
	
	public function pluralize($word) {
		$plural = array(
			array('/(x|ch|ss|sh)$/i', "$1es"),
			array('/([^aeiouy]|qu)y$/i', "$1ies"),
			array('/([^aeiouy]|qu)ies$/i', "$1y"),
			array('/(bu)s$/i', "$1ses"),
			array('/s$/i', "s"),
			array('/$/', "s"));

		// Check for matches using regular expressions
		foreach ($plural as $pattern)
		{
			if (preg_match($pattern[0], $word))
			{
				$word = preg_replace($pattern[0], $pattern[1], $word);
				break;
			}
		}
		return $word;
	}

	public function recursive_ksort(&$array) {
	    foreach ($array as $k => $v) {
	        if (is_array($v)) {
	            self::recursive_ksort($v);
	        }
	    }
	    return ksort($array);
	}
	
}