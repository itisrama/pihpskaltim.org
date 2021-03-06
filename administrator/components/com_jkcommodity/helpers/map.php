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

class JKHelperMap {
	
	function initialize($id, $marker=array())
	{
		self::loadLibrary($id);
		self::setMarker($marker);
	}
	
	function loadLibrary($id)
	{
		$document = JFactory::getDocument();
		$api_key = 'AIzaSyCk-B4PDquNoM5WOQmGZLj8Q45Idy9r5Ow';
		$sensor = 'false';
		$component_assets_uri = JURI::root( true ) . '/components/com_jkcommodity/assets';
		$document->addScript(sprintf('http://maps.googleapis.com/maps/api/js?key=%s&sensor=%s', $api_key, $sensor));
		$document->addScript($component_assets_uri . '/js/map.js');
		$document->addScriptDeclaration("
			var canvas_id = '$id';
		");
	}
	
	function setMarker($markers) {
		$document = JFactory::getDocument();
		$markers = json_encode(array_values($markers));
		$document->addScriptDeclaration("
			var markers = '$markers';
		");
	}
}