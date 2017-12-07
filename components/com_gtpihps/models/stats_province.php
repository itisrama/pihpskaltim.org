<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelStats_Province extends GTModelList
{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		if (isset($config['ignore_request'])) {
			unset($config['ignore_request']);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) {
		parent::populateState($ordering, $direction);
		
		$is_external	= $this->input->get('is_external', 0);		
		if($is_external) {
			$price_type_id	= $this->input->get('price_type_id', 1);
			$layout			= $this->input->get('layout');
			$province_id	= $this->input->get('province_id');
			$province_ids	= $this->input->get('province_ids', array($province_id), 'array');
			$regency_id		= $this->input->get('regency_id');
			$regency_ids	= $this->input->get('regency_ids', array($regency_id), 'array');
			$market_id		= $this->input->get('market_id');
			$market_ids		= $this->input->get('market_ids', array($market_id), 'array');
			$commodity_id	= $this->input->get('commodity_id', 'cat-1');
			$commodity_ids 	= $this->input->get('commodity_ids', array($commodity_id), 'array');

			$date		= $this->input->get('date');
			$count		= $this->input->get('date_count', 20);
			$periods	= $this->getPeriodsExt($layout, $date, $count);
			$start_date	= reset($periods);
			$end_date	= end($periods);
			$start_date	= $start_date->mysql;
			$end_date	= $end_date->mysql;
		} else {
			$price_type_id	= is_object($this->menu) ? $this->menu->params->get('price_type_id', 1) : 1;
			$price_type_id2	= $this->input->get('price_type_id', 0);
			$price_type_id 	= $price_type_id2 ? $price_type_id2 : $price_type_id;
			$this->context	.= '.' . $price_type_id;
			$layout			= $this->getUserStateFromRequest($this->context . '.filter.layout', 'filter_layout', 'default');
			$this->context	.= '.' . $layout;

			$province_ids	= $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', array(), 'array');
			$regency_ids	= $this->getUserStateFromRequest($this->context . '.filter.regency_ids', 'filter_regency_ids', array(), 'array');
			$market_ids		= $this->getUserStateFromRequest($this->context . '.filter.market_ids', 'filter_market_ids', array(), 'array');
			$commodity_ids	= $this->getUserStateFromRequest($this->context . '.filter.commodity_ids', 'filter_commodity_ids', array(), 'array');
			
			$start_date		= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date');
			$end_date		= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date');

			$filter 				= new stdClass();
			$filter->price_type_id	= $price_type_id;
			$filter->province_ids	= $province_ids;
			$filter->regency_ids	= $regency_ids;
			$filter->market_ids		= $market_ids;
			$periods				= $this->getPeriods($layout, $start_date, $end_date, $filter);

			if(!($start_date && $end_date)) {
				$start_date	= reset($periods);
				$end_date	= end($periods);
				$start_date	= $start_date->mysql;
				$end_date	= $end_date->mysql;
			}
		}

		JArrayHelper::toInteger($province_ids);
		JArrayHelper::toInteger($regency_ids);
		JArrayHelper::toInteger($market_ids);

		$category_ids = array();
		$commodity_ids2 = array();
		foreach ($commodity_ids as $commodity_id) {
			if(!is_numeric($commodity_id)) {
				$category_id	= explode('-', $commodity_id);
				$category_id	= end($category_id);
				$category_ids[]	= $category_id;
			} else {
				$commodity_ids2[] = $commodity_id;
			}
		}
		$category_ids	= $this->getCategoryIDs($category_ids, $commodity_ids2);
		$price_type_id	= $price_type_id > 0 ? $price_type_id : 1;

		$this->setState('filter.is_external', $is_external);		
		$this->setState('filter.price_type_id', $price_type_id);
		$this->setState('filter.province_ids', array_filter($province_ids));		
		$this->setState('filter.regency_ids', array_filter($regency_ids));
		$this->setState('filter.market_ids', array_filter($market_ids));
		$this->setState('filter.commodity_ids', array_filter($commodity_ids));
		$this->setState('filter.commodity_ids2', array_filter($commodity_ids2));
		$this->setState('filter.category_ids', array_filter($category_ids));

		// Set Date Filters
		$dates	= array(JHtml::date($start_date, 'Y-m-d'), JHtml::date($end_date, 'Y-m-d'));

		$this->setState('filter.periods', $periods);
		$this->setState('filter.start_date', JHtml::date(min($dates), 'd-m-Y'));
		$this->setState('filter.end_date', JHtml::date(max($dates), 'd-m-Y'));
		$this->setState('filter.layout', $layout);

		$this->input->set('layout', $layout);
	}

	protected function getPeriods($layout, $start_date, $end_date, $filter) {
		if($start_date && $end_date) {
			$start_date	= strtotime($start_date);
			$end_date	= strtotime($end_date);
			switch($layout) {
				default:
					$dates	= GTHelperDate::getDayPeriod($start_date, $end_date);
					break;
				case 'weekly':
					$dates	= GTHelperDate::getWeekPeriod($start_date, $end_date);
					break;
				case 'monthly':
					$dates	= GTHelperDate::getMonthPeriod($start_date, $end_date);
					break;
				case 'yearly':
					$dates	= GTHelperDate::getYearPeriod($start_date, $end_date);
					break;
				case 'wtw':
					$dates = GTHelperDate::getBetweenPeriod($start_date, $end_date, 'week');
					break;
				case 'mtm':
					$dates = GTHelperDate::getBetweenPeriod($start_date, $end_date, 'month');
					break;
			}
		} else {
			$date	= $this->getLatestDate($filter);
			$date 	= strtotime($date);
			switch($layout) {
				default:
					$dates = GTHelperDate::getCountPeriod($date, 6, 'day');
					break;
				case 'wtw':
					$dates = GTHelperDate::getCountPeriod($date, 6, 'week');
					break;
				case 'mtm':
					$dates = GTHelperDate::getCountPeriod($date, 6, 'month');
					break;
			}
		}

		return $dates;
	}

	protected function getPeriodsExt($layout, $date, $count) {
		$layout		= $this->input->get('layout');
		$date 		= $date ? $date : $this->getLatestDate();
		$date 		= strtotime($date);
		switch($layout) {
			default:
				$dates = GTHelperDate::getCountPeriod($date, $count, 'day');
				break;
			case 'wtw':
				$dates = GTHelperDate::getCountPeriod($date, $count, 'week');
				break;
			case 'mtm':
				$dates = GTHelperDate::getCountPeriod($date, $count, 'month');
				break;
			case 'weekly':
				$sDate	= strtotime('-'.($count-1).' week', $date);
				$dates	= GTHelperDate::getWeekPeriod($sDate, $date);
				break;
			case 'monthly':
				$sDate	= strtotime('-'.($count-1).' month', $date);
				$dates	= GTHelperDate::getMonthPeriod($sDate, $date);
				break;
			case 'yearly':
				$sDate	= strtotime('-'.($count-1).' year', $date);
				$dates	= GTHelperDate::getYearPeriod($sDate, $date);
				break;
		}
		return $dates;
	}

	public function getLatestDate($filter = null) {
		$price_type_id	= @$filter->price_type_id ? $filter->price_type_id : $this->input->get('price_type_id');

		$province_id	= @$filter->province_ids ? $filter->province_id : $this->input->get('province_id');
		$regency_id		= @$filter->regency_id ? $filter->regency_id : $this->input->get('regency_id');
		$market_id		= @$filter->market_id ? $filter->market_id : $this->input->get('market_id');

		$province_ids	= @$filter->province_ids ? $filter->province_ids : array($this->input->get('province_ids'));
		$regency_ids	= @$filter->regency_ids ? $filter->regency_ids : array($this->input->get('regency_ids'));
		$market_ids		= @$filter->market_ids ? $filter->market_ids : array($this->input->get('market_ids'));

		$province_ids 	= $province_id ? array($province_id) : $province_ids;
		$regency_ids 	= $regency_id ? array($regency_id) : $regency_ids;
		$market_ids 	= $market_id ? array($market_id) : $market_ids;

		$province_ids 	= array_filter($province_ids);
		$regency_ids 	= array_filter($regency_ids);
		$market_ids 	= array_filter($market_ids);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').')');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));
		$query->where($db->quoteName('b.price') . ' > 50');
		$query->where($db->quoteName('a.published') . ' = 1');
		
		if($price_type_id) {
			$query->where($db->quoteName('a.price_type_id') . ' = '.intval($price_type_id));
		}

		if($province_ids) {
			$province_ids = array_map(array($db, 'quote'), $province_ids);
			$province_ids = implode(',', $province_ids);
			$query->where($db->quoteName('a.province_id') . ' IN ('.$province_ids.')');
		}

		if($regency_ids) {
			$regency_ids = array_map(array($db, 'quote'), $regency_ids);
			$regency_ids = implode(',', $regency_ids);
			$query->where($db->quoteName('a.regency_id') . ' IN ('.$regency_ids.')');
		}

		if($market_ids) {
			$market_ids = array_map(array($db, 'quote'), $market_ids);
			$market_ids = implode(',', $market_ids);
			$query->where($db->quoteName('a.market_id') . ' IN ('.$market_ids.')');
		}

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$date	= $db->loadResult();
		$date	= $date ? $date : JHtml::date('now', 'Y-m-d');
		$date 	= strtotime($date);
		$dates	= GTHelperDate::getCountPeriod($date, 1, 'day');
		$date	= end($dates);
		return $date->mysql;
	}

	protected function getCategoryIDs($category_ids, $commodity_ids) {
		array_push($commodity_ids, 0);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('DISTINCT '.$db->quoteName('a.category_id'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		if(count($commodity_ids)) {
			$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $commodity_ids).')');
		}
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$db->setQuery($query);
		$category_ids2 = $db->loadColumn();

		$result = array_merge($category_ids, $category_ids2);
		return array_unique($result);
	}

	public function getData($type = 'commodity') {
		$layout				= $this->getState('filter.layout');
		$province_ids		= array_filter($this->getState('filter.province_ids'));
		$regency_ids		= array_filter($this->getState('filter.regency_ids'));
		$market_ids			= array_filter($this->getState('filter.market_ids'));
		$commodity_ids		= $this->getState('filter.commodity_ids2');
		$category_ids		= $this->getState('filter.category_ids');
		$price_type_id		= $this->getState('filter.price_type_id');
		$periods 			= $this->getState('filter.periods');
		$start_date 		= reset($periods);
		$end_date 			= end($periods);
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// SELECT & JOIN
		// =========================================================
		// Select Price Details
		$query->select($db->quoteName(array('a.province_id', 'a.regency_id', 'a.market_id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('ROUND(AVG('.$db->quoteName('b.price').')/50)*50 price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c').' ON '.
			$db->quoteName('b.commodity_id') . ' = ' . $db->quoteName('c.id')
		);

		// Alternate sources
		$location_group = 'province';
		if($market_ids) {
			$location_group = 'market';
			$query->where($db->quoteName('a.market_id') . ' IN ('.implode(',', $market_ids).')');
		} elseif($regency_ids) {
			$location_group = 'regency';
			$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		} elseif($province_ids) {
			$location_group = 'province';
			$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		}

		// FILTERING
		// =========================================================
		$query->where($db->quoteName('a.published') . ' = 1');
		//$query->where($db->quoteName('b.price') . ' > '.$db->quoteName('c.price').'*0.25');
		//$query->where($db->quoteName('b.price') . ' < '.$db->quoteName('c.price').'*((100+('.$db->quoteName('c.deviance').'*10))/100)');
		if($location_group != 'market') {
			$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote($price_type_id));
		}
		
		// Date filter
		$start_date	= $start_date->mysql;
		$end_date	= $end_date->mysql;
		switch($layout) {
			default:
				$query->where($db->quoteName('a.date') . ' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
				break;
			case 'wtw':
			case 'mtm':
				foreach ($periods as &$period) {
					$period = $db->quote($period->mysql);
				}
				$query->where($db->quoteName('a.date') . ' IN ('.implode(',', $periods).')');
				break;
			case 'weekly':
				$start_date	= 'YEARWEEK('.$db->quote($start_date).')';
				$end_date	= 'YEARWEEK('.$db->quote($end_date).')';
				$query->where('YEARWEEK('.$db->quoteName('a.date').') BETWEEN '.$start_date.' AND '.$end_date);
				break;
			case 'monthly':
				$start_date	= 'CONCAT(YEAR('.$db->quote($start_date).'), LPAD(MONTH('.$db->quote($start_date).'), 2, '.$db->quote('0').'))';
				$end_date	= 'CONCAT(YEAR('.$db->quote($end_date).'), LPAD(MONTH('.$db->quote($end_date).'), 2, '.$db->quote('0').'))';
				$query->where('CONCAT(YEAR('.$db->quoteName('a.date').'), LPAD(MONTH('.$db->quoteName('a.date').'), 2, '.$db->quote('0').')) BETWEEN '.$start_date.' AND '.$end_date);
				break;
			case 'yearly':
				$start_date	= 'YEAR('.$db->quote($start_date).')';
				$end_date	= 'YEAR('.$db->quote($end_date).')';
				$query->where('YEAR('.$db->quoteName('a.date').') BETWEEN '.$start_date.' AND '.$end_date);
				break;
		}
		
		// Switch Type
		switch($type) {
			default:
			case 'commodity':
				$query->select($db->quoteName('b.commodity_id', 'id'));
				$query->group($db->quoteName('b.commodity_id'));

				if(count($commodity_ids)) {
					$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
					$query->where($db->quoteName('b.commodity_id') . ' IN ('.implode(',', $commodity_ids).')');
				}
				break;
			case 'category':
				$query->select($db->quoteName('c.category_id', 'id'));
				$query->group($db->quoteName('c.category_id'));

				if(count($category_ids)) {
					$category_ids = array_map(array($db, 'quote'), $category_ids);
					$query->where($db->quoteName('c.category_id') . ' IN ('.implode(',', $category_ids).')');
				}
				break;
		}

		$query->group($db->quoteName(array('a.date', 'a.market_id')));	

		switch($location_group) {
			case 'market':
				$query2 = $db->getQuery(true);
				$query2->select($db->quoteName(array('a.date', 'a.id')));
				$query2->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query2->from('('.$query.') a');
				$query = $query2;
				break;
			case 'regency':
				$query2 = $db->getQuery(true);
				$query2->select($db->quoteName(array('a.province_id', 'a.regency_id', 'a.date', 'a.id')));
				$query2->select($db->quote('0').' market_id');
				$query2->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query2->from('('.$query.') a');
				$query2->group($db->quoteName(array('a.regency_id', 'a.id', 'a.date')));

				$query3 = $db->getQuery(true);
				$query3->select($db->quoteName(array('a.date', 'a.id')));
				$query3->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query3->from('('.$query2.') a');
				$query = $query3;
				break;
			case 'province':
				$query2 = $db->getQuery(true);
				$query2->select($db->quoteName(array('a.province_id', 'a.regency_id', 'a.date', 'a.id')));
				$query2->select($db->quote('0').' market_id');
				$query2->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query2->from('('.$query.') a');
				$query2->group($db->quoteName(array('a.regency_id', 'a.id', 'a.date')));

				$query3 = $db->getQuery(true);
				$query3->select($db->quoteName(array('a.province_id', 'a.date', 'a.id')));
				$query3->select($db->quote('0').' regency_id');
				$query3->select($db->quote('0').' market_id');
				$query3->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query3->from('('.$query2.') a');
				$query3->group($db->quoteName(array('a.province_id', 'a.id', 'a.date')));

				$query4 = $db->getQuery(true);
				$query4->select($db->quoteName(array('a.date', 'a.id')));
				$query4->select('ROUND(AVG('.$db->quoteName('a.price').')/50)*50 price');
				$query4->from('('.$query3.') a');
				$query = $query4;
				break;
		}

		// GROUPING
		// =========================================================
		$query->group($db->quoteName('a.id'));
		switch($layout) {
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
		$query->order($db->quoteName(array('a.id', 'a.date')));
		
		//echo nl2br(str_replace('#__','pihps_',$query)).'<br/><br/>';
		$db->setQuery($query);
		$items = $db->loadObjectList();

		//echo "<pre>"; print_r($items); echo "</pre>";

		$layout	= $this->input->get('layout');
		$data = array();
		foreach($items as $item) {
			$price	= $item->price;
			$price 	= $price > 0 ? $price : NULL;
			$date	= strtotime($item->date);

			switch($layout) {
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

	public function getItemsCom($table = false) {
		return $this->getData('commodity');
	}

	public function getItemsCat($table = false) {
		return $this->getData('category');
	}

	public function getCommodities($all = false, $prepare = false) {
		$commodity_ids	= $this->getState('filter.commodity_ids');
		$commodity_ids	= $all ? array() : $commodity_ids;
		JArrayHelper::toInteger($commodity_ids);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.denomination', 'a.image')));
		if($all) {
			$query->select($db->quoteName(array('a.name')));
		} else {
			$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').', ")") name');
		}
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(count($commodity_ids)) {
			$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $commodity_ids).')');
		}

		$db->setQuery($query);
		$raw = $db->loadObjectList('id');
		if($prepare) {
			$data = array();
			foreach ($raw as $item) {
				$data[$item->category_id][$item->id] = $item;
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

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name', 'a.denomination')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item;
		}
		return $data;
	}

	public function getCommodityOptions() {
		$category_ids	= $this->getState('filter.category_ids');
		$commodities	= $this->getCommodities(true, true);
		$categories		= $this->getCategories();
		
		return $commodities ? GTHelperHtml::setCommodities($categories[0], $categories, $commodities, $category_ids) : array();
	}

	public function getCommodityList($all = false) {
		$category_ids	= $this->getState('filter.category_ids');
		$commodities	= $this->getCommodities($all, true);
		$categories		= $this->getCategories();

		return GTHelperHtml::setCommodities($categories[0], $categories, $commodities, $category_ids);
	}

	public function getProvinces($all = false) {
		$province_ids	= $this->getState('filter.province_ids'); array_push($province_ids, 0);
		$province_ids 	= $all ? array() : $province_ids;

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if(count($province_ids)) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $province_ids).')');
		}
		$query->order($db->quoteName('a.id'));

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		$items = $db->loadObjectList('id');
		foreach ($items as &$item) {
			$item = $item->name;
		}
		
		return $items;
	}

	public function getProvinceOptions() {
		return GTHelperArray::toOption($this->getProvinces(true));
	}

	public function getRegencies($all = false) {
		$province_ids 	= $this->getState('filter.province_ids');
		$regency_ids	= $this->getState('filter.regency_ids');
		$regency_ids 	= $all ? array() : $regency_ids;
		array_push($province_ids, 0);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.type', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');

		if(count($regency_ids)) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $regency_ids).')');
		}

		$query->order($db->quoteName('a.province_capital').' desc');
		$query->order($db->quoteName('a.type'));

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		$items = $db->loadObjectList('id');
		foreach ($items as &$item) {
			$item = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($item->type)), $item->name);
		}
		return $items;
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
		$regency_ids 	= $this->getState('filter.regency_ids');
		$market_ids		= $this->getState('filter.market_ids');
		$market_ids 	= $all ? array() : $market_ids;
		$price_type_id	= $this->getState('filter.price_type_id');
		array_push($regency_ids, 0);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');

		if(count($market_ids)) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $market_ids).')');
		}

		// Price Type Filter
		$price_type 	= $this->getPriceType($price_type_id);
		$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote($price_type_id));
		$query->order($db->quoteName('a.name'));

		$db->setQuery($query);
		$items = $db->loadObjectList('id');
		foreach ($items as &$item) {
			$item = $item->name;
		}
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $items;
	}

	public function getMarketOptions() {
		return GTHelperArray::toOption($this->getMarkets(true));
	}

	public function getStdDev($commodity_id = null) {
		$province_id	= $this->getState('filter.province_ids');
		$province_id 	= reset($province_id);
		$date			= $this->getState('filter.end_date');
		$date 			= JHtml::date($date, 'Y-m-d');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select fields from main table
		$query->select('STDDEV('.$db->quoteName('a.fluctuation').') stddev');
		$query->from($db->quoteName('#__gtpihps_fluc_details', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihps_flucs', 'b') . ' ON ' . 
			$db->quoteName('a.fluc_id') . ' = ' . $db->quoteName('b.id')
		);

		$query->select($db->quoteName('c.id', 'commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . 
			$db->quoteName('a.category_id') . ' = ' . $db->quoteName('c.category_id')
		);

		$query->where($db->quoteName('b.date') . ' BETWEEN SUBDATE(' . $db->quote($date).', INTERVAL 2 YEAR) AND '.$db->quote($date));
		$query->where($db->quoteName('b.province_id') . ' = ' . $db->quote($province_id));
		if($commodity_id) {
			$query->where($db->quoteName('c.id') . ' = ' . $db->quote($commodity_id));
		}

		$query->group($db->quoteName('c.id'));

		$db->setQuery($query);
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$data = $commodity_id ? $db->loadObject() : $db->loadObjectList('commodity_id');

		return $data;
	}
}
