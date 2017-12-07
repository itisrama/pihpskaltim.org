<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelImport extends GTModelAdmin
{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}

	public function getCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.code', 'a.source_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		return $db->loadObjectList('code');
	}

	public function getProvinces() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.code_name', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$data = $db->loadObjectList('code_name');

		$national				= new stdClass();
		$national->id			= 0;
		$national->code_name	= 'Nasional';
		$national->name			= $national->code_name;
		$data[$national->name]	= $national->code_name;

		return $data;
	}

	public function getMasterIDs($start_date, $end_date, $provinces) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.id'));
		$query->select('CONCAT('.$db->quoteName('a.date').',":",'.$db->quoteName('a.province_id').') key_id');
		$query->from($db->quoteName('#__gtpihps_flucs', 'a'));
		
		$query->where($db->quoteName('a.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
		$query->where($db->quoteName('a.province_id').' IN ('.implode(',', array_map(array($db, 'quote'), $provinces)).')');
		
		$db->setQuery($query);
		return $db->loadObjectList('key_id');
	}

	public function getDetailIDs($start_date, $end_date, $provinces) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.id'));
		$query->select('CONCAT('.$db->quoteName('b.date').',":",'.$db->quoteName('b.province_id').',":",'.$db->quoteName('a.category_id').') key_id');
		$query->from($db->quoteName('#__gtpihps_fluc_details', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_flucs', 'b').' ON '.
			$db->quoteName('a.fluc_id').' = '.$db->quoteName('b.id')
		);
		
		$query->where($db->quoteName('b.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
		$query->where($db->quoteName('b.province_id').' IN ('.implode(',', array_map(array($db, 'quote'), $provinces)).')');
		
		$db->setQuery($query);
		return $db->loadObjectList('key_id');
	}

}
