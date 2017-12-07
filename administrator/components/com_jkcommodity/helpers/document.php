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

class JKHelperDocument {

	function loadHeaders()
	{
		$document = JFactory::getDocument();
		$document->addStylesheet(JK_GLOBAL_CSS . '/style.css');
		$document->addScript(JK_ADMIN_JS . '/jquery.min.js');
		$document->addScript(JK_ADMIN_JS . '/jquery.formatCurrency.js');
		$component_url = JK_GLOBAL_COMPONENT;
		$assets_url = JK_GLOBAL_ASSETS;
		$document->addScriptDeclaration("
		// Set variables
			var component_url = '$component_url';
			var assets_url = '$assets_url';
		");
		self::setCurrency();
		$document->addScript(JK_GLOBAL_JS . '/script.js');
	}
	
	static function setTitle($title = '') {
		$app		= JFactory::getApplication();
		$position	= $app->getCfg('sitename_pagetitles');
		$document	= JFactory::getDocument();
		switch($position) {
			case 1:
				$document->setTitle($app->getCfg('sitename') . ' - ' . $title);
				break;
			case 2:
				$document->setTitle($title . ' - ' . $app->getCfg('sitename'));
				break;
			default:
				$document->setTitle($title);
				break;
		}
	}

	function setCurrency()
	{
		$document = JFactory::getDocument();

		$symbol = 'Rp';
		$decimal_symbol = ',';
		$digit_group_symbol = '.';

		$document->addScriptDeclaration("
			// Initiate currency locale
			jQuery.noConflict();
			(function($) {
				$.formatCurrency.regions['custom'] = {
					symbol: '$symbol',
					positiveFormat: '%s%n',
					negativeFormat: '(%s%n)',
					decimalSymbol: '$decimal_symbol',
					digitGroupSymbol: '$digit_group_symbol',
					groupDigits: true,
					roundToDecimalPlace: -1
				};
			})(jQuery);
		");
	}
	
	static function toCurrency($number, $symbol = 'Rp')
	{
		$is_negative = $number < 0;
		$decimal_symbol = ',';
		$digit_group_symbol = '.';
		$number = $symbol.number_format(abs($number), 0, $decimal_symbol, $digit_group_symbol);
		$number = $is_negative ? "($number)" : $number;
		return $number;
	}
	
	static function toNumber($currency, $symbol = 'Rp')
	{
		$decimal_symbol = ',';
		$digit_group_symbol = '.';
		
		$currency = trim(str_replace($symbol, '', $currency));
		$currency = str_replace($digit_group_symbol, '', $currency);
		$currency = str_replace($decimal_symbol, '.', $currency);
		
		return $currency;
	}

	static function getReferences($ids, $table) {
		$ids = is_array($ids) ? $ids : array($ids);
		// Get a db connection.
		$db = JFactory::getDbo();
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select data.
		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__jkcommodity_'.$table, 'a'));
		
		// Publish filter
		$query->where($db->quoteName('a.id'). ' IN ('.implode(',', $ids).')');
		
		$query->order($db->quoteName('a.name'));
		
		//echo nl2br(str_replace('#__','eburo_',$query));

		$db->setQuery($query);

		return $db->loadObjectList();
	}
	
	static function getReportHeader($period, $layout)
	{
		$header = new stdClass();			
		$city_id = JRequest::getVar('city_id');
		$city_id = is_array($city_id) ? $city_id : array($city_id);
		if($city_id && !in_array(0, (array) $city_id)) {
			$cities = self::getReferences($city_id, 'city');
			foreach($cities as $k => $city) {
				$cities[$k] = $city->name;
			}
			$header->city = implode(', ', $cities);
		} else {
			$header->city = JText::_('COM_JKCOMMODITY_FIELD_ALL_CITY');
		}
		$market_id = JRequest::getVar('market_id');
		if($market_id && !in_array(0, (array) $market_id)) {
			$markets = self::getReferences($market_id, 'market');
			foreach($markets as $k => $market) {
				$markets[$k] = $market->name;
			}
			$header->market = implode(', ', $markets);
		} else {
			$header->market = JText::_('COM_JKCOMMODITY_FIELD_ALL_MARKET');
		}
		if(count($period)>1) {
			$header->period = array_shift($period)->ldate . ' - ' . array_pop($period)->ldate;
		} else {
			$header->period = array_shift($period)->ldate;
		}
		
		$layouts = array(
			"default" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_DEFAULT'),
			"weekly" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_WEEKLY'),
			"monthly" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_MONTHLY'),
			"yearly" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_YEARLY'),
			"market" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_MARKET'),
			"city" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_CITY'),
			"period" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_DATE'),
			"chart" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_CHART'),
			"map" => JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE_MAP')
		);		
		$header->layout = $layouts[$layout];
		
		return $header;
	}
	
	static function prepareCommodity($element, $categories, $commodities, $level = 0, $type = 'select', $include_cat = array()) {
		$data = array();
		foreach($element as $item) {
			$child_categories = (array) @$categories[$item->id];
			$child_commodities = (array) @$commodities[$item->id];

			if(!(count($child_categories) || count($child_commodities))) {
				continue;
			}

			if(count($child_categories)) {
				$countsub = 0;
				foreach ($child_categories as $child_category) {
					$countsub += count((array) @$categories[$child_category->id]) + count((array) @$commodities[$child_category->id]);
				}
				if(!$countsub) continue;
			}

			if(count($include_cat) && !in_array($item->id, $include_cat)) continue;
			$row = new stdClass();
			$row->text = str_repeat('&nbsp;', $level * 4) . $item->name;
			$row->value = $type === 'select' ? '<OPTGROUP>' : 'category';
			$data[] = $row;
			if(isset($commodities[$item->id])) {
				foreach($commodities[$item->id] as $com) {
					$multiplier = $type === 'select' ? $level : $level + 1;
					if($type == 'select') {
						$row = new stdClass();
						$row->text = str_repeat('&nbsp;', $multiplier * 4) . $com->name;
						$row->value = $com->id;
					} else {
						$com->text = str_repeat('&nbsp;', $multiplier * 4) . $com->name;
						$com->value = $com->id;
						$row = $com;
					}
					$data[] = $row;
				}
			}
			$row = new stdClass();
			if($type === 'select') {
				$row->text = '';
				$row->value = '</OPTGROUP>';
				$data[] = $row;
			}
			if(isset($categories[$item->id])) {
				$data = array_merge($data, self::prepareCommodity($categories[$item->id], $categories, $commodities, $level+1, $type, $include_cat));
			}
		}
		return $data;
	}

	static function setCommodities($rows, $categories, $commodities, $type = null, $level = 0) {
		$data = array();
		foreach($rows as $category_id => $category) {
			$child_categories = (array) @$categories[$category_id];
			$child_commodities = (array) @$commodities[$category_id];

			if(!(count($child_categories) || count($child_commodities))) {
				continue;
			}

			if(count($child_categories)) {
				$countsub = 0;
				foreach (array_keys($child_categories) as $child_category) {
					$countsub += count((array) @$categories[$child_category]) + count((array) @$commodities[$child_category]);
				}
				if(!$countsub) continue;
			}
			
			$count = 0;
			if(count($child_categories)) {
				foreach ($child_categories as $child_id => $child) {
					$count += count((array) @$categories[$child_id]);
					$count += count((array) @$commodities[$child_id]);
				}
				if(!$count && !count($child_commodities)) {
					continue;
				}
			}
			
			$row = new stdClass();
			$row->text = str_repeat('&nbsp;', $level * 4) . $category;
			$row->value = $type === 'select' ? '<OPTGROUP>' : NULL;
			$data[] = $row;
			if(isset($commodities[$category_id])) {
				foreach($commodities[$category_id] as $commodity_id => $commodity) {
					$multiplier = $type === 'select' ? $level : $level + 1;
					$row = new stdClass();
					$row->text = str_repeat('&nbsp;', $multiplier * 4) . $commodity;
					$row->value = $commodity_id;
					$data[] = $row;
				}
			}
			$row = new stdClass();
			if($type === 'select') {
				$row->text = '';
				$row->value = '</OPTGROUP>';
				$data[] = $row;
			}
			if(isset($categories[$category_id])) {
				$data = array_merge($data, self::setCommodities($categories[$category_id], $categories, $commodities, $type, $level+1));
			}
		}
		return $data;
	}
}
