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

class JKHelper {

	function getInfo() {
		$xml = JPATH_COMPONENT_ADMINISTRATOR . DS . 'manifest.xml';
		$xml = JApplicationHelper::parseXMLInstallFile($xml);

		$info = new stdClass();
		$info->name = $xml['name'];
		$info->type = $xml['type'];
		$info->creationDate = $xml['creationdate'];
		$info->creationYear = array_pop(explode(' ', $xml['creationdate']));
		$info->author = $xml['author'];
		$info->copyright = $xml['copyright'];
		$info->authorEmail = $xml['authorEmail'];
		$info->authorUrl = $xml['authorUrl'];
		$info->version = $xml['version'];
		$info->description = $xml['description'];

		return $info;
	}
	
	/**
	 * Get the actions
	 */
	public static function getActions()
	{
		jimport('joomla.access.access');
		$user		= JFactory::getUser();
		$result		= new JObject;

		$assetName = 'com_jkcommodity';
		$level = 'component';

		$actions = JAccess::getActions('com_jkcommodity', $level);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}
	
	/**
	 * Check user permission for accessing view directly.
	 */
	public static function checkPermission($canDo, $created_by = 0) {
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$jinput		= $app->input;

		$id			= $jinput->get('id');
		$option		= $jinput->get('option');
		$view		= $jinput->get('view');
		$viewList	= self::pluralize($view);
		$layout		= $jinput->get('layout');
		$canEdit	= $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $created_by == $user->id);
		$canCreate	= $canDo->get('core.create');

		if ($layout == 'edit' && !$canEdit && $id) {
			$app->redirect(
				JRoute::_('index.php?option=' . $option . '&view=' . $viewList), JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error'
			);
		} else if($layout == 'edit' && !$canCreate && !$id) {
			$app->redirect(
				JRoute::_('index.php?option=' . $option . '&view=' . $viewList), JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error'
			);
		}
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
	
	public static function showFieldsetEdit($fields, $ignore = array()) {
		$form = array();
		foreach($fields as $field) {
			if(in_array($field->fieldname, $ignore)) {
				continue;
			}
			switch(strtolower($field->type)) {
				case 'hidden':
					$form[] = $field->input;
					break;
				default:
					$form[] = sprintf('
						<div class="control-group">
							<div class="control-label">%s</div>
							<div class="controls">%s</div>
						</div>
					', $field->label, $field->input);
					break;
			}
		}
		return implode('', $form);
	}

	public function showFieldsetDisplay($fields, $data, $ignore = array()) {
		$form = array();
		foreach($fields as $field) {
			$fieldName = $field->fieldname;
			$fieldDisplay = $fieldName . '_display';
			$fieldData = isset($data->$fieldDisplay) ? $data->$fieldDisplay : $data->$fieldName;

			if(in_array($fieldName, $ignore)) {
				continue;
			}
			switch(strtolower($field->type)) {
				case 'hidden':
					$form[] = $field->input;
					break;
				default:
					$form[] = sprintf('
						<div class="control-group">
							<div class="control-label">%s</div>
							<div class="controls" style="padding-top:5px;">%s</div>
						</div>
					', $field->label, nl2br($fieldData));
					break;
			}
		}
		return implode('', $form);
	}

	function dataToObject($data) {
		$count = count(array_shift(array_slice($data, 0, 1)));
		$result = array();
		for ($i = 1; $i < $count; $i++) {
			$item = new stdClass();
			foreach ($data as $k => $v) {
				$item->$k = $v[$i];
			}
			$result[$i] = $item;
		}
		return $result;
	}

	function isAdmin($userid = NULL) {
		$user = JFactory::getUser($userid);
		$admin_groups = array(7, 8);

		foreach ($admin_groups as $group_id) {
			if (in_array($group_id, $user->groups)) {
				return true;
				break;
			}
		}

		return false;
	}

	public static function arrayToFiles($array) {
		$files = array();
		foreach ($array as $field => $names) {
			foreach ($names as $name => $value) {
				$files[$name][$field] = $value;
			}
		}
		return JArrayHelper::toObject($files);
	}

	public static function cleanstr($str) {
		return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
	}

	/*
	  |---------------------------------------------------------------------------------
	  | @create a roman numeral from a number
	  | @param int $num
	  | @return string
	  |-------------------------------------------------------------------------------------
	  |
	 */

	function romanNumerals($num) {
		$n = intval($num);
		$res = '';

		/*		 * * roman_numerals array  ** */
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
			/*			 * * divide to get  matches ** */
			$matches = intval($n / $number);

			/*			 * * assign the roman char * $matches ** */
			$res .= str_repeat($roman, $matches);

			/*			 * * substract from the number ** */
			$n = $n % $number;
		}

		/*		 * * return the res ** */
		return $res;
	}
	
	public static function makeButton($type, $task, $ignoreList = false) {
		$buttons				= array();
		$buttons['apply']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_APPLY'), 'btn-success', 'icon-edit icon-white');
		$buttons['save']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_SAVE'), '', 'icon-ok');
		$buttons['save2new']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_SAVE_AND_NEW'), '', 'icon-plus');
		$buttons['save2copy']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_SAVE_AS_COPY'), '', 'icon-file');
		$buttons['download']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_DOWNLOAD'), 'btn-info', 'icon-download-alt icon-white');
		$buttons['cancel']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_CLOSE'), 'btn-danger', 'icon-remove-sign icon-white');
		$buttons['back']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_BACK'), '', 'icon-circle-arrow-left');
		$buttons['addNew']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_NEW'), 'btn-success', 'icon-plus-sign icon-white');
		$buttons['editList']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_EDIT'), '', 'icon-edit');
		$buttons['read']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_READ'), '', 'icon-eye-open');
		$buttons['publish']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_PUBLISH'), '', 'icon-ok');
		$buttons['unpublish']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_UNPUBLISH'), '', 'icon-remove-sign');
		$buttons['archiveList']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_ARCHIVE'), '', 'icon-inbox');
		$buttons['deleteList']	= array(JText::_('COM_JKCOMMODITY_TOOLBAR_EMPTY_TRASH'), 'btn-danger', 'icon-remove icon-white');
		$buttons['trash']		= array(JText::_('COM_JKCOMMODITY_TOOLBAR_TRASH'), 'btn-danger', 'icon-trash icon-white');
		$listButtons			= array('editList', 'publish', 'unpublish', 'archiveList', 'deleteList', 'trash');

		if(!in_array($type, array_keys($buttons))) {
			return false;
		}
		if(in_array($type, $listButtons) && !$ignoreList) {
			$action = sprintf("
				if(document.adminForm.boxchecked.value==0) {
					alert('%s');
				} else {
					Joomla.submitbutton('%s')
				}
			", JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), $task);
		} else {
			$action = "Joomla.submitbutton('$task')";
		}

		$icon = null;
		list($text, $class, $iconClass) = $buttons[$type];
		if($iconClass) {
			$icon = '<i class="'.$iconClass.'"></i> ';
		}
		$button = sprintf('
			<button type="button" class="btn %s" onclick="%s">%s%s</button>
		', $class, $action, $icon, $text);

		return $button;
	}
	
	public static function addSubmenu($vName) {
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_CATEGORY'),
			'index.php?option=com_jkcommodity&amp;view=ref_categories',
			$vName == 'ref_categories'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_COMMODITY'),
			'index.php?option=com_jkcommodity&amp;view=ref_commodities',
			$vName == 'ref_commodities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_CITY'),
			'index.php?option=com_jkcommodity&amp;view=ref_cities',
			$vName == 'ref_cities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_CITY_COMMODITY'),
			'index.php?option=com_jkcommodity&amp;view=ref_city_commodities',
			$vName == 'ref_city_commodities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_MARKET'),
			'index.php?option=com_jkcommodity&amp;view=ref_markets',
			$vName == 'ref_markets'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_GROUP_CITY'),
			'index.php?option=com_jkcommodity&amp;view=ref_group_cities',
			$vName == 'ref_group_cities'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JKCOMMODITY_LABEL_EXCEL_FORMAT'),
			'index.php?option=com_jkcommodity&amp;view=ref_excel_formats',
			$vName == 'ref_excel_formats'
		);

		/*
		JHtmlSidebar::addEntry(
			JText::_('COM_GTRESTO_CONFIG'),
			'index.php?option=com_config&amp;view=component&amp;component=com_gtpihps',
			$vName == 'component'
		);
		*/
	}

	public static function httpQuery($query, $postman = true) {
		$query = http_build_query($query, "", "&");
		$query = str_replace(array('%5B', '%5D'), array('[', ']'), $query);

		if($postman) {
			$query = str_replace(array('=', '&'), array(':', PHP_EOL), $query);
			$query = urldecode($query);
		}
		return $query;
	}

}
