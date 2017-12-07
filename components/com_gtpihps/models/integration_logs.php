<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelIntegration_Logs extends GTModelList{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields']	= array('a.id', 'b.name', 'a.date', 'a.type', 'a.status', 'a.created');
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

		$status_log = $this->getUserStateFromRequest($this->context . '.filter.status_log', 'filter_status_log');
		$this->setState('filter.status_log', $status_log);

		$province_ids	= $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', $defaultProvIDs, 'array');
		JArrayHelper::toInteger($province_ids, 0);
		$this->setState('filter.province_ids', $province_ids);
		
		$all_provinces	= $this->getUserStateFromRequest($this->context . '.filter.all_provinces', 'filter_all_provinces', 1);
		$this->setState('filter.all_provinces', intval($all_provinces));
		
		// Set Date Filters
		$start_date		= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date', JHtml::date('-14 days', 'Y-m-d'));
		$end_date		= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date', JHtml::date('now', 'Y-m-d'));
		
		$dates_unix		= array(strtotime($start_date), strtotime($end_date));
		$sdate			= min($dates_unix);
		$edate			= max($dates_unix);
		
		$this->setState('filter.start_date', JHtml::date($sdate, 'd-m-Y'));
		$this->setState('filter.end_date', JHtml::date($edate, 'd-m-Y'));
	}

	protected function getListQuery() {
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select log
		$query->select($db->quoteName(array('a.id', 'a.date', 'a.type',  'a.url', 'a.status', 'a.log', 'a.created')));
		$query->from($db->quoteName('#__gtpihps_integration_logs', 'a'));
	
		// Join province
		$query->select($db->quoteName('b.name', 'province'));
		$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'b').' ON '.$db->quoteName('a.province_id').' = '.$db->quoteName('b.id'));

		// Publish filter
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int)$published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// status_log filter
		$status_log = $this->getState('filter.status_log');
		if ($status_log) {
			$query->where($db->quoteName('a.status').' = '.$db->quote($status_log));
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
			$search_query[] = $db->quoteName('a.url') . 'LIKE ' . $search;
			$query->where('(' . implode(' OR ', $search_query) . ')');
		}

		// Province filter
		
		$all_provinces	= $this->getState('filter.all_provinces');
		$province_ids	= $all_provinces ? array_keys($this->getProvinces(true, true)) : $this->getState('filter.province_ids');
		$province_ids[]	= 0;
		$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
	

		// Dates filter
		$start_date			= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date			= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');
		$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
		$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));
		
		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.id');
		$orderDirn = $this->getState('list.direction', 'desc');
		$this->setState('list.ordering', $orderCol);
		$this->setState('list.direction', $orderDirn);
		
		$query->group($db->escape('a.province_id'));
		$query->group($db->escape('a.created'));
		$query->order($db->escape($db->quoteName($orderCol) . ' ' . $orderDirn) . ', ' . $db->quoteName('a.province_id'));
		
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $query;
	}

	public function getProvinces($all = false, $simplified = false) {
		// Filter
		$groups = $this->user->get('groups');

		$province_ids	= (array) $this->getState('filter.province_ids');
		$all_provinces	= $this->getState('filter.all_provinces');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));

		// Join Permission
		$query->join('LEFT', $db->quoteName('#__gtpihps_permissions', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.province_id'));
		
		$query->where($db->quoteName('a.published') . ' IN (0,1)');

		if(!$this->user->authorise('core.admin')) {
			$query->where($db->quoteName('b.group_id') . 'IN (' . implode($groups) . ')');
		}

		if(!$all && !$all_provinces) {
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $province_ids).')');
		}

		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		if($simplified) {
			$data = array();
			foreach ($raw as $item) {
				$data[$item->id] = $item->name;
			}
			return $data;
		} else {
			return $raw;
		}
	}

	public function getProvinceOptions() {
		$options = $this->getProvinces(true, true);

		return GTHelperArray::toOption($options);
	}
}
