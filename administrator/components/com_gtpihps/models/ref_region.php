<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelRef_Region extends GTModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem();
		if(!is_object($data)) return false;

		$data->commodities	= GTHelperArray::toJSON($this->getTableGrid('ref_region_commodity'));

		$this->item	= $data;
		return $data;
	}

	public function getItemView() {
		$data		= parent::getItem();
		if(!is_object($data)) return false;

		$this->item	    = $data;
		return $data;
	}

	public function getTableGrid($name, $id = null, $id_only = false) {
		$regionId	= $id ? $id : $this->state->get('ref_region.id');
		$table 	= $this->getTable($name);

		$selfields = $id_only ? array('id') : array();
		$data = $table->getList(array('region_id' => $regionId), array(), $selfields);

		return $data;
	}

	public function saveTableGrid($cityId, $data, $table) {
		$rawdata 	= (array) json_decode($data);
		$cleandata 	= array();

		if($rawdata) {
			$keys = array_keys($rawdata);
			$rows = $rawdata[$keys[0]];
			foreach ($rows as $k => $value) {
				foreach ($keys as $key) {
					$cleandata[$k][$key] = $rawdata[$key][$k];
				}
			}
		}
		$return = true;
		foreach($cleandata as $k => $item) {
			$item 			= GTHelperArray::handleItem($item);
			$item 			= JArrayHelper::toObject($item);
			$item->city_id	= $cityId;

			$return = parent::saveExternal($item, $table);
		}

		return $return;
	}

	public function save($data, $return_num = false) {
		$data		= JArrayHelper::toObject($data);
		
		if(!$this->saveTableGrid($data->id, $data->commodities, 'ref_region_commodity')) return false;

		$return = parent::save($data);
		return $return;
	}
}
