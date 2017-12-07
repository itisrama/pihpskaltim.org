<?php

/**
 * @package		GT PIHPS
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class TableRef_Group_City extends JKTable{

    public $group;
    public $city;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		parent::__construct('#__jkcommodity_group_city', 'id', $db);
	}
	
	public function bind($array, $ignore = '') {
		$row = JArrayHelper::toObject($array);

		$array = JArrayHelper::fromObject($row);
		return parent::bind($array, $ignore);
	}
}
