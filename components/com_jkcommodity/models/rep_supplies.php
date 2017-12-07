<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelRep_Supplies extends JKModelList {

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
		$query->select('SUM('.$db->quoteName('a.production').') '.$db->quoteName('production'));
		$query->select('SUM('.$db->quoteName('a.consumption').') '.$db->quoteName('consumption'));
		$query->select('SUM('.$db->quoteName('a.transported').') '.$db->quoteName('transported'));
		$query->select($db->quoteName(array('a.supply_id', 'a.commodity_id')));
		$query->from($db->quoteName('#__jkcommodity_supply_detail', 'a'));
		
		$query->select('YEAR('.$db->quoteName('b.date').') '.$db->quoteName('year'));
		$query->select('MONTH('.$db->quoteName('b.date').') '.$db->quoteName('month'));
		$query->select($db->quoteName(array('b.city_id')));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('a.supply_id').' = '.$db->quoteName('b.id'));
		
		// Join City
		$query->select($db->quoteName('c.name', 'city'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'c').' ON '.$db->quoteName('b.city_id').' = '.$db->quoteName('c.id'));
		
		// Join Commodity
		$query->select($db->quoteName('d.name', 'commodity'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_commodity', 'd').' ON '.$db->quoteName('a.commodity_id').' = '.$db->quoteName('d.id'));

		$query->where($db->quoteName('b.published').' = '.$db->quote(1));
		
		// Filter by dates
		$start_date = $this->getState('filter.start_date');
		$end_date 	= $this->getState('filter.end_date');
		if ($start_date && $end_date) {
			$start_date = date('Y-m-d', strtotime($start_date));
			$end_date 	= date('Y-m-d', strtotime($end_date));
			$query->where($db->quoteName('b.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
		}
		
		// Filter by cities
		$cities = $this->getState('filter.city_id');
		JArrayHelper::toInteger($cities);
		if ($cities && !in_array(0, $cities)) {
			$cities = implode(',', $cities);
			$query->where($db->quoteName('b.city_id').' IN ('.$cities.')');
		}
		
		$query->group($db->quoteName('a.commodity_id'));
		$query->group($db->quoteName('b.city_id'));
		$query->group('YEAR('.$db->quoteName('b.date').')');
		$query->group('MONTH('.$db->quoteName('b.date').')');

		$query->order($db->quoteName('b.date').' DESC');
		$query->order($db->quoteName('b.city_id'));
		$query->order($db->quoteName('a.commodity_id'));

		//echo nl2br(str_replace('#__','tpid_',$query));
		return $query;
	}

	public function getItems($table = true) {
		$items = parent::getItems(false);
		
		/*
		$result = array();
		foreach($items as $item){
			if(!isset($result[$item->city_id])){
				$result[$item->city_id] = new stdClass();
				$result[$item->city_id]->name = $item->city;
				$result[$item->city_id]->data = array();
				
				$result[$item->city_id]->data[$item->year] = array();
				$result[$item->city_id]->data[$item->year][$item->month] = array();
			}
			$commodity = new stdClass();
			$commodity->name 		= $item->commodity;
			$commodity->production 	= $item->production;
			$commodity->consumption = $item->consumption;
			$commodity->transported = $item->transported;

			$result[$item->city_id]->data[$item->year][$item->month][$item->commodity_id] = $commodity;
		}
		*/
		//echo"<pre>";var_dump($items);echo"</pre>";exit();
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
}
