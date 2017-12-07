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

class JKHelperHTML{
	
	static function loadHeaders() {
		JHtml::_('jquery.framework');
		
		$document = JFactory::getDocument();
		// Add Styles
		$document->addStylesheet(JK_GLOBAL_CSS . '/style.css');

		// Add Scripts
		$document->addScript(JK_GLOBAL_JS . '/script.js');
		$document->addScript(JK_ADMIN_JS . '/script.js');

		// Set JS Variables
		$component_url = JK_GLOBAL_COMPONENT;
		$assets_url = JK_GLOBAL_ASSETS;
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
		JText::script('COM_GTRESTO_CONFIRM_DELETE');
		
		$document->addScript(JK_ADMIN_JS . '/jquery-sortable-min.js');
	}
	
	static function setTitle($title = '') {
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

	static function gridSort($name, $field, $ordering, $direction) {
		$search		= array('icon-arrow-up-3', 'icon-arrow-down-3');
		$replace	= array('fa fa-caret-up', 'fa fa-caret-down');
		$gridSort	= JHtml::_('grid.sort', $name, $field, $direction, $ordering);

		return str_replace($search, $replace, $gridSort);
	}

	static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null) {
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

	static function getSelectize($name, $value, $query, $class = null, $requests = null, $parent = array(), $child = null, $task = 'selectize.getItems', $attr = null) {
		$db		= JFactory::getDBO();
		
		$id			= str_replace(array('[',']'), '', $name);
		$fieldname	= $id;
		$attr 		.= ' class ="'.$class.'"';
		
		$db->setQuery(str_replace('%s', '"'.implode('","', (array) $value).'"', $query));
		$items 		= $db->loadObjectlist();
		$options	= array();
		
		if ($items) {
			foreach ($items as $item) {
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}
		
		// Load JSs
		$document	= JFactory::getDocument();
		$document->addScript(JK_ADMIN_JS . '/selectize.min.js');
		$document->addStylesheet(JK_ADMIN_CSS . '/selectize.bootstrap3.css');;
		
		$component_url = JK_GLOBAL_COMPONENT;

		$parent_f	= @$parent[0];
		$parent_v	= @$parent[1] ? @$parent[1] : @$parent[0];
		$requests2 = '{}';
		if($parent_f) {
			$requests2 = sprintf("{parent_field: '%s', parent_value: %s.getValue()}", $parent_f, $parent_v);
		}


		$script		= "
			var $fieldname = null;
			(function ($){
				$(document).ready(function (){
					var $$fieldname = $('#$id').selectize({
						persist: false,
						valueField: 'id',
						labelField: 'name',
						searchField: 'name',
						preload: true,
						load: function(query, callback) {
							data = $.extend($requests, $requests2);
							data.search = query;
							data.task = '$task';
							$.ajax({
								url: '$component_url',
								data: data,
								type: 'GET',
								error: function() {
									callback();
								},
								success: function(result) {
									callback($.parseJSON(result));
								}
							});
						},
					});
					$fieldname = $$fieldname"."[0].selectize;
				});
			})(jQuery);
		";
		$document->addScriptDeclaration($script);

		if($child) {
			$script2 = "
				(function ($){
					$(document).ready(function (){
						$fieldname.on('change', function(){
							$child.clear();
							$child.clearOptions();
							$child.onSearchChange('');
						});
					});
				})(jQuery);
			";
			$document->addScriptDeclaration($script2);
		}

		return JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);
	}
	
	public static function getSelect($name, $options, $value, $style = ''){
		$html = array();
		
		$names	= explode('.', $name);
		$id		= 'jform_'.implode('_', $names);
		$name	= 'jform';
		foreach($names as $part){
			$name .= '['.$part.']';
		}
		
		$html[] = '<select name="'.$name.'" id="'.$id.'" class="inputbox" style="max-width:none !important;width:auto;'.$style.'">';
		$html[] = JHtml::_('select.options', $options, 'value', 'text', $value, true);
		$html[] = '</select>';
		
		return implode('', $html);
	}
	
	public static function getInput($name, $type, $value){
		$names	= explode('.', $name);
		$id		= 'jform_'.implode('_', $names);
		$name	= 'jform';
		foreach($names as $part){
			$name .= '['.$part.']';
		}
		
		return '<input name="'.$name.'" id="'.$id.'" type="'.$type.'" class="input-small inputbox" value="'.$value.'">';
	}
}
