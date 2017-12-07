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

class TableRef_Category extends JTable{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		parent::__construct('#__jkcommodity_category', 'id', $db);
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
		
		// New newsfeed. A feed created and created_by field can be set by the user,
		// so we don't touch either of these if they are set.
		if (!intval($this->created)) {
			$this->created = $date->toSql();
		}
		if (empty($this->created_by)) {
			$this->created_by = $user->get('id');
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}
