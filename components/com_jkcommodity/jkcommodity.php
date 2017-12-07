<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Define DS
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

// Load constants and helpers
require_once( JPATH_COMPONENT_ADMINISTRATOR . DS . 'loader.php' );

// Require the core
require_once( JK_ADMIN_CORE . DS . 'controller.php' );
require_once( JK_ADMIN_CORE . DS . 'controllerform.php' );
require_once( JK_ADMIN_CORE . DS . 'controlleradmin.php' );
require_once( JK_ADMIN_CORE . DS . 'model.php' );
require_once( JK_ADMIN_CORE . DS . 'modeladmin.php' );
require_once( JK_ADMIN_CORE . DS . 'modellist.php' );
require_once( JK_ADMIN_CORE . DS . 'modelform.php' );
require_once( JK_ADMIN_CORE . DS . 'view.php' );

// Load default controller
require_once( JK_CONTROLLERS . DS . 'controller.php' );

// By default, we use the tables specified at the back end.
JTable::addIncludePath( JK_ADMIN_TABLES );

// We treat the view as the controller. Load other controller if there is any.
$controller	= JKController::getInstance( 'JKCommodity' );

// Execute the task.
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
