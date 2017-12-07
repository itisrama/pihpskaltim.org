<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelService extends GTModelAdmin{
	
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

	public function getIntegrationMarkets() {
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('CONCAT('.$db->quoteName('e.code').', '.$db->quoteName('a.id').') market_id');
		$query->select($db->quoteName('a.name', 'market_desc'));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_sellers', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.market_id')
		);

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_price_types', 'e').' ON '.
			$db->quoteName('a.price_type_id').' = '.$db->quoteName('e.id')
		);

		$query->select($db->quoteName('c.id', 'region_id'));
		$query->select($db->quoteName('c.long_name', 'region_desc'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'c').' ON '.
			$db->quoteName('a.regency_id').' = '.$db->quoteName('c.id')
		);

		if($province_id) {
			$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'd').' ON '.
				$db->quoteName('a.province_id').' = '.$db->quoteName('d.id')
			);
			$query->where($db->quoteName('d.source_id') . ' = '.$db->quote($province_id));
		}
		
		
		$query->group('a.id');

		$query->where($db->quoteName('a.published') . ' = '.$db->quote(1));

		$db->setQuery($query);
		
		//echo nl2br(str_replace('#__','eburo_',$query)); die;
		
		return $db->loadObjectList();
	}

	public function getIntegrationPrices() {
		$date			= $this->input->get('period');
		$province_id	= $this->input->get('province_id');
		$sources		= array('dotnet', 'dotnet2', 'mobile');
		
		$source_type	= $this->input->get('source_type');
		$source			= $sources[$source_type-1];
		$source_type	= $source_type > 1 ? $source_type : null;
		$source_type2	= $source_type == 2 ? 2 : null;

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.date'));
		$query->select('MAX('.$db->quoteName('a.modified').') created');
		$query->from($db->quoteName('#__gtpihps_prices_copy', 'a'));

		$query->select('ROUND(AVG('.$db->quoteName('b.price').')/50)*50 price');
		$query->join('INNER', $db->quoteName('#__gtpihps_price_details_copy', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.price_id')
		);

		$query->select($db->quoteName('c.source_id'.$source_type, ' market_id'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets_copy', 'c').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('c.id')
		);

		$query->select('SUBSTRING_INDEX('.$db->quoteName('d.source_id'.$source_type2).', "-", 1) commodity_id');
		$query->select('SUBSTRING_INDEX('.$db->quoteName('d.source_id'.$source_type2).', "-", -1) quality_id');
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_commodities', 'd').' ON '.
			$db->quoteName('b.commodity_id').' = '.$db->quoteName('d.id')
		);

		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'e').' ON '.
			$db->quoteName('a.province_id').' = '.$db->quoteName('e.id')
		);

		$query->group('a.date');
		$query->group('a.market_id');
		$query->group('b.commodity_id');

		$query->where($db->quoteName('e.source_id') . ' = '.$db->quote($province_id));
		$query->where($db->quoteName('c.source_id'.$source_type) . ' IS NOT NULL');
		$query->where($db->quoteName('a.date') . ' = '.$db->quote($date));
		$query->where($db->quoteName('b.price') . ' > 50');
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.source_type') . ' = '.$db->quote($source));

		//echo nl2br(str_replace('#__','eburo_',$query)); die;

		$db->setQuery($query);
		$data = $db->loadObjectList();

		$items = array();
		foreach ($data as $item) {
			$items[$item->market_id]['market_id']	= $item->market_id;
			$items[$item->market_id]['date']		= $item->date;
			$items[$item->market_id]['created']		= $item->created;
			$items[$item->market_id]['details'][$item->commodity_id.'-'.$item->quality_id]['commodity_id']	= $item->commodity_id;
			$items[$item->market_id]['details'][$item->commodity_id.'-'.$item->quality_id]['quality_id']	= $item->quality_id;
			$items[$item->market_id]['details'][$item->commodity_id.'-'.$item->quality_id]['price']			= $item->price;
		}

		sort($items);
		foreach ($items as &$item) {
			sort($item['details']);
		}

		return $items;
	}

	
}
