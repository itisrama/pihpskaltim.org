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

class TableScheduler extends GTTable
{
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		parent::__construct('#__gtpihpsbcast_schedulers', 'id', $db);
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

		$this->schedules = implode(',', $this->schedules);
		$this->group_ids = implode(',', $this->group_ids);
		$this->market_ids = implode(',', $this->market_ids);
		$this->commodity_ids = $this->type == 'commodity' ? implode(',', $this->commodity_ids) : reset($this->commodity_ids);
		
		// Attempt to store the data.
		return parent::store($updateNulls);
	}
	
	public function bind($array, $ignore = '') {

		$row = JArrayHelper::toObject($array);
		
		if(!$row->id) 
			return parent::bind($array, $ignore);

		if(is_string($row->schedules)) {
			$row->schedules = explode(',', $row->schedules);
		}
		if(is_string($row->group_ids)) {
			$row->group_ids = explode(',', $row->group_ids);
		}
		if(is_string($row->market_ids)) {
			$row->market_ids = explode(',', $row->market_ids);
		}
		if(is_string($row->commodity_ids)) {
			$row->commodity_ids = explode(',', $row->commodity_ids);
		}

		$array = JArrayHelper::fromObject($row);
		return parent::bind($array, $ignore);
	}
}
