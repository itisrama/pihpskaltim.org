<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once (dirname(__FILE__).DS.'helper.php');

$regencies			= modGTPIHPSPrices::getRegencies();
$priceTypes			= modGTPIHPSPrices::getPriceTypes();

$layout				= $params->get('layout', 'default');

// process data
$proc_data = array();

$prices = array();
foreach($proc_data as $item_data) {
	foreach ($item_data as $item) {
		$prices[] = $item;
	}
}

$document = JFactory::getDocument();
$component_url = JURI::root(true) . '/index.php?option=com_gtpihps';
$document->addScriptDeclaration("
// Set variables
	var component_url = '$component_url';
");

$document->addScript(JURI::root(true).'/modules/mod_gtpihps_prices/jquery.sparkline.min.js');
$document->addScript(JURI::root(true).'/modules/mod_gtpihps_prices/scriptprice.js');

require JModuleHelper::getLayoutPath('mod_gtpihps_prices', $layout);