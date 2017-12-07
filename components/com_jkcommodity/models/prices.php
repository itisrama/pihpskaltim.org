<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelPrices extends JKModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'un.name',
				'ct.name',
				'mk.name',
				'us.name',
				'pc.date',
				'pc.id'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {

		// Adjust the context to support modal layouts.
		$layout = $this->jinput->get('layout', 'default');
		if ($layout) {
			$this->context .= '.'.$layout;
		}

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('pc.date', 'asc');
		
		$this->_db->setQuery("SELECT MAX(date) AS date FROM #__jkcommodity_price");
		$max_date = $this->_db->loadObject();
		$today = $max_date ? strtotime($max_date->date) : time();
		
		$start_date = $this->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', date('d-m-Y', strtotime('-7 day', $today)));
		$end_date = $this->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', date('d-m-Y', $today));

		$this->setState('filter.start_date', $start_date);
		$this->setState('filter.end_date', $end_date);
		
		$cities = $this->getUserStateFromRequest($this->context.'.filter.city_id', 'filter_city_id', array());
		$this->setState('filter.city_id', in_array(0, $cities) ? array(0) : $cities);
		$markets = $this->getUserStateFromRequest($this->context.'.filter.market_id', 'filter_market_id', array());
		$this->setState('filter.market_id', in_array(0, $markets) ? array(0) : $markets);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */

	protected function getListQuery() {
		// Create a new query object.
		$query = $this->_db->getQuery(true);

		// Select fields from main table
		$query->select('
			pc.date,
			pc.id
		');
		$query->from('#__jkcommodity_price AS pc');
		
		// Join Users
		$query->select('us.name AS author');
		$query->join('LEFT', '#__users AS us ON pc.created_by = us.id');
		
		// Join Unit
		$query->select('un.name AS unit');
		$query->join('LEFT', '#__jkcommodity_unit AS un ON pc.unit_id = un.id');
		
		// Join City
		$query->select('ct.name AS city');
		$query->join('LEFT', '#__jkcommodity_city AS ct ON pc.city_id = ct.id');
		
		// Join Market
		$query->select('mk.name AS market');
		$query->join('LEFT', '#__jkcommodity_market AS mk ON pc.market_id = mk.id');

		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('pc.published = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(pc.published = 0 OR pc.published = 1)');
		}

		// Filter by search in name.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('pc.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $this->_db->Quote('%'.$this->_db->escape(substr($search, 7), true).'%');
				$query->where('(us.name LIKE '.$search.' OR us.username LIKE '.$search.')');
			}
			else {
				$search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
				$query->where('(un.name LIKE '.$search.' OR ct.name LIKE '.$search.' OR mk.name LIKE '.$search.')');
			}
		}
		if(!JKHelperAccess::isAdmin()){
		    $city_list = JKHelperPrivilege::getPrivileges();
		    $query->where("ct.id IN ($city_list)");
		}
		
		
		// Filter by dates
		$start_date = $this->getState('filter.start_date');
		$end_date = $this->getState('filter.end_date');
		if ($start_date && $end_date) {
			$start_date = date('Y-m-d', strtotime($start_date));
			$end_date = date('Y-m-d', strtotime($end_date));
			$query->where("pc.date BETWEEN '$start_date' AND '$end_date'");
		}
		
		// Filter by cities
		$cities = $this->getState('filter.city_id');
		JArrayHelper::toInteger($cities);
		if ($cities && !in_array(0, $cities)) {
			$cities = implode(',', $cities);
			$query->where("pc.city_id IN ($cities)");
		}
		
		// Filter by markets
		$markets = $this->getState('filter.market_id');
		JArrayHelper::toInteger($markets);
		if ($markets && !in_array(0, $markets)) {
			$markets = implode(',', $markets);
			$query->where("pc.market_id IN ($markets)");
		}
		
		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');
		$order = $this->_db->escape($orderCol . ' ' . $orderDirn);
		$query->order($order);

		//echo nl2br(str_replace('#__','tpid_',$query));
		return $query;
	}

	public function getItems() {
		$items = parent::getItems(false);

		foreach ($items as $k => $item) {
			$item->date	= JHtml::date($item->date, 'd-m-Y');
			$items[$k]	= $item;
		}

		return $items;
	}
	
	public function getCity(){
		$query = "
			SELECT `id` AS `value`, `name` AS `text`
			FROM #__jkcommodity_city
			WHERE `published` = 1
			AND `type` = 'consumer'
		";

		if(!JKHelperAccess::isAdmin()){
            $city_list = JKHelperPrivilege::getPrivileges();
            if(!empty($city_list)){
                $query .= "AND `id` IN (".$city_list.")";
            }
        }
		$data = $this->_getList($query);
		array_unshift($data, (object) array('value' => 0, 'text' => JText::_('COM_JKCOMMODITY_FIELD_ALL_CITY')));
		return $data;
	}
	
	public function getMarket(){
		$city_id = $this->state->get('filter.city_id');
		$query = "
			SELECT `id` AS `value`, `name` AS `text`
			FROM #__jkcommodity_market
			WHERE `published` = 1
		";
		JArrayHelper::toInteger($city_id);
        if($city_id && !in_array(0, $city_id)) {
			$city_list = implode(',', $city_id);
    		$query .= "AND `city_id` IN ($city_list)";
		}
		elseif(!JKHelperAccess::isAdmin()){
            $city_list = JKHelperPrivilege::getPrivileges();
    		$query .= "AND `city_id` IN ($city_list)";
		}
		
		$data = $this->_getList($query);
		array_unshift($data, (object) array('value' => 0, 'text' => JText::_('COM_JKCOMMODITY_FIELD_ALL_MARKET')));
		return $data;
	}
	
	public function findPrice() {
		$date		= $this->jinput->get('date', 0);
		$market_id	= $this->jinput->get('market_id', 0);
		
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.unit_id', 'a.city_id', 'a.market_id')));
		
		$query->from($db->quoteName('#__jkcommodity_price', 'a'));
		
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));
		$query->where($db->quoteName('a.date').' = '.$db->quote($date));
		$query->where($db->quoteName('a.market_id').' = '.$db->quote($market_id));

		$db->setQuery($query);
		$data = $db->loadObjectList();

		return $data;
	}
}
