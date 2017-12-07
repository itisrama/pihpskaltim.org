<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelRef_Region_Commodity extends GTModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem();
		if(!is_object($data)) return false;
		
		$province 	= parent::getItemExternal($data->province_id, 'ref_province');
		
		$data->province = $province->name;

		$data->commodities = $this->getNationalCommodities($data->id);

		$this->item	= $data;
		return $data;
	}

	public function getItemView() {
		$data		= parent::getItem();
		if(!is_object($data)) return false;

		$this->item	    = $data;
		return $data;
	}

	public function getNationalCommodities($region_id) {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select data.
		$query->select($db->quoteName(array('a.id', 'a.region_id', 'a.commodity_national_id')));
		$query->select($db->quoteName(array('b.name', 'b.denomination')));
		$query->from($db->quoteName('#__gtpihps_province_commodities', 'a'));
		$query->join('RIGHT', '#__gtpihps_national_commodities AS b ON a.commodity_national_id = b.id');

		$query->where($db->quoteName('a.region_id') . ' = ' . (int)$region_id);
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getCommodities($region_id) {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select data.
		$query->select($db->quoteName(array('a.id', 'a.region_id', 'a.commodity_national_id')));
		$query->from($db->quoteName('#__gtpihps_province_commodities', 'a'));

		$query->where($db->quoteName('a.region_id') . ' = ' . (int)$region_id);
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function save($data, $return_num = false) {
		$data		= JArrayHelper::toObject($data);
		$commodityInputs = $this->input->post->get('commodities', array(), 'array');

		if(!empty($commodityInputs)){
			$commodities = $this->getCommodities($data->id);

			$db 	= JFactory::getDbo();
			$query 	= 'INSERT INTO '.$db->quoteName('#__gtpihps_province_commodities').'('.$db->quoteName('id').','.$db->quoteName('commodity_national_id').') VALUES ';
		
			$values = array();
			
			$noChange = true;

			foreach ($commodities as $id => $commodity) {
				if(!$commodity->commodity_national_id){
					foreach($commodityInputs as $i => $commodityInput){
						if($commodityInput['id'] == $commodity->id){
							$values[] = '('.$db->quote($commodity->id).','.$db->quote($i).')';
							$noChange = false;
							break;
						}
					}
				}
				else if($commodityInputs[$commodity->commodity_national_id]['id'] != $commodity->id){
					$values[] = '('.$db->quote($commodity->id).', NULL)';
					$noChange = false;
				}
			}
			if(!$noChange){
				$query .= implode(',', $values);
				$query .= ' ON DUPLICATE KEY UPDATE '.$db->quoteName('commodity_national_id').' = VALUES('.$db->quoteName('commodity_national_id').')';
		
				$db->setQuery($query);

				$return = $db->execute();
			}
			else{
				$return = true;
			}
		}
		else{
			$return = true;
		}

		$return = $return && parent::save($data);
		return $return;
	}
}
