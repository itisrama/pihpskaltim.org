<?php
/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.path' );
jimport( 'joomla.html.parameter' );
jimport( 'joomla.access.access' );

// NEW
jimport('joomla.utilities.arrayhelper');

$component_name = JRequest::getVar('option');
require_once( JPATH_COMPONENT_ADMINISTRATOR . DS . 'constants.php' );
// Require helpers
require_once( JK_ADMIN_HELPERS . DS . 'default.php' );
require_once( JK_ADMIN_HELPERS . DS . 'document.php' );
require_once( JK_ADMIN_HELPERS . DS . 'privilege.php' );
require_once( JK_ADMIN_HELPERS . DS . 'date.php' );
// New helpers
require_once( JK_ADMIN_HELPERS . DS . 'access.php' );
require_once( JK_ADMIN_HELPERS . DS . 'array.php' );
require_once( JK_ADMIN_HELPERS . DS . 'currency.php' );
require_once( JK_ADMIN_HELPERS . DS . 'fieldset.php' );
require_once( JK_ADMIN_HELPERS . DS . 'flot.php' );
require_once( JK_ADMIN_HELPERS . DS . 'html.php' );
require_once( JK_ADMIN_HELPERS . DS . 'morris.php' );
require_once( JK_ADMIN_HELPERS . DS . 'number.php' );
require_once( JK_ADMIN_HELPERS . DS . 'authencrypt.php' );

// Set Currency Helper
JKHelperHTML::loadHeaders();
JKHelperCurrency::setCurrency('Rp ', ',', '.');
