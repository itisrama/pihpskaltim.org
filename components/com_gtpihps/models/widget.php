<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelWidget extends GTModelList {
	
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
		
		$date			= $this->input->get('date');
		$id				= $this->input->get('id', '0');
		$location		= $this->input->get('location', 'province');
		$price_type_id	= $this->input->get('price_type_id', '1');

		$this->setState('filter.date', $date);
		$this->setState('filter.id', $id);
		$this->setState('filter.location', $location);
		$this->setState('filter.price_type_id', $price_type_id);
	}

	public function getData($type = 'commodity') {
		$layout 			= $this->input->get('layout');
		$id					= $this->getState('filter.id');
		$location			= $this->getState('filter.location');
		$price_type_id		= $this->getState('filter.price_type_id');
		$dates				= $this->getLatestDates();
		
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// SELECT & JOIN
		// =========================================================
		// Select Price Details
		$query->select('AVG(IF('.$db->quoteName('a.price').' > 0, '.$db->quoteName('a.price').', NULL)) price');
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));

		// Join Prices
		$query->select($db->quoteName('b.date'));
		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b') . ' ON ' . $db->quoteName('a.price_id') . ' = ' . $db->quoteName('b.id'));

		// Join Markets
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'd') . ' ON ' . $db->quoteName('b.market_id') . ' = ' . $db->quoteName('d.id'));

		// FILTERING
		// =========================================================
		// Publish filter
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('a.price') . ' > 50');

		// Price Type Filter
		if($location != 'market') {
			$query->where($db->quoteName('d.price_type_id') . ' = '.$db->quote($price_type_id));
		}

		if($id > 0) {
			$query->where($db->quoteName('b.'.$location.'_id') . ' = '.$db->quote($id));
		}
		
		// Dates filter
		$query->where($db->quoteName('b.date').' BETWEEN '.$db->quote(end($dates)).' AND '.$db->quote(reset($dates)));
	

		// Switch Type
		switch($type) {
			case 'commodity':
				$query->select($db->quoteName('a.commodity_id', 'id'));
				$query->group($db->quoteName(array('a.commodity_id', 'b.date')));
				break;
			case 'category':
				$query->select($db->quoteName('c.category_id', 'id'));
				$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . $db->quoteName('a.commodity_id') . ' = ' . $db->quoteName('c.id'));
				$query->group($db->quoteName(array('c.category_id', 'b.date')));
				break;
		}

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$items = $db->loadRowList();

		$prices = array();
		foreach ($items as $item) {
			list($price, $date, $id) = $item;

			$price	= round($price/50)*50;
			$price	= $price > 0 ? $price : NULL;

			$prices[$id][$date] = $price;
		}

		foreach ($prices as &$priceDates) {
			//$price	= GTHelperCurrency::fromNumber($price, '');
			$price = new stdClass();

			$priceDates = array_intersect_key($priceDates, array_flip($dates));

			$prevPrice = reset($priceDates);
			$flucs = array();
			foreach ($priceDates as $priceDate) {
				$flucs[]	= $priceDate - $prevPrice;
				$prevPrice	= $priceDate;
			}
			array_shift($flucs);
			$lastFluc = end($flucs);

			$price->current = end($priceDates);
			$price->current = $price->current > 0 ? GTHelperCurrency::fromNumber($price->current, 'Rp') : '-';
			$price->flucs = implode(',', $flucs);
			$price->status = $lastFluc == 0 ? 'still' : ($lastFluc > 0 ? 'up' : 'down');

			$priceDates = $price;
		}

		return $prices;
	}

	public function getItems($table = false) {
		return $this->getData('commodity');
	}

	public function getItemsCat($table = false) {
		return $this->getData('category');
	}

	public function getLatestDates() {
		$date				= $this->getState('filter.date');
		$id					= $this->getState('filter.id');
		$location			= $this->getState('filter.location');
		$price_type_id		= $this->getState('filter.price_type_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');

		// Join Markets
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b') . ' ON ' . $db->quoteName('a.market_id') . ' = ' . $db->quoteName('b.id'));

		// Join holiday
		$query->join('LEFT', $db->quoteName('#__gtpihps_holidays', 'c').' ON '.$db->quoteName('a.date').' BETWEEN '.$db->quoteName('c.start').' AND '.$db->quoteName('c.end'));
		$query->where($db->quoteName('c.id') . ' IS NULL');

		// Price Type Filter
		if($location != 'market') {
			$query->where($db->quoteName('b.price_type_id') . ' = '.$db->quote($price_type_id));
		}


		// Date Filter
		if($date) {
			$query->where($db->quoteName('a.date') . ' <= '.$db->quote($date));
		}

		if($id > 0) {
			$query->where($db->quoteName('a.'.$location.'_id') . ' = '.$db->quote($id));
		}

		$query->group($db->quoteName('a.date'));
		$query->order($db->quoteName('a.date').' desc');
		$query->setLimit(7);

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadColumn();
	}

	public function getLocation($all = false, $prepare = false) {
		$id				= $this->getState('filter.id');
		$location		= $this->getState('filter.location');
		$price_type_id	= $this->getState('filter.price_type_id');
		$table			= '#__gtpihpssurvey_ref_'.GTHelper::pluralize($location);

		// Get a db connection.
		$db = $this->_db;

		if($id > 0 && $location) {
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select('a.*');
			$query->from($db->quoteName($table, 'a'));
			
			$query->where($db->quoteName('a.id') . ' = '.intval($id));
			$db->setQuery($query);
			
			$loc = $db->loadObject();
		}

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		$query->where($db->quoteName('a.id') . ' = '.intval($price_type_id));
		$db->setQuery($query);
		
		$price_type = $db->loadResult();


		switch($location) {
			default:
				//return '<span>'.JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES').'</span><small>'.$price_type.'</small>';
				return '<span>'.JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES').'</span>';
				break;
			case 'province':
				//return '<span>'.$loc->name.'</span><small>'.$price_type.'</small>';
				return '<span>'.$loc->name.'</span>';
				break;
			case 'regency':
				//return '<span>'.sprintf(JText::_('COM_GTPIHPS_'.strtoupper($loc->type)), $loc->name).'</span><small>'.$price_type.'</small>';
				return '<span>'.sprintf(JText::_('COM_GTPIHPS_'.strtoupper($loc->type)), $loc->name).'</span>';
				break;
			case 'market':
				return '<span>'.$loc->name.'</span>';
				break;
		}
	}

	public function getCommodities($all = false, $prepare = false) {
		$commodity_ids		= $this->getState('filter.commodity_ids');
		$all_commodities	= $this->getState('filter.all_commodities');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.denomination')));
		if($all) {
			$query->select($db->quoteName('a.name'));
		} else {
			$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').', ")") name');
		}
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$all && !$all_commodities) {
			$commodity_ids = array_map(array($db, 'quote'), $commodity_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $commodity_ids).')');
		}

		$db->setQuery($query);
		$raw = $db->loadObjectList('id');
		if($prepare) {
			$data = array();
			foreach ($raw as $item) {
				$data[$item->category_id][$item->id] = $item;
			}
		} else {
			$data = $raw;
		}
		
		//echo "<pre>"; print_r($data); echo "</pre>";
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name', 'a.denomination')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item;
		}
		return $data;
	}

	public function getCommodityOptions() {
		$commodities	= $this->getCommodities(true, true);
		$categories		= $this->getCategories();
		
		return $commodities ? GTHelperHtml::setCommodities($categories[0], $categories, $commodities) : array();
	}

	public function getCommodityList() {
		$commodities	= $this->getCommodities(true, true);
		$categories		= $this->getCategories();

		return GTHelperHtml::setCommodities($categories[0], $categories, $commodities);
	}
}
