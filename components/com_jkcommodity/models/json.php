<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelJson extends JKModelAdmin{
	
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
		$layout = $this->jinput->get('layout', 'default');
		if ($layout) {
			$this->context.= '.' . $layout;
		}
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '1');
		$this->setState('filter.published', $published);
	}

	public function getReference($id, $type = 'market') {
		$type = JKHelper::pluralize($type);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_'.$type, 'a'));
		$query->where($db->quoteName('a.id') . ' = '.intval($id));

		$db->setQuery($query);
		
		return $db->loadObject();
	}

	public function getProvinces(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select(array('a.id', 'a.name'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));

		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		$data = $this->_getList($query);
		
		return $data;
	}

	public function getRegencies($published = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->select($db->quoteName('a.created', 'date'));
		//$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		if($published){
			$query->where($db->quoteName('a.published') . ' = 1');
			$query->where($db->quoteName('a.published') . ' IS NOT NULL');
			$query->where($db->quoteName('a.published') . ' <> ""');
		}
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		foreach ($data as &$item) {
			$item->name = trim($item->name);
			$item->date = JHtml::date($item->date, 'U');
		}

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $data;
	}

	public function getMarkets($regency_id = null) {
		$regency_id = $regency_id ? $regency_id : $this->jinput->get('regency_id');
		if(!$regency_id) return null;

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->select($db->quoteName('a.regency_id'));
		$query->select($db->quoteName('a.created', 'date'));
		//$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = '.$db->quote($regency_id));
		}
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		foreach ($data as &$item) {
			$item->name = trim($item->name);
			$item->date = JHtml::date($item->date, 'U');
		}
		
		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $data;
	}

	public function getMarketTree() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.id', 'city_id'));
		$query->select($db->quoteName('a.name', 'city_name'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));

		$query->select($db->quoteName(array('b.id', 'b.name')));
		$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.$db->quoteName('b.regency_id').' = '.$db->quoteName('a.id'));
		
		$query->order($db->quoteName('a.id'));
		$query->order($db->quoteName('b.name'));

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
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id')));
		$query->select($db->quoteName('a.image', 'img'));
		$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').',")") name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$commodities = $db->loadObjectList();

		foreach ($commodities as &$commodity) {
			$commodity->img = GT_MEDIA_URI . '/img/commodities/'.$commodity->img.'.png';
		}

		return $commodities;
	}
	
	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select($db->quoteName('a.image', 'img'));
		$query->select('CONCAT('.$db->quoteName('a.name').', ":",'.$db->quoteName('a.denomination').') name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item->name;
		}

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $this->prepareCommodities($data);
	}

	protected function prepareCommodities($data) {
		$categories		= $this->getCommodityCategories();
		$data			= JKHelperDocument::setCommodities($categories[0], $categories, $data, 'select');
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
			$commodity->level	+= is_numeric($item->value) ? 1 : 0;
			
			$commodities[]		= $commodity;
		}

		
		return $commodities;
	}

	function getLatestDate($market_id, $exception=NULL)	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		$query->where($db->quoteName('a.published').' = 1');

		if($market_id) {
			$query->where($db->quoteName('a.market_id').' = '.$market_id);
		}
		if($exception) {
			$query->where($db->quoteName('a.date').' < '.$db->quote($exception));
		} else {
			$query->where($db->quoteName('a.date').' <= DATE(NOW())');
		}

		//echo nl2br(str_replace('#__','tpid_',$query));
		$db->setQuery($query);
		$result = $db->loadObject();

		return @$result->date ? JHtml::date($result->date, 'Y-m-d') : null;
	}

	function getCommodityPrices() {
		$date		= $this->jinput->get('date', 0);
		$market_id	= $this->jinput->get('market_id', 0);

		$db = $this->_db;
		$query = $db->getQuery(true);

		$date_now	= $this->getLatestDate($market_id, JHtml::date($date.' +1 day', 'Y-m-d'));
		$date_then	= $this->getLatestDate($market_id, $date_now);

		$dates = array_filter(array($date_now, $date_then));
		$dates = array_map(array($db, 'quote'), $dates);

		$query->select('ROUND(AVG('.$db->quoteName('a.price').'), 0) price');
		$query->select($db->quoteName('a.commodity_id'));
		$query->select($db->quoteName('b.date'));

		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b').' ON '.
			$db->quoteName('a.price_id').' = '.$db->quoteName('b.id')
		);

		$query->where($db->quoteName('b.date').' IN ('.implode(',', $dates).')');
		$query->where($db->quoteName('b.published').' = 1');

		if($market_id) {
			$query->where($db->quoteName('b.market_id').' = '.$market_id);
		}

		$query->group($db->quoteName(array('a.commodity_id', 'b.date')));
		$query->order($db->quoteName(array('a.commodity_id', 'b.date')));
		
		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		$commodities = $this->getCommodities();

		$prices = array();
		foreach($data as $item) {
			$prices[$item->commodity_id]['id']					= $item->commodity_id;
			//$prices[$item->commodity_id]['name']				= $item->commodity_name;
			//$prices[$item->commodity_id]['denom']				= $item->denomination;
			//$prices[$item->commodity_id]['img']					= $item->commodity_img;
			$prices[$item->commodity_id]['prices'][$item->date]	= $item->price;
		}

		foreach ($commodities as $k => &$commodity) {
			$price		= JArrayHelper::toObject($prices[$commodity->id], 'JObject', false);

			$price_now	= round(intval(@$price->prices[$date_now])/50)*50;

			$commodity->price = '-';
			$commodity->diff = '-';
			$commodity->trend = '-';

			if($price_now == 0) {
				continue;
			}

			$price_then	= round(intval(@$price->prices[$date_then])/50)*50;
			$price_then	= $price_then ? $price_then : $price_now;
			$price_diff	= $price_now - $price_then;

			$diff		= abs($price_diff);
			$percent	= round($diff/$price_now * 100, 1);
			
			$commodity->price = $price_now;
			$commodity->diff = $diff;
			
			if($price_diff < 0) {
				$commodity->trend = 'price_down';
			} else if($price_diff > 0) {
				$commodity->trend = 'price_up';
			} else {
				$commodity->trend = 'price_still';
			}
		}
		$data			= new stdClass();
		$data->date		= JHtml::date($date_now, 'j F Y');
		$data->prices	= $commodities;
		
		//echo "<pre>"; print_r($data); echo "</pre>"; die;
		return $data;
	}

	function getRegencyPrices() {
		$date			= $this->jinput->get('date', 0);
		$commodity_id	= $this->jinput->get('commodity_id', 0);

		$db = $this->_db;
		$query = $db->getQuery(true);

		$date_now	= $this->getLatestDate(null, JHtml::date($date.' +1 day', 'Y-m-d'));
		$date_then	= $this->getLatestDate(null, $date_now);

		$dates = array_filter(array($date_now, $date_then));
		$dates = array_map(array($db, 'quote'), $dates);

		$query->select('ROUND(AVG('.$db->quoteName('a.price').'), 0) price');
		$query->select($db->quoteName('b.regency_id'));
		$query->select($db->quoteName('b.date'));

		$query->from($db->quoteName('#__gtpihps_price_details', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihps_prices', 'b').' ON '.
			$db->quoteName('a.price_id').' = '.$db->quoteName('b.id')
		);

		$query->where($db->quoteName('b.date').' IN ('.implode(',', $dates).')');
		$query->where($db->quoteName('b.published').' = 1');

		if($commodity_id) {
			$query->where($db->quoteName('a.commodity_id').' = '.$commodity_id);
		}

		$query->group($db->quoteName(array('b.regency_id', 'b.date')));
		$query->order($db->quoteName(array('b.regency_id', 'b.date')));
		
		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		$regencies = $this->getRegencies();

		$prices = array();
		foreach($data as $item) {
			$prices[$item->regency_id]['id']					= $item->commodity_id;
			$prices[$item->regency_id]['name']					= $item->commodity_name;
			$prices[$item->regency_id]['denom']					= $item->denomination;
			$prices[$item->regency_id]['img']					= $item->commodity_img;
			$prices[$item->regency_id]['prices'][$item->date]	= $item->price;
		}

		foreach ($regencies as $k => &$regency) {
			$price		= JArrayHelper::toObject($prices[$regency->id], 'JObject', false);

			$price_now	= round(intval(@$price->prices[$date_now])/50)*50;

			$regency->price = '-';
			$regency->diff = '-';
			$regency->trend = '-';

			if($price_now == 0) {
				continue;
			}

			$price_then	= round(intval(@$price->prices[$date_then])/50)*50;
			$price_then	= $price_then ? $price_then : $price_now;
			$price_diff	= $price_now - $price_then;

			$diff		= abs($price_diff);
			$percent	= round($diff/$price_now * 100, 1);
			
			$regency->price = $price_now;
			$regency->diff = $diff;
			
			if($price_diff < 0) {
				$regency->trend = 'price_down';
			} else if($price_diff > 0) {
				$regency->trend = 'price_up';
			} else {
				$regency->trend = 'price_still';
			}
		}
		$data			= new stdClass();
		$data->date		= JHtml::date($date_now, 'j F Y');
		$data->prices	= $regencies;
		
		//echo "<pre>"; print_r($data); echo "</pre>"; die;
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
				$date_start	= strtotime(date('Y-m-01',strtotime($date)));
				$date_end	= strtotime(date('Y-m-t',strtotime($date)));
				$count		= JKHelperDate::getWeekdaysCount($date_start, $date_end);
				
				$periods = JKHelperDate::getDayPeriod2($date_end, $count);
				break;
			case 'week':
				$periods = JKHelperDate::getWeekPeriod($date_start, $date_end);
				break;
			case 'month':
				$periods = JKHelperDate::getMonthPeriod($date_start, $date_end);
				break;
			case 'year':
				$periods = JKHelperDate::getYearPeriod($date_start, $date_end);
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

	public function getCommodityPricePeriods() {
		$commodity_id	= $this->jinput->get('commodity_id');
		$regency_id		= $this->jinput->get('regency_id');
		//$regency		= $this->getReference($regency_id, 'city');
		$market			= $this->getReference($regency_id, 'market');
		$market_id		= $this->jinput->get('market_id', '');
		$type			= $this->jinput->get('type', 'day');

		if(!$commodity_id) {
			return null;
		}

		$date		= $this->jinput->get('date');
		$date		= JHtml::date($date, 'Y-m-d');
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
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = '.$db->quote($regency_id));
		} else if($market_id) {
			$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));
		}

		// Dates filter
		switch ($type) {
			default:
				//$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
				//$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));
				$month = JHtml::date($date, 'n');
				$query->where('MONTH('.$db->quoteName('a.date').') = '.$db->quote($month));
				break;
			case 'week':
				$start_date	= JHtml::date($start_date, 'Y') . sprintf('%02d', JHtml::date($start_date, 'W'));
				$end_date	= JHtml::date($end_date, 'Y') . sprintf('%02d', JHtml::date($end_date, 'W'));
				$query->where($db->quoteName('a.date').' >= STR_TO_DATE('.$db->quote($start_date.' Monday').', '.$db->quote('%X%V %W').')');
				$query->where($db->quoteName('a.date').' <= STR_TO_DATE('.$db->quote($end_date.' Monday').', '.$db->quote('%X%V %W').')');
				break;
			case 'month':
				//$query->where($db->quoteName('a.date').' > LAST_DAY(SUBDATE('.$db->quote($start_date).', INTERVAL 1 MONTH))');
				//$query->where($db->quoteName('a.date').' <= LAST_DAY('.$db->quote($end_date).')');
				$year = JHtml::date($date, 'Y');
				$query->where('YEAR('.$db->quoteName('a.date').') = '.$db->quote($year));
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

		//echo nl2br(str_replace('#__','tpid_',$query)); die;

		$price_data = $db->loadObjectList();

		$prices = array();
		foreach ($price_data as $price_item) {
			$price	= round($price_item->price/50)*50;
			$price 	= $price > 0 ? $price : NULL;
			$date	= strtotime($price_item->date);
			switch ($type) {
				case 'week':
					$prices[strtotime('this week', $date)] = JKHelperCurrency::fromNumber($price, '');
					break;
				case 'month':
					$prices[strtotime(JHtml::date($date, 'Y-m-01'))] = JKHelperCurrency::fromNumber($price, '');
					break;
				case 'year':
					$prices[strtotime(JHtml::date($date, 'Y-01-01'))] = JKHelperCurrency::fromNumber($price, '');
					break;
				default:
					$prices[$date] = JKHelperCurrency::fromNumber($price, '');
					break;
			}
		}
		
		if($type == 'month'){
			$year = JHtml::date($date, 'Y');
			
			$periods = array();
			for($i = 1;$i <= 12;$i++){
				$month = ($i < 10 && $i != 0)? '0'.$i : $i;
				$period			= new stdClass();
				$period->date	= $year.'-'.$month.'-01';
				$period->unix	= strtotime($period->date);
				$period->short	= JHtml::date($period->unix, 'M Y');
				$period->long	= JHtml::date($period->unix, 'F Y');
				$periods[] = $period;
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
		if($regency_id) {
			$data->regency_id = $regency_id;
		} else if($market_id) {
			$data->market_id = $market_id;
		}
		$data->price = $price_data;

		return $data;
	}

	public function insertFirebase() {
		$key = $this->jinput->get('key', '', 'RAW');
		$db  = $this->_db;

		$query = $db->getQuery(true);
		
		$query->insert('#__gtpihpssurvey_ref_firebase_keys')->columns($db->quoteName('key'))->values($db->quote($key));
		
		$db->setQuery($query);
		
		$result = new stdClass();
		
		try{
			$result->status = $db->execute();
			$result->msg 	= JText::_('COM_JKCOMMODITY_FIREBASE_REGISTER_SUCCEED');
		}
		catch(Exception $e){
			$result->status	= false;
			$result->msg	= JText::_('COM_JKCOMMODITY_FIREBASE_REGISTER_FAILED');
		}
		
		return $result;
	}

	public function updateFirebase() {
		$key	= $this->jinput->get('key', '', 'RAW');
		$active	= $this->jinput->get('active');
		$db  	= $this->_db;

		$query = $db->getQuery(true);
		
		$query->update('#__gtpihpssurvey_ref_firebase_keys')->set($db->quoteName('active').' = '.$db->quote($active));
		$query->where($db->quoteName('key').' = '.$db->quote($key));
		
		$db->setQuery($query);
		$result = new stdClass();
		
		try{
			$result->status = $db->execute();
			$result->msg 	= JText::_('COM_JKCOMMODITY_FIREBASE_SETTING_SUCCEED');
		}
		catch(Exception $e){
			$result->status	= false;
			$result->msg	= JText::_('COM_JKCOMMODITY_FIREBASE_SETTING_FAILED');
		}
		
		return $result;
	}

	
	public function findUser($username) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.username', 'a.password', 'a.email')));
		$query->from($db->quoteName('#__users', 'a'));

		$query->select($db->quoteName(array('b.group_id')));
		$query->join('RIGHT', $db->quoteName('#__user_usergroup_map', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.user_id'));

		$query->select($db->quoteName('c.regency_id', 'city_id'));
		$query->select($db->quoteName('c.name', 'city_name'));
		$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_group_city', 'c').' ON '.$db->quoteName('c.group_id').' = '.$db->quoteName('b.group_id'));

		$query->select($db->quoteName('d.id', 'market_id'));
		$query->select($db->quoteName('d.name', 'market_name'));
		$query->join('LEFT', $db->quoteName('#__gtpihpssurvey_ref_market', 'd').' ON '.$db->quoteName('c.regency_id').' = '.$db->quoteName('d.regency_id'));
		
		$query->select($db->quoteName('e.userid', 'cometchat_id'));
		$query->select($db->quoteName('e.avatar', 'cometchat_avatar'));
		$query->join('LEFT', $db->quoteName('cometchat_users', 'e').' ON '.$db->quoteName('e.username').' = '.$db->quoteName('a.username'));
		
		$query->where($db->quoteName('a.block').' = '.$db->quote(0));
		$query->where($db->quoteName('a.username').' = '.$db->quote($username));

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$result = null;
		$user	= null;
		$cities = array();
		foreach($rows as $row){
			if(!$result){
				$result = new stdClass();
				$result->id			= $row->id;
				$result->name		= $row->name;
				$result->username	= $row->username;
				$result->password	= $row->password;
				$result->email		= $row->email;
				
				$result->cometchat_id		= $row->cometchat_id? $row->cometchat_id : false;
				$result->cometchat_avatar	= $row->cometchat_avatar? $row->cometchat_avatar : false;

				$result->cities	= array();
				
				if(!$user){
					$array = JKHelperArray::toArray($result);
					$user  = new JUser();
					$user->bind($array);
					
					if($user->authorise('core.admin')){
						$result->cities = array();
							
						$markets = $this->getMarketTree();
						foreach($markets as $market){
							if(!isset($cities[$market->city_id])){
								$cities[$market->city_id] = new stdClass();
								$cities[$market->city_id]->id		= $market->city_id;
								$cities[$market->city_id]->name		= $market->city_name;
								$cities[$market->city_id]->markets	= array();
				
								$result->cities[] = &$cities[$market->city_id];
							}
							
							if($market->id){
								$item = new stdClass();
								$item->id   = $market->id;
								$item->name = $market->name;
					
								$cities[$market->city_id]->markets[] = $item;
							}
						}
						break;
					}
				}
			}
			
			if($row->city_id){
				if(!isset($cities[$row->city_id])){
					$cities[$row->city_id] = new stdClass();
					$cities[$row->city_id]->id		= $row->city_id;
					$cities[$row->city_id]->name	= $row->city_name;
					$cities[$row->city_id]->markets	= array();
				
					$result->cities[] = &$cities[$row->city_id];
				}
				
				if($row->market_id){
					$market = new stdClass();
					$market->id   = $row->market_id;
					$market->name = $row->market_name;
					
					$cities[$row->city_id]->markets[] = $market;
				}
			}
		}
		
		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		return $result;
	}

	function getCometChatUsers()	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.userid', 'a.username', 'a.displayname', 'a.avatar')));
		$query->from($db->quoteName('cometchat_users', 'a'));
		
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}


	public function saveServiceLog($input, $output = null) {
		$post	= $input->post;
		$get	= $input->get;
		$task	= @$post['task'] ? $post['task'] : @$get['task'];

		unset($post['option']);
		unset($get['option']);

		$serviceLog						= new stdClass();
		$serviceLog->id 				= 0;
		$serviceLog->name				= $task;
		$serviceLog->input_get			= count($get) > 0 ? JKHelper::httpQuery($get) : null;
		$serviceLog->input_post			= count($post) > 0 ? JKHelper::httpQuery($post) : null;
		$serviceLog->input_get_json		= count($get) > 0 ? json_encode($get) : null;
		$serviceLog->input_post_json	= count($post) > 0 ? json_encode($post) : null;
		$serviceLog->output				= count($output) > 0 ? json_encode($output) : null;

		return $this->saveExternal($serviceLog, 'service_log');
	}
}
