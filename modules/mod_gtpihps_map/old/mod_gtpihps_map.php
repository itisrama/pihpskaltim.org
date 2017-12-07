<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once (dirname(__FILE__).DS.'helper.php');
require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_gtpihps' . DS . 'helpers' . DS . 'html.php');
require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_gtpihps' . DS . 'helpers' . DS . 'currency.php');

GTHelperCurrency::setCurrencyDefault();

// Input
$input			= JFactory::getApplication()->input;
$commodity_id	= $input->get('filter_map_commodity_id', @modGTPIHPSMap::getCommodity()->id);
$default_date 	= JHtml::date(@modGTPIHPSMap::getLatestDate(), 'd-m-Y');
$date			= JHtml::date($input->get('filter_map_date', $default_date), 'Y-m-d');
$commodity		= modGTPIHPSMap::getCommodity($commodity_id);

// Data
$provinces = modGTPIHPSMap::getProvinces($commodity_id, $date);
$commodities = modGTPIHPSMap::getCommodities();
$categories = modGTPIHPSMap::getCategories();

$commodityOptions = GTHelperHtml::setCommodities($categories[0], $categories, $commodities, 'select');

require JModuleHelper::getLayoutPath('mod_gtpihps_map', $params->get('layout', 'default'));