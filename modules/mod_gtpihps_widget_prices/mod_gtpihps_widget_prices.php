<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

//JFactory::getApplication()->setHeader('X-Frame-Options', 'GOFORIT');

// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

$jinput = JFactory::getApplication()->input;

$filter_raw	= explode(',', $jinput->get('filter', '','raw'));
$filters	= array();
foreach($filter_raw as $filter){
	$filters[] = trim($filter);
}

$theme 			= $jinput->get('theme', null);
$show_province 	= in_array('province', $filters);
$show_market	= in_array('market', $filters);
$show_city 		= in_array('city', $filters) || $show_market;
$province 		= $jinput->get('province', null);
$province_id 	= $jinput->get('province_id', null);
$city 			= $jinput->get('city', null);
$city_id		= $jinput->get('city_id', null);
$market 		= $jinput->get('market', null);
$market_id	 	= $jinput->get('market_id', null);

$theme			= $theme? $theme 							: $params->get('theme', null);
$show_province	= $show_province ? $show_province 			: ($params->get('show_province', null) 	== '1' ? true : false);
$show_market	= $show_market ? $show_market				: ($params->get('show_market', null) 	== '1' ? true : false);
$show_city		= $show_city || $show_market ? $show_city	: ($params->get('show_city', null) 		== '1' ? true : false);

$province 		= $province ? $province 				: $params->get('province', null);
$province_id  	= $province_id ? $province_id 			: $params->get('province_id', 0);
$city			= $city ? $city 						: $params->get('city', null);
$city_id 		= $city_id  ? $city_id 					: $params->get('city_id', 0);
$market			= $market ? $market	 					: $params->get('market', null);
$market_id	 	= $market_id ? $market_id 				: $params->get('market_id', 0);

$layout	   		= $params->get('layout', 'default');

require JModuleHelper::getLayoutPath('mod_gtpihps_widget_prices', $layout);

