<?php

/**
 * @package		GT PIHPS
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelRef_Regencies extends GTModelList{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array('a.name', 'b.name', 'c.name');
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
	}
	
	protected function getListQuery() {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select data.
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.published')));
		$query->select($db->quoteName('b.name', 'province'));
		$query->select($db->quoteName('c.name', 'region'));
		$query->from($db->quoteName('#__gtpihps_regencies', 'a'));
		$query->join('LEFT', '#__gtpihps_provinces AS b ON a.province_id = b.id');
		$query->join('LEFT', '#__gtpihps_regions AS c ON a.region_id = c.id');
		
		// Publish filter
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int)$published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}
		
		// Published request filter.
		//$query->where('b.published = 1');
		
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			// If contains spaces, the words will be used as keywords.
			if (preg_match('/\s/', $search)) {
				$search = str_replace(' ', '%', $search);
			}
			$search = $db->quote('%' . $search . '%');
			
			$search_query = array();
			$search_query[] = $db->quoteName('a.name') . 'LIKE ' . $search;
			$search_query[] = $db->quoteName('b.name') . 'LIKE ' . $search;
			$search_query[] = $db->quoteName('c.name') . 'LIKE ' . $search;
			$query->where('(' . implode(' OR ', $search_query) . ')');
		}
		
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		
		$query->group($db->escape('a.id'));
		$query->order($db->escape($db->quoteName($orderCol) . ' ' . $orderDirn));
		
		//echo nl2br(str_replace('#__','eburo_',$query));
		return $query;
	}
}
