<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelIntegration extends GTModelAdmin{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function save() {
		return false;
	}
	
	public function getMarkets($province_id = null, $published = 1) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name', 'a.source_id', 'a.source_id2', 'a.source_id3', 'a.published')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		if($province_id) {
			$query->where($db->quoteName('a.province_id').' = '.$db->quote($province_id));
		}
		if($published) {
			$query->where($db->quoteName('a.published').' = 1');
		}

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getRegencies($province_id = null, $published = 1) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.name', 'a.long_name', 'a.source_id', 'a.source_id2', 'a.source_id3', 'a.published')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		if($province_id) {
			$query->where($db->quoteName('a.province_id').' = '.$db->quote($province_id));
		}
		if($published) {
			$query->where($db->quoteName('a.published') . ' = 1');
		}

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getProvince($province_id) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.source_id', 'a.source_id2', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		$query->where($db->quoteName('a.id') . ' = ' . $db->quote($province_id));
		//$query->where($db->quoteName('a.published') . ' = 1');

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getProvinces() {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.source_id', 'a.source_id2', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		//$query->where($db->quoteName('a.published') . ' = 1');

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getCommodities() {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.source_id', 'a.source_id2')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getMasterIDs($province_id, $date) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.market_id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$data = $db->loadObjectList('market_id');
		foreach($data as &$item) {
			$item = $item->id;
		}

		return $data;
	}

	public function getMasterIDsByMarket($market_id, $dates) {
		// Get a db connection.
		$db = $this->_db;

		// Set variables
		$dates = implode(',', array_map(array($db, 'quote'), $dates));

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->where($db->quoteName('a.market_id') . ' = ' . $db->quote($market_id));
		$query->where($db->quoteName('a.date') . ' IN ('.$dates.')');

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$data = $db->loadObjectList('date');
		foreach($data as &$item) {
			$item = $item->id;
		}

		return $data;
	}

	public function getDetailIDs($province_id, $date) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.price_id', 'a.commodity_id')));
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b').' ON '.$db->quoteName('a.price_id').' = '.$db->quoteName('b.id'));

		$query->where($db->quoteName('b.province_id') . ' = ' . $db->quote($province_id));
		$query->where($db->quoteName('b.date') . ' = ' . $db->quote($date));

		$db->setQuery($query);
		$data = $db->loadObjectList();
		$result = array();
		foreach($data as &$item) {
			$result[$item->price_id][$item->commodity_id] = $item->id;
		}

		return $result;
	}

	public function updateProvinces($publish = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query1 = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		$query1->select('COUNT('.$db->quoteName('id').') count');
		$query1->select($db->quoteName('province_id'));
		$query1->from($db->quoteName('#__gtpihps_prices'));
		$query1->where($db->quoteName('date').' >= DATE_SUB(curdate(), INTERVAL 2 WEEK)');
		$query1->group($db->quoteName('province_id'));

		$query2->update($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		$query2->join('LEFT', '('.$query1.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.province_id')
		);

		if($publish) {
			$query2->set($db->quoteName('a.published').' = 1');
			$query2->where($db->quoteName('b.count').' IS NOT NULL');
		} else {
			$query2->set($db->quoteName('a.published').' = 0');
			$query2->where($db->quoteName('b.count').' IS NULL');
		}

		//echo nl2br(str_replace('#__','pihps_',$query2)); die;
		
		$db->setQuery($query2);
		return $db->execute();
	}

	public function updateRegencies($publish = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query1 = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		$query1->select('COUNT('.$db->quoteName('id').') count');
		$query1->select($db->quoteName('regency_id'));
		$query1->from($db->quoteName('#__gtpihps_prices'));
		$query1->where($db->quoteName('date').' >= DATE_SUB(curdate(), INTERVAL 2 WEEK)');
		$query1->group($db->quoteName('regency_id'));

		$query2->update($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		$query2->join('LEFT', '('.$query1.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.regency_id')
		);

		if($publish) {
			$query2->set($db->quoteName('a.published').' = 1');
			$query2->where($db->quoteName('b.count').' IS NOT NULL');
		} else {
			$query2->set($db->quoteName('a.published').' = 0');
			$query2->where($db->quoteName('b.count').' IS NULL');
		}

		//echo nl2br(str_replace('#__','pihps_',$query2)); die;
		
		$db->setQuery($query2);
		return $db->execute();
	}

	public function updateMarkets($publish = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query1 = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		$query1->select('COUNT('.$db->quoteName('id').') count');
		$query1->select($db->quoteName('market_id'));
		$query1->from($db->quoteName('#__gtpihps_prices'));
		$query1->where($db->quoteName('date').' >= DATE_SUB(curdate(), INTERVAL 2 WEEK)');
		$query1->group($db->quoteName('market_id'));

		$query2->update($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		$query2->join('LEFT', '('.$query1.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.market_id')
		);

		if($publish) {
			$query2->set($db->quoteName('a.published').' = 1');
			$query2->where($db->quoteName('b.count').' IS NOT NULL');
		} else {
			$query2->set($db->quoteName('a.published').' = 0');
			$query2->where($db->quoteName('b.count').' IS NULL');
		}

		//echo nl2br(str_replace('#__','pihps_',$query2)); die;
		
		$db->setQuery($query2);
		return $db->execute();
	}
}
