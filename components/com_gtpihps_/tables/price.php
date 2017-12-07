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

class TablePrice extends GTTable{
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		// Get Feeder ID
		$input		= JFactory::getApplication()->input;

		parent::__construct('#__gtpihps_prices' , 'id', $db);
	}
	
	/**
	 * Stores a contact
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false) {
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		// Set metadata
		if ($this->id) {
			// Existing item
			$this->modified		= $date->toSql();
			$this->modified_by	= $user->get('id');
		} else {
			$this->published = 1;
			// New item
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}
		
		// Attempt to store the data.
		return parent::store($updateNulls);
	}
	
	public function bind($array, $ignore = '') {
		$row = JArrayHelper::toObject($array);
		
		if(!@$row->id) 
			return parent::bind($array, $ignore);

		$array = JArrayHelper::fromObject($row);
		return parent::bind($array, $ignore);
	}
}
