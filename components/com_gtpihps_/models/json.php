<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelJSON extends GTModelList{
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array();
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

	public function getProvinces($grouping = false) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.region_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		if($grouping) {
			$result = array();
			foreach ($data as $item) {
				$item->name = trim($item->name);
				$result[$item->region_id][] = $item;
			}

			return $result;

		} else {
			foreach ($data as &$item) {
				$item->name = trim($item->name);
			}

			return $data;
		}
	}

	public function getRegions() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regions', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	public function getRegencies($province_id = null) {
		$province_id = $province_id ? $province_id : $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.type', 'a.name')));
		$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.published') . ' IS NOT NULL');
		$query->where($db->quoteName('a.published') . ' <> ""');
		if($province_id) {
			$query->where($db->quoteName('a.province_id') . ' = '.$db->quote($province_id));
		}
		$query->order($db->quoteName('a.id'));
		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		foreach ($data as &$item) {
			$item->name = trim($item->name);
		}
		
		return $data;
	}

	public function getRegency($regency_id) {
		$table = $this->getTable('Regency');
		$table->load($regency_id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getPriceTypes($all = false) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		if (JFactory::getUser()->guest && !$all) {
			$query->where($db->quoteName('a.published') . ' = 1');
		}
		//echo nl2br(str_replace('#__','pihpsnas_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getMarkets($regency_id = null) {
		$regency_id = $regency_id ? $regency_id : $this->input->get('regency_id');
		if(!$regency_id) return null;

		$priceTypes = $this->getPriceTypes(true);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.regency_id', 'a.price_type_id', 'a.name')));
		$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = '.$db->quote($regency_id));
		}
		$query->order($db->quoteName('a.price_type_id'));
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		$counter = array();
		foreach ($data as &$item) {
			if($item->price_type_id == 1) {
				$item->name = trim($item->name);
			} else {
				$priceType = $priceTypes[$item->price_type_id];
				$count = intval(@$counter[$item->price_type_id]);

				$count++;
				$item->name = $priceType->name.' #'.$count;

				$counter[$item->price_type_id] = $count;
			}
		}
		
		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		return $data;
	}

	public function getReferences() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.market_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		$query->select($db->quoteName('b.name', 'regency'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'b').' ON '.$db->quoteName('a.regency_id').' = '.$db->quoteName('b.id'));

		$query->select($db->quoteName('c.name', 'province'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'c').' ON '.$db->quoteName('a.province_id').' = '.$db->quoteName('c.id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('c.published') . ' = 1');
		
		$query->order($db->quoteName('c.id'));
		$query->order($db->quoteName('b.province_capital').' desc');
		$query->order($db->quoteName('b.type'));
		$query->order($db->quoteName('b.name'));
		$query->order($db->quoteName('a.name'));

		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	public function getCommodityCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item->name;
		}
		return $data;
	}

	public function getCommodityImages() {
		$commodity_id = $this->input->get('commodity_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.image')));
		$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').',")") name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($commodity_id) {
			$query->where($db->quoteName('a.id') . ' = '.$db->quote($commodity_id));
		}

		$db->setQuery($query);
		$commodities = $db->loadObjectList();

		foreach ($commodities as &$commodity) {
			$commodity->image = GT_MEDIA_URI . '/img/commodities/'.$commodity->image.'.png';
		}

		return $commodities;
	}
	
	public function getCategories() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		return $result;
	}
	
	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.image')));
		$query->select('CONCAT('.$db->quoteName('a.name').', ":",'.$db->quoteName('a.denomination').') name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item->name;
		}

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		return $this->prepareCommodities($data);
	}

	protected function prepareCommodities($data) {
		$categories		= $this->getCommodityCategories();
		$data			= GTHelperHtml::setCommodities($categories[0], $categories, $data, 'select');
		$commodities	= array();
		foreach ($data as &$item) {
			if(!$item->text) continue;
			list($name, $denom)	= explode(':', $item->text.':');
			$commodity			= new stdClass();
			$commodity->id		= is_numeric($item->value) ? $item->value : '';
			$commodity->name	= str_replace('&nbsp;', '', $name);
			$commodity->denom	= $denom;
			$commodity->type	= is_numeric($item->value) ? 'commodity' : 'category';
			$commodity->level	= substr_count($name, str_repeat('&nbsp;', 4));
			//$commodity->level	+= is_numeric($item->value) ? 1 : 0;
			
			$commodities[]		= $commodity;
		}

		//echo "<pre>"; print_r($commodities); echo "</pre>"; die;
		return $commodities;
	}

	protected function getStatLatestDate($date = null) {
		$province_id	= $this->input->get('province_id');
		$regency_id		= $this->input->get('regency_id');
		$market_id		= $this->input->get('market_id');
		$yesterday		= JHtml::date($date.' -1 day', 'Y-m-d');
		$last_week		= JHtml::date($yesterday.' -2 week', 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($province_id) {
			$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		}
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = ' . $db->quote($regency_id));
		}
		if($market_id) {
			$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));
		}
		$query->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($last_week).' AND '.$db->quote($yesterday));

		$db->setQuery($query);

		$data = $db->loadObject();
		
		//echo nl2br(str_replace('#__','pihpsnas_',$query));

		return @$data->date ? $data->date : $yesterday;
	}

	public function getStatNationalByProvince() {
		$last_date 		= $this->getStatLatestDate(JHtml::date('tomorrow', 'Y-m-d'));
		$date 			= $this->input->get('date');
		$date			= JHtml::date($date ? $date : $last_date, 'Y-m-d');
		$province_id	= $this->input->get('province_id');
		$commodities 	= $this->getCommodities();

		if(!$province_id) {
			return null;
		}
		
		$data				= new stdClass();
		$data->province_id	= $province_id;
		$data->date			= $date;
		$yesterday			= $this->getStatLatestDate($date);
		$yesterday			= $yesterday ? $yesterday : $data->date;

		if(!$data->date) {
			return null;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select($db->quoteName('b.commodity_id'));
		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		
		$dates = array($db->quote($yesterday), $db->quote($data->date));
		$query->where($db->quoteName('a.date').' IN ('.implode(',', $dates).')');
		
		$query->order($db->quoteName('b.commodity_id'));
		$query->order($db->quoteName('a.date'));

		$query->group($db->quoteName('b.commodity_id'));
		$query->group($db->quoteName('a.date'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$price_data = $db->loadObjectList();

		$commodity_data = array();
		foreach ($price_data as $price) {
			if($price->price <= 0) continue;
			$commodity_data[$price->commodity_id][$price->date] = GTHelperCurrency::roundNumber($price->price);
		}

		$prices = array();
		foreach ($commodities as $commodity) {
			$commodity->price	= "-";
			$commodity->diff	= "-";
			$commodity->trend	= "-";

			if($commodity->type == 'category') {
				$prices[] = $commodity;
				continue;
			}

			$commodity_prices = $commodity_data[$commodity->id];
			if(!isset($commodity_prices[$data->date])) {
				$commodity->price	= "-";
				$prices[]			= $commodity;
				continue;
			}

			$commodity->price	= GTHelperCurrency::fromNumber(end($commodity_prices), '');
			$commodity->diff	= end($commodity_prices) - reset($commodity_prices);
			$commodity->diff	= isset($commodity_prices[$yesterday]) ? $commodity->diff : '';

			if(!isset($commodity_prices[$yesterday])) {
				$commodity->trend = 'unknown';
			} else if($commodity->diff == 0) {
				$commodity->trend = 'still';
			} else if($commodity->diff < 0) {
				$commodity->trend = 'down';
			} else if($commodity->diff > 0) {
				$commodity->trend = 'up';
			}

			$commodity->diff = GTHelperCurrency::fromNumber($commodity->diff, '');

			$prices[] = $commodity;
		}

		$data->prices = $prices;
		return $data;
	}

	public function getStatNationalByCommodity() {
		$last_date 		= $this->getStatLatestDate(JHtml::date('tomorrow', 'Y-m-d'));
		$date 			= $this->input->get('date');
		$date			= JHtml::date($date ? $date : $last_date, 'Y-m-d');
		$commodity_id	= $this->input->get('commodity_id');

		if(!$commodity_id) {
			return null;
		}
		
		$provinces	= $this->getProvinces();
		$end		= $date;
		$start		= JHtml::date($date.' -1 week', 'Y-m-d');

		$data				= new stdClass();
		$data->commodity_id	= $commodity_id;
		$data->date_start	= $start;
		$data->date_end		= $end;

		if(!$end) {
			return null;
		}		

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.date', 'a.province_id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		
		$query->where($db->quoteName('a.date').' BETWEEN '.$db->quote($start).' AND '.$db->quote($end));
		
		$query->order($db->quoteName('a.province_id'));
		$query->order($db->quoteName('a.date'));

		$query->group($db->quoteName('a.province_id'));
		$query->group($db->quoteName('a.date'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$price_data = $db->loadObjectList();

		$province_data = array();
		foreach ($price_data as $price) {
			if($price->price <= 0) continue;
			$province_data[$price->province_id][$price->date] = GTHelperCurrency::roundNumber($price->price);
		}

		//echo "<pre>"; print_r($commodities); echo "</pre>";

		$prices = array();
		foreach ($provinces as $province) {
			$province->date		= "-";
			$province->price	= "-";
			$province->diff		= "-";
			$province->trend	= "-";

			$province_prices	= (array) @$province_data[$province->id];
			$province_dates 	= array_keys($province_prices);

			if(!$province_prices || end($province_dates) != $date) {
				$prices[] = $province;
				continue;
			}

			array_pop($province_dates);
			$prev_date = end($province_dates);
			$prev_date = $prev_date ? $prev_date : $date;

			$province_prices	= array_slice($province_prices, -2, 2, true);
			$province->date		= $date;
			$province->price	= GTHelperCurrency::fromNumber($province_prices[$date], '');
			$province->diff		= $province_prices[$date] - $province_prices[$prev_date];

			if($prev_date == $date) {
				$province->trend = 'unknown';
			} else if($province->diff == 0) {
				$province->trend = 'still';
			} else if($province->diff < 0) {
				$province->trend = 'down';
			} else if($province->diff > 0) {
				$province->trend = 'up';
			}

			$province->diff = GTHelperCurrency::fromNumber($province->diff, '');

			$prices[] = $province;
		}

		$data->prices = $prices;
		return $data;
	}

	public function getStatProvinceByRegency() {
		$last_date 		= $this->getStatLatestDate(JHtml::date('tomorrow', 'Y-m-d'));
		$date 			= $this->input->get('date');
		$date			= JHtml::date($date ? $date : $last_date, 'Y-m-d');
		$regency_id		= $this->input->get('regency_id');
		$regency		= $this->getRegency($regency_id);
		$province_id	= $this->input->get('province_id');
		$province_id 	= $province_id ? $province_id : $regency->province_id;
		$commodities 	= $this->getCommodities();

		$market_id		= $this->input->get('market_id');

		$data				= new stdClass();
		if($province_id) {
			$data->province_id	= $province_id;
		}
		if($regency_id) {
			$data->regency_id	= $regency_id;
		}
		if($market_id) {
			$data->market_id	= $market_id;
		}
		$data->date			= $date;
		$yesterday			= $this->getStatLatestDate($date);
		$yesterday			= $yesterday ? $yesterday : $data->date;
		$data->notification = '';

		if(!$data->date) {
			return null;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select($db->quoteName('b.commodity_id'));
		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($province_id) {
			$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		}
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = ' . $db->quote($regency_id));
		}
		if($market_id) {
			$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));
		}
		
		$dates = array($db->quote($yesterday), $db->quote($data->date));
		$query->where($db->quoteName('a.date').' IN ('.implode(',', $dates).')');
		
		$query->order($db->quoteName('b.commodity_id'));
		$query->order($db->quoteName('a.date'));

		$query->group($db->quoteName('b.commodity_id'));
		$query->group($db->quoteName('a.date'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$price_data = $db->loadObjectList();

		$commodity_data = array();
		foreach ($price_data as $price) {
			if($price->price <= 0) continue;
			$commodity_data[$price->commodity_id][$price->date] = GTHelperCurrency::roundNumber($price->price);
		}

		//echo "<pre>"; print_r($commodities); echo "</pre>";

		$prices = array();

		foreach ($commodities as &$commodity) {
			$commodity->price	= "-";
			$commodity->diff	= "-";
			$commodity->trend	= "-";

			if($commodity->type == 'category') {
				$prices[] = $commodity;
				continue;
			}

			$commodity_prices = $commodity_data[$commodity->id];
			if(!isset($commodity_prices[$data->date])) {
				$commodity->price	= "-";
				$prices[]			= $commodity;
				continue;
			}

			$commodity->price	= GTHelperCurrency::fromNumber(end($commodity_prices), '');
			$commodity->diff	= end($commodity_prices) - reset($commodity_prices);
			$commodity->diff	= isset($commodity_prices[$yesterday]) ? $commodity->diff : '';

			if(!isset($commodity_prices[$yesterday])) {
				$commodity->trend = 'unknown';
			} else if($commodity->diff == 0) {
				$commodity->trend = 'still';
			} else if($commodity->diff < 0) {
				$commodity->trend = 'down';
			} else if($commodity->diff > 0) {
				$commodity->trend = 'up';
			}

			$commodity->diff = GTHelperCurrency::fromNumber($commodity->diff, '');

			$prices[] = $commodity;
		}

		$data->prices = $prices;


		return $data;
	}

	protected function getPeriods($date, $type) {
		$count 			= 7;
		$count--;
		$date_start		= JHtml::date($date.' -'.$count.$type, 'Y-m-d');

		$date_start		= strtotime($date_start);
		$date_end		= strtotime($date);

		$data = new stdClass();
		switch($type) {
			default:
				$periods = GTHelperDate::getDayPeriod2($date_end, $count);
				break;
			case 'week':
				$periods = GTHelperDate::getWeekPeriod($date_start, $date_end);
				break;
			case 'month':
				$periods = GTHelperDate::getMonthPeriod($date_start, $date_end);
				break;
			case 'year':
				$periods = GTHelperDate::getYearPeriod($date_start, $date_end);
				break;
		}

		foreach ($periods as &$period) {
			$per		= new stdClass();
			$per->date	= $period->mysql;
			$per->long	= isset($period->full) ? $period->full : $period->ldate;
			$per->short	= isset($period->full) ? $period->ldate : $period->sdate;
			$per->unix	= $period->unix;
			$period		= $per;
		}

		return$periods;
	}

	public function getStatProvinceByCommodity() {
		$commodity_id	= $this->input->get('commodity_id');
		$regency_id		= $this->input->get('regency_id');
		$regency		= $this->getRegency($regency_id);
		$province_id	= $this->input->get('province_id');
		$province_id	= $province_id ? $province_id : $regency->province_id;
		$market_id		= $this->input->get('market_id', '');
		$type			= $this->input->get('type', 'day');

		if(!$commodity_id || !$province_id) {
			return null;
		}

		$date		= $this->input->get('date');
		$date		= JHtml::date($date ? $date : $last_date, 'Y-m-d');
		$periods	= $this->getPeriods($date, $type);

		$start_date	= reset($periods);
		$start_date	= $start_date->date;
		$end_date	= end($periods);
		$end_date	= $end_date->date;

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.regency_id', 'a.market_id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->select($db->quoteName('b.commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = ' . $db->quote($regency_id));
		}
		if($market_id) {
			$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));
		}

		// Dates filter
		switch ($type) {
			default:
				$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
				$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));
				break;
			case 'week':
				$start_date	= JHtml::date($start_date, 'Y') . sprintf('%02d', JHtml::date($start_date, 'W'));
				$end_date	= JHtml::date($end_date, 'Y') . sprintf('%02d', JHtml::date($end_date, 'W'));
				$query->where($db->quoteName('a.date').' >= STR_TO_DATE('.$db->quote($start_date.' Monday').', '.$db->quote('%X%V %W').')');
				$query->where($db->quoteName('a.date').' <= STR_TO_DATE('.$db->quote($end_date.' Monday').', '.$db->quote('%X%V %W').')');
				break;
			case 'month':
				$query->where($db->quoteName('a.date').' > LAST_DAY(SUBDATE('.$db->quote($start_date).', INTERVAL 1 MONTH))');
				$query->where($db->quoteName('a.date').' <= LAST_DAY('.$db->quote($end_date).')');
				break;
			case 'year':
				$query->where('YEAR('.$db->quoteName('a.date').') >= YEAR('.$db->quote($start_date).')');
				$query->where('YEAR('.$db->quoteName('a.date').') <= YEAR('.$db->quote($end_date).')');
				break;
		}

		// GROUPING
		// =========================================================
		switch ($type) {
			default:
				$query->group($db->quoteName('a.date'));
				break;
			case 'week':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				$query->group('WEEK('.$db->quoteName('a.date').')');
				break;
			case 'month':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				$query->group('MONTH('.$db->quoteName('a.date').')');
				break;
			case 'year':
				$query->group('YEAR('.$db->quoteName('a.date').')');
				break;
		}
		
		$query->order($db->quoteName('a.date'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$price_data = $db->loadObjectList();

		$prices = array();
		foreach ($price_data as $price_item) {
			$price	= GTHelperCurrency::roundNumber($price_item->price);
			$price 	= $price > 0 ? $price : NULL;
			$date	= strtotime($price_item->date);
			switch ($type) {
				case 'week':
					$prices[strtotime('this week', $date)] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'month':
					$prices[strtotime(JHtml::date($date, 'Y-m-01'))] = GTHelperCurrency::fromNumber($price, '');
					break;
				case 'year':
					$prices[strtotime(JHtml::date($date, 'Y-01-01'))] = GTHelperCurrency::fromNumber($price, '');
					break;
				default:
					$prices[$date] = GTHelperCurrency::fromNumber($price, '');
					break;
			}
		}
		
		$price_data = array();
		foreach ($periods as $period) {
			$price = @$prices[$period->unix];
			$price = $price ? $price : '-';

			$item = new stdClass();
			$item->unix = $period->unix;
			$item->price = $price;
			$item->date = $period->date;
			$item->long_date = $period->long;
			$item->short_date = $period->short;

			$price_data[] = $item;
		}

		$data = new stdClass();
		$data->commodity_id = $commodity_id;
		$data->regency_id = $regency_id;
		$data->market_id = $market_id;
		$data->prices = $price_data;

		return $data;
	}

	public function checkChatroomMember($user_id) {
		$chatroom_id = $this->input->get('chatroom_id');

		if(!$chatroom_id) {
			return false;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.userid'));
		$query->from($db->quoteName('cometchat_chatrooms_users', 'a'));
		$query->where($db->quoteName('a.chatroomid').' = '.$db->quote($chatroom_id));
		$query->where($db->quoteName('a.userid').' = '.$db->quote($user_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		return $db->loadResult() > 0;
	}

	public function getChatroomMembers() {
		$chatroom_id = $this->input->get('chatroom_id');

		if(!$chatroom_id) {
			return null;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('b.id', 'b.name', 'b.username', 'c.message')));
		$query->select('IF(TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME('.$db->quoteName('c.lastseen').'), NOW()) > 180, '.$db->quote('offline').', '.$db->quoteName('c.status').') status');
		$query->from($db->quoteName('cometchat_chatrooms_users', 'a'));
		$query->join('INNER', $db->quoteName('#__users', 'b').' ON '.$db->quoteName('a.userid').' = '.$db->quoteName('b.id'));
		$query->join('INNER', $db->quoteName('cometchat_status', 'c').' ON '.$db->quoteName('a.userid').' = '.$db->quoteName('c.userid'));

		$query->where($db->quoteName('a.chatroomid').' = '.$db->quote($chatroom_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		return $db->loadObjectList();
	}

	public function getStatRegencyByCommodity() {
		$last_date 		= $this->getStatLatestDate(JHtml::date('tomorrow', 'Y-m-d'));
		$date 			= $this->input->get('date');
		$date			= JHtml::date($date ? $date : $last_date, 'Y-m-d');
		$commodity_id	= $this->input->get('commodity_id');

		if(!$commodity_id) {
			return null;
		}
		
		$regencies	= $this->getRegencies();
		$end		= $date;
		$start		= JHtml::date($date.' -1 week', 'Y-m-d');

		$data				= new stdClass();
		$data->commodity_id	= $commodity_id;
		$data->date_start	= $start;
		$data->date_end		= $end;

		if(!$end) {
			return null;
		}		

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.date', 'a.regency_id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('AVG(IF('.$db->quoteName('b.price').' > 0, '.$db->quoteName('b.price').', NULL)) price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.price_id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		
		$query->where($db->quoteName('a.date').' BETWEEN '.$db->quote($start).' AND '.$db->quote($end));
		
		$query->order($db->quoteName('a.regency_id'));
		$query->order($db->quoteName('a.date'));

		$query->group($db->quoteName('a.regency_id'));
		$query->group($db->quoteName('a.date'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$price_data = $db->loadObjectList();

		$regency_data = array();
		foreach ($price_data as $price) {
			if($price->price <= 0) continue;
			$regency_data[$price->regency_id][$price->date] = GTHelperCurrency::roundNumber($price->price);
		}

		//echo "<pre>"; print_r($commodities); echo "</pre>";

		$prices = array();
		foreach ($regencies as $regency) {
			$regency->date		= "-";
			$regency->price	= "-";
			$regency->diff		= "-";
			$regency->trend	= "-";

			$regency_prices	= (array) @$regency_data[$regency->id];
			$regency_dates 	= array_keys($regency_prices);

			if(!$regency_prices || end($regency_dates) != $date) {
				$prices[] = $regency;
				continue;
			}

			array_pop($regency_dates);
			$prev_date = end($regency_dates);
			$prev_date = $prev_date ? $prev_date : $date;

			$regency_prices	= array_slice($regency_prices, -2, 2, true);
			$regency->date		= $date;
			$regency->price	= GTHelperCurrency::fromNumber($regency_prices[$date], '');
			$regency->diff		= $regency_prices[$date] - $regency_prices[$prev_date];

			if($prev_date == $date) {
				$regency->trend = 'unknown';
			} else if($regency->diff == 0) {
				$regency->trend = 'still';
			} else if($regency->diff < 0) {
				$regency->trend = 'down';
			} else if($regency->diff > 0) {
				$regency->trend = 'up';
			}

			$regency->diff = GTHelperCurrency::fromNumber($regency->diff, '');

			$prices[] = $regency;
		}

		$data->prices = $prices;
		return $data;
	}

	function getProvinceDates($province_id, $count=11, $exception=NULL)	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		$query->where($db->quoteName('a.published').' = 1');

		if($province_id) {
			$query->where($db->quoteName('a.province_id').' = '.$province_id);
		}

		if($exception) {
			$query->where($db->quoteName('a.date').' <= '.$db->quote($exception));
		} else {
			$query->where($db->quoteName('a.date').' <= DATE(NOW())');
		}

		$query->group($db->quoteName('a.date'));
		$query->order($db->quoteName('a.date').' desc');
		$query->setLimit(10);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		$db->setQuery($query);
		return $db->loadColumn();
	}

	function getCommodityPrices() {
		// Parameters
		$province_id	= $this->input->get('province_id', 0);
		$commodity_id	= $this->input->get('commodity_id', 0);
		$category_id	= $this->input->get('category_id', 0);
		$regency_id 	= $this->input->get('regency_id',0);
		$market_id		= $this->input->get('market_id',0);
		$province		= $this->input->get('province');
		$commodity		= $this->input->get('commodity');
		$category		= $this->input->get('category');
		$regency 		= $this->input->get('regency');
		$market 		= $this->input->get('market');

		$dates		= $this->getProvinceDates($province_id);
		$date_start = end($dates);
		$date_end 	= reset($dates);
		
		$db 	= $this->_db;
		$query 	= $db->getQuery(true);

		$query->select('ROUND(AVG('.$db->quoteName('a.price').')/50, 0)*50 price');
		$query->select($db->quoteName(array('a.commodity_id')));
		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));

		$query->select($db->quoteName(array('b.date')));
		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b').' ON '.
			$db->quoteName('a.price_id').' = '.$db->quoteName('b.id')
		);

		$query->where($db->quoteName('b.date').' BETWEEN '.$db->quote($date_start).' AND '.$db->quote($date_end));
		$query->where($db->quoteName('b.published').' = 1');
		$query->where($db->quoteName('a.price'). ' > 50');
		
		// Filter
		if($province_id) {
			$query->where($db->quoteName('b.province_id').' = '.$province_id);
		}
		if($commodity_id) {
			$query->where($db->quoteName('a.commodity_id').' = '.$commodity_id);
		}
		if($regency_id) {
			$query->where($db->quoteName('b.regency_id').' = '.$regency_id);
		}
		if($market_id) {
			$query->where($db->quoteName('b.market_id').' = '.$market_id);
		}

		if(!$province_id && $province) {
			$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'pro').' ON '.
				$db->quoteName('pro.id').' = '.$db->quoteName('b.province_id')
			);
			$query->where($db->quoteName('pro.name').' LIKE '.$db->quote('%'.$province.'%'));
		}

		if(!$regency_id && $regency) {
			$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'reg').' ON '.
				$db->quoteName('reg.id').' = '.$db->quoteName('b.regency_id')
			);
			$query->where('('.$db->quoteName('reg.name').' LIKE '.$db->quote('%'.$regency.'%').' OR '.$db->quoteName('reg.long_name').' LIKE '.$db->quote('%'.$regency.'%').')');
		}

		if(!$market_id && $market) {
			$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_markets', 'mar').' ON '.
				$db->quoteName('mar.id').' = '.$db->quoteName('b.market_id')
			);
			$query->where('('.$db->quoteName('mar.name').' LIKE '.$db->quote('%'.$market.'%').' OR '.$db->quoteName('mar.short_name').' LIKE '.$db->quote('%'.$market.'%').')');
		}

		$query->group($db->quoteName(array('a.commodity_id', 'b.date')));

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		$query2 = $db->getQuery(true);

		$query2->select($db->quoteName(array('c.id', 'c.name', 'c.denomination', 'c.category_id', 'c.image')));
		$query2->select('GROUP_CONCAT('.$db->quoteName('d.price').') prices');
		$query2->select('GROUP_CONCAT('.$db->quoteName('d.date').') dates');

		$query2->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'c'));
		
		$query2->select($db->quoteName('cat.name', 'category'));
		$query2->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_categories', 'cat').' ON '.
			$db->quoteName('cat.id').' = '.$db->quoteName('c.category_id')
		);
		
		$query2->join('INNER', '('.$query.') d ON '.
			$db->quoteName('c.id').' = '.$db->quoteName('d.commodity_id')
		);

		// Filter
		if(!$commodity_id && $commodity) {
			$query2->where($db->quoteName('c.name').' LIKE '.$db->quote('%'.$commodity.'%'));
		}
		if($category_id) {
			$query2->where($db->quoteName('c.category_id').' = '.$category_id);
		}
		if(!$category_id && $category) {
			$query2->where($db->quoteName('cat.name').' LIKE '.$db->quote('%'.$category.'%'));
		}

		$query2->where($db->quoteName('c.published').' = 1');
		$query2->group($db->quoteName('c.id'));

		//echo nl2br(str_replace('#__','pihpsnas_',$query2)); die;
		$db->setQuery($query2);
		$prices = $db->loadObjectList('id');

		foreach ($prices as $k => &$price) {
			$block		= new stdClass();
			$com_prices	= (array) explode(',', $price->prices);
			$com_dates	= (array) explode(',', $price->dates);

			$com_prices = array_combine($com_dates, $com_prices);
			ksort($com_prices);
			
			$price_now	= array_pop($com_prices);
			$price_then	= end($com_prices);
			$price_diff	= $price_now - $price_then;

			array_push($com_prices, $price_now);
			$start_price = array_shift($com_prices);
			$start_price = GTHelperCurrency::roundNumber($start_price);
			foreach ($com_prices as &$com_price) {
				$end_price		= GTHelperCurrency::roundNumber($com_price);
				$com_price		= $com_price - $start_price;
				$start_price	= $end_price;
			}

			$price->prices = implode(',', $com_prices);

			if($price_now == 0) {
				unset($prices[$k]);
				continue;
			}

			if($price_diff < 0) {
				$block->class	= 'price_down';
				$block->icon	= 'fa fa-arrow-down';
				$block->status	= 'Turun';
			} else if($price_diff > 0) {
				$block->class	= 'price_up';
				$block->icon	= 'fa fa-arrow-up';
				$block->status	= 'Naik';
			} else {
				$block->class	= 'price_still';
				$block->icon	= 'fa fa-pause';
				$block->status	= 'Harga Tetap';
			}
			
			$diff		= abs($price_diff);
			$percent	= round($diff/$price_then * 100, 2);
			
			$block->title	= $price->name;
			$block->price	= 'Rp'.number_format($price_now, 0, ',', '.');
			$block->denom	= 'Per '.$price->denomination;
			$block->image	= JURI::root(true) . '/images/commodities/'.$price->image.'.png';
			$block->prices 	= $price->prices;
			$block->status 	= $diff > 0 ? $block->status.' '.$percent.'%'.' - Rp'.number_format($diff, 0, ',', '.') : $block->status;
			$block->category	= $price->category;
			$block->category_id	= $price->category_id;

			$price = $block;
		}
		$data			= new stdClass();
		$data->date		= JHtml::date($date_end, 'j F Y');
		$data->dateSQL	= $date_end;
		$data->prices	= $prices;

		return $data;
	}

	public function getDateReference($table = 'market') {
		$table = GTHelper::pluralize($table);
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.created').') created');
		$query->select('MAX('.$db->quoteName('a.modified').') modified');
		$query->from($db->quoteName('#__gtpihps_'.$table, 'a'));

		$db->setQuery($query);
		$result = $db->loadObject();

		$date = $result->created > $result->modified ? $result->created : $result->modified;
		$date = JFactory::getDate($date)->toUnix();

		return $date;
	}

	public function getHolidays() {
		$date = $this->input->get('date');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.start', 'a.end')));		
		$query->from($db->quoteName('#__gtpihps_holidays', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($date) {
			$date = JFactory::getDate($date)->format('Y-m-d H:i:s');
			$query->where('('.$db->quoteName('a.created').' > '.$db->quote($date).' OR '.$db->quoteName('a.modified').' > '.$db->quote($date).')');
		}

		$db->setQuery($query);
		return $db->loadObjectList();
	}


	public function getIntegrationPrices() {
		$date = $this->input->get('date');
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('ROUND(AVG('.$db->quoteName('b.price').')/50)*50 price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query->select($db->quoteName('c.id', 'market_id'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'c').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.id')
		);

		$query->select($db->quoteName('d.id', 'commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'd').' ON '.
			$db->quoteName('b.commodity_id').' = '.$db->quoteName('d.id')
		);

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'e').' ON '.
			$db->quoteName('a.province_id').' = '.$db->quoteName('e.id')
		);
		
		$query->group('a.date');
		$query->group('a.market_id');
		$query->group('b.commodity_id');

		$query->where($db->quoteName('c.price_type_id') . ' = '.$db->quote('1'));
		$query->where($db->quoteName('e.id') . ' = '.$db->quote($province_id));
		$query->where($db->quoteName('a.date') . ' = '.$db->quote($date));
		$query->where($db->quoteName('b.price') . ' > 0');
		//$query->where($db->quoteName('c.price_type_id') . ' = '.$db->quote('1'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		
		$data = $db->loadObjectList();

		$items = array();
		foreach ($data as $item) {
			$items[$item->market_id]['market_id']	= $item->market_id;
			$items[$item->market_id]['date']		= $item->date;
			$items[$item->market_id]['details'][$item->commodity_id.'-'.$item->quality_id]['commodity_id']	= $item->commodity_id;
			$items[$item->market_id]['details'][$item->commodity_id.'-'.$item->quality_id]['price']			= $item->price;
		}

		sort($items);
		foreach ($items as &$item) {
			sort($item['details']);
		}

		return $items;
	}

	public function getIntegrationMarkets() {
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.id', 'market_id'));
		$query->select($db->quoteName('a.name', 'market_name'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		$query->select($db->quoteName('d.name', 'market_type'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_price_types', 'd').' ON '.
			$db->quoteName('a.price_type_id').' = '.$db->quoteName('d.id')
		);


		$query->select($db->quoteName('c.id', 'regency_id'));
		$query->select($db->quoteName('c.long_name', 'regency_name'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'c').' ON '.
			$db->quoteName('a.regency_id').' = '.$db->quoteName('c.id')
		);

		$query->where($db->quoteName('a.province_id') . ' = '.$db->quote($province_id));
		
		
		$query->group('a.id');

		$query->where($db->quoteName('a.published') . ' = '.$db->quote(1));
		$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote(1));
		//$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote('1'));

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		
		return $db->loadObjectList();
	}

	public function getIntegrationCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.id', 'commodity_id'));
		$query->select($db->quoteName('a.name', 'commodity_name'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));

		$query->select($db->quoteName('c.id', 'category_id'));
		$query->select($db->quoteName('c.name', 'category_name'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_categories', 'c').' ON '.
			$db->quoteName('a.category_id').' = '.$db->quoteName('c.id')
		);

		$query->group('a.id');

		$query->where($db->quoteName('a.published') . ' = '.$db->quote(1));
		$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote(1));
		//$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote('1'));

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		
		return $db->loadObjectList();
	}
}
