<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class JKCommodityModelImport_Excel extends JKModelForm
{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}

	public function getCity() {
		$jform = JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select project
		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__jkcommodity_city', 'a'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_excel_format', 'b'). ' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.city_id'));

		$query->where($db->quoteName('b.id').' = ' . $db->quote($jform->format_id));

		// Set Query
		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getFormat() {
		$jform = JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select project
		$query->select($db->quoteName(array('a.name', 'a.city_id', 'a.commodity_column', 'a.market_columns')));
		$query->from($db->quoteName('#__jkcommodity_excel_format', 'a'));
		$query->where($db->quoteName('a.id').' = ' . $db->quote($jform->format_id));

		//echo nl2br(str_replace('#__','tpid_',$query));
		// Set Query
		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getFormats() {		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select project
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.city_id', 'a.commodity_column', 'a.market_columns')));
		$query->from($db->quoteName('#__jkcommodity_excel_format', 'a'));

		//echo nl2br(str_replace('#__','tpid_',$query));
		// Set Query
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getMarkets() {
		$jform = JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select project
		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__jkcommodity_market', 'a'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_excel_format', 'b').' ON '.$db->quoteName('a.city_id').' = '.$db->quoteName('b.city_id'));
		
		$query->where($db->quoteName('b.id') . ' = ' . $db->quote($jform->format_id));
		$query->order($db->quoteName('a.city_id') . ' asc');

		// Set Query
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getCommodities($prepare = false) {
		$jform = JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('CONCAT(TRIM('.$db->quoteName('a.name').'), " (",TRIM('.$db->quoteName('a.denomination').'), ")") name');
		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.denomination')));
		
		$query->from($db->quoteName('#__jkcommodity_commodity', 'a'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_city_commodity', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.commodity_id'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_excel_format', 'c').' ON '.$db->quoteName('b.city_id').' = '.$db->quoteName('c.city_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('c.id') . ' = ' . $db->quote($jform->format_id));

		$query->order($db->quoteName('a.category_id'));
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		$raw = $db->loadObjectList('id');
		if($prepare) {
			$data = array();
			foreach ($raw as $item) {
				$data[$item->category_id][$item->id] = $item->name;
			}
		} else {
			$data = $raw;
		}
		
		//echo nl2br(str_replace('#__','tpid_',$query));
		return $data;
	}

	public function getCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name')));
		$query->from($db->quoteName('#__jkcommodity_category', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item->name;
		}
		return $data;
	}

	public function getCommodityList() {
		$commodities	= $this->getCommodities(true);
		$categories		= $this->getCategories();

		return JKHelperDocument::setCommodities($categories[0], $categories, $commodities);
	}

	public function getCommodityNames($format_id = null) {
		$jform = JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.commodity_id', 'a.original_name', 'a.multiplier')));
		
		$query->from($db->quoteName('#__jkcommodity_city_commodity', 'a'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_excel_format', 'b').' ON '.$db->quoteName('a.city_id').' = '.$db->quoteName('b.city_id'));
		
		$query->where($db->quoteName('b.id') . ' = ' . $db->quote($format_id ? $format_id : $jform->format_id));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.commodity_id') . ' > 0');
		$query->order($db->quoteName('a.id'));

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);

		return $db->loadObjectList('commodity_id');		
	}

}
