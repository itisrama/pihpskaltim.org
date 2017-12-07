<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelProvince_Statistics extends GTModelList
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
		
		$province_id 		= $this->input->get('province_id');
		if(is_numeric($province_id)) {
			$province_ids	= array($province_id);
			if($province_id == 0) {
				$province_ids = array_keys($this->getProvinces(true));
			}
		}

		$province_ids		= is_numeric($province_id) ? $province_ids : $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', array(0), 'array');
		JArrayHelper::toInteger($province_ids, 0);
		$this->setState('filter.province_ids', $province_ids);

		$rids 				= array_keys($this->getRegencies(true));
		$regency_ids		= $this->getUserStateFromRequest($this->context . '.filter.regency_ids', 'filter_regency_ids', $rids, 'array');
		JArrayHelper::toInteger($regency_ids, 0);
		$this->setState('filter.regency_ids', $regency_ids);

		$show_market		= $this->getUserStateFromRequest($this->context . '.filter.show_market', 'filter_show_market', 1);
		$this->setState('filter.show_market', intval($show_market));
		
		$com_id 			= $this->input->get('commodity_id');
		$commodity_id		= $com_id ? $com_id : $this->getUserStateFromRequest($this->context . '.filter.commodity_id', 'filter_commodity_id', 2);
		$this->setState('filter.commodity_id', $commodity_id);

		// Set Date Filters
		$date = $this->input->get('date');
		if($date) {
			$start_date		= $this->getLatestDate($date, 20);
			$end_date		= $this->getLatestDate($date, 1);
		} else {
			$interval 			= $this->getInterval();
			$latest_sdate		= $this->getLatestDate('now', $interval);
			$latest_edate		= $this->getLatestDate('now', 1);
			
			$start_date			= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date', $latest_sdate);
			$end_date			= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date', $latest_edate);
		}
		
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

	protected function getData($type = 'province', $currency = true) {
		$layout			= $this->input->get('layout');
		$province_ids	= $this->getState('filter.province_ids');
		$regency_ids	= $this->getState('filter.regency_ids');
		$commodity_id	= $this->getState('filter.commodity_id');
		
		$start_date		= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date		= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// SELECT & JOIN
		// =========================================================
		// Select Prices
		if($type) {
			$query->select($db->quoteName('a.'.$type.'_id', 'id'));
			$query->group($db->quoteName('a.'.$type.'_id'));
			
		} else {
			$query->select($db->quote('0'). ' id');
		}

		$query->select($db->quoteName(array('a.date')));

		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join Price Details
		$query->select('AVG('.$db->quoteName('b.price').') price');
		$query->select($db->quoteName('b.commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));

		// Join Markets
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'd') . ' ON ' . $db->quoteName('a.market_id') . ' = ' . $db->quoteName('d.id'));
		
		// FILTERING
		// =========================================================
		// Publish filter
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.price') . ' > 50');

		// Price Type Filter
		$price_type_id	= $this->menu->params->get('price_type_id');
		$query->where($db->quoteName('d.price_type_id') . ' = '.$db->quote($price_type_id));

		// Commodity filter
		if(is_numeric($commodity_id)) {
			$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . 
				$db->quoteName('b.commodity_id') . ' = ' . $db->quoteName('c.id')
			);
			$category_id = end(explode('-', $commodity_id));
			$query->where($db->quoteName('c.category_id') . ' = ' . $db->quote(intval($category_id)));
		}

		switch ($type) {
			case 'market':
			case 'regency':
				$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
				break;
		}

		// Dates filter
		switch ($layout) {
			default:
				$start_date = $price_type_id == 1 ? $start_date : JHtml::date($start_date.' -7 days', 'Y-m-d');
				$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
				$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));
				break;
			case 'weekly':
				$start_date	= JHtml::date($start_date, 'Y') . sprintf('%02d', JHtml::date($start_date, 'W'));
				$end_date	= JHtml::date($end_date, 'Y') . sprintf('%02d', JHtml::date($end_date, 'W'));
				$query->where($db->quoteName('a.date').' >= STR_TO_DATE('.$db->quote($start_date.' Monday').', '.$db->quote('%X%V %W').')');
				$query->where($db->quoteName('a.date').' <= STR_TO_DATE('.$db->quote($end_date.' Monday').', '.$db->quote('%X%V %W').')');
				break;
			case 'monthly':
				$start_date	= $db->quote($start_date);
				$end_date	= 'ADDDATE('.$db->quote($end_date).', INTERVAL 1 MONTH)';
				$query->where($db->quoteName('a.date').' >= DATE_FORMAT('.$start_date.' ,'.$db->quote('%Y-%m-01').')');
				$query->where($db->quoteName('a.date').' < DATE_FORMAT('.$end_date.' ,'.$db->quote('%Y-%m-01').')');
				break;
			case 'yearly':
				$query->where('YEAR('.$db->quoteName('a.date').') >= YEAR('.$db->quote($start_date).')');
				$query->where('YEAR('.$db->quoteName('a.date').') <= YEAR('.$db->quote($end_date).')');
				break;
		}

		// GROUPING
		// =========================================================

		
		switch ($layout) {
			default:
				$query->group($db->quoteName('a.date'));
				break;
			case 'weekly':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				$query->group('WEEK('.$db->quoteName('a.date').')');
				break;
			case 'monthly':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				$query->group('MONTH('.$db->quoteName('a.date').')');
				break;
			case 'yearly':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				break;
		}

		// ORDERING
		// =========================================================
		switch ($layout) {
			default:
				$query->order($db->quoteName('a.date') . 'asc');
				break;
		}
		
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$data = array();
		foreach($items as $item) {
			$price	= round($item->price/50)*50;
			$price 	= $price > 0 ? $price : NULL;
			$date	= strtotime($item->date);

			switch($layout) {
				case 'chart':
					$data[$item->id][$date] = $price;
					break;
				case 'weekly':
					$data[$item->id][strtotime('this week', $date)] = $currency ? GTHelperCurrency::fromNumber($price, '') : $price;
					break;
				case 'monthly':
					$data[$item->id][strtotime('first day of this month', $date)] = $currency ? GTHelperCurrency::fromNumber($price, '') : $price;
					break;
				case 'yearly':
					$data[$item->id][strtotime('first day of January this year', $date)] = $currency ? GTHelperCurrency::fromNumber($price, '') : $price;
					break;
				default:
					$data[$item->id][$date] = $currency ? GTHelperCurrency::fromNumber($price, '') : $price;
					break;
			}
		}

		return $data;
	}

	public function getItems($table = false) {
		return $this->getData('province');
	}

	public function getItemsAll() {
		return $this->getData(null);
	}

	public function getItemsRegency() {
		return $this->getData('regency');
	}

	public function getItemsMarket() {
		return $this->getData('market');
	}

	public function getJsonItems($params) {
		foreach ($params as $k => $v) {
			$this->setState('filter.'.$k, $v);
		}

		return $this->getData('province');
	}

	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select($db->quoteName('a.name'));
		
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

	public function getCommodityOptions() {
		$commodities	= $this->getCommodities();
		$categories		= $this->getCategories();
		
		return $commodities ? GTHelperHtml::setCommodities($categories[0], $categories, $commodities, 'select') : array();
	}

	public function getCommodityList() {
		$commodities	= $this->getCommodities();
		$categories		= $this->getCategories();

		return GTHelperHtml::setCommodities($categories[0], $categories, $commodities);
	}

	public function getCommodityDefault($commodity_id) {
		if(is_numeric($commodity_id)) {
			$table = $this->getTable('Commodity');
			$table->load($commodity_id);
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$category_id = end(explode('-', $commodity_id));
			$table = $this->getTable('Category');
			$table->load(intval($category_id));
		}
		
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getCommodity() {
		$commodity_id = $this->getState('filter.commodity_id');
		$data = $this->getCommodityDefault($commodity_id);
		return $data->name . ' (' . $data->denomination . ')';
	}

	public function getProvinces($all = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$all) {
			$province_ids	= (array) $this->getState('filter.province_ids');
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $province_ids).')');
		}

		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->id] = trim($item->name);
		}
		return $data;
	}

	public function getProvinceOptions() {
		$options = $this->getProvinces(true);

		return GTHelperArray::toOption($options);
	}

	public function getProvinceList($all = false) {
		$items = $this->getProvinces($all);

		foreach ($items as $k => $item) {
			$province = new stdClass();
			$province->value = $k;
			$province->text = $item;
			$items[$k] = $province;
		}

		return $items;
	}

	public function getRegencies($all = false, $prepare = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.type', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));		
		$query->where($db->quoteName('a.published') . ' = 1');

		if(!$all) {
			$regency_ids	= $this->getState('filter.regency_ids');
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $regency_ids).')');
		}

		$query->order($db->quoteName('a.province_capital').' desc');
		$query->order($db->quoteName('a.type'));
		$query->order($db->quoteName('a.name') . ' asc');

		$db->setQuery($query);
		$items = $db->loadObjectList('id');

		if(!$prepare) {
			return $items;
		}

		$data = array();
		foreach ($items as $regency) {
			$data[$regency->id] = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($regency->type)), trim($regency->name));
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getRegencyOptions() {
		return GTHelperArray::toOption($this->getRegencies(true));
	}

	public function getRegencyList($all = false) {
		$items = $this->getRegencies($all, false);

		$data = array();
		foreach ($items as $k => $item) {
			$data[$item->id] = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($item->type)), trim($item->name));
		}

		return $data;
	}

	public function getMarkets($all = false, $prepare = true) {
		$regency_ids	= $this->getState('filter.regency_ids');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.name'));

		// Price Type Filter
		$price_type_id	= $this->menu->params->get('price_type_id');
		$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote($price_type_id));

		if(!$all) {
			$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		}

		$db->setQuery($query);
		$items = $db->loadObjectList();

		if(!$prepare) {
			return $items;
		}

		$data = array();
		foreach ($items as $regency) {
			$data[$regency->id] = trim($regency->name);
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getMarketList($all = false) {
		$items = $this->getMarkets($all, false);

		$data = array();
		foreach ($items as $k => $item) {
			$data[$item->regency_id][$item->id] = trim($item->name);
		}

		return $data;
	}
}
