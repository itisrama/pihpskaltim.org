<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class JKCommodityModelRef_City_Commodity extends JKModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$data		= parent::getItemExternal($pk, 'ref_city');
		if(!is_object($data)) return false;

		$data->commodities = $this->getCommodities($data->id);

		$this->item	= $data;
		return $data;
	}

	public function getCommodities($city_id) {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select data.
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.denomination', 'a.multiplier', 'a.commodity_id')));
		$query->from($db->quoteName('#__jkcommodity_city_commodity', 'a'));
		$query->where($db->quoteName('a.city_id') . ' = ' . (int)$city_id);
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
	
	public function save($data, $return_num = false) {
		$data		= JArrayHelper::toObject($data);
		$commodities = $this->input->post->get('commodities', array(), 'array');

		foreach ($commodities as $id => $commodity) {
			$commodity = JArrayHelper::toObject($commodity);
			$commodity->id = $id;
			parent::saveExternal($commodity, 'city_commodity');
		}
		$return = parent::save($data);
		return $return;
	}
}
