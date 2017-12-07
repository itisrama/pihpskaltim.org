<?php

/**
 * @package		GT PIHPS BCast
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class TableGroup extends GTTable
{
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		parent::__construct('#__gtpihpsbcast_groups', 'id', $db);
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
		
		$this->published = 1;
		// Set metadata
		if ($this->id) {
			// Existing item
			$this->modified		= $date->toSql();
			$this->modified_by	= $user->get('id');
		} else {
			// New item
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}

		$this->members = implode(',', $this->members);
		
		// Attempt to store the data.
		return parent::store($updateNulls);
	}
	
	public function bind($array, $ignore = '') {

		$row = JArrayHelper::toObject($array);
		
		if(!$row->id) 
			return parent::bind($array, $ignore);

		if(is_string($row->members)) {
			$row->members = explode(',', $row->members);
		}
		
		$array = JArrayHelper::fromObject($row);
		return parent::bind($array, $ignore);
	}
}
