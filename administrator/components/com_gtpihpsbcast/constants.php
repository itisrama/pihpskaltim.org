<?php
defined('_JEXEC') or die('Restricted access');
$component_name	= JRequest::getVar('option');
$menu_id		= JRequest::getVar('Itemid');
$menu_id		= $menu_id ? '&Itemid=' . $menu_id : NULL;

/* GLOBAL PATHS
 ---------------------------------- */

// Component Root Path
define('GT_GLOBAL_ROOT', JPATH_BASE . DS . 'components' . DS . $component_name);

// Temporary Path
define('GT_GLOBAL_TEMP', GT_GLOBAL_ROOT . DS . 'temp');

// URI
define('GT_GLOBAL_URI', JURI::base(true) . '/components/' . $component_name);

// Component URI
define('GT_GLOBAL_COMPONENT', JURI::base(true) . '/?option=' . $component_name);

// Assets URI
define('GT_GLOBAL_ASSETS', GT_GLOBAL_URI . '/assets');

// Image URI
define('GT_GLOBAL_IMAGES', GT_GLOBAL_ASSETS . '/images');

// Javascript URI
define('GT_GLOBAL_JS', GT_GLOBAL_ASSETS . '/js');

// CSS URI
define('GT_GLOBAL_CSS', GT_GLOBAL_ASSETS . '/css');

/* SITE PATHS
 ---------------------------------- */

// Component Root Path
define('GT_ROOT', JPATH_ROOT . DS . 'components' . DS . $component_name);

// Table Path
define('GT_TABLES', GT_ROOT . DS . 'tables');

// Model Path
define('GT_MODELS', GT_ROOT . DS . 'models');

// View Path
define('GT_VIEWS', GT_ROOT . DS . 'views');

// Controller Path
define('GT_CONTROLLERS', GT_ROOT . DS . 'controllers');

// Temporary Path
define('GT_TEMP', GT_ROOT . DS . 'temp');

// Temporary Path
define('GT_FILES', GT_ROOT . DS . 'files');

// URI
define('GT_URI', JURI::root(true) . '/components/' . $component_name);

// Component URI
define('GT_COMPONENT', 'index.php/?option=' . $component_name . $menu_id);

// Assets URI
define('GT_ASSETS', GT_URI . '/assets');

// Image URI
define('GT_IMAGES', GT_ASSETS . '/images');

// Javascript URI
define('GT_JS', GT_ASSETS . '/js');

// CSS URI
define('GT_CSS', GT_ASSETS . '/css');

/* ADMIN PATHS
 ---------------------------------- */

// Component Root Path
define('GT_ADMIN_ROOT', JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . $component_name);

// Core Path
define('GT_ADMIN_CORE', GT_ADMIN_ROOT . DS . 'core');

// Table Path
define('GT_ADMIN_TABLES', GT_ADMIN_ROOT . DS . 'tables');

// Model Path
define('GT_ADMIN_MODELS', GT_ADMIN_ROOT . DS . 'models');

// View Path
define('GT_ADMIN_VIEWS', GT_ADMIN_ROOT . DS . 'views');

// Controller Path
define('GT_ADMIN_CONTROLLERS', GT_ADMIN_ROOT . DS . 'controllers');

// Helper Path
define('GT_ADMIN_HELPERS', GT_ADMIN_ROOT . DS . 'helpers');

// Temporary Path
define('GT_ADMIN_TEMP', GT_ADMIN_ROOT . DS . 'temp');

// URI
define('GT_ADMIN_URI', JURI::root(true) . '/administrator/components/' . $component_name);

// Component URI
define('GT_ADMIN_COMPONENT', JURI::root(true) . '/administrator/?option=' . $component_name);

// Assets URI
define('GT_ADMIN_ASSETS', GT_ADMIN_URI . '/assets');

// Image URI
define('GT_ADMIN_IMAGES', GT_ADMIN_ASSETS . '/images');

// Javascript URI
define('GT_ADMIN_JS', GT_ADMIN_ASSETS . '/js');

// CSS URI
define('GT_ADMIN_CSS', GT_ADMIN_ASSETS . '/css');

// LIBRARIES URI
define('GT_ADMIN_LIBRARIES', GT_ADMIN_URI . '/libraries');
