<?php

defined ( '_JEXEC' ) or die ( 'Restricted access' );

// loads module function file
jimport('joomla.event.dispatcher');

class modGTPIHPSTable {

	public static function getItems($date) {
		$date = JHtml::date($date, 'Y-m-d');
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select Prices
		$query->select($db->quoteName(array('a.province_id')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join Price Details
		$query->select('IF('.$db->quoteName('b.price').' = 0, NULL,'.$db->quoteName('b.price').') price');
		$query->select($db->quoteName('b.commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . 
			' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));
		
		// WHERE
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));
		$query->where($db->quoteName('b.price') . ' > 50');

		$query->order($db->quoteName('a.province_id') .', '. $db->quoteName('b.price') . ' desc');

		$db->setQuery($query);
		$data = $db->loadObjectList();
		$prices = array();
		$items = array();
		foreach ($data as $item) {
			$price							= round($item->price / 50) * 50;
			$prices[$item->commodity_id][] 	= $price;
		}
		foreach ($data as $key => $item) {
			$price_array 		= $prices[$item->commodity_id];
			$price 				= round($item->price / 50) * 50;
			$current_price		= $price - min($price_array);
			$total_price 		= max($price_array) - min($price_array);
			
			$item->rank			= $total_price > 0 ? round($current_price / $total_price * 8) + 1 : 1;
			$item->price 		= GTHelperCurrency::fromNumber($price, '');

			$items[$item->province_id][$item->commodity_id] = $item;
		}
		return $items;
	}

	public function getLatestDate($date = null) {		
		// Get a db connection.
		$db =& JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		$query->join('LEFT', $db->quoteName('#__gtpihps_holidays', 'b').' ON '.
			$db->quoteName('a.date').' BETWEEN '.$db->quoteName('b.start').' AND '.$db->quoteName('b.end')
		);

		$query->where($db->quoteName('b.id').' IS NULL');
		$query->where('DAYOFWEEK('.$db->quoteName('a.date').') NOT IN (1,7)');
		
		if($date) {
			$query->where($db->quoteName('a.date') . ' < ' . $db->quote($date));
		}

		$db->setQuery($query);

		$data = $db->loadObject();
		
		//echo nl2br(str_replace('#__','pihps_',$query));
		return isset($data->date) ? $data->date : JHtml::date('now', 'Y-m-d');
	}

	public static function getProvinces() {
		//$commodity_ids		= $this->getState('filter.commodity_ids');

		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadObjectList();
	}

	public static function getCommodities() {
		//$commodity_ids		= $this->getState('filter.commodity_ids');

		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.denomination')));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$query->order($db->quoteName('a.category_id'));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadObjectList();
	}
}
