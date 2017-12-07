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
$default_date 	= JHtml::date(@modGTPIHPSTable::getLatestDate(JHtml::date('+1 days', 'Y-m-d')), 'd-m-Y');
$date			= $input->get('filter_map_date', $default_date);

// Data
$items			= modGTPIHPSTable::getItems($date);
$provinces		= modGTPIHPSTable::getProvinces();
$commodities	= modGTPIHPSTable::getCommodities();


$document 		= JFactory::getDocument();
$document->addScript(JURI::root(true).'modules/mod_gtpihps_table/table.js');

require JModuleHelper::getLayoutPath('mod_gtpihps_table', $params->get('layout', 'default'));