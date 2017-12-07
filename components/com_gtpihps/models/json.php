<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelJSON extends GTModelList{
	
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
		
		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		if ($layout) {
			$this->context.= '.' . $layout;
		}
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '1');
		$this->setState('filter.published', $published);
	}

	public function getProvinces($grouping = false) {
		$province_id = $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.region_id', 'a.name', 'a.short_name', 'a.iso_code')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_provinces', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($province_id) {
			$query->where($db->quoteName('a.id') . ' = '.intval($province_id));
		}

		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		if($grouping) {
			$result = array();
			foreach ($data as $item) {
				$item->name = trim($item->name);
				$result[$item->region_id][] = $item;
			}

			return $result;

		} else {
			foreach ($data as &$item) {
				$item->name = trim($item->name);
			}

			return $data;
		}
	}

	public function getProvince() {
		$province_id	= $this->input->get('province_id', 1);
		$table = $this->getTable('Province');
		$table->load($province_id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getRegions() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regions', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	public function getRegencies($province_id = null) {
		$province_id = $province_id ? $province_id : $this->input->get('province_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.type', 'a.name', 'a.short_name')));
		$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_regencies', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($province_id) {
			$query->where($db->quoteName('a.province_id') . ' = '.$db->quote($province_id));
		}
		$query->order($db->quoteName('a.id'));
		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		foreach ($data as &$item) {
			$item->name = trim($item->name);
		}
		
		return $data;
	}

	public function getRegency() {
		$regency_id	= $this->input->get('regency_id', 1);
		$table = $this->getTable('Regency');
		$table->load($regency_id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getPriceTypes($all = false) {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_price_types', 'a'));
		
		if (JFactory::getUser()->guest && !$all) {
			$query->where($db->quoteName('a.published') . ' = 1');
		}
		//echo nl2br(str_replace('#__','pihps_',$query));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	public function getMarkets($regency_id = null) {
		$regency_id = $regency_id ? $regency_id : $this->input->get('regency_id');
		if(!$regency_id) return null;

		$priceTypes = $this->getPriceTypes(true);

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.regency_id', 'a.price_type_id', 'a.name')));
		$query->select('UNIX_TIMESTAMP(IF('.$db->quoteName('a.modified').','.$db->quoteName('a.modified').','.$db->quoteName('a.modified').')) date');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($regency_id) {
			$query->where($db->quoteName('a.regency_id') . ' = '.$db->quote($regency_id));
		}
		$query->order($db->quoteName('a.price_type_id'));
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		
		$data = $db->loadObjectList();
		
		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $data;
	}

	public function getMarket() {
		$market_id	= $this->input->get('market_id', 1);
		$table = $this->getTable('Market');
		$table->load($market_id);
		return JArrayHelper::toObject($table->getProperties(1));
	}

	public function getReferences() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.province_id', 'a.market_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_markets', 'a'));

		$query->select($db->quoteName('b.name', 'regency'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'b').' ON '.$db->quoteName('a.regency_id').' = '.$db->quoteName('b.id'));

		$query->select($db->quoteName('c.name', 'province'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_provinces', 'c').' ON '.$db->quoteName('a.province_id').' = '.$db->quoteName('c.id'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('c.published') . ' = 1');
		
		$query->order($db->quoteName('c.id'));
		$query->order($db->quoteName('b.province_capital').' desc');
		$query->order($db->quoteName('b.type'));
		$query->order($db->quoteName('b.name'));
		$query->order($db->quoteName('a.name'));

		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	public function getCommodityCategories() {
		// Get a db connection.
		$db = $this->_db;

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

	public function getCommodityImages() {
		$commodity_id = $this->input->get('commodity_id');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.image')));
		$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').',")") name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($commodity_id) {
			$query->where($db->quoteName('a.id') . ' = '.$db->quote($commodity_id));
		}

		$db->setQuery($query);
		$commodities = $db->loadObjectList();

		$mediaUrl = str_replace(JURI::root(true), '', GT_MEDIA_URI);
		foreach ($commodities as &$commodity) {
			$commodity->image = $mediaUrl . '/img/commodities/'.$commodity->image.'.png';
		}

		return $commodities;
	}

	public function getCategories($prepare = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name', 'a.denomination')));
		$query->from($db->quoteName('#__gtpihpssurvey_ref_categories', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();

		if(!$prepare) return $raw;
		
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item;
		}
		return $data;
	}

	public function getCommodities($prepare = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.denomination', 'a.name')));		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$db->setQuery($query);
		$raw = $db->loadObjectList('id');

		if(!$prepare) return $raw;

		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item;
		}

		$categories		= $this->getCategories();
		$commodities	= GTHelperHtml::setCommodities($categories[0], $categories, $data);
		foreach ($commodities as &$commodity) {
			$commodity->id		= $commodity->type == 'commodity' ? $commodity->id : '';
			$commodity->denom	= $commodity->denomination;

			unset($commodity->text);
			unset($commodity->value);
			unset($commodity->denomination);
		}

		return $commodities;
	}

	public function getCommodity() {
		$commodity_id	= $this->input->get('commodity_id', 'cat-1');
		
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

	public function checkChatroomMember($user_id) {
		$chatroom_id = $this->input->get('chatroom_id');

		if(!$chatroom_id) {
			return false;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.userid'));
		$query->from($db->quoteName('cometchat_chatrooms_users', 'a'));
		$query->where($db->quoteName('a.chatroomid').' = '.$db->quote($chatroom_id));
		$query->where($db->quoteName('a.userid').' = '.$db->quote($user_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		return $db->loadResult() > 0;
	}

	public function getChatroomMembers() {
		$chatroom_id = $this->input->get('chatroom_id');

		if(!$chatroom_id) {
			return null;
		}

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('b.id', 'b.name', 'b.username', 'c.message')));
		$query->select('IF(TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME('.$db->quoteName('c.lastseen').'), NOW()) > 180, '.$db->quote('offline').', '.$db->quoteName('c.status').') status');
		$query->from($db->quoteName('cometchat_chatrooms_users', 'a'));
		$query->join('INNER', $db->quoteName('#__users', 'b').' ON '.$db->quoteName('a.userid').' = '.$db->quoteName('b.id'));
		$query->join('INNER', $db->quoteName('cometchat_status', 'c').' ON '.$db->quoteName('a.userid').' = '.$db->quoteName('c.userid'));

		$query->where($db->quoteName('a.chatroomid').' = '.$db->quote($chatroom_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		return $db->loadObjectList();
	}

	public function getDateReference($table = 'market') {
		$table = GTHelper::pluralize($table);
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('MAX('.$db->quoteName('a.created').') created');
		$query->select('MAX('.$db->quoteName('a.modified').') modified');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_'.$table, 'a'));

		$db->setQuery($query);
		$result = $db->loadObject();

		$date = $result->created > $result->modified ? $result->created : $result->modified;
		$date = JFactory::getDate($date)->toUnix();

		return $date;
	}

	public function getHolidays() {
		$date = $this->input->get('date');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.start', 'a.end')));		
		$query->from($db->quoteName('#__gtpihps_holidays', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		if($date) {
			$date = JFactory::getDate($date)->format('Y-m-d H:i:s');
			$query->where('('.$db->quoteName('a.created').' > '.$db->quote($date).' OR '.$db->quoteName('a.modified').' > '.$db->quote($date).')');
		}

		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
