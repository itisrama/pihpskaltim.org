<?php

/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;
jimport('joomla.utilities.arrayhelper');
require_once( JK_ADMIN_HELPERS . DS . 'date.php' );
class JKCommodityModelReport extends JKModelForm {

	var $_data;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getData() {
		$layout = JRequest::getCmd('layout');
		$query = $this->buildQuery();
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadObjectList();

		if ($this->_db->getErrorNum() > 0) {
			JError::raiseError($this->_db->getErrorNum(), $this->_db->getErrorMsg() . $this->_db->stderr());
		}
		if (in_array($layout, array('weekly','monthly', 'yearly'))) {
			foreach ($this->_data as $k => $item) {
				switch ($layout) {
					case 'weekly':
						$unix = strtotime($item->date);
						$w = date('W', $unix);
						$m = date('m', $unix);
						$y = date('Y', $unix);
						$first_week = JKHelperDate::getFirstWeekday($m, $y);
						if($w == date('W',$first_week)) {
							$unix = $first_week;
						} else {
							$unix = strtotime($y.'W'.sprintf('%02d',$w));
						}
						$item->date = date('Y-m-d', $unix);
						break;
					case 'monthly':
						$item->date = date('Y-m-d', strtotime(date('Y-m-01', strtotime($item->date))));
						break;
					case 'yearly':
						$item->date = date('Y-m-d', strtotime(date('Y-01-01', strtotime($item->date))));
						break;
				}
				$this->_data[$k] = $item;
			}
		}
		return $this->_data;
	}

	public function getForm($data = array(), $loadData = true) {
		return parent::getForm($data, $loadData, '');
	}

	public function getCommodity() {
		$commodity_id = JRequest::getVar('commodity_id');
		$commodity_id = implode(',', (array) $commodity_id);
		$commodity_id = $commodity_id ? $commodity_id : 0;
		$commodity_limit = JRequest::getVar('commodity_limit');
		$all_commodity = JRequest::getInt('all_commodity');
		$query = "
			SELECT `id`, CONCAT(`name`, ' (', `denomination`, ')') AS `name`, `category_id`
			FROM #__jkcommodity_commodity
			WHERE `published` = 1
			AND `type` = 'consumer'
		";
		if (!$all_commodity && !$commodity_limit) {
			$query .= " AND `id` IN ($commodity_id)";
		}
		if ($commodity_limit) {
			$query .= " LIMIT $commodity_limit";
		}
		$this->_db->setQuery($query);
		$commodity = $this->_db->loadObjectList('id');
		return $commodity;
	}

	public function getAllCommodity() {
		$query = "
			SELECT `id`, `name`, `category_id`
			FROM #__jkcommodity_commodity
			WHERE `published` = 1
			AND `type` = 'consumer'
		";
		$this->_db->setQuery($query);
		$commodity = $this->_db->loadObjectList('id');
		return $commodity;
	}

	public function getCategory() {
		$query = "
			SELECT `id`, `name`, `key`, `parent_id`
			FROM #__jkcommodity_category
			WHERE `published` = '1' 
			AND `type` = 'consumer'
			ORDER BY `id`
		";
		$this->_db->setQuery($query);
		$data = $this->_db->loadObjectList('id');

		return $data;
	}

	public function getCity() {
		$city_id = (array) JRequest::getVar('city_id');
		$query = "
			SELECT `id`, `name` FROM #__jkcommodity_city
		";
		$where = array();
		$where[] = "`type` = 'consumer'";
		if ($city_id && !in_array(0, $city_id)) {
			JArrayHelper::toInteger($city_id);
			$city_id = $city_id ? implode(',', $city_id) : 0;
			$where[] = "`id` IN ($city_id)";
		}
		$query .= $where ? " WHERE " . implode(' AND ', $where) : NULL;
		$query .= " ORDER BY `key`";
		$this->_db->setQuery($query);
		$city = $this->_db->loadObjectList('id');
		return $city;
	}

	public function getMarket() {
		$city_id = (array) JRequest::getVar('city_id');
		$market_id = (array) JRequest::getVar('market_id');
		$query = "
			SELECT `id`, `name` FROM #__jkcommodity_market
		";
		$where = array();
		if ($city_id && !in_array(0, $city_id)) {
			JArrayHelper::toInteger($city_id);
			$city_id = $city_id ? implode(',', $city_id) : 0;
			$where[] = "`city_id` IN ($city_id)";
		}
		if($market_id && !in_array(0, $market_id)) {
			JArrayHelper::toInteger($city_id);
			$market_id = $market_id ? implode(',', $market_id) : 0;
			$where[] = "`id` IN ($market_id)";
		}
		$where[] = "`published` = 1";
		$query .= $where ? " WHERE " . implode(' AND ', $where) : NULL;
		$query .= " ORDER BY `key`";
		$this->_db->setQuery($query);
		$market = $this->_db->loadObjectList('id');
		return $market;
	}

	public function getLatestDate()
	{
		$city_id = (array) JRequest::getVar('city_id');
		$market_id = (array) JRequest::getVar('market_id');
		$query = "
			SELECT MAX(`date`) AS `date` FROM #__jkcommodity_price 
		";
		$where = array();
		if ($city_id && !in_array(0, $city_id)) {
			JArrayHelper::toInteger($city_id);
			$city_id = $city_id ? implode(',', $city_id) : 0;
			$where[] = "`city_id` IN ($city_id)";
		}
		if($market_id && !in_array(0, $market_id)) {
			JArrayHelper::toInteger($city_id);
			$market_id = $market_id ? implode(',', $market_id) : 0;
			$where[] = "`market_id` IN ($market_id)";
		}
		
		$where[] = "`date` <= DATE(NOW())";
		$where[] = "`published` = 1";
		$query .= $where ? " WHERE " . implode(' AND ', $where) : NULL;

		//echo "<pre>"; print_r($query); echo "</pre>";
		$this->_db->setQuery($query);

		$result = $this->_db->loadObject();
		if($result) $result = $result->date;

		return $result;
	}

	private function buildQuery() {
		$querySelect = $this->buildSelect();
		$queryWhere = $this->buildWhere();
		$queryGroup = $this->buildGroup();
		$queryOrder = $this->buildOrder();
		$queryLimit = $this->buildLimit();

		$query = implode(' ', array($querySelect, $queryWhere, $queryGroup, $queryOrder, $queryLimit));
		//echo "<pre>"; print_r($query); echo "</pre>";
		return $query;
	}

	private function buildSelect() {
		$select = "
			SELECT
				AVG(IF(pricedt.`price` = 0, NULL, pricedt.`price`)) AS price, price.`date`,
				price.`market_id`, market.`name` AS market_name,
				pricedt.`commodity_id`, commodity.`name` AS commodity_name,
				WEEK(price.`date`,7),MONTH(price.`date`),YEAR(price.`date`)
			FROM #__jkcommodity_price_detail AS pricedt
			LEFT JOIN #__jkcommodity_price AS price
				ON pricedt.`price_id` = price.`id`
			LEFT JOIN #__jkcommodity_market AS market
				ON price.`market_id` = market.`id`
			LEFT JOIN #__jkcommodity_commodity AS commodity
				ON pricedt.`commodity_id` = commodity.`id`
		";
		return $select;
	}

	private function buildWhere() {
		$where = array();
		$layout = JRequest::getCmd('layout');
		$city_id = (array) JRequest::getVar('city_id');
		$market_id = (array) JRequest::getVar('market_id');
		$commodity_id = (array) JRequest::getVar('commodity_id');
		$all_commodity = JRequest::getInt('all_commodity');
		$sunix = strtotime(JRequest::getVar('start_date'));
		$eunix = strtotime(JRequest::getVar('end_date'));
		$start_date = JHtml::date($sunix, 'Y-m-d');
		$end_date = JHtml::date($eunix, 'Y-m-d');
		
		$where[] = "WEEKDAY(price.`date`) NOT IN (5,6)";
		$where[] = "commodity.`type` = 'consumer'";

		if ($city_id && !in_array(0, $city_id)) {
			JArrayHelper::toInteger($city_id);
			$city_id = $city_id ? implode(',', $city_id) : 0;
			$where[] = "price.`city_id` IN ($city_id)";
		}
		if($market_id && !in_array(0, $market_id)) {
			JArrayHelper::toInteger($city_id);
			$market_id = $market_id ? implode(',', $market_id) : 0;
			$where[] = "price.`market_id` IN ($market_id)";
		}
		if (!$all_commodity) {
			JArrayHelper::toInteger($city_id);
			$commodity_id = $commodity_id ? implode(',', $commodity_id) : 0;
			$where[] = "pricedt.`commodity_id` IN ($commodity_id)";
		}
		if ($start_date && $end_date) {
			switch ($layout) {
				default:
					$where[] = "price.`date` BETWEEN '$start_date' AND '$end_date'";
					break;
				case 'weekly':
					list($w,$m,$y) = explode("-", date('W-m-Y', $sunix));
					$first_week = JKHelperDate::getFirstWeekday($m, $y);

					if($w == date('W',$first_week) || ($m == 1 && $w > 5)) {
						$start_date = date('Y-m-d', $first_week);
					} else {
						$start_date = date('Y-m-d', strtotime($y.'W'.sprintf('%02d',$w)));
					}

					list($w,$m,$y) = explode("-", date('W-m-Y', strtotime("+1 week", $eunix)));
					$first_week = JKHelperDate::getFirstWeekday($m, $y);
					if($w == date('W',$first_week) || ($m == 1 && $w > 5)) {
						$end_date = date('Y-m-d', $first_week);
					} else {
						$end_date = date('Y-m-d', strtotime($y.'W'.sprintf('%02d',$w)));
					}
					break;
				case 'monthly':
					$start_date	= JHtml::date($sunix, 'Y-m-01');
					$end_date	= JHtml::date(strtotime('+1 month', $eunix), 'Y-m-01');
					$end_date	= JHtml::date($end_date.'-1 day', 'Y-m-d');
					break;
				case 'yearly':
					$start_date	= JHtml::date($sunix, 'Y-m-01');
					$end_date	= JHtml::date($eunix, 'Y-12-31');
					break;
			}
			$where[] = "price.`date` BETWEEN '$start_date' AND '$end_date'";
		}

		$where = ($where) ? "WHERE " . implode(' AND ', $where) : NULL;
		return $where;
	}

	private function buildGroup() {
		$layout = JRequest::getCmd('layout');
		switch ($layout) {
			default:
				$group = 'price.`date`';
				break;
			case 'map':
				$group = 'price.`market_id`, price.`date`';
				break;
			case 'market':
				$group = 'price.`market_id`';
				break;
			case 'weekly':
				$group = 'YEAR(price.`date`), MONTH(price.`date`), WEEK(price.`date`,7)';
				break;
			case 'monthly':
				$group = 'YEAR(price.`date`), MONTH(price.`date`)';
				break;
			case 'yearly':
				$group = 'YEAR(price.`date`)';
				break;
		}
		$group = "GROUP BY pricedt.`commodity_id`, $group";
		return $group;
	}

	private function buildOrder() {
		$layout = JRequest::getCmd('layout');
		switch ($layout) {
			case 'market':
				$order = 'price.`market_id`';
				break;
			default:
				$order = 'price.`date`';
				break;
		}
		$order = "ORDER BY pricedt.`commodity_id`, $order";
		return $order;
	}

	private function buildLimit() {
		$limit = '';
		return $limit;
	}

}
