<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelRef_Province extends GTModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem();
		if(!is_object($data)) return false;
		$this->item	= $data;
		return $data;
	}

	public function getItemView() {
		$data		= parent::getItem();
		if(!is_object($data)) return false;

		$this->item	    = $data;
		return $data;
	}

	public function save($data, $return_num = false) {
		$data		= JArrayHelper::toObject($data);

		$return = parent::save($data);
		return $return;
	}

	public function getRegions($all = false) {
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_regions', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.province_id') . ' = '. intval($province_id));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		
		$data = array();
		foreach ($raw as $item) {
			$data[$item->id] = $item->name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}
	
	public function getRegionOptions() {
		return GTHelperArray::toOption($this->getRegions(true));
	}
}
