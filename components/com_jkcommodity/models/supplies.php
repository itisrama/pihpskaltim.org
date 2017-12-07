<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelSupplies extends JKModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'ct.name',
				'us.name',
				'sp.date',
				'sp.id'
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
		parent::populateState('sp.date', 'asc');
		
		$this->_db->setQuery("SELECT MAX(date) AS date FROM #__jkcommodity_supplies");
		$max_date = $this->_db->loadObject();
		$today = $max_date ? strtotime($max_date->date) : time();
		
		$start_date = $this->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', date('d-m-Y', strtotime('-7 day', $today)));
		$end_date = $this->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', date('d-m-Y', $today));

		$this->setState('filter.start_date', $start_date);
		$this->setState('filter.end_date', $end_date);
		
		$cities = $this->getUserStateFromRequest($this->context.'.filter.city_id', 'filter_city_id', array());
		$this->setState('filter.city_id', in_array(0, $cities) ? array(0) : $cities);
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
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);

		// Select fields from main table
		$query->select('
			sp.date,
			sp.id
		');
		$query->from($db->quoteName('#__jkcommodity_supplies', 'sp'));
		
		// Join Users
		$query->select($db->quoteName('us.name', 'author'));
		$query->join('LEFT', $db->quoteName('#__users', 'us').' ON '.$db->quoteName('sp.created_by').' = '.$db->quoteName('us.id'));
		
		// Join City
		$query->select('ct.name AS city');
		$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'ct').' ON '.$db->quoteName('sp.city_id').' = '.$db->quoteName('ct.id'));

		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where($db->quoteName('sp.published').' = '.$db->quote($published));
		} elseif ($published === '') {
			$query->where('('.$db->quoteName('sp.published').' = '.$db->quote(0).' OR '.$db->quoteName('sp.published').' = '.$db->quote(1).')');
		}

		// Filter by search in name.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where($db->quoteName('sp.id').' = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $this->_db->Quote('%'.$this->_db->escape(substr($search, 7), true).'%');
				$query->where('('.$db->quoteName('us.name').' LIKE '.$search.' OR '.$db->quoteName('us.username').' LIKE '.$search.')');
			}
			else {
				$search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
				$query->where('('.$db->quoteName('un.name').' LIKE '.$search.' OR '.$db->quoteName('ct.name').' LIKE '.$search.' OR '.$db->quoteName('mk.name').' LIKE '.$search.')');
			}
		}
		if(!JKHelperAccess::isAdmin()){
		    $city_list = JKHelperPrivilege::getPrivileges();
		    $query->where($db->quoteName('ct.id').' IN ('.$city_list.')');
		}
		
		
		// Filter by dates
		$start_date = $this->getState('filter.start_date');
		$end_date = $this->getState('filter.end_date');
		if ($start_date && $end_date) {
			$start_date = date('Y-m-d', strtotime($start_date));
			$end_date = date('Y-m-d', strtotime($end_date));
			$query->where($db->quoteName('sp.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
		}
		
		// Filter by cities
		$cities = $this->getState('filter.city_id');
		JArrayHelper::toInteger($cities);
		if ($cities && !in_array(0, $cities)) {
			$cities = implode(',', $cities);
			$query->where($db->quoteName('sp.city_id').' IN ('.$cities.')');
		}
		
		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');
		$order = $this->_db->escape($orderCol . ' ' . $orderDirn);
		$query->order($order);

		//echo nl2br(str_replace('#__','tpid_',$query));
		return $query;
	}

	public function getItems($table = true) {
		$items = parent::getItems(false);

		if($items){
			foreach ($items as $k => $item) {
				$item->date	= JHtml::date($item->date, 'd-m-Y');
				$items[$k]	= $item;
			}
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

	public function getRegencies($published = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->select($db->quoteName('a.created', 'date'));
		//$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__jkcommodity_city', 'a'));
		
		if($published){
			$query->where($db->quoteName('a.published') . ' = 1');
			$query->where($db->quoteName('a.published') . ' IS NOT NULL');
			$query->where($db->quoteName('a.published') . ' <> ""');
		}
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		foreach ($data as &$item) {
			$item->name = trim($item->name);
			$item->date = JHtml::date($item->date, 'U');
		}

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $data;
	}

	public function getProvinces(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select(array('a.id', 'a.name'));
		$query->from($db->quoteName('#__jkcommodity_province', 'a'));

		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		$data = $this->_getList($query);
		
		return $data;
	}
	
	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.short_name', 'a.denomination')));
		$query->select($db->quoteName('a.image', 'img'));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		$db->setQuery($query);
		$result = $db->loadObjectList();

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $result;
	}
	
	public function getCommodity($id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.denomination')));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));
		$query->where($db->quoteName('a.id').' = '.$db->quote($id));

		$db->setQuery($query);
		$result = $db->loadObject();

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $result;
	}
	
	public function getMonthlySupply(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$commodity_id = $this->jinput->get('commodity_id');
		$month	= $this->jinput->get('month');
		$year	= $this->jinput->get('year');
		$month	= $month? $db->quote($month) : 'MONTH(NOW())';
		$year	= $year? $db->quote($year) : 'YEAR(NOW())';

		$query->select('SUM('.$db->quoteName('a.production').')'.$db->quoteName('production'));
		$query->select('SUM('.$db->quoteName('a.consumption').')'.$db->quoteName('consumption'));
		$query->select('SUM('.$db->quoteName('a.traded').')'.$db->quoteName('traded'));
		
		$query->from($db->quoteName('#__jkcommodity_supply_detail', 'a'));
		
		$query->select($db->quoteName('b.city_id'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('b.id').' = '.$db->quoteName('a.supply_id'));

		$query->select($db->quoteName('c.name', 'city'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('b.city_id'));

		$query->select($db->quoteName('d.denomination', 'denomination'));
		$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'd').' ON '.$db->quoteName('d.id').' = '.$db->quoteName('a.commodity_id'));

		if($commodity_id)
			$query->where($db->quoteName('a.commodity_id').' = '.$db->quote($commodity_id));
		
		$query->where($db->quoteName('b.published').' = '.$db->quote(1));
		$query->where('MONTH('.$db->quoteName('b.date').') = '.$month);
		$query->where('YEAR('.$db->quoteName('b.date').') = '.$year);

		$query->group($db->quoteName('b.city_id'));
		
		//echo nl2br(str_replace('#__','tpid_',$query));exit();
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
	
	public function getYearlySupply(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$commodity_id = $this->jinput->get('commodity_id');
		$city_id = $this->jinput->get('city_id');
		
		$year = $this->jinput->get('year');
		$year = $year? $db->quote($year) : 'YEAR(NOW())';

		$query->select('SUM('.$db->quoteName('a.production').')'.$db->quoteName('production'));
		$query->select('SUM('.$db->quoteName('a.consumption').')'.$db->quoteName('consumption'));
		$query->select('SUM('.$db->quoteName('a.traded').')'.$db->quoteName('traded'));
		
		$query->from($db->quoteName('#__jkcommodity_supply_detail', 'a'));
		
		$query->select('MONTH('.$db->quoteName('b.date').')'.$db->quoteName('month'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('b.id').' = '.$db->quoteName('a.supply_id'));

		if($commodity_id)
			$query->where($db->quoteName('a.commodity_id').' = '.$db->quote($commodity_id));
		
		$query->where($db->quoteName('b.published').' = '.$db->quote(1));
		$query->where('YEAR('.$db->quoteName('b.date').') = '.$year);

		if($city_id && $city_id != 0)
			$query->where($db->quoteName('b.city_id').' = '.$db->quote($city_id));

		$query->group('MONTH('.$db->quoteName('b.date').')');
		
		//echo nl2br(str_replace('#__','tpid_',$query));exit();
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
	
	public function getMonthlyTrade($type = null){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$commodity_id	= $this->jinput->get('commodity_id');
		$city_id		= $this->jinput->get('city_id');
		
		$month	= $this->jinput->get('month');
		$year	= $this->jinput->get('year');
		$month	= $month? $db->quote($month) : 'MONTH(NOW())';
		$year	= $year? $db->quote($year) : 'YEAR(NOW())';

		$query->select('SUM('.$db->quoteName('a.traded_in').')'.$db->quoteName('traded_in'));
		$query->select('SUM('.$db->quoteName('a.traded_out').')'.$db->quoteName('traded_out'));
		
		$query->select($db->quoteName(array('a.type', 'a.partner_city_id', 'a.partner_province_id')));
		$query->from($db->quoteName('#__jkcommodity_supply_trade', 'a'));
		
		$query->select($db->quoteName('b.city_id'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('b.id').' = '.$db->quoteName('a.supply_id'));

		$query->select($db->quoteName('c.name', 'city'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('b.city_id'));

		$query->select($db->quoteName('d.denomination', 'denomination'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supply_commodity', 'd').' ON '.$db->quoteName('d.id').' = '.$db->quoteName('a.commodity_id'));

		if($type === 0){
			$query->select($db->quoteName('e.name', 'partner'));
			$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'e').' ON '.$db->quoteName('a.partner_city_id').' = '.$db->quoteName('e.id'));
			$query->where($db->quoteName('a.type').' = '.$db->quote(0));
		}
		else if($type === 1){
			$query->select($db->quoteName('e.name', 'partner'));
			$query->join('LEFT', $db->quoteName('#__jkcommodity_province', 'e').' ON '.$db->quoteName('a.partner_province_id').' = '.$db->quoteName('e.id'));
			
			$query->where($db->quoteName('a.type').' = '.$db->quote(1));
		}

		if($commodity_id)
			$query->where($db->quoteName('a.commodity_id').' = '.$db->quote($commodity_id));

		if($city_id && $city_id != 0)
			$query->where($db->quoteName('b.city_id').' = '.$db->quote($city_id));
		
		$query->where($db->quoteName('b.published').' = '.$db->quote(1));
		$query->where('MONTH('.$db->quoteName('b.date').') = '.$month);
		$query->where('YEAR('.$db->quoteName('b.date').') = '.$year);

		$query->group($db->quoteName('a.id'));
		
		//echo nl2br(str_replace('#__','tpid_',$query));exit();
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
	
	public function getTradeHistory($type = null){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$commodity_id	= $this->jinput->get('commodity_id');
		$city_id		= $this->jinput->get('city_id');
		$partner_id		= $this->jinput->get('partner_id');
		
		$year	= $this->jinput->get('year');
		$year	= $year? $db->quote($year) : 'YEAR(NOW())';

		$query->select('SUM('.$db->quoteName('a.traded_in').')'.$db->quoteName('traded_in'));
		$query->select('SUM('.$db->quoteName('a.traded_out').')'.$db->quoteName('traded_out'));
		
		$query->select($db->quoteName(array('a.type', 'a.partner_city_id', 'a.partner_province_id')));
		$query->from($db->quoteName('#__jkcommodity_supply_trade', 'a'));
		
		$query->select('MONTH('.$db->quoteName('b.date').')'.$db->quoteName('month'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('b.id').' = '.$db->quoteName('a.supply_id'));

		if($commodity_id)
			$query->where($db->quoteName('a.commodity_id').' = '.$db->quote($commodity_id));

		if($city_id && $city_id != 0)
			$query->where($db->quoteName('b.city_id').' = '.$db->quote($city_id));
		
		$query->where($db->quoteName('b.published').' = '.$db->quote(1));
		$query->where('YEAR('.$db->quoteName('b.date').') = '.$year);

		if($type === 0){
			$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'e').' ON '.$db->quoteName('a.partner_city_id').' = '.$db->quoteName('e.id'));
			$query->where($db->quoteName('a.type').' = '.$db->quote(0));
		}
		else if($type === 1){
			$query->join('LEFT', $db->quoteName('#__jkcommodity_province', 'e').' ON '.$db->quoteName('a.partner_province_id').' = '.$db->quoteName('e.id'));
			
			$query->where($db->quoteName('a.type').' = '.$db->quote(1));
		}
		
		if($partner_id)
			$query->where($db->quoteName('e.id').' = '.$db->quote($partner_id));

		$query->group('MONTH('.$db->quoteName('b.date').')');
		$query->group($db->quoteName('a.id'));
		
		//echo nl2br(str_replace('#__','tpid_',$query));exit();
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
	
	public function findSupply(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$date	 = $this->jinput->get('date', 0);
		$city_id = $this->jinput->get('city_id');

		$query->select($db->quoteName(array('a.id', 'a.city_id', 'a.date')));
		$query->from($db->quoteName('#__jkcommodity_supplies', 'a'));

		if($city_id && $city_id != 0)
			$query->where($db->quoteName('a.city_id').' = '.$db->quote($city_id));
		
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));
		$query->where($db->quoteName('a.date').' = '.$db->quote($date));
		
		//echo nl2br(str_replace('#__','tpid_',$query));exit();
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
}
