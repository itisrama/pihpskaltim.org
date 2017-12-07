<?php

defined ( '_JEXEC' ) or die ( 'Restricted access' );

// loads module function file
jimport('joomla.event.dispatcher');

class modGTPIHPSMap {

	public static function getProvinces($commodity_id, $date) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select Prices
		$query->select($db->quoteName(array('a.province_id', 'a.date')));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));

		// Join Price Details
		$query->select('IF('.$db->quoteName('b.price').' = 0, NULL,'.$db->quoteName('b.price').') price');
		$query->select($db->quoteName('b.commodity_id'));
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details', 'b') . 
			' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));

		// Join commodity
		$query->select($db->quoteName('c.name', 'commodity'));
		$query->select($db->quoteName('c.denomination'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'c') . 
			' ON ' . $db->quoteName('b.commodity_id') . ' = ' . $db->quoteName('c.id'));

		// Join province
		$query->select($db->quoteName(array('d.name', 'd.iso_code')));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'd') . 
			' ON ' . $db->quoteName('a.province_id') . ' = ' . $db->quoteName('d.id'));

		// WHERE
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));
		$query->where($db->quoteName('b.commodity_id') . ' = ' . $db->quote($commodity_id));
		$query->where($db->quoteName('b.price') . ' > 50');

		$query->order($db->quoteName('a.province_id') .', '. $db->quoteName('b.price') . ' desc');

		$db->setQuery($query);
		$data = $db->loadObjectList();
		$prices = array();
		foreach ($data as $item) {
			$price		= round($item->price / 50) * 50;
			$prices[]	= $price;
		}
		foreach ($data as $key => $item) {
			$price 				= round($item->price / 50) * 50;
			$current_price		= $price - min($prices);
			$total_price 		= max($prices) - min($prices);
			
			$item->value	= $total_price > 0 ? round($current_price / $total_price * 8) + 1 : 1;
			$item->name 	= strtoupper($item->name) . ' #' . $item->value;
			$item->tooltip	= $item->commodity . ' : ' . GTHelperCurrency::fromNumber($price, '') . '/' . $item->denomination;
		}
		return $data;
	}

	public static function getCommodity($commodity_id = null) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.denomination')));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		if($commodity_id) {
			$query->where($db->quoteName('a.id') . ' = '. $db->quote($commodity_id));
		}
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->limit(1);

		$db->setQuery($query);
		return $db->loadObject();
	}

	public static function getCommodities() {
		//$commodity_ids		= $this->getState('filter.commodity_ids');

		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select($db->quoteName('a.name'));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item;
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public static function getPriceTypes() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		if (JFactory::getUser()->guest) {
			$query->where($db->quoteName('a.published') . ' = 1');
		}
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public static function getCategories() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item;
		}
		return $data;
	}

	public static function getLatestDate($date = null) {		
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.date').') date');
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		if($date) {
			$query->where($db->quoteName('a.date') . ' < ' . $db->quote($date));
		}

		$db->setQuery($query);

		$data = $db->loadObject();
		
		return isset($data->date) ? $data->date : JHtml::date('now', 'Y-m-d');
	}

	public static function getDefaultCommodityID() {
		$latestDate = self::getLatestDate();

		// Get a db connection.
		$db = JFactory::getDBO();
		
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
}
