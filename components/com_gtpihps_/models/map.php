<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelMap extends GTModel
{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}
	
	public function getDefaultCommodityID() {
		$latestDate = $this->getStatLatestDate(null, true);

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		$query->select('COUNT(DISTINCT '.$db->quoteName('a.province_id').') total');
		$query->select($db->quoteName('b.commodity_id'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query->where($db->quoteName('a.date').' = '.$db->quote($latestDate));
		$query->group($db->quoteName('b.commodity_id'));

		// Create a new query object.
		$query2	= $db->getQuery(true);

		$query2->select($db->quoteName('a.commodity_id'));
		$query2->from('('.$query.') a');
		$query2->order($db->quoteName('a.total').' desc');
		$query2->order($db->quoteName('a.commodity_id').' asc');

		$db->setQuery($query2);
		return intval(@$db->loadObject()->commodity_id);
	}

	public function getFluctuation($single_date = false) {
		$commodity_id	= $this->input->get('commodity_id', 1);
		$periodType		= $this->input->get('period_type', 'wtw');
		$dataType		= $this->input->get('data_type', 'fluctuation');
		$commodity_id	= $this->input->get('commodity_id', 1);
		$price_type_id	= $this->input->get('price_type_id', 1);
		

		$date			= $this->input->get('date', 'now');
		$date			= JHtml::date($date, 'Y-m-d');
		$date 			= $this->getStatLatestDate($date, 1);
		$today 			= $this->getStatLatestDate(JHtml::date('now', 'Y-m-d'), 1);
		$isBeforeLimit	= JHtml::date('now', 'G') <= 12 && $date == $today;

		$row = array(
			'dtd' => 2,
			'wtw' => 5,
			'mtm' => 20
		);
		$row = $row[$periodType];

		$dates = array();
		if($isBeforeLimit) {
			$dates[]	= $date;
			$dates[]	= $this->getStatLatestDate($date, $row);

			$date2		= $this->getStatLatestDate($date, 2);
			$dates[]	= $date2;
			$dates[]	= $this->getStatLatestDate($date2, $row);
		} else {
			$dates[]	= $date;
			$dates[]	= $this->getStatLatestDate($date, $row);
		}
		
		$stddevs		= $this->getComStdDev($commodity_id, $date);

		$data = array();		
		
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select fields from main table
		$query->select($db->quoteName(array('a.province_id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('ROUND(AVG('.$db->quoteName('b.price').')/50, 0)*50 price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . 
			$db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id')
		);
		
		$query->where($db->quoteName('a.date').' IN ('.implode(',', array_map(array($db, 'quote'), $dates)).' )');

		if(is_numeric($commodity_id)) {
			$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . 
				$db->quoteName('b.commodity_id') . ' = ' . $db->quoteName('c.id')
			);
			$category_id = end(explode('-', $commodity_id));
			$query->where($db->quoteName('c.category_id') . ' = ' . $db->quote(intval($category_id)));
		}

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'd') . ' ON ' . 
			$db->quoteName('a.market_id') . ' = ' . $db->quoteName('d.id')
		);
		$query->where($db->quoteName('d.price_type_id') . ' = '.$db->quote($price_type_id));

		$query->group($db->quoteName(array('a.province_id', 'a.date')));
		$query->order($db->quoteName('a.province_id') . ' asc');
		$query->order($db->quoteName('a.date') . ' desc');
		
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$query2	= $db->getQuery(true);
		$query2->select('a.*');
		$query2->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));

		$query2->select('GROUP_CONCAT('.$db->quoteName('b.price').') prices');
		$query2->select('GROUP_CONCAT('.$db->quoteName('b.date').') dates');
		$query2->join('LEFT', '('.$query.') b ON ' . 
			$db->quoteName('a.id') . ' = ' . $db->quoteName('b.province_id')
		);
		$query2->group($db->quoteName('a.id'));
		
		$db->setQuery($query2);
		$items	= $db->loadObjectList('id');
	
		foreach ($items as &$item) {
			$stddev 	= @$stddevs[$item->id];
			$stddev		= round(@$stddev->stddev, 2);
			$provDates	= explode(',', $item->dates);
			$prices		= explode(',', $item->prices);
			$prices		= array_combine($provDates, $prices);

			$province = new stdClass();

			$startDate	= !in_array($dates[0], $provDates) && $isBeforeLimit ? $dates[3] : $dates[1];
			$endDate	= !in_array($dates[0], $provDates) && $isBeforeLimit ? $dates[2] : $dates[0];

			$startPrice	= @$prices[$startDate];
			$endPrice	= @$prices[$endDate];

			$province->id			= $item->id;
			$province->name			= $item->name;
			$province->date			= $endDate ? $endDate : $date;
			$province->dateinfo		= JHtml::date($endDate, 'd M').' / '. JHtml::date($startDate, 'd M');
			$province->value		= null;
			$province->displaylong	= null;
			$province->display		= null;
			$province->price_prev	= null;
			$province->price_cur	= null;
			$province->date_prev	= JHtml::date($startDate, 'd M Y');
			$province->date_cur		= JHtml::date($endDate, 'd M Y');
			$province->stddev		= $stddev;

			if($startPrice > 0 && $endPrice > 0 && $stddev > 0) {
				$priceDiff		= $endPrice - $startPrice;
				$stddevPrice	= round((($stddev * $startPrice)/100)/50)*50;
				
				$province->value		= round($priceDiff / $startPrice * 100, 2);
				$province->displaylong	= GTHelperCurrency::fromNumber($endPrice).'<br/>('.GTHelperCurrency::fromNumber($priceDiff).' / '.$province->value.'%)<br/> StdDev: '.GTHelperCurrency::fromNumber($stddevPrice).' / '.$stddev.'%';
				$province->display		= $province->value.'%';
				$province->price_prev	= GTHelperCurrency::fromNumber($startPrice);
				$province->price_cur	= GTHelperCurrency::fromNumber($endPrice);
				
				if($province->value < ($stddev * -1)) {
					$province->rank = 1;
				} else if($province->value < 0) {
					$province->rank = 2;
				} else if($province->value <= $stddev) {
					$province->rank = 3;
				} else if($province->value < ($stddev * 2)) {
					$province->rank = 4;
				} else {
					$province->rank = 5;
				}
			} elseif(count($provDates) > 0 && $stddev > 0) {
				$province->rank = 6;
			} else {
				$province->rank = 0;
			}

			$ranks = array();
			$ranks[] = array(
				'rank' => '> -'.($stddev*2).'%',
				'info' => 1
			);
			$ranks[] = array(
				'rank' => '> -'.$stddev.'%',
				'info' => 2
			);
			$ranks[] = array(
				'rank' => '< '.$stddev.'%',
				'info' => 3
			);
			$ranks[] = array(
				'rank' => '< '.($stddev*2).'%',
				'info' => 4
			);
			$ranks[] = array(
				'rank' => '> '.($stddev*2).'%',
				'info' => 5
			);

			$province->ranks_info = $ranks;

			$item = $province;
		}

		//echo "<pre>"; print_r($items); echo "</pre>"; die;
		return $items;
	}

	protected function getProvStdDev($province_id, $start_date, $end_date, $commodity_id = null) {
		$date = $end_date ? $end_date : $start_date;

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select fields from main table
		$query->select('STDDEV('.$db->quoteName('a.fluctuation').') stddev');
		$query->from($db->quoteName('#__gtpihps_fluc_details', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihps_flucs', 'b') . ' ON ' . 
			$db->quoteName('a.fluc_id') . ' = ' . $db->quoteName('b.id')
		);

		$query->select($db->quoteName('c.id', 'commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . 
			$db->quoteName('a.category_id') . ' = ' . $db->quoteName('c.category_id')
		);

		$query->where($db->quoteName('b.date') . ' BETWEEN SUBDATE(' . $db->quote($date).', INTERVAL 1 YEAR) AND '.$db->quote($date));
		$query->where($db->quoteName('b.province_id') . ' = ' . $db->quote($province_id));
		if($commodity_id) {
			$query->where($db->quoteName('c.id') . ' = ' . $db->quote($commodity_id));
		}

		$query->group($db->quoteName('c.id'));

		$db->setQuery($query);
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$data = $commodity_id ? $db->loadObject() : $db->loadObjectList('commodity_id');

		return $data;
	}

	protected function getComStdDev($commodity_id, $start_date, $end_date = null) {
		$date = $end_date ? $end_date : $start_date;

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select fields from main table
		$query->select('STDDEV('.$db->quoteName('a.fluctuation').') stddev');
		$query->from($db->quoteName('#__gtpihps_fluc_details', 'a'));

		$query->select($db->quoteName('b.province_id'));
		$query->join('INNER', $db->quoteName('#__gtpihps_flucs', 'b') . ' ON ' . 
			$db->quoteName('a.fluc_id') . ' = ' . $db->quoteName('b.id')
		);

		if(is_numeric($commodity_id)) {
			$query->select($db->quoteName('c.id', 'commodity_id'));
			$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . ' ON ' . 
				$db->quoteName('a.category_id') . ' = ' . $db->quoteName('c.category_id')
			);
			$query->where($db->quoteName('c.id') . ' = ' . $db->quote($commodity_id));
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$category_id = end(explode('-', $commodity_id));
			$query->where($db->quoteName('a.category_id') . ' = ' . $db->quote($category_id));
		}
		
		$query->where($db->quoteName('b.date') . ' BETWEEN SUBDATE(' . $db->quote($date).', INTERVAL 1 YEAR) AND '.$db->quote($date));

		$query->group($db->quoteName('b.province_id'));

		$db->setQuery($query);
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		return $db->loadObjectList('province_id');
	}

	public function getFluctuationByProvince() {
		$province_id	= $this->input->get('province_id', 1);
		$end_date		= $this->input->get('date', 'now');
		$end_date		= JHtml::date($end_date, 'Y-m-d');
		$end_date 		= JHtml::date('now', 'G') <= 12 && $date2 == JHtml::date('now', 'Y-m-d') ? $this->getStatLatestDate($end_date, 1) : $end_date;
		$start_date		= $this->getStatLatestDate($end_date, 2);
		$stddevs		= $this->getProvStdDev($province_id, $start_date, $end_date);

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select fields from main table
		$query->select($db->quoteName(array('b.commodity_id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->select('AVG('.$db->quoteName('b.price').') price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . 
			$db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id')
		);
		
		$query->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));

		$query->group($db->quoteName('a.date'));
		$query->group($db->quoteName('b.commodity_id'));
		$query->order($db->quoteName('a.date') . ' asc');
		$query->order($db->quoteName('b.commodity_id') . ' asc');

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$data = $db->loadObjectList();

		$commodities = $this->getCommodities();
		$flucs = array();
		foreach ($data as $item) {
			$flucs[$item->commodity_id][$item->date] = $item;
		}

		foreach ($flucs as $commodity_id => &$fluc) {
			$commodity = @$commodities[$commodity_id];
			if(!$commodity) continue;

			ksort($fluc);
			$start	= reset($fluc);
			$end	= end($fluc);

			$fluc 				= new stdClass();
			$fluc->id			= $commodity_id;
			$fluc->name			= $commodity->name.' ('.$commodity->denomination.')';
			$fluc->date			= $end->date;
			$fluc->value		= round(($end->price - $start->price) / $start->price * 100, 2);
			$fluc->displaylong	= GTHelperCurrency::fromNumber($end->price).'<br/>('.$fluc->value.'%)';
			$fluc->display		= $fluc->value.'%';
			$fluc->price_prev	= GTHelperCurrency::fromNumber(round($start->price/50)*50);
			$fluc->price_cur	= GTHelperCurrency::fromNumber(round($end->price/50)*50);
			$fluc->date_prev	= JHtml::date($start->date, 'd M Y');
			$fluc->date_cur		= JHtml::date($end->date, 'd M Y');
			
			$stddev	= @$stddevs[$commodity_id];
			$stddev	= floatval(round($stddev->stddev, 2));
			$fluc->stddev		= $stddev;

			/*
			if(!$stddev <> 0 | !$fluc->value <> 0) {
				unset($flucs[$commodity_id]);
				continue;
			}
			*/

			/*if($fluc->value < ($stddev * -1)) {
				$fluc->rank = 1;
			} else if($fluc->value < 0) {
				$fluc->rank = 2;
			} else if($fluc->value <= $stddev) {
				$fluc->rank = 3;
			} else if($fluc->value < ($stddev * 2)) {
				$fluc->rank = 4;
			} else {
				$fluc->rank = 5;
			}
			*/

			if($fluc->value < ($stddev * -2)) {
				$fluc->rank = 1;
			} else if($fluc->value < ($stddev * -1)) {
				$fluc->rank = 2;
			} else if($fluc->value <= $stddev) {
				$fluc->rank = 3;
			} else if($fluc->value < ($stddev * 2)) {
				$fluc->rank = 4;
			} else {
				$fluc->rank = 5;
			}

			$ranks = array();
			$ranks[] = array(
				'rank' => '> -'.($stddev*2).'%',
				'info' => 1
			);
			$ranks[] = array(
				'rank' => '> -'.$stddev.'%',
				'info' => 2
			);
			$ranks[] = array(
				'rank' => '< '.$stddev.'%',
				'info' => 3
			);
			$ranks[] = array(
				'rank' => '< '.($stddev*2).'%',
				'info' => 4
			);
			$ranks[] = array(
				'rank' => '> '.($stddev*2).'%',
				'info' => 5
			);

			$fluc->ranks_info = $ranks;
		}

		return $flucs;
	}

	public function getPrice() {
		$price_type_id	= $this->input->get('price_type_id', 1);
		$commodity_id	= $this->input->get('commodity_id', 1);
		

		if($price_type_id == 1) {
			$end_date		= $this->input->get('date', 'now');
			$end_date		= JHtml::date($end_date, 'Y-m-d');
			$end_date 		= JHtml::date('now', 'G') <= 12 && $end_date == JHtml::date('now', 'Y-m-d') ? $this->getStatLatestDate($end_date, 1) : $end_date;
			$start_date		= $this->getStatLatestDate($end_date, 2);
		} else {
			$end_date		= $this->input->get('date', 'now');
			$end_date		= JHtml::date($end_date, 'Y-m-d');
			$start_date		= JHtml::date($end_date.' -2 week', 'Y-m-d');
		}

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query2	= $db->getQuery(true);

		// Select fields from main table
		$query2->select($db->quoteName(array('a.regency_id')));
		$query2->select('MAX('.$db->quoteName('a.date').') date');
		$query2->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query2->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . ' ON ' . 
			$db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id')
		);
		$query2->join('LEFT', $db->quoteName('#__gtpihps_holidays', 'c') . ' ON ' . 
			$db->quoteName('a.date') . ' BETWEEN ' . $db->quoteName('c.start').' AND '.$db->quoteName('c.end')
		);

		$query2->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query2->where('WEEKDAY('.$db->quoteName('a.date') . ') NOT IN (5,6)');
		$query2->where($db->quoteName('c.id') . ' IS NULL');
		$query2->where($db->quoteName('b.price') . ' > 50');
		$query2->group($db->quoteName('a.regency_id'));

		$query2->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'e') . ' ON ' . 
			$db->quoteName('a.market_id') . ' = ' . $db->quoteName('e.id')
		);
		$query2->where($db->quoteName('e.price_type_id') . ' = '.$db->quote($price_type_id));

		// Select fields from main table
		$query->select($db->quoteName(array('a.regency_id', 'b.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->join('INNER', '('.$query2.') b ON ('.
			$db->quoteName('a.regency_id') . ' = ' . $db->quoteName('b.regency_id').' AND '.
			$db->quoteName('a.date') . ' = ' . $db->quoteName('b.date')
		.')');

		$query->select('AVG('.$db->quoteName('c.price').') price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'c') . ' ON ' . 
			$db->quoteName('a.id') . ' = ' . $db->quoteName('c.price_id')
		);
		
		$query->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));

		if(is_numeric($commodity_id)) {
			$query->where($db->quoteName('c.commodity_id') . ' = ' . $db->quote($commodity_id));
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'd') . ' ON ' . 
				$db->quoteName('c.commodity_id') . ' = ' . $db->quoteName('d.id')
			);
			$category_id = end(explode('-', $commodity_id));
			$query->where($db->quoteName('d.category_id') . ' = ' . $db->quote(intval($category_id)));
		}
		
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'e') . ' ON ' . 
			$db->quoteName('a.market_id') . ' = ' . $db->quoteName('e.id')
		);
		$query->where($db->quoteName('e.price_type_id') . ' = '.$db->quote($price_type_id));

		$query->group($db->quoteName('a.regency_id'));
		$query->order($db->quoteName('a.regency_id') . ' asc');

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$db->setQuery($query);
		$data = $db->loadObjectList('regency_id');

		$prices = array();
		foreach ($data as &$item) {
			$item->value		= round($item->price/50)*50;
			$item->display		= GTHelperCurrency::fromNumber($item->value);
			$item->dateinfo 	= '<small>'.JText::_('COM_GTPIHPS_UPDATE').' : '.JHtml::date($item->date, 'd M y').'</small>';
			$prices[]			= $item->value;
		}

		$priceAvg = count($prices) > 0 ? round((array_sum($prices) / count($prices))/50)*50 : 0;
		$priceStdDev = round($this->stdDev($prices)/50)*50;

		foreach ($data as &$item) {
			$item->displaylong	= GTHelperCurrency::fromNumber($item->value).'<br/>Avg: '.GTHelperCurrency::fromNumber($priceAvg).' / StdDev: '.GTHelperCurrency::fromNumber($priceStdDev);
		}

		foreach ($data as &$item) {
			$item->rank = $item->value == min($prices) ? 1 : ceil((($item->value - min($prices)) / (max($prices) - min($prices))) * 5);
		}

		return $data;
	}

	protected function stdDev($aValues, $bSample = false) {
		$fMean = count($aValues) > 0 ? array_sum($aValues) / count($aValues) : 0;
		$fVariance = 0.0;
		foreach ($aValues as $i) {
			$fVariance += pow($i - $fMean, 2);
		}
		$fVarianceDiv = ( $bSample ? count($aValues) - 1 : count($aValues) );
		$fVariance /= $fVarianceDiv > 0 ? $fVarianceDiv : 1;
		return (float) sqrt($fVariance);
	}

	public function getCommodity() {
		$commodity_id	= $this->input->get('commodity_id', 1);
		
		if(is_numeric($commodity_id)) {
			$table = $this->getTable('Commodity');
			$table->load($commodity_id);
		} elseif(strpos($commodity_id, 'cat') == 0) {
			$category_id = end(explode('-', $commodity_id));
			$table = $this->getTable('Category');
			$table->load(intval($category_id));
		}
		
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getCommodities() {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.source_id', 'a.name', 'a.denomination')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		$query->order($db->quoteName('a.id') . ' asc');
		$query->where($db->quoteName('a.published') . ' = 1');
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$data = $db->loadObjectList('id');

		foreach ($data as &$item) {
			$item->name = trim($item->name);
		}

		return $data;
	}

	public function getRegency() {
		$regency_id	= $this->input->get('regency_id', 1);
		
		$table = $this->getTable('Province');
		$table->load($regency_id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getRegencies() {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.type', 'a.short_name', 'a.iso_code')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		$query->order($db->quoteName('a.id') . ' asc');
		//$query->where($db->quoteName('a.published') . ' = 1');
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		$data = $db->loadObjectList('id');

		foreach ($data as &$item) {
			$item->name = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($item->type)), trim($item->name));
		}

		return $data;
	}

	public function getAllPrices() {
		$commodity_id	= $this->input->get('commodity_id');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		$query->select($db->quoteName(array('a.date')));
		$query->select('ROUND(AVG('.$db->quoteName('b.price').')/50, 0)*50 price');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query->where($db->quoteName('b.price'). ' > 50');
		$query->where($db->quoteName('a.published').' = 1');
		
		$query->group($db->quoteName('a.date'));

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		
		$items = $db->loadObjectList('date');
		foreach ($items as &$item) {
			$item = $item->price;
		}


		return $items;
	}

	public function getProvincePrices() {
		$commodity_id	= $this->input->get('commodity_id');
		$province_id	= $this->input->get('province_id');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query2	= $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->select('GROUP_CONCAT('.$db->quoteName('b.date').') dates');
		$query->select('GROUP_CONCAT('.$db->quoteName('b.price').') prices');

		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));

		$query2->select($db->quoteName(array('a.date', 'a.province_id')));
		$query2->select('ROUND(AVG('.$db->quoteName('b.price').')/50, 0)*50 price');
		$query2->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query2->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query2->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'c').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.id')
		);

		$query2->where($db->quoteName('a.published').' = 1');
		$query2->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query2->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query2->where($db->quoteName('c.price_type_id') . ' = ' . $db->quote($price_type_id));
		$query2->where($db->quoteName('b.price'). ' > 50');
		
		$query2->group($db->quoteName('a.province_id'));
		$query2->group($db->quoteName('a.date'));

		//echo nl2br(str_replace('#__','pihps_',$query2)); die;

		$query->join('LEFT', '('.$query2.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.province_id')
		);

		$query->where($db->quoteName('a.published') . ' = 1');

		if($province_id > 0) {
			$query->where($db->quoteName('a.id') . ' = ' . $db->quote($province_id));
		}

		$query->group($db->quoteName('a.id'));
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		
		$items = $db->loadObjectList('id');

		foreach ($items as &$item) {
			//echo "<pre>"; print_r($item); echo "</pre>"; die;
			$dates = explode(',', $item->dates);
			$prices = explode(',', $item->prices);
			

			$price_data = array();
			foreach ($dates as $k => $date) {
				$price_data[$date] = $prices[$k];
			}

			$item->prices = $price_data;

			unset($item->type);
			unset($item->dates);
		}
		
		return $items;
	}

	public function getRegencyPrices() {
		$commodity_id	= $this->input->get('commodity_id');
		$province_id	= $this->input->get('province_id');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');
		$price_type_id	= $this->input->get('price_type_id', '1');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query2	= $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.province_id', 'a.type')));
		$query->select('GROUP_CONCAT('.$db->quoteName('b.date').') dates');
		$query->select('GROUP_CONCAT('.$db->quoteName('b.price').') prices');

		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));

		$query2->select($db->quoteName(array('a.date', 'a.regency_id')));
		$query2->select('ROUND(AVG('.$db->quoteName('b.price').')/50, 0)*50 price');
		$query2->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query2->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query2->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'c').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.id')
		);

		$query2->where($db->quoteName('a.published').' = 1');
		$query2->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query2->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query2->where($db->quoteName('c.price_type_id') . ' = ' . $db->quote($price_type_id));
		$query2->where($db->quoteName('b.price'). ' > 50');
		
		$query2->group($db->quoteName('a.regency_id'));
		$query2->group($db->quoteName('a.date'));

		//echo nl2br(str_replace('#__','pihps_',$query2));

		$query->join('LEFT', '('.$query2.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.regency_id')
		);

		$query->where($db->quoteName('a.published') . ' = 1');

		if($province_id > 0) {
			$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		}

		$query->order($db->quoteName('a.province_capital') . ' desc');
		$query->order($db->quoteName('a.type') . ' asc');
		$query->order($db->quoteName('a.name') . ' asc');

		$query->group($db->quoteName('a.id'));
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		
		$items = $db->loadObjectList();

		$data = array();
		foreach ($items as $item) {
			$province_id = $item->province_id;
			$dates = explode(',', $item->dates);
			$prices = explode(',', $item->prices);

			$price_data = array();
			foreach ($dates as $k => $date) {
				$price_data[$date] = $prices[$k];
			}

			$item->prices = array_filter($price_data);
			$item->name = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($item->type).'_S'), $item->name);

			unset($item->type);
			unset($item->dates);
			unset($item->province_id);

			$data[$province_id][] = $item;
		}
		return $data;
	}

	public function getMarketPrices() {
		$commodity_id	= $this->input->get('commodity_id');
		$province_id	= $this->input->get('province_id');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');
		$price_type_id	= $this->input->get('price_type_id', '1');

		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);
		$query2	= $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name', 'a.price_type_id')));
		$query->select('GROUP_CONCAT('.$db->quoteName('b.date').') dates');
		$query->select('GROUP_CONCAT('.$db->quoteName('b.price').') prices');

		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		$query2->select($db->quoteName(array('a.date', 'a.regency_id', 'a.market_id')));
		$query2->select('ROUND(AVG('.$db->quoteName('b.price').')/50, 0)*50 price');
		$query2->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query2->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query2->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'c').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.id')
		);

		$query2->where($db->quoteName('a.published').' = 1');
		$query2->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote($start_date).' AND '.$db->quote($end_date));
		$query2->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query2->where($db->quoteName('c.price_type_id') . ' = ' . $db->quote($price_type_id));
		$query2->where($db->quoteName('b.price'). ' > 50');
		
		$query2->group($db->quoteName('a.market_id'));
		$query2->group($db->quoteName('a.date'));

		//echo nl2br(str_replace('#__','pihps_',$query2)); die;

		$query->join('LEFT', '('.$query2.') b ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.market_id')
		);

		$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.price_type_id') . ' = ' . $db->quote($price_type_id));

		$query->order($db->quoteName('a.price_type_id') . ' asc');
		$query->order($db->quoteName('a.name') . ' asc');

		$query->group($db->quoteName('a.id'));

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		
		$items = $db->loadObjectList();
		$data = array();

		$priceTypes = $this->getPriceTypes();

		$counts = array();
		foreach ($items as $item) {
			$regency_id = $item->regency_id;
			$dates = explode(',', $item->dates);
			$prices = explode(',', $item->prices);

			$price_data = array();
			foreach ($dates as $k => $date) {
				$price_data[$date] = $prices[$k];
			}

			$item->prices = array_filter($price_data);

			unset($item->type);
			unset($item->dates);
			unset($item->regency_id);

			$hideName		= $this->user->guest && $item->price_type_id != 1;
			$priceType		= $priceTypes[$item->price_type_id];
			$priceTypeCount	= intval(@$counts[$regency_id][$item->price_type_id])+1;
			$item->name		= $hideName ? $priceType->name.' #'.$priceTypeCount : trim($item->name);

			$counts[$regency_id][$item->price_type_id] = $priceTypeCount;
			$data[$regency_id][] = $item;
		}

		return $data;
	}

	public function getPriceTypes() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	protected function getStatLatestDate($date = null, $row = 1) {
		$province_id	= $this->input->get('province_id');
		$regency_id		= $this->input->get('regency_id');
		$market_id		= $this->input->get('market_id');
		$row 			= intval($row);
		$date 			= $date ? $date : 'now';
		$date 			= JHtml::date($date, 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($province_id) {
			$query->where($db->quoteName('a.province_id') . ' = ' . $db->quote($province_id));
		}
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = ' . $db->quote($regency_id));
		}
		if($market_id) {
			$query->where($db->quoteName('a.market_id') . ' = ' . $db->quote($market_id));
		}

		if($row > 1) {
			$query->where($db->quoteName('a.date') . ' <= ' . $db->quote($date));
		} else {
			$query->where($db->quoteName('a.date') . ' BETWEEN ' . $db->quote(JHtml::date($date.' -5 day', 'Y-m-d')).' AND '.$db->quote($date));
		}

		$query->group($db->quoteName('a.date'));
		$query->order($db->quoteName('a.date').' desc');
		$query->setLimit(1, $row-1);

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadResult();
	}
}
