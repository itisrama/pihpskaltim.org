<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSBCastModelScheduler extends GTModelAdmin {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data = parent::getItem($pk);
		if(!is_object($data)) return false;
		$this->item = $data;
		return $data;
	}

	public function getIDs() {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);

		// Select project
		$query->select($db->quoteName(array('a.id')));
		$query->from($db->quoteName('#__gtpihpsbcast_schedulers', 'a'));
		$query->where('FIND_IN_SET('.JHtml::date('now', 'w').','.$db->quoteName('a.schedules').') > 0');
		$query->where($db->quoteName('a.published') .' = 1');

		//echo nl2br(str_replace('#__','eburo_',$query));
		$db->setQuery($query);
		return array_keys($db->loadObjectList('id'));
	}

	public function getMembers($group_ids) {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);

		// Select project
		$query->select($db->quoteName(array('a.members')));
		$query->from($db->quoteName('#__gtpihpsbcast_groups', 'a'));
		$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $group_ids) . ')');

		//echo nl2br(str_replace('#__','eburo_',$query));
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getContacts($contact_ids) {
		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);

		// Select project
		$query->select($db->quoteName(array('a.name', 'a.phone')));
		$query->from($db->quoteName('#__gtpihpsbcast_contacts', 'a'));
		$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $contact_ids) . ')');

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getCommodityName($commodity_id) {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Select commodities
		$query->select('CONCAT('.$db->quoteName('a.short_name').', "/",'.$db->quoteName('a.denomination').') name');
		$query->from($db->quoteName('#__gtpihps_commodities', 'a'));
		$query->where($db->quoteName('a.id') . ' = ' . intval($commodity_id));

		//echo nl2br(str_replace('#__','gtw_',$query));

		$db->setQuery($query);
		return @$db->loadObject()->name;
	}

	public function getCity($market_ids) {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName('b.name'));
		$query->from($db->quoteName('#__gtpihps_markets', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_regencies', 'b').' ON '.
			$db->quoteName('a.regency_id').' = '.$db->quoteName('b.id')
		);

		if(count($market_ids)) {
			$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $market_ids) . ')');
		}
		$db->limit(1);
		

		//echo nl2br(str_replace('#__','gtw_',$query));

		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getLatestDate($market_ids) {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Select commodities
		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		if(count($market_ids)) {
			$query->where($db->quoteName('a.market_id') . ' IN (' . implode(',', $market_ids) . ')');
		}
		

		//echo nl2br(str_replace('#__','gtw_',$query));

		$db->setQuery($query);
		$date = @$db->loadObject()->date;
		return $date ? $date : date();
	}

	public function getPrices($market_ids, $commodity_ids, $date, $type = 'commodity') {
		$db = $this->_db;
		$query = $db->getQuery(true);

		// Select price
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join price detail
		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').
			' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));

		if($type == 'commodity') {
			// Join commodity
			$query->select('CONCAT('.$db->quoteName('c.short_name').', "/",'.$db->quoteName('c.denomination').') name');
			$query->join('INNER', $db->quoteName('#__gtpihps_commodities', 'c').
				' ON '.$db->quoteName('b.commodity_id').' = '.$db->quoteName('c.id'));
		} else {
			// Join market
			$query->select($db->quoteName('c.name'));
			$query->join('INNER', $db->quoteName('#__gtpihps_markets', 'c').
				' ON '.$db->quoteName('a.market_id').' = '.$db->quoteName('c.id'));
		}

		if(count($market_ids)) {
			$query->where($db->quoteName('a.market_id') . ' IN (' . implode(',', $market_ids) . ')');
		}

		if(count($commodity_ids)) {
			$query->where($db->quoteName('b.commodity_id') . ' IN (' . implode(',', $commodity_ids) . ')');
		}
		
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));
		$query->where($db->quoteName('c.short_name') . ' IS NOT NULL');

		$query->group($type == 'commodity' ? $db->quoteName('b.commodity_id') : $db->quoteName('a.market_id'));

		//echo nl2br(str_replace('#__','pihps_',$query));

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function save($data) {
		$data	= JArrayHelper::toObject($data);
		return parent::save($data);
	}

	public function delete(&$pks) {
		return parent::delete($pks);
	}
}
