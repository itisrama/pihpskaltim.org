<?php
defined('_JEXEC') or die('Restricted access');
$component_name = JRequest::getVar('option');
$menu_id = JRequest::getVar('Itemid');
$menu_id = $menu_id ? '&Itemid='.$menu_id : NULL;

/* GLOBAL PATHS
---------------------------------- */
// Component Root Path
define( 'JK_GLOBAL_ROOT', JPATH_BASE . DS . 'components' . DS . $component_name );

// Temporary Path
define( 'JK_GLOBAL_TEMP', JK_GLOBAL_ROOT . DS . 'temp' );

// URI 
define( 'JK_GLOBAL_URI', JURI::base( true ) . '/components/' . $component_name  );

// Component URI
define( 'JK_GLOBAL_COMPONENT', JURI::base( true ) . '/?option=' . $component_name  );

// Assets URI
define( 'JK_GLOBAL_ASSETS', JK_GLOBAL_URI . '/assets' );

// Image URI
define( 'JK_GLOBAL_IMAGES', JK_GLOBAL_ASSETS . '/images' );

// Javascript URI
define( 'JK_GLOBAL_JS', JK_GLOBAL_ASSETS . '/js' );

// CSS URI
define( 'JK_GLOBAL_CSS', JK_GLOBAL_ASSETS . '/css' );


/* SITE PATHS
---------------------------------- */
// Component Root Path
define( 'JK_ROOT', JPATH_ROOT . DS . 'components' . DS . $component_name );

// Table Path
define( 'JK_TABLES', JK_ROOT . DS . 'tables' );

// Model Path
define( 'JK_MODELS', JK_ROOT . DS . 'models' );

// View Path
define( 'JK_VIEWS', JK_ROOT . DS . 'views' );

// Controller Path
define( 'JK_CONTROLLERS', JK_ROOT . DS . 'controllers' );

// Temporary Path
define( 'JK_TEMP', JK_ROOT . DS . 'temp' );

// Temporary Path
define( 'JK_FILES', JK_ROOT . DS . 'files' );

// URI 
define( 'JK_URI', JURI::root( true ) . '/components/' . $component_name  );

// Component URI
define( 'JK_COMPONENT', 'index.php/?option=' . $component_name . $menu_id );

// Assets URI
define( 'JK_ASSETS', JK_URI . '/assets' );

// Image URI
define( 'JK_IMAGES', JK_ASSETS . '/images' );

// Javascript URI
define( 'JK_JS', JK_ASSETS . '/js' );

// CSS URI
define( 'JK_CSS', JK_ASSETS . '/css' );


/* ADMIN PATHS
---------------------------------- */
// Component Root Path
define( 'JK_ADMIN_ROOT', JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . $component_name );

// Core Path
define( 'JK_ADMIN_CORE', JK_ADMIN_ROOT . DS . 'core' );

// Table Path
define( 'JK_ADMIN_TABLES', JK_ADMIN_ROOT . DS . 'tables' );

// Model Path
define( 'JK_ADMIN_MODELS', JK_ADMIN_ROOT . DS . 'models' );

// View Path
define( 'JK_ADMIN_VIEWS', JK_ADMIN_ROOT . DS . 'views' );

// Controller Path
define( 'JK_ADMIN_CONTROLLERS', JK_ADMIN_ROOT . DS . 'controllers' );

// Helper Path
define( 'JK_ADMIN_HELPERS', JK_ADMIN_ROOT . DS . 'helpers' );

// Temporary Path
define( 'JK_ADMIN_TEMP', JK_ADMIN_ROOT . DS . 'temp' );

// URI 
define( 'JK_ADMIN_URI', JURI::root( true ) . '/administrator/components/' . $component_name  );

// Component URI
define( 'JK_ADMIN_COMPONENT', JURI::root( true ) . '/administrator/?option=' . $component_name  );

// Assets URI
define( 'JK_ADMIN_ASSETS', JK_ADMIN_URI . '/assets' );

// Image URI
define( 'JK_ADMIN_IMAGES', JK_ADMIN_ASSETS . '/images' );

// Javascript URI
define( 'JK_ADMIN_JS', JK_ADMIN_ASSETS . '/js' );

// CSS URI
define( 'JK_ADMIN_CSS', JK_ADMIN_ASSETS . '/css' );

// LIBRARIES URI
define( 'JK_ADMIN_LIBRARIES', JK_ADMIN_URI . '/libraries' );