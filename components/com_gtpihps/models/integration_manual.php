<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelIntegration_Manual extends GTModelList
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
	}

	public function getProvinces($all = false, $simplified = false) {
		$province_ids	= (array) $this->getState('filter.province_ids');
		$all_provinces	= $this->getState('filter.all_provinces');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' IN (0,1)');
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
