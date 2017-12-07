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

class TableUpload extends JTable
{
	var $id = null;

	var $city_id = null;
	var $market_id = null;
	var $date = null;
	var $file = null;
	var $checked_out = null;
	var $checked_out_time = null;
	var $created_by = null;
	var $created = null;
	var $published = 1;
	
	function __construct(&$db)
	{
		parent::__construct('#__jkcommodity_file', 'id', $db);
	}
}