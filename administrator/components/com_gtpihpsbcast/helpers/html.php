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

class GTHelperHTML
{
	
	function loadHeaders() {
		$document = JFactory::getDocument();
		// Add Styles
		$document->addStylesheet(GT_GLOBAL_CSS . '/style.css');

		// Add Scripts
		$document->addScript(GT_GLOBAL_JS . '/script.js');
		$document->addScript(GT_ADMIN_JS . '/script.js');
		$document->addScript(GT_ADMIN_JS . '/jquery.min.js');

		// Set JS Variables
		$component_url = GT_GLOBAL_COMPONENT;
		$assets_url = GT_GLOBAL_ASSETS;
		$document->addScriptDeclaration("
		// Set variables
			var component_url = '$component_url';
			var assets_url = '$assets_url';
		");

		// Set translation constant to JS
		JText::script('ERROR');
		JText::script('WARNING');
		JText::script('SUCCESS');
		JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		JText::script('COM_GTPIHPSBCAST_CONFIRM_DELETE');
		
		$document->addScript(GT_ADMIN_JS . '/jquery-sortable-min.js');
	}
	
	function setTitle($title = '') {
		$app = JFactory::getApplication();
		$position = $app->getCfg('sitename_pagetitles');
		$document = JFactory::getDocument();
		switch ($position) {
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

	function gridSort($name, $field, $ordering, $direction) {
		$search		= array('icon-arrow-up-3', 'icon-arrow-down-3');
		$replace	= array('fa fa-caret-up', 'fa fa-caret-down');
		$gridSort	= JHtml::_('grid.sort', $name, $field, $direction, $ordering);

		return str_replace($search, $replace, $gridSort);
	}

	function setCommodities($rows, $categories, $commodities, $type = 'select', $level = 0) {
		$data = array();
		foreach($rows as $category_id => $category) {
			$child_categories = (array) @$categories[$category_id];
			$child_commodities = (array) @$commodities[$category_id];

			if(!(count($child_categories) || count($child_commodities))) {
				continue;
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

	function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null) {
		$calendar = JHtml::calendar($value, $name, $id, $format, $attribs);
		$search = array(
			'<div class="input-append">',
			'hasTooltip',
			'btn',
			'<button',
			'icon-calendar',
			'</button>'
		);

		$replace = array(
			'<div class="input-group '.'">',
			'hasTooltip form-control',
			'btn btn-info',
			'<div class="input-group-btn"><button',
			'fa fa-calendar',
			'</button></div>'
		);
		return str_replace($search, $replace, $calendar);
	}
}
