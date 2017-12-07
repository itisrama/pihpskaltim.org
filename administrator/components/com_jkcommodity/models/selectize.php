<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelSelectize extends JKModelList
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
	
	public function searchItems() {
		$type			= $this->input->get('type');
		$id_field		= $this->input->get('id_field', 'id');
		$name_field		= $this->input->get('name_field', 'name');
		$code_field		= $this->input->get('code_field', 'code');
		$parent_field	= $this->input->get('parent_field');
		$parent_value	= $this->input->get('parent_value');
		$order			= $this->input->get('order', 'a.id');
		$wheres			= array_filter(explode(',', $this->input->get('wheres', '', true)));

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		
		// Select fields from main table
		$query->select($db->quoteName('a.'.$id_field, 'id'));
		if($code_field) {
			$query->select('CONCAT('.$db->quoteName('a.'.$code_field).', " - ", '.$db->quoteName('a.'.$name_field).') name');
		} else {
			$query->select($db->quoteName('a.'.$name_field, 'name'));
		}
		$query->from($db->quoteName('#__jkcommodity_'.$type, 'a'));
		
		// Filter search
		$search		= JRequest::getVar('search');
		if (!empty($search)) {
			
			// If contains spaces, the words will be used as keywords.
			if (preg_match('/\s/', $search)) {
				$search = str_replace(' ', '%', $search);
			}
			$search			= $db->quote('%' . $search . '%');
			
			$search_query	= array();
			$search_query[]	= $db->quoteName('a.'.$name_field) . 'LIKE ' . $search;
			if($code_field) {
				$search_query[]	= $db->quoteName('a.'.$code_field) . 'LIKE ' . $search;
			}
			$query->where('(' . implode(' OR ', $search_query) . ')');
		}

		if($parent_field) {
			$query->where($db->quoteName($parent_field) . ' = ' . $db->quote($parent_value));
		}

		if(count($wheres)>0) {
			foreach ($wheres as $where) {
				list($where_f, $where_o, $where_v) = explode(' ', $where);
				$query->where($db->quoteName($where_f) . ' ' .$where_o . ' ' . $db->quote($where_v));
			}
		}

		$query->order($db->quoteName($order));

		$data = $this->_getList($query, 0, 100);

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $data;
	}
}
