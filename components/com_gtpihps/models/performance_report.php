<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelPerformance_Report extends GTModelList{
	
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

		$this->setState('list.start', 0);
		$this->setState('list.limit', 0);
		
		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		if ($layout) {
			$this->context.= '.' . $layout;
		}

		$start_date			= $this->getUserStateFromRequest($this->context . '.filter.start_date', 'filter_start_date', JHtml::date('now - 1 month', 'Y-m-d'));
		$end_date			= $this->getUserStateFromRequest($this->context . '.filter.end_date', 'filter_end_date', JHtml::date('now', 'Y-m-d'));
		
		$dates_unix			= array(strtotime($start_date), strtotime($end_date));
		$sdate				= min($dates_unix);
		$edate				= max($dates_unix);
		
		$this->setState('filter.start_date', JHtml::date($sdate, 'd-m-Y'));
		$this->setState('filter.end_date', JHtml::date($edate, 'd-m-Y'));

		//$defProvinces 		= array_rand(array_keys($this->getProvinces()), 3);
		$price_type_id		= $this->getUserStateFromRequest($this->context . '.filter.price_type_id', 'filter_price_type_id', '1');
		$this->setState('filter.price_type_id', $price_type_id);


		$province_ids		= $this->getUserStateFromRequest($this->context . '.filter.province_ids', 'filter_province_ids', array(), 'array');
		$this->setState('filter.province_ids', array_filter($province_ids));

		$report_type		= $this->getUserStateFromRequest($this->context . '.filter.report_type', 'filter_report_type', '0');
		$this->setState('filter.report_type', $report_type);
	}

	public function getDayCount() {
		$start_date		= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date		= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select prices
		$query->select('COUNT(DISTINCT '.$db->quoteName('a.date').') total');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join holiday
		$query->join('LEFT', $db->quoteName('#__gtpihps_holidays', 'b').' ON '.$db->quoteName('a.date').' BETWEEN '.$db->quoteName('b.start').' AND '.$db->quoteName('b.end'));
		$query->where($db->quoteName('b.id') . ' IS NULL');
		$query->where('DAYOFWEEK('.$db->quoteName('a.date').') NOT IN (1,7)');
		$query->where($db->quoteName('a.date').' BETWEEN '.$db->quote($start_date).' AND '.$db->quote($end_date));

		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return @$db->loadObject()->total;
	}
	
	protected function getCounts($report_type = null) {
		$province_ids	= (array) $this->getState('filter.province_ids');
		$start_date		= JHtml::date($this->getState('filter.start_date'), 'Y-m-d');
		$end_date		= JHtml::date($this->getState('filter.end_date'), 'Y-m-d');

		// Get a db connection.
		$db = $this->_db;
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		// Select prices
		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.regency_id', 'a.market_id')));
		$query->select('COUNT('.$db->quoteName('a.id').') total');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join regency
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'c') . ' ON ' . $db->quoteName('a.regency_id') . ' = ' . $db->quoteName('c.id'));

		// Join holiday
		$query->join('LEFT', $db->quoteName('#__gtpihps_holidays', 'd').' ON '.$db->quoteName('a.date').' BETWEEN '.$db->quoteName('d.start').' AND '.$db->quoteName('d.end'));

		// Dates filter
		$query->where($db->quoteName('a.date').' >= '.$db->quote($start_date));
		$query->where($db->quoteName('a.date').' <= '.$db->quote($end_date));
		
		// Publish filter
		$query->where($db->quoteName('a.published') . ' = 1');

		$query->where($db->quoteName('d.id') . ' IS NULL');
		$query->where('DAYOFWEEK('.$db->quoteName('a.date').') NOT IN (1,7)');

		$time_diff = JHtml::date('now', 'Z');
		$whereOnTime	= '('.$db->quoteName('a.date').' = DATE(DATE_ADD('.$db->quoteName('a.created').', INTERVAL '.$time_diff.' SECOND)) AND HOUR(DATE_ADD('.$db->quoteName('a.created').', INTERVAL '.$time_diff.' SECOND)) <= '.$db->quote('13').')';
		$whereOnTime2	= '('.$db->quoteName('a.date').' = DATE(DATE_ADD('.$db->quoteName('a.validated').', INTERVAL '.$time_diff.' SECOND)) AND HOUR(DATE_ADD('.$db->quoteName('a.validated').', INTERVAL '.$time_diff.' SECOND)) <= '.$db->quote('13').')';
		//$whereOnTime	= 'IF('.$db->quoteName('a.validated').' > 0, '.$whereOnTime2.', '.$whereOnTime.')';
		switch ($report_type) {
			case '1':
				$query->where($whereOnTime);
				break;
			case '2':
				$query->where('!'.$whereOnTime);
				break;
		}

		if(count($province_ids) > 0) {
			$province_ids = array_map(array($db, 'quote'), $province_ids);
			$query->where($db->quoteName('c.province_id') . ' IN ('.implode(',', $province_ids).')');
		}
		
		$query->group($db->quoteName('a.market_id'));
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$db->setQuery($query);
		$data = $db->loadObjectList('market_id');

		$items = array();
		foreach ($data as $market_id => $item) {
			$items[$item->province_id][$item->regency_id][$market_id] = $item->total;
		}
		return $items;
	}

	public function getItems($table = false) {
		$dayCount		= $this->getDayCount();
		$refProvinces	= $this->getProvinces();
		$refRegencies	= $this->getRegencies();
		$refMarkets		= $this->getMarkets();

		$percentages		= array();
		$counts				= $this->getCounts();
		$countsOnTime		= $this->getCounts(1);
		
		foreach ($refProvinces as $province_id => $province) {
			$countProvince = 0;
			$countDayProvince = 0;
			$sumProvince = 0;
			$sumProvinceOT = 0;
			$provRegencies = @$refRegencies[$province_id];
			if(!is_array($provRegencies)) {
				unset($refProvinces[$province_id]);
				continue;
			}
			
			//echo "<pre>"; print_r($provRegencies); echo "</pre>";
			foreach ($provRegencies as $regency_id => $regency) {
				$countRegency = 0;
				$countDayRegency = 0;
				$sumRegency = 0;
				$sumRegencyOT = 0;
				$regMarkets = @$refMarkets[$regency_id];
				if(!is_array($regMarkets)) {
					unset($provRegencies[$regency_id]);
					continue;
				}

				foreach ($regMarkets as $market_id => $market) {
					$marketCount	= intval(@$counts[$province_id][$regency_id][$market_id]);
					$marketCountOT	= intval(@$countsOnTime[$province_id][$regency_id][$market_id]);

					if(!$marketCount > 0) {
						unset($regMarkets[$market_id]);
						continue;
					}

					$total			= $marketCount;

					$ontime	= 0;
					$late	= 0;

					if($marketCountOT > 0) {
						$ontime	= round(($marketCountOT/$marketCount) * 100);

						$sumProvinceOT += $marketCountOT;
						$sumRegencyOT += $marketCountOT;
					}
					
					$countProvince++;
					$countRegency++;
					$countDayProvince = $marketCount > $countDayProvince ? $marketCount : $countDayProvince;
					$countDayRegency = $marketCount > $countDayRegency ? $marketCount : $countDayRegency;
					$sumProvince += $marketCount;
					$sumRegency += $marketCount;

					$mItem			= new stdClass();
					$mItem->name	= $market;
					$mItem->ontime	= $ontime;
					$mItem->late	= 100 - $ontime;
					$mItem->desc	= sprintf('%s/%s (%s%%)', $marketCount, $dayCount, round(($marketCount/$dayCount)*100));

					$regMarkets[$market_id] = $mItem;
				}

				if(!$countRegency > 0) {
					unset($provRegencies[$regency_id]);
					continue;
				}

				$rItem				= new stdClass();
				$rItem->name		= $regency;
				$rItem->ontime		= $sumRegencyOT > 0 ? round(($sumRegencyOT/$sumRegency) * 100) : 0;
				$rItem->late		= 100 - $rItem->ontime;
				$rItem->desc		= sprintf('%s/%s (%s%%)', $countDayRegency, $dayCount, round(($countDayRegency/$dayCount)*100));
				$rItem->count		= $countRegency;
				$rItem->children	= $regMarkets;

				$provRegencies[$regency_id] = $rItem;
			}

			if(!$countProvince > 0) {
				unset($refProvinces[$province_id]);
				continue;
			}
			
			$pItem				= new stdClass();
			$pItem->name		= $province;
			$pItem->ontime		= $sumProvinceOT ? round(($sumProvinceOT/$sumProvince) * 100) : 0;
			$pItem->late		= 100 - $pItem->ontime;
			$pItem->desc		= sprintf('%s/%s (%s%%)', $countDayProvince, $dayCount, round(($countDayProvince/$dayCount)*100));
			$pItem->count		= $countProvince;
			$pItem->children	= $provRegencies;

			$refProvinces[$province_id] = $pItem;
		}
		return $refProvinces;
	}

	public function getSelectedProvinces() {
		return $this->getProvinces(false);
	}

	public function getProvinces($all = true) {
		$province_ids = (array) $this->getState('filter.province_ids');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));

		$query->where($db->quoteName('a.published') . ' = 1');

		if(!$all && count($province_ids) > 0) {
			$province_ids = array_map(array($db, 'quote'), $province_ids);
			$query->where($db->quoteName('a.id') . ' IN ('.implode(',', $province_ids).')');
		}

		$query->order($db->quoteName('a.id'));
		$query->group($db->quoteName('a.id'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->id] = trim($item->name);
		}
		return $data;
	}

	public function getRegencies() {
		// Get a db connection.
		$db = $this->_db;

		$province_ids = (array) $this->getState('filter.province_ids');

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.type', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));

		// Join Market
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('b.regency_id'));
		
		$query->group($db->quoteName('a.id'));
		$query->where($db->quoteName('b.published') . ' = 1');
		if(count($province_ids) > 0) {
			$province_ids = array_map(array($db, 'quote'), $province_ids);
			$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		}

		$query->order($db->quoteName('a.province_capital').' desc');
		$query->order($db->quoteName('a.type'));

		$db->setQuery($query);
		
		$result = array();
		foreach ($db->loadObjectList('id') as $k => $regency) {
			$name = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($regency->type)), $regency->name);
			$result[$regency->province_id][$k] = trim($name);
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $result;
	}

	public function getMarkets() {
		// Get a db connection.
		$db = $this->_db;

		$province_ids	= (array) $this->getState('filter.province_ids');
		
		$price_type_id	= $this->getState('filter.price_type_id');

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(count($province_ids) > 0) {
			$province_ids	= array_map(array($db, 'quote'), $province_ids);
			$query->where($db->quoteName('a.province_id') . ' IN ('.implode(',', $province_ids).')');
		}
		
		$query->where($db->quoteName('a.price_type_id'). ' = '.$db->quote($price_type_id));

		$db->setQuery($query);
		
		$result = array();
		foreach ($db->loadObjectList('id') as $k => $market) {
			$result[$market->regency_id][$k] = trim($market->name);
		}
		
		return $result;
	}

	public function getPriceTypes() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		if (JFactory::getUser()->guest) {
			$query->where($db->quoteName('a.published') . ' = 1');
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}