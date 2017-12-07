<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelRegency_Statistics extends GTModelList
{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) {
		parent::populateState($ordering, $direction);
		
		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		if ($layout) {
			$this->context.= '.' . $layout;
		}

		$this->setState('list.start', 0);
		$this->setState('list.limit', 0);
		
		$province_ids		= $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', array(0), 'array');
		JArrayHelper::toInteger($province_ids, 0);
		$this->setState('filter.province_ids', $province_ids);
		
		$regency_ids		= $this->getUserStateFromRequest($this->context . '.filter.regency_ids', 'filter_regency_ids', array(0), 'array');
		JArrayHelper::toInteger($regency_ids, 0);
		$this->setState('filter.regency_ids', $regency_ids);
		
		$market_ids			= $this->getUserStateFromRequest($this->context . '.filter.market_ids', 'filter_market_ids', array(0), 'array');
		JArrayHelper::toInteger($market_ids, 0);
		$this->setState('filter.market_ids', $market_ids);
		
		$all_commodities	= $this->getUserStateFromRequest($this->context . '.filter.all_commodities', 'filter_all_commodities', 0);
		$this->setState('filter.all_commodities', intval($all_commodities));
		

		$cids = array_keys($this->getCommodities(true));
		$commodity_ids		= $this->getUserStateFromRequest($this->context . '.filter.commodity_ids', 'filter_commodity_ids', $cids, 'array');
		$this->setState('filter.commodity_ids', $all_commodities ? array() : $commodity_ids);

		// Set Date Filters
		$interval		= $this->getInterval();
		$latest_sdate	= $this->getLatestDate('now', $interval);
		$latest_edate	= $this->getLatestDate('now', 1);
		
		$start_date		= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date', $latest_sdate);
		$end_date		= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date', $latest_edate);
		
		$dates	= array(JHtml::date($start_date, 'Y-m-d'), JHtml::date($end_date, 'Y-m-d'));
		$sdate	= min($dates);
		$edate	= max($dates);
		
		$this->setState('filter.start_date', JHtml::date($sdate, 'd-m-Y'));
		$this->setState('filter.end_date', JHtml::date($edate, 'd-m-Y'));
		$this->setState('filter.layout', $layout);
	}

	protected function getInterval() {
		$layout			= $this->input->get('layout');
		switch ($layout) {
			default:
				$interval = 20;
				break;
			case 'weekly':
				$interval = 5;
				break;
			case 'monthly':
				$interval = 20;
				break;
			case 'yearly':
				$interval = 240;
				break;
		}

		return $interval;
	}

	protected function getCategoryIDs() {
		$commodity_ids		= $this->getState('filter.commodity_ids');
		$all_commodities	= $this->getState('filter.all_commodities');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('DISTINCT '.$db->quoteName('a.category_id'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$all && !$all_commodities) {
			$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $commodity_ids).')');
		}
		
		$db->setQuery($query);
		return $db->loadColumn();
	}

	protected function getLatestDate($date = null, $row = 1) {
		$row 			= intval($row);
		$date 			= $date ? $date : 'now';
		$date 			= JHtml::date($date, 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');

		$query->where($db->quoteName('a.date') . ' <= ' . $db->quote($date));

		$query->group($db->quoteName('a.date'));
		$query->order($db->quoteName('a.date').' desc');
		$query->setLimit(1, $row-1);

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadResult();
	}

	public function getData($type = 'commodity') {
		$layout 			= $this->input->get('layout');
		$province_ids		= $this->getState('filter.province_ids');
		$regency_ids		= $this->getState('filter.regency_ids');
		$market_ids			= $this->getState('filter.market_ids');
		$commodity_ids		= $this->getState('filter.commodity_ids');
		$all_commodities	= $this->getState('filter.all_commodities');
		$start_date			= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date			= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');
		

		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// SELECT & JOIN
		// =========================================================
		// Select Price Details
		$query->select('AVG(IF('.$db->quoteName('a.price').' > 0, '.$db->quoteName('a.price').', NULL)) price');
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));

		// Join Prices
		$query->select($db->quoteName(array('b.regency_id', 'b.market_id', 'b.date')));
		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b') . ' ON ' . $db->quoteName('a.price_id') . ' = ' . $db->quoteName('b.id'));

		// Join Markets
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'd') . ' ON ' . $db->quoteName('b.market_id') . ' = ' . $db->quoteName('d.id'));

		// FILTERING
		// =========================================================
		// Publish filter
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('a.price') . ' > 50');

		// Price Type Filter
		$price_type_id	= $this->menu->params->get('price_type_id');
		$query->where($db->quoteName('d.price_type_id') . ' = '.$db->quote($price_type_id));

		// Province filter
		if(array_filter($province_ids)) {
			$query->where($db->quoteName('b.province_id') . ' IN ('.implode(',', $province_ids).')');
		}

		// Regency filter
		if(array_filter($regency_ids)) {
			$query->where($db->quoteName('b.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		}

		// Market filter
		if(array_filter($market_ids)) {
			$query->where($db->quoteName('b.market_id') . ' IN ('.implode(',', $market_ids).')');
		}

		// Dates filter
		switch ($layout) {
			default:
				$start_date = $price_type_id == 1 ? $start_date : JHtml::date($start_date.' -7 days', 'Y-m-d');
				$query->where($db->quoteName('b.date').' >= '.$db->quote($start_date));
				$query->where($db->quoteName('b.date').' <= '.$db->quote($end_date));
				break;
			case 'weekly':
				$start_date	= JHtml::date($start_date, 'Y') . sprintf('%02d', JHtml::date($start_date, 'W'));
				$end_date	= JHtml::date($end_date, 'Y') . sprintf('%02d', JHtml::date($end_date, 'W'));
				$query->where($db->quoteName('b.date').' >= STR_TO_DATE('.$db->quote($start_date.' Monday').', '.$db->quote('%X%V %W').')');
				$query->where($db->quoteName('b.date').' <= STR_TO_DATE('.$db->quote($end_date.' Monday').', '.$db->quote('%X%V %W').')');
				break;
			case 'monthly':
				$query->where($db->quoteName('b.date').' > LAST_DAY(SUBDATE('.$db->quote($start_date).', INTERVAL 1 MONTH))');
				$query->where($db->quoteName('b.date').' <= LAST_DAY('.$db->quote($end_date).')');
				break;
			case 'yearly':
				$query->where('YEAR('.$db->quoteName('b.date').') >= YEAR('.$db->quote($start_date).')');
				$query->where('YEAR('.$db->quoteName('b.date').') <= YEAR('.$db->quote($end_date).')');
				break;
		}

		// Switch Type
		switch($type) {
			default:
			case 'commodity':
				$query->select($db->quoteName('a.commodity_id', 'id'));
				// Commodity filter
				if(!$all_commodities) {
					$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
					$query->where($db->quoteName('a.commodity_id') . ' IN ('.implode(',', $commodity_ids).')');
				}
				$query->group($db->quoteName('a.commodity_id'));
				break;
			case 'category':
				$category_ids 		= $this->getCategoryIDs();
				$category_ids[]		= 0;
				
				$query->select($db->quoteName('c.category_id', 'id'));
				$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . $db->quoteName('a.commodity_id') . ' = ' . $db->quoteName('c.id'));
				$query->where($db->quoteName('c.category_id') . ' IN ('.implode(',', $category_ids).')');
				$query->group($db->quoteName('c.category_id'));
				break;
		}

		// GROUPING
		// =========================================================
		
		switch ($layout) {
			default:
				$query->group($db->quoteName('b.date'));
				break;
			case 'market':
				$query->group($db->quoteName('b.market_id'));
				break;
			case 'regency':
				$query->group($db->quoteName('b.regency_id'));
				break;
			case 'weekly':
				$query->group('YEAR('.$db->quoteName('b.date').')');
				$query->group('WEEK('.$db->quoteName('b.date').')');
				break;
			case 'monthly':
				$query->group('YEAR('.$db->quoteName('b.date').')');
				$query->group('MONTH('.$db->quoteName('b.date').')');
				break;
			case 'yearly':
				$query->group('YEAR('.$db->quoteName('b.date').')');
				break;
		}

		// ORDERING
		// =========================================================
		switch ($layout) {
			case 'market':
				$query->order($db->quoteName('b.market_id') . 'asc');
				break;
			default:
				$query->order($db->quoteName('b.date') . 'asc');
				break;
		}
		
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		//echo "<pre>"; print_r($items); echo "</pre>";

		$layout	= $this->input->get('layout');
		$data = array();
		foreach($items as $item) {
			$price	= round($item->price/50)*50;
			$price 	= $price > 0 ? $price : NULL;
			$date	= strtotime($item->date);

			switch($layout) {
				case 'market':
					$data[$item->id][$item->market_id] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'city':
					$data[$item->id][$item->regency_id] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'map':
					$data[$item->market_id][$item->id][$date] = $price;
					break;
				case 'chart':
					$data[$item->id][$date] = $price;
					break;
				case 'weekly':
					$data[$item->id][strtotime('this week', $date)] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'monthly':
					$data[$item->id][strtotime('first day of this month', $date)] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'yearly':
					$data[$item->id][strtotime('first day of January this year', $date)] = GTHelperCurrency::fromNumber($price, '');
					break;
				default:
					$data[$item->id][$date] = GTHelperCurrency::fromNumber($price, '');
					break;
			}
		}
		unset($data[0]);

		return $data;
	}

	public function getItems($table = false) {
		return $this->getData('commodity');
	}

	public function getCatItems($table = false) {
		return $this->getData('category');
	}

	public function getCommodities($all = false, $prepare = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		if($all) {
			$query->select($db->quoteName('a.name'));
		} else {
			$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').', ")") name');
		}
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$all && !$all_commodities) {
			$commodity_ids		= $this->getState('filter.commodity_ids');
			$all_commodities	= $this->getState('filter.all_commodities');

			$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $commodity_ids).')');
		}

		$db->setQuery($query);
		$raw = $db->loadObjectList('id');
		if($prepare) {
			$data = array();
			foreach ($raw as $item) {
				$data[$item->category_id][$item->id] = $item->name;
			}
		} else {
			$data = $raw;
		}
		
		//echo "<pre>"; print_r($data); echo "</pre>";
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

	public function getCommodityOptions() {
		$commodities	= $this->getCommodities(true, true);
		$categories		= $this->getCategories();
		
		return $commodities ? GTHelperHtml::setCommodities($categories[0], $categories, $commodities, 'select') : array();
	}

	public function getCommodityList() {
		$commodities	= $this->getCommodities(false, true);
		$categories		= $this->getCategories();

		return GTHelperHtml::setCommodities($categories[0], $categories, $commodities);
	}

	public function getProvinces($all = false) {
		$province_ids	= $this->getState('filter.province_ids');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if(!$all) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $province_ids).')');
		}

		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $province) {
			$data[$province->id] = trim($province->name);
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getProvinceOptions() {
		return GTHelperArray::toOption($this->getProvinces(true));
	}

	public function getRegencies($all = false) {
		$province_ids	= $this->getState('filter.province_ids');
		$regency_ids	= $this->getState('filter.regency_ids');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.type', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$all) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $regency_ids).')');
		}

		$query->order($db->quoteName('a.province_capital').' desc');
		$query->order($db->quoteName('a.type'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $regency) {
			$data[$regency->id] = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($regency->type)), trim($regency->name));
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getRegencyOptions() {
		return GTHelperArray::toOption($this->getRegencies(true));
	}

	public function getPriceType($id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		$query->where($db->quoteName('a.id') . ' = '.$db->quote($id));
		
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getMarkets($all = false) {
		//$province_ids	= $this->getState('filter.province_ids');
		$regency_ids	= $this->getState('filter.regency_ids');
		$market_ids		= $this->getState('filter.market_ids');
		

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		//$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		if($regency_ids) {
			$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		}
		if(!$all) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $market_ids).')');
		}

		// Price Type Filter
		$price_type_id  = $this->menu ? $this->menu->params->get('price_type_id') : $this->input->get('price_type_id');
		$price_type 	= $this->getPriceType($price_type_id);
		$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote($price_type_id));
		$query->order($db->quoteName('a.name'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $k => $item) {
			$name = $this->user->guest && $price_type_id != 1 ? $price_type.' #'.($k+1) : trim($item->name);
			$data[$item->id] = $name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getMarketOptions() {
		return GTHelperArray::toOption($this->getMarkets(true));
	}
}
