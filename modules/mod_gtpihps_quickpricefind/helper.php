<?php

defined ( '_JEXEC' ) or die ( 'Restricted access' );

// loads module function file
jimport('joomla.event.dispatcher');

class modGTPIHPSQuickPriceFind {

	public static function getRegencies($all = false) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.type', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.province_capital').' desc');
		$query->order($db->quoteName('a.type'));

		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $regency) {
			$data[$regency->id] = sprintf(JText::_('MOD_GTPIHPS_QUICKPRICEFIND_'.strtoupper($regency->type)), trim($regency->name));
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getRegencyOptions() {
		return GTHelperArray::toOption(self::getRegencies(true));
	}

	public function getCommodities($prepare = false) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select($db->quoteName('a.name'));
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);
		$raw = $db->loadObjectList('id');

		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item;
		}
		
		//echo "<pre>"; print_r($data); echo "</pre>";
		//echo nl2br(str_replace('#__','pihps_',$query));
		return $data;
	}

	public function getCategories() {
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

	public static function getCommodityOptions() {
		$commodities	= self::getCommodities();
		$categories		= self::getCategories();
		
		return $commodities ? GTHelperHtml::setCommodities($categories[0], $categories, $commodities) : array();
	}


	public static function getStatLatestDate($date = null, $row = 7) {
		$row 			= intval($row);
		$date 			= $date ? $date : 'now';
		$date 			= JHtml::date($date, 'Y-m-d');

		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->from($db->quoteName('#__gtpihps_prices', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.date') . ' <= ' . $db->quote($date));

		$query->group($db->quoteName('a.date'));
		$query->order($db->quoteName('a.date').' desc');
		$query->setLimit($row);

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','pihps_',$query));

		return $db->loadColumn();
	}
}
