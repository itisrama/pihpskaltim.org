<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelPrice extends GTModelAdmin{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem($pk);
		if(!is_object($data)) return false;
		
		$this->item	= $data;
		return $data;
	}

	public function getItemView($pk = null) {
		$data		= parent::getItem($pk);
		if(!is_object($data)) return false;

		$this->item	= $data;
		return $data;
	}

	public function getItemDetails($pks = array(), $province_id = null, $key = 'commodity_id') {
		$pks = $pks ? $pks : $this->getState('price.id');
		$pks = is_numeric($pks) ? array($pks) : $pks;
		JArrayHelper::toInteger($pks, 0);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id', 'a.commodity_id', 'a.price')));
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));
		$query->where($db->quoteName('a.price_id') . ' IN (' . implode(', ', $pks) . ')');
		
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList($key);
	}

	public function getLatestPrices() {
		$province_id = sprintf('%02d', $this->input->get('province_id'));
		$regency_id = $this->input->get('regency_id');
		$market_id = $this->input->get('market_id');
		$date = JHtml::date($this->input->get('date'), 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->where($db->quoteName('a.regency_id') . ' = ' . intval($regency_id));
		$query->where($db->quoteName('a.market_id') . ' = ' . intval($market_id));
		
		$query->where($db->quoteName('a.date') . ' < ' . $db->quote($date));

		$query->order($db->quoteName('a.date') . 'desc');
		$query->limit(1);

		$db->setQuery($query);
		$price_id = @$db->loadObject()->id;
		//echo nl2br(str_replace('#__','pihps_',$query));

		$data = $price_id ? $this->getItemDetails($price_id, $province_id) : array();
		return $data;
	}

	public function getLatestDate($province_id, $market_ids, $date) {
		$province_id = sprintf('%02d', $province_id);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->where($db->quoteName('a.market_id') . ' IN (' . implode(',', $market_ids) . ')');
		
		$query->where($db->quoteName('a.date') . ' <= ' . $db->quote($date));

		$query->order($db->quoteName('a.date') . 'desc');
		$query->limit(1);

		$db->setQuery($query);
		//echo nl2br(str_replace('#__','pihps_',$query));

		return isset($db->loadObject()->date) ? $db->loadObject()->date : $date;
	}

	public function getMarket($market_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.regency_id')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.id') . ' = '. intval($market_id));

		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getMarketBySource($region_id, $market_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.regency_id')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.region_id') . ' = '. intval($region_id));
		$query->where($db->quoteName('a.market_source_id') . ' = '. intval($market_id));

		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getMarkets($all = false) {
		$regency_id = $this->input->get('regency_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.regency_id') . ' = '. intval($regency_id));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->id] = $item->name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getMarketOptions() {
		return GTHelperArray::toOption($this->getMarkets(true));
	}

	public function getRegencies($all = false) {
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
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

	public function getRegencyOptions() {
		return GTHelperArray::toOption($this->getRegencies(true));
	}

	public function getCommodityList() {
		$commodities	= $this->getCommodities();
		$categories		= $this->getCategories();

		return GTHelperHtml::setCommodities($categories[0], $categories, $commodities);
	}

	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').', ")") name');

		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item->name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item->name;
		}
		return $data;
	}

	public function getID($market_id, $date) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		$query->where($db->quoteName('a.market_id') . ' = ' . $db->quote($market_id));
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));

		//echo nl2br(str_replace('#__','pihps_',$query));

		$db->setQuery($query);
		return intval(@$db->loadObject()->id);		
	}

	public function save($data) {
		$data = is_array($data) ? JArrayHelper::toObject($data) : $data;
		
		$prevID		= $data->id;
		$data->id 	= $this->getID($data->market_id, $data->date);
		if($data->id != $prevID && $prevID > 0) {
			$this->delete(array($prevID));
			$data->published = 1;
		}

		$data->date = JHtml::date($data->date, 'Y-m-d');
		$details = $data->details; unset($data->details);

		$prevDate = $data->date;
		if($data->id > 0 && $prevID > 0) {
			$prevDate = $this->getItem($data->id)->date;
		}

		$return = $this->saveMaster($data, $province_id);
		$this->saveDetail($details, $province_id);
		
		return $return;
	}

	public function saveMaster($data, $province_id, $return_num = false) {
		$data	= is_array($data) ? JArrayHelper::toObject($data) : $data;
		$return	= parent::save($data);
		$return	= $return_num ? $this->getState($this->getName() . '.id') : $return;
		return $return;
	}

	public function saveDetail($data, $province_id, $price_id = null) {
		$data			= is_object($data) ? JArrayHelper::fromObject($data) : $data;
		$price_id		= !$price_id ? $this->getState($this->getName() . '.id') : $price_id;
		$detailTable	= 'price_details';
		
		if(is_numeric($price_id)) {
			$details 	= array();
			$detailIDs	= $this->getItemDetails($price_id, $province_id);
			foreach ($data as $commodity_id => $price) {
				$price = is_string($price) ? GTHelperCurrency::toNumber($price) : $price;
				if(!$price) continue;

				$detail = new stdClass();
				$detail->id = intval(@$detailIDs[$commodity_id]->id);
				$detail->price_id = $price_id;
				$detail->commodity_id = $commodity_id;
				$detail->price = is_numeric($price) ? $price : GTHelperCurrency::toNumber($price);

				$details[] = $detail;

				if(isset($detailIDs[$commodity_id])) {
					unset($detailIDs[$commodity_id]);
				}
			}

			$this->saveBulk($details, $detailTable, false);

			foreach ($detailIDs as &$detailID) {
				$detailID = $detailID->id;
			}
			parent::deleteExternal($detailIDs, $detailTable);
		}
	}

	public function delete($pks) {
		$province_id	= $this->input->get('province_id');
		$dates			= array();
		foreach ($pks as $pk) {
			$dates[] = $this->getItem($pk)->date;
		}

		$details = $this->getItemDetails($pks, $province_id, 'id');
		$details = array_keys($details);
		parent::deleteExternal($details, 'price_detail');

		return parent::delete($pks);
	}
}
