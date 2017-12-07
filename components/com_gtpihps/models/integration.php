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
	
	public function getMarkets($province_id = null, $published = 1, $uniqueID = false) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name', 'a.published', 'a.price_type_id', 'b.source_type', 'b.source_id')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_market_sources', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.market_id')
		);

		if($province_id) {
			$query->where($db->quoteName('a.province_id').' = '.$db->quote($province_id));
		}
		if($published) {
			$query->where($db->quoteName('a.published').' = 1');
		}

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if($uniqueID) {
			$result = array();
			foreach ($items as $item) {
				$result[$item->source_type.'_'.$item->source_id] = $item;
			}
			return $result;
		} else {
			return $items;
		}
	}

	public function getMarkets2($province_id = null, $published = 1, $uniqueID = false) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.regency_id', 'a.name', 'a.published', 'a.price_type_id', 'b.long_name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'b').' ON '.
			$db->quoteName('a.regency_id').' = '.$db->quoteName('b.id')
		);

		if($province_id) {
			$query->where($db->quoteName('a.province_id').' = '.$db->quote($province_id));
		}
		if($published) {
			$query->where($db->quoteName('a.published').' = 1');
		}

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if($uniqueID) {
			$result = array();
			foreach ($items as $item) {
				$regCode	= preg_replace("/[^A-Za-z0-9?!]/",'',$item->long_name);
				$regCode	= $item->province_id.':'.strtolower($regCode);
				$marketCode	= preg_replace("/[^A-Za-z0-9?!]/",'',$item->name);
				$marketCode	= $regCode.':'.strtolower($marketCode);

				$result[$marketCode] = $item;
			}
			return $result;
		} else {
			return $items;
		}
	}

	public function getRegencies($province_id = null, $published = 1, $uniqueID = false) {
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
		$items = $db->loadObjectList('id');

		if($uniqueID) {
			$result = array();
			foreach ($items as $item) {
				$source_id	= trim($item->source_id);
				$source_id2	= trim($item->source_id2);
				$source_id3	= trim($item->source_id3);
				
				if($source_id) {
					$result['1_'.$source_id] = $item;
				}
				if($source_id2) {
					$result['2_'.$source_id2] = $item;
				}
				if($source_id3) {
					$result['3_'.$source_id3] = $item;
				}
			}
			return $result;
		} else {
			return $items;
		}
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

	public function getPriceTypes($key = 'code') {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.short_name', 'a.code')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		//$query->where($db->quoteName('a.published') . ' = 1');

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList($key);
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

	public function getData($province_id, $date) {
		$start_date = JHtml::date($date.' -14 day', 'Y-m-d');
		$end_date = JHtml::date($date.' -1 day', 'Y-m-d');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query2	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quote('0').' id');
		$query->select($db->quoteName('a.id').' price_id_source');
		$query->select($db->quote($date).' date');
		
		$query->select($db->quoteName(array(
			'a.source_type', 'a.price_type_id', 'a.province_id', 'a.regency_id', 'a.market_id', 'a.created', 'a.inputted', 'a.validated'
		)));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select($db->quoteName(array('b.commodity_id', 'b.price')));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query2->select('MAX('.$db->quoteName('date').') date');
		$query2->select($db->quoteName('a.market_id'));
		$query2->select($db->quoteName('b.commodity_id'));
		$query2->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query2->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);
		
		$query2->where($db->quoteName('a.published').' = 1');
		$query2->where($db->quoteName('a.price_type_id').' > 1');
		$query2->where($db->quoteName('a.province_id').' = '.$db->quote($province_id));
		$query2->where($db->quoteName('a.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));
		$query2->where('IFNULL('.$db->quoteName('b.price_id_source').', 0) = 0');

		$query2->group($db->quoteName('a.market_id'));
		$query2->group($db->quoteName('b.commodity_id'));

		$query->join('INNER', '('.$query2.') c ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.market_id').' AND '.
			$db->quoteName('a.date').' = '.$db->quoteName('c.date').' AND '.
			$db->quoteName('b.commodity_id').' = '.$db->quoteName('c.commodity_id')
		);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$db->setQuery($query);
		return $db->loadObjectList();
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

		$query->select($db->quoteName(array('a.id', 'b.market_id', 'a.commodity_id')));
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b').' ON '.$db->quoteName('a.price_id').' = '.$db->quoteName('b.id'));

		$query->where($db->quoteName('b.province_id') . ' = ' . $db->quote($province_id));
		$query->where($db->quoteName('b.date') . ' = ' . $db->quote($date));

		$db->setQuery($query);
		$data = $db->loadObjectList();
		$result = array();
		foreach($data as &$item) {
			$result[$item->market_id][$item->commodity_id] = $item->id;
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
