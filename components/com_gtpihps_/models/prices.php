<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelPrices extends GTModelList{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array('a.id', 'a.name', 'b.name', 'c.name', 'd.name', 'a.date');
		}

		if (isset($config['ignore_request'])) {
			unset($config['ignore_request']);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) {
		parent::populateState($ordering, $direction);
		
		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		if ($layout) {
			$this->context.= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '1');
		$this->setState('filter.published', $published);
		
		$province_ids		= $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', array(0), 'array');
		JArrayHelper::toInteger($province_ids, 0);
		$this->setState('filter.province_ids', $province_ids);
		
		$regency_ids		= $this->getUserStateFromRequest($this->context . '.filter.regency_ids', 'filter_regency_ids', array(0), 'array');
		JArrayHelper::toInteger($regency_ids, 0);
		$this->setState('filter.regency_ids', $regency_ids);
		
		$market_ids			= $this->getUserStateFromRequest($this->context . '.filter.market_ids', 'filter_market_ids', array(0), 'array');
		JArrayHelper::toInteger($market_ids, 0);
		$this->setState('filter.market_ids', $market_ids);

		// Set Date Filters
		$start_date			= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date', JHtml::date('-1 years', 'Y-m-d'));
		$end_date			= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date', JHtml::date('now', 'Y-m-d'));
		
		$dates_unix			= array(strtotime($start_date), strtotime($end_date));
		$this->setState('filter.start_date', JHtml::date(min($dates_unix), 'd-m-Y'));
		$this->setState('filter.end_date', JHtml::date(max($dates_unix), 'd-m-Y'));
	}

	public function getItems($table = false) {
		return parent::getItems($table);
	}
	
	protected function getListQuery() {
		$province_ids		= $this->getState('filter.province_ids');
		$regency_ids		= $this->getState('filter.regency_ids');
		$market_ids			= $this->getState('filter.market_ids');
		$start_date			= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date			= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select prices
		$query->select($db->quoteName(array('a.id', 'a.date', 'a.created')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join Regency
		$query->select($db->quoteName('b.name', 'regency'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'b') . ' ON ' . $db->quoteName('a.regency_id') . ' = ' . $db->quoteName('b.id'));

		// Join Market
		$query->select($db->quoteName('c.name', 'market'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'c') . ' ON ' . $db->quoteName('a.market_id') . ' = ' . $db->quoteName('c.id'));

		// Join User
		$query->select($db->quoteName('d.name', 'author'));
		$query->join('LEFT', $db->quoteName('#__users', 'd') . ' ON ' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('d.id'));

		// Province filter
		if(array_filter($province_ids)) {
			$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		}

		// Regency filter
		if(array_filter($regency_ids)) {
			$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		}

		// Market filter
		if(array_filter($market_ids)) {
			$query->where($db->quoteName('a.market_id') . ' IN ('.implode(',', $market_ids).')');
		}

		// Dates filter
		$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
		$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));

		// Publish filter
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int)$published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}
		
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			// If contains spaces, the words will be used as keywords.
			if (preg_match('/\s/', $search)) {
				$search = str_replace(' ', '%', $search);
			}
			$search = $db->quote('%' . $search . '%');
			
			$search_query = array();
			$search_query[] = $db->quoteName('b.name') . 'LIKE ' . $search;
			$search_query[] = $db->quoteName('b.name') . 'LIKE ' . $search;
			$query->where('(' . implode(' OR ', $search_query) . ')');
		}
		
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		
		$query->group($db->escape('a.id'));
		$query->order($db->escape($db->quoteName($orderCol) . ' ' . $orderDirn));
		
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $query;
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
		
		$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
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

	public function getMarkets($all = false) {
		$province_ids	= $this->getState('filter.province_ids');
		$regency_ids	= $this->getState('filter.regency_ids');
		$market_ids		= $this->getState('filter.market_ids');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		if($regency_ids) {
			$query->where($db->quoteName('a.regency_id') . ' IN ('.implode(',', $regency_ids).')');
		}
		if(!$all) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $market_ids).')');
		}

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->id] = trim($item->name);
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getMarketOptions() {
		return GTHelperArray::toOption($this->getMarkets(true));
	}
}
