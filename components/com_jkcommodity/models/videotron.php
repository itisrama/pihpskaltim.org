<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelVideotron extends JKModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array();
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		// Adjust the context to support modal layouts.
		$layout = $this->jinput->get('layout', 'default');
		if ($layout) {
			$this->context .= '.'.$layout;
		}

		// List state information.
		parent::populateState($ordering, $direction);
		
		$date = $this->getUserStateFromRequest($this->context.'.filter.date', 'date', JHtml::date('+1 day', 'Y-m-d'));
		$this->setState('filter.date', $date);
		
		$city_id = $this->getUserStateFromRequest($this->context.'.filter.city_id', 'city_id', 13);
		$this->setState('filter.city_id', $city_id);

		$markets = $this->getUserStateFromRequest($this->context.'.filter.market_ids', 'market_ids', array(1,2,3,4,5));
		$this->setState('filter.market_ids', $markets);

		$category_ids = $this->getUserStateFromRequest($this->context.'.filter.category_ids', 'category_ids', array(8));
		$this->setState('filter.category_ids', $category_ids);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */

	protected function getLatestDate($date) {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__jkcommodity_price', 'a'));

		$market_ids = $this->getState('filter.market_ids');
		if(count($market_ids) > 0) {
			JArrayHelper::toInteger($market_ids);
			$query->where($db->quoteName('a.market_id').' IN ('.implode(',',$market_ids).')');
		}

		$query->where($db->quoteName('a.published').' = 1');
		$query->where($db->quoteName('a.date').' < '. $db->quote($date));
		$query->limit(5);

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);
		$item = $db->loadObject();
		return property_exists($item, 'date') ? $item->date : $date;
	}

	public function getCity() {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.short_name')));
		$query->from($db->quoteName('#__jkcommodity_city', 'a'));

		$city_id = $this->getState('filter.city_id');
		$query->where($db->quoteName('a.id').' = '.$db->quote($city_id));

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);

		return $db->loadObject();
	}

	public function getCommodities() {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.commodity_id', 'a.name', 'a.denomination')));
		$query->from($db->quoteName('#__jkcommodity_city_commodity', 'a'));
		$query->where($db->quoteName('a.published').' = 1');

		$city_id = $this->getState('filter.city_id');
		$query->where($db->quoteName('a.city_id').' = '.$db->quote($city_id));

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);
		return $db->loadObjectList('commodity_id');
	}

	public function getNews() {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.title', 'a.created')));
		$query->from($db->quoteName('#__content', 'a'));

		$category_ids = $this->getState('filter.category_ids');
		if(count($category_ids) > 0) {
			JArrayHelper::toInteger($category_ids);
			$query->where($db->quoteName('a.catid').' IN ('.implode(',',$category_ids).')');
		}
		$query->where($db->quoteName('a.state').' = 1');
		$query->order($db->quoteName('a.created') . ' desc');

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getMarkets() {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.short_name')));
		$query->from($db->quoteName('#__jkcommodity_market', 'a'));

		$market_ids = $this->getState('filter.market_ids');
		if(count($market_ids) > 0) {
			JArrayHelper::toInteger($market_ids);
			$query->where($db->quoteName('a.id').' IN ('.implode(',',$market_ids).')');
		}
		$query->where($db->quoteName('a.published').' = 1');
		$city_id = $this->getState('filter.city_id');
		$query->where($db->quoteName('a.city_id').' = '.$db->quote($city_id));

		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	protected function getPrices($date) {
		// Get DB Object
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->from($db->quoteName('#__jkcommodity_price', 'a'));

		$query->select($db->quoteName(array('b.commodity_id')));
		$query->select('GROUP_CONCAT('.$db->quoteName('a.market_id').') market_ids');
		$query->select('GROUP_CONCAT('.$db->quoteName('b.price').') prices');
		$query->join('LEFT', $db->quoteName('#__jkcommodity_price_detail', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));

		$market_ids = $this->getState('filter.market_ids');
		if(count($market_ids) > 0) {
			JArrayHelper::toInteger($market_ids);
			$query->where($db->quoteName('a.market_id').' IN ('.implode(',',$market_ids).')');
		}
		
		$city_id = $this->getState('filter.city_id');
		$query->where($db->quoteName('a.city_id').' = '.$db->quote($city_id));
		$query->where($db->quoteName('a.date').' = '.$db->quote($date));
		$query->group($db->quoteName('b.commodity_id'));
		$query->where($db->quoteName('a.published').' = 1');
		//echo nl2br(str_replace('#__','tpid_',$query));

		$db->setQuery($query);
		return $db->loadObjectList('commodity_id');
	}

	public function getDates() {
		$date		= $this->getState('filter.date');
		
		$item = new stdClass();
		$item->now	= $this->getLatestDate($date);
		$item->then	= $this->getLatestDate($item->now);

		return $item;
	}

	public function getItems() {
		// Get DB Object
		$db = $this->_db;

		$date 			= $this->getDates();
		$markets		= $this->getMarkets();
		$commodities	= $this->getCommodities();
		$prices_now		= $this->getPrices($date->now);
		$prices_then	= $this->getPrices($date->then);

		$items = array();

		foreach ($commodities as $id => $commodity) {
			$price_now	= @$prices_now[$id];
			$price_then	= @$prices_then[$id];

			if(!$price_now) continue;

			$market_ids1	= explode(',', @$price_now->market_ids);
			$prices1		= explode(',', @$price_now->prices);
			$market_ids2	= explode(',', @$price_then->market_ids);
			$prices2		= explode(',', @$price_then->prices);

			$item	= array();
			$item[]	= trim($commodity->name) .'<small> / '. $commodity->denomination .'</small>';
			foreach ($markets as $market) {
				$market_index1 = array_search($market->id, $market_ids1);
				$market_index2 = array_search($market->id, $market_ids2);

				//$item[] = is_numeric($market_index2) ? JKHelperCurrency::fromNumber($prices2[$market_index2], '') : '-';
				$item[] = is_numeric($market_index1) ? JKHelperCurrency::fromNumber($prices1[$market_index1], '') : '-';
			}
			$items[] = $item;
		}

		return $items;
	}
}
