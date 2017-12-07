<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

require_once (dirname(__FILE__).DS.'helper.php');
require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_gtpihps' . DS . 'helpers' . DS . 'array.php');
require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_gtpihps' . DS . 'helpers' . DS . 'html.php');

$regencyOptions		= modGTPIHPSQuickPriceFind::getRegencyOptions();
$commodityOptions	= modGTPIHPSQuickPriceFind::getCommodityOptions();
$dates 				= modGTPIHPSQuickPriceFind::getStatLatestDate();
$document = JFactory::getDocument();
$document->addScript(JURI::root(true).'/modules/mod_gtpihps_quickpricefind/script.js');

$component_url = JRoute::_(JURI::root() . 'index.php?Itemid=114', false);

$app			= JFactory::getApplication(); 
$menu			= $app->getMenu();
$componentURL	= 'index.php?option=com_gtpihps&view=regency_statistics';
$menuItem		= $menu->getItems( 'link', $componentURL, true );
$componentURL	= @$menuItem->id > 0 ? JRoute::_('index.php?Itemid='.$menuItem->id) : $componentURL;


$document->addScriptDeclaration("
// Set variables
	var component_url = '$component_url';
");

require JModuleHelper::getLayoutPath('mod_gtpihps_quickpricefind', $params->get('layout', 'default'));