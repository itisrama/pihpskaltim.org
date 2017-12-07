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
class JKCommodityModelAjax extends JKModel {
	function getPrices($date_now, $date_yesterday) {
		$db = $this->_db;
		$query = "
			SELECT
				ROUND(AVG(pricedt.`price`),0) AS price, commodity.denomination,
				pricedt.`commodity_id`, commodity.`name` AS commodity_name, commodity.`img` AS commodity_img
			FROM #__jkcommodity_price_detail AS pricedt
			LEFT JOIN #__jkcommodity_price AS price
				ON pricedt.`price_id` = price.`id`
			LEFT JOIN #__jkcommodity_commodity AS commodity
				ON pricedt.`commodity_id` = commodity.`id`
			WHERE price.`date` IN ('$date_yesterday', '$date_now') 
				AND price.`city_id` = 7 AND price.`unit_id` = 1
				AND price.`published` = 1
				AND commodity.`published` = 1
			GROUP BY pricedt.`commodity_id`, price.`date`
			ORDER BY pricedt.`commodity_id` ASC, price.`date` ASC
		";

		$db->setQuery($query);
		$result = $db->loadObjectList();
		$prices = array();
		foreach($result as $k => $item) {
			$prices[$item->commodity_id]->id = $item->commodity_id;
			$prices[$item->commodity_id]->name = $item->commodity_name;
			$prices[$item->commodity_id]->denomination = $item->denomination;
			$prices[$item->commodity_id]->commodity_img = JURI::base(false).'/images/commodities/'.$item->commodity_img.'.png';
			$prices[$item->commodity_id]->commodity_prices[] = $item->price;
		}
		$prices = array_values($prices);
		return $prices;
	}

	function getLatestDate($city_id, $exception=NULL)	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__jkcommodity_price', 'a'));
		
		$query->where($db->quoteName('a.published').' = 1');

		if($city_id) {
			$query->where($db->quoteName('a.city_id').' = '.$city_id);
		}
		if($exception) {
			$query->where($db->quoteName('a.date').' < '.$db->quote($exception));
		} else {
			$query->where($db->quoteName('a.date').' <= DATE(NOW())');
		}

		//echo nl2br(str_replace('#__','tpid_',$query));
		$db->setQuery($query);
		$result = $db->loadObject();

		return JHtml::date(@$result->date, 'Y-m-d');
	}

	function getCommodityPrices($city_id = false) {
		$date_now	= $this->getLatestDate($city_id);
		$date_then	= $this->getLatestDate($city_id, $date_now);

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('ROUND(AVG('.$db->quoteName('a.price').'), 0) price');
		$query->select($db->quoteName('a.commodity_id'));
		$query->select($db->quoteName('b.date'));
		$query->select($db->quoteName('c.name', 'commodity_name'));
		$query->select($db->quoteName('c.img', 'commodity_img'));
		$query->select($db->quoteName('c.denomination'));

		$query->from($db->quoteName('#__jkcommodity_price_detail', 'a'));

		$query->join('INNER', $db->quoteName('#__jkcommodity_price', 'b').' ON '.
			$db->quoteName('a.price_id').' = '.$db->quoteName('b.id')
		);

		$query->join('INNER', $db->quoteName('#__jkcommodity_commodity', 'c').' ON '.
			$db->quoteName('a.commodity_id').' = '.$db->quoteName('c.id')
		);

		$query->where($db->quoteName('b.date').' IN ('.$db->quote($date_now).', '.$db->quote($date_then).')');
		$query->where($db->quoteName('b.published').' = 1');
		$query->where($db->quoteName('c.published').' = 1');

		if($city_id) {
			$query->where($db->quoteName('b.city_id').' = '.$city_id);
		}

		$query->group($db->quoteName(array('a.commodity_id', 'b.date')));
		$query->order($db->quoteName(array('a.commodity_id', 'b.date')));
		
		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		$prices = array();
		foreach($data as $item) {
			$prices[$item->commodity_id]['id']					= $item->commodity_id;
			$prices[$item->commodity_id]['name']				= $item->commodity_name;
			$prices[$item->commodity_id]['denom']				= $item->denomination;
			$prices[$item->commodity_id]['img']					= $item->commodity_img;
			$prices[$item->commodity_id]['prices'][$item->date]	= $item->price;
		}

		foreach ($prices as $k => &$price) {
			$block		= new stdClass();
			$price		= JArrayHelper::toObject($price, 'JObject', false);
			
			$price_now	= round(intval(@$price->prices[$date_now])/50)*50;
			$price_then	= round(intval(@$price->prices[$date_then])/50)*50;
			$price_diff	= $price_now - $price_then;

			if($price_now == 0) {
				unset($prices[$k]);
				continue;
			}

			if($price_diff < 0) {
				$block->class	= 'price_down';
				$block->icon	= 'icon-arrow-down';
				$block->status	= 'Turun';
			} else if($price_diff > 0) {
				$block->class	= 'price_up';
				$block->icon	= 'icon-arrow-up';
				$block->status	= 'Naik';
			} else {
				$block->class	= 'price_still';
				$block->icon	= 'icon-pause';
				$block->status	= 'Harga Stabil';
			}
			
			$diff		= abs($price_diff);
			$percent	= round($diff/$price_now * 100, 1);
			$img_path	= implode(DIRECTORY_SEPARATOR, array(JPATH_BASE,'images','commodities',$price->img.'.png'));
			
			$block->title	= $price->name;
			$block->price	= 'Rp'.number_format($price_now, 0, ',', '.');
			$block->denom	= 'Per '.$price->denom;
			$block->style	= JURI::base(true).'/images/commodities/'.$price->img.'.png';
			$block->style	= is_file($img_path) ? 'background-image: url('.$block->style.')' : '';
			$block->status 	= $diff > 0 ? $block->status.' '.$percent.'%'.' - Rp'.number_format($diff, 0, ',', '.') : $block->status;

			$price = $block;
		}

		$data			= new stdClass();
		$data->date		= JHtml::date($date_now, 'j F Y');
		$data->prices	= $prices;

		return $data;
	}
}