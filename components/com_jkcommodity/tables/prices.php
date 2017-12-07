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

class TablePrices extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__jkcommodity_price_detail', 'id', $db);
	}
}