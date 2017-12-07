<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

//JFactory::getApplication()->setHeader('X-Frame-Options', 'GOFORIT');

// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once (dirname(__FILE__).DS.'helper.php');

$jinput = JFactory::getApplication()->input;
 
$filter_raw	= explode(',', $jinput->get('filter', '','raw'));

$filters	= array();
foreach($filter_raw as $filter){
	$filters[] = trim($filter);
}


$theme 			= $jinput->get('theme', null);
$show_province 	= in_array('province', $filters);
$show_category 	= in_array('category', $filters);
$show_commodity = in_array('commodity', $filters);
$province 		= $jinput->get('province', null);
$province_id 	= $jinput->get('province_id', null);
$category 		= $jinput->get('category', null);
$category_id	= $jinput->get('category_id', null);
$commodity 		= $jinput->get('commodity', null);
$commodity_id 	= $jinput->get('commodity_id', null);

$theme			= $theme? $theme 						: $params->get('theme', null);
$show_province	= $show_province ? $show_province 		: ($params->get('show_province', null) == '1' ? 'true' : 'false');
$show_category	= $show_category ? $show_category 		: ($params->get('show_category', null) == '1' ? 'true' : 'false');
$show_commodity	= $show_commodity ? $show_commodity		: ($params->get('show_commodity',null) == '1' ? 'true' : 'false');

$province 		= $province ? $province 				: $params->get('province', null);
$province_id  	= $province_id ? $province_id 			: $params->get('province_id', null);
$category		= $category ? $category 				: $params->get('category', null);
$category_id 	= $category_id  ? $category_id 			: $params->get('category_id', null);
$commodity		= $commodity ? $commodity	 			: $params->get('commodity', null);
$commodity_id 	= $commodity_id ? $commodity_id 		: $params->get('commodity_id', null);

$layout	   		= $params->get('layout', 'default');

// process data
//$data = json_decode(file_get_contents(JURI::root(true).'?option=com_gtpihps&task=json.commodityPrices&province_id=' . $province_id . '&commodity_id=' . $commodity_id));
//$prices = $data->prices;

require JModuleHelper::getLayoutPath('mod_gtpihps_widget_commodities', $layout);

