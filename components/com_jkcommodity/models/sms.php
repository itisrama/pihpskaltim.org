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
class JKCommodityModelSMS extends JKModel {
	function getConsumer($commodity, $city) {
		$date = $this->getLatestDate();
		$commodity = !trim($commodity) ? 'emptyinput' : trim($commodity);
		$city = !trim($city) ? 'emptyinput' : trim($city);
		$query = "
			SELECT 
				pc.date, 
				pc.city_id, ct.name AS city, 
				pc.market_id, mk.name AS market, 
				pcd.commodity_id, cm.name AS commodity, cm.denomination,
				pcd.price
			FROM #__jkcommodity_price AS pc
			JOIN #__jkcommodity_price_detail AS pcd ON pc.id = pcd.price_id
			JOIN #__jkcommodity_city AS ct ON pc.city_id = ct.id
			JOIN #__jkcommodity_market AS mk ON pc.market_id = mk.id
			JOIN #__jkcommodity_commodity AS cm ON pcd.commodity_id = cm.id
			JOIN #__jkcommodity_category AS cg ON cm.category_id = cg.id
			WHERE 
				pc.date = '$date' AND
				ct.name LIKE '%$city%' AND
				(
					cm.name LIKE '% $commodity %' OR cg.name LIKE '% $commodity % ' OR
					cm.name LIKE '$commodity %' OR cg.name LIKE '$commodity % '
				) AND
				ct.type = 'consumer' AND
				cm.type = 'consumer'
			GROUP BY pc.market_id, pcd.commodity_id
			ORDER BY mk.name
		";

		//echo nl2br(str_replace('#__','tpid_',$query));
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		$data = array();
		foreach($result as $item) {
			$data['date'] = $item->date;
			$data['city'] = $item->city;
			$data['commodities'][$item->commodity_id]['name'] = $item->commodity;
			$data['commodities'][$item->commodity_id]['denomination'] = $item->denomination;
			$data['commodities'][$item->commodity_id]['markets'][$item->market_id]['name'] = $item->market;
			$data['commodities'][$item->commodity_id]['markets'][$item->market_id]['price'] = $item->price;
		}
		
		return $data;
	}
	
	function getProducer($commodity) {
		$date = $this->getProdLatestDate();
		$commodity = !trim($commodity) ? 'emptyinput' : trim($commodity);
		$query = "
			SELECT 
				pc.date, 
				pc.city_id, ct.name AS city, 
				pcd.commodity_id, cm.name AS commodity, cm.denomination,
				pcd.price
			FROM #__jkcommodity_prod_price AS pc
			JOIN #__jkcommodity_prod_price_detail AS pcd ON pc.id = pcd.price_id
			JOIN #__jkcommodity_city AS ct ON pc.city_id = ct.id
			JOIN #__jkcommodity_commodity AS cm ON pcd.commodity_id = cm.id
			WHERE 
				pc.date = '$date' AND
				cm.name LIKE '%$commodity%' AND
				ct.type = 'producer' AND
				cm.type = 'producer'
			GROUP BY pc.city_id, pcd.commodity_id
			ORDER BY ct.name
		";
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		$data = array();
		foreach($result as $item) {
			$data['date'] = $item->date;
			$data['city'] = $item->city;
			$data['commodities'][$item->commodity_id]['name'] = $item->commodity;
			$data['commodities'][$item->commodity_id]['denomination'] = $item->denomination;
			$data['commodities'][$item->commodity_id]['cities'][$item->city_id]['name'] = $item->city;
			$data['commodities'][$item->commodity_id]['cities'][$item->city_id]['price'] = $item->price;
		}

		return $data;
	}
	
	function getLatestDate()
	{
		$query = "
			SELECT 
				MAX(`date`) AS `date` 
			FROM #__jkcommodity_price 
			WHERE
				`date` <= DATE(NOW()) AND
				`published` = 1
		";
		$this->_db->setQuery($query);
		$result = $this->_db->loadObject();
		$result = $result ? $result->date : date('Y-m-d');

		return $result;
	}
	
	function getProdLatestDate()
	{
		$query = "
			SELECT 
				MAX(`date`) AS `date` 
			FROM #__jkcommodity_prod_price 
			WHERE
				`date` <= DATE(NOW()) AND
				`published` = 1
		";
		$this->_db->setQuery($query);
		$result = $this->_db->loadObject();
		$result = $result ? $result->date : date('Y-m-d');

		return $result;
	}
}