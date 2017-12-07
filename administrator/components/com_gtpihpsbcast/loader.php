<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
jimport('joomla.html.parameter');
jimport('joomla.access.access');
jimport('joomla.utilities.arrayhelper');

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'constants.php');

// Require helpers
require_once (GT_ADMIN_HELPERS . DS . 'default.php');
require_once (GT_ADMIN_HELPERS . DS . 'access.php');
require_once (GT_ADMIN_HELPERS . DS . 'html.php');
require_once (GT_ADMIN_HELPERS . DS . 'array.php');
require_once (GT_ADMIN_HELPERS . DS . 'fieldset.php');
require_once (GT_ADMIN_HELPERS . DS . 'number.php');
require_once (GT_ADMIN_HELPERS . DS . 'date.php');
require_once (GT_ADMIN_HELPERS . DS . 'currency.php');
require_once (GT_ADMIN_HELPERS . DS . 'morris.php');

// Set Currency Helper
GTHelperCurrency::setCurrency('Rp ', ',', '.');
