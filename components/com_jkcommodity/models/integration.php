<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityModelIntegration extends JKModel {

	public function getPrices() {
		$date = $this->app->input->get('date');
		$market_id = $this->app->input->get('market_id');
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.market_id', 'a.date')));
		$query->from($db->quoteName('#__jkcommodity_price', 'a'));

		$subsquery = $db->getQuery(true);

		$subsquery->select($db->quoteName('c.price_id'));
		$subsquery->select('COUNT('.$db->quoteName('c.id').') details');
		$subsquery->from($db->quoteName('#__jkcommodity_price_detail', 'c'));

		$subsquery->join('LEFT', $db->quoteName('#__jkcommodity_price', 'd') . ' ON ' . $db->quoteName('c.price_id') . ' = ' . $db->quoteName('d.id'));

		$subsquery->where($db->quoteName('d.date').' = '.$db->quote($date));
		$subsquery->where($db->quoteName('d.published') . ' = 1');

		$subsquery->group($db->quoteName('c.price_id'));

		$query->select('MAX('. $db->quoteName('b.details').') details');
		$query->join('LEFT', '('. $subsquery .') b ' . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.price_id'));

		$query->join('LEFT', $db->quoteName('#__jkcommodity_market', 'e') . ' ON ' . $db->quoteName('a.market_id') . ' = ' . $db->quoteName('e.id'));

		$query->where($db->quoteName('a.date').' = '.$db->quote($date));
		if($market_id) {
			$query->where($db->quoteName('a.market_id').' = '.$db->quote($market_id));
		}
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('e.integration') . ' = 1');

		$query->group($db->quoteName('a.market_id'));

		//echo nl2br(str_replace('#__','tpid_',$query)); die;
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList('id');
	}

	public function getPriceDetails($pks) {
		$date = $this->app->input->get('date');
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.price_id', 'a.commodity_id', 'a.price')));
		$query->from($db->quoteName('#__jkcommodity_price_detail', 'a'));
		$query->where($db->quoteName('a.price_id').' IN ('.implode(',', $pks).')');
		$this->_db->setQuery($query);

		$data = $this->_db->loadObjectList();
		$return = array();

		foreach ($data as $item) {
			$price_id = $item->price_id;
			$item->price = round($item->price);
			unset($item->price_id);
			$return[$price_id][] = $item;
		}

		return $return;
	}
}