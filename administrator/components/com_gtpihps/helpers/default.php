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

	public static function getInfo() {
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
	
	public static function pluralize($word) {
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

	public static function recursive_ksort(&$array) {
	    foreach ($array as $k => $v) {
	        if (is_array($v)) {
	            self::recursive_ksort($v);
	        }
	    }
	    return ksort($array);
	}
	
	public static function addSubmenu($vName) {
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_CATEGORY'),
			'index.php?option=com_gtpihps&amp;view=ref_categories',
			$vName == 'ref_commodities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_NATIONAL_COMMODITY'),
			'index.php?option=com_gtpihps&amp;view=ref_national_commodities',
			$vName == 'ref_national_commodities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_REGION_COMMODITY'),
			'index.php?option=com_gtpihps&amp;view=ref_region_commodities',
			$vName == 'ref_region_commodities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_PROVINCE'),
			'index.php?option=com_gtpihps&amp;view=ref_provinces',
			$vName == 'ref_provinces'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_REGION'),
			'index.php?option=com_gtpihps&amp;view=ref_regions',
			$vName == 'ref_regions'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_REGENCY'),
			'index.php?option=com_gtpihps&amp;view=ref_regencies',
			$vName == 'ref_regencies'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_GTPIHPS_PT_HOLIDAY'),
			'index.php?option=com_gtpihps&amp;view=ref_holidays',
			$vName == 'ref_holidays'
		);
		/*
		JHtmlSidebar::addEntry(
			JText::_('COM_GTRESTO_CONFIG'),
			'index.php?option=com_config&amp;view=component&amp;component=com_gtpihps',
			$vName == 'component'
		);
		*/
	}

	public static function cleanstr($str) {
		return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
	}

	public static function getUserToken($username) {
		$user = JFactory::getUser($username);
		$token = urlencode(base64_encode(md5(self::getIPAddress()).':'.$user->id.':'.$user->password));

		return $token;	
	}

	public static function checkUserToken($token) {
		list($ipToken, $userID, $password) = explode(':', base64_decode($token).'::');

		/*
		if(md5(self::getIPAddress()) != $ipToken) {
			return false;
		}
		*/

		if(!is_numeric($userID)) {
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, username')
			->from('#__users')
			->where('id =' . $db->quote($userID))
			->where('password = ' . $db->quote($password));

		$db->setQuery($query);
		$user = $db->loadObject();

		return @$user->id > 0;
	}

	public static function verifyUserToken($token) {
		$result = self::checkUserToken(urldecode($token));
		$result = $result ? $result : self::checkUserToken($token);
		
		return $result;
	}

	public static function getIPAddress() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		  $ip=$_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	public static function getMenuId($url) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')->from('#__menu')->where($db->quoteName('link') .' = '.$db->quote($url));

		$db->setQuery($query);
		return intval(@$db->loadObject()->id);
	}

	public static function getRecentPrice($unix, &$item) {
		//return $price = @$item[$unix];

		$price = null;
		for($i=0; $i<=7; $i++) { 
			$unix -= ($i*24*60*60);
			$price = @$item[$unix];

			if($price) {
				break;
			}
		}
		return $price;
	}

	public static function getReference($id, $table) {
		$table = $this->getTable(ucwords($table));
		$table->load($id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public static function httpQuery($query, $postman = false) {
		$query = http_build_query($query, "", "&");
		$query = str_replace(array('%5B', '%5D'), array('[', ']'), $query);

		if($postman) {
			$query = str_replace(array('=', '&'), array(':', PHP_EOL), $query);
			$query = urldecode($query);
		}
		return $query;
	}
}
