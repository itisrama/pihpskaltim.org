<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelSurvey extends GTModelAdmin{
	
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

	public function getUser($user_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__gtsurveypihps_users', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.user_id') . ' = '.$db->quote($user_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $db->loadObject();
	}

	public function updateToken($id, $token) {
		$data			= new stdClass();
		$data->id		= $id;
		$data->token	= $token;

		return $this->saveExternal($data, 'survey_user');
	}

	public function getReferencesByUser($user_id, $prepare = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.market_id', 'a.name')));
		$query->from($db->quoteName('#__gtsurveypihps_sellers', 'a'));

		$query->select($db->quoteName('b.name', 'market'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('b.id')
		);

		$query->select($db->quoteName('c.name', 'regency'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_regencies', 'c').' ON '.
			$db->quoteName('a.regency_id').' = '.$db->quoteName('c.id')
		);

		$query->select($db->quoteName('d.name', 'type'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_price_types', 'd').' ON '.
			$db->quoteName('b.price_type_id').' = '.$db->quoteName('d.id')
		);

		$query->join('INNER', $db->quoteName('#__gtsurveypihps_users', 'e').' ON '.
			'FIND_IN_SET('.$db->quoteName('a.regency_id').', '.$db->quoteName('e.regency_ids').') AND '.
			'IF(LENGTH(e.market_ids) > 0, FIND_IN_SET('.$db->quoteName('a.market_id').', '.$db->quoteName('e.market_ids').'), TRUE) AND '.
			'IF(LENGTH(e.seller_ids) > 0, FIND_IN_SET('.$db->quoteName('a.id').', '.$db->quoteName('e.seller_ids').'), TRUE)'
		);
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('c.published') . ' = 1');
		$query->where($db->quoteName('d.published') . ' = 1');
		$query->where($db->quoteName('e.published') . ' = 1');

		$query->where($db->quoteName('e.user_id') . ' = '.$db->quote($user_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		
		$data = $db->loadObjectList();

		if(!$prepare) {
			return $data;
		}

		$cities = array();
		$markets = array();
		$sellers = array();
		foreach ($data as $item) {
			$city = new stdClass();
			$city->id = $item->regency_id;
			$city->name = $item->regency;
			$cities[$item->regency_id] = $city;

			$market = new stdClass();
			$market->id = $item->market_id;
			$market->name = $item->market;
			$market->type = $item->type;
			$markets[$item->regency_id][$item->market_id] = $market;

			$seller = new stdClass();
			$seller->id = $item->id;
			$seller->name = $item->name;
			$sellers[$item->market_id][$item->id] = $seller;
		}

		$cities = array_values($cities);
		foreach ($cities as &$city) {
			$markets = array_values((array) @$markets[$city->id]);
			foreach ($markets as &$market) {
				$market->sellers = array_values((array) @$sellers[$market->id]);
			}

			$city->markets = $markets;
		}

		return $cities;
	}

	public function getPrices($show_pending = true) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$user_id	= $this->input->get('user_id', '', 'int');
		$limit 		= 20;
		$offset 	= $this->input->get('offset', 0, 'int');

		$query->select($db->quoteName(array('a.id', 'a.market_id')));
		$query->from($db->quoteName('#__gtsurveypihps_prices', 'a'));

		$query->select($db->quoteName('b.name', 'market'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('b.id')
		);

		$query->select($db->quoteName('a.created_by', 'user_id'));

		$query->select($db->quoteName(array('a.status', 'a.date', 'a.created', 'a.modified')));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if(!$show_pending) {
			$query->where($db->quoteName('a.status') . ' != '.$db->quote('pending'));
		}
		$query->where($db->quoteName('a.created_by') . ' = '.$db->quote($user_id));

		$query->order($db->quoteName('a.modified') . ' desc');
		$query->setLimit($limit, $offset * $limit);

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $db->loadObjectList();
	}

	public function getPrice($latest = false) {
		$user_id	= $this->input->get('user_id', '', 'INT');
		$market_id	= $this->input->get('market_id', '', 'INT');
		$date		= $this->input->get('date');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.market_id', 'a.status', 'a.message')));
		$query->select($db->quoteName('a.created_by', 'user_id'));
		$query->from($db->quoteName('#__gtsurveypihps_prices', 'a'));

		$query->select($db->quoteName('b.name', 'type'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_price_types', 'b').' ON '.
			$db->quoteName('a.price_type_id').' = '.$db->quoteName('b.id')
		);
		
		$query->where($db->quoteName('a.published') . ' = 1');
		if($user_id) {
			$query->where($db->quoteName('a.created_by') . ' = '.$db->quote($user_id));
		}
		$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));
		if($latest) {
			$query->where($db->quoteName('a.date') . ' BETWEEN SUBDATE('.$db->quote($date).', INTERVAL 1 MONTH) AND SUBDATE('.$db->quote($date).', INTERVAL 1 DAY)');
		} else {
			$query->where($db->quoteName('a.date') . ' = '.$db->quote($date));
		}
		
		$query->order($db->quoteName('a.id') . ' desc');
		$query->setLimit(1);

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $db->loadObject();
	}

	public function getPriceDetail($price_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.seller_id', 'a.commodity_id', 'a.price', 'a.is_revision')));
		$query->from($db->quoteName('#__gtsurveypihps_price_details', 'a'));
		
		$query->where($db->quoteName('a.price_id') . ' = '.$db->quote($price_id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		$items = $db->loadObjectList();

		$prices = array();
		foreach ($items as $item) {
			$prices[$item->seller_id][$item->commodity_id] = $item;
		}

		return $prices;
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
			$data[$item->parent_id][$item->id] = $item->name;
		}
		return $data;
	}

	public function getSellers() {
		$user_id	= $this->input->get('user_id', '', 'INT');
		$market_id	= $this->input->get('market_id', '', 'INT');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->select($db->quoteName('a.commodity_ids', 'commodities'));

		$query->join('INNER', $db->quoteName('#__gtsurveypihps_users', 'e').' ON '.
			'FIND_IN_SET('.$db->quoteName('a.regency_id').', '.$db->quoteName('e.regency_ids').') AND '.
			'IF(LENGTH(e.market_ids) > 0, FIND_IN_SET('.$db->quoteName('a.market_id').', '.$db->quoteName('e.market_ids').'), TRUE) AND '.
			'IF(LENGTH(e.seller_ids) > 0, FIND_IN_SET('.$db->quoteName('a.id').', '.$db->quoteName('e.seller_ids').'), TRUE)'
		);
		
		$query->from($db->quoteName('#__gtsurveypihps_sellers', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('e.user_id') . ' = '.$db->quote($user_id));
		$query->where($db->quoteName('a.market_id') . ' = '.$db->quote($market_id));

		//echo nl2br(str_replace('#__','pihps_',$query)); die;

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getCommodities() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id', 'a.price_type_id')));
		$query->select('CONCAT('.$db->quoteName('a.name').', ":",'.$db->quoteName('a.denomination').') name');
		
		$query->from($db->quoteName('#__gtpihpssurvey_ref_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function prepareCommodities($categories, $commodities, $selected) {
		$coms = array();
		foreach ($commodities as $com) {
			if(!in_array($com->id, $selected)) {
				continue;
			}
			$coms[$com->category_id][$com->id] = $com->name;
		}

		$data			= GTHelperHtml::setCommodities($categories[0], $categories, $coms, 'select');
		$commodities	= array();
		foreach ($data as &$item) {
			if(!$item->text) continue;
			list($name, $denom)	= explode(':', $item->text.':');
			$commodity			= new stdClass();
			$commodity->id		= is_numeric($item->value) ? $item->value : '';
			$commodity->name	= str_replace('&nbsp;', '', $name);
			$commodity->denom	= $denom;
			$commodity->type	= is_numeric($item->value) ? 'commodity' : 'category';
			$commodity->level	= substr_count($name, str_repeat('&nbsp;', 4));

			$commodities[]		= $commodity;
		}

		//echo "<pre>"; print_r($commodities); echo "</pre>"; die;
		return $commodities;
	}

	public function getDetailIDs($price_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.price_id', 'a.seller_id', 'a.commodity_id')));
		$query->from($db->quoteName('#__gtsurveypihps_price_details', 'a'));

		$query->where($db->quoteName('a.price_id') . ' = ' . $db->quote($price_id));

		$db->setQuery($query);
		$data = $db->loadObjectList();
		$result = array();
		foreach($data as &$item) {
			$result[$item->seller_id][$item->commodity_id] = $item->id;
		}

		return $result;
	}

	public function submit() {
		$id			= $this->input->get('id', '', 'int');
		$user_id	= $this->input->get('user_id', '', 'int');
		$market_id	= $this->input->get('market_id', '', 'int');
		$prices		= $this->input->get('prices', array(), 'array');
		$date		= $this->input->get('date');
		$location	= $this->input->get('location', '', 'raw');
		$token		= $this->input->get('token', '', 'raw');
		$market 	= $this->getItemExternal($market_id, 'market');

		if(!$market->id) {
			return 2;
		}

		$user = $this->getUser($user_id);

		if(@$user->token !== $token) {
			return 3;
		}

		$price					= new stdClass();
		$price->id				= intval($id);
		$price->price_type_id	= $market->price_type_id;
		$price->province_id		= $market->province_id;
		$price->regency_id		= $market->regency_id;
		$price->market_id		= $market_id;
		$price->date			= $date;
		$price->status			= @$user->type == 'validator' ? 'approved' : 'pending';

		if(!$id) {
			$price->location	= $location;
			$price->created_by	= $user_id;
		} else {
			$price->modified_by	= $user_id;
		}

		$price_id	= $this->saveExternal($price, 'survey_price', true);
		if(!$price_id > 0) {
			return 2;
		}

		$detail_ids	= $this->getDetailIDs($id);
		$details 	= array();
		foreach ($prices as $seller_id => $commodities) {
			foreach ($commodities as $commodity_id => $price) {
				$detail					= new stdClass();
				$detail->id				= intval(@$detail_ids[$seller_id][$commodity_id]);
				$detail->price_id		= $price_id;
				$detail->seller_id		= $seller_id;
				$detail->commodity_id	= $commodity_id;
				$detail->price			= $price;
				$details[]				= $detail;
				
				unset($detail_ids[$seller_id][$commodity_id]);
			}
		}

		if(count($detail_ids) > 0) {
			$detail_ids = call_user_func_array('array_merge', $detail_ids);
			$this->deleteExternal($detail_ids, 'survey_price_detail');
		}

		$this->saveBulk($details, 'survey_price_detail', false, true);

		return 1;
	}

	public function getSurvey() {
		$id = $this->input->get('id', 0, 'int');

		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.market_id')));
		$query->from($db->quoteName('#__gtsurveypihps_prices', 'a'));

		$query->select($db->quoteName('b.name', 'market'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('b.id')
		);

		$query->select($db->quoteName('a.created_by', 'surveyor_id'));
		$query->select($db->quoteName('c.name', 'surveyor'));
		$query->join('INNER', $db->quoteName('#__users', 'c').' ON '.
			$db->quoteName('a.created_by').' = '.$db->quoteName('c.id')
		);

		$query->select($db->quoteName(array('a.status', 'a.date', 'a.created', 'a.modified')));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.id') . ' = '.$db->quote($id));

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $db->loadObject();
	}

	public function getSurveys() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$user_id	= $this->input->get('user_id', '', 'int');
		$date		= $this->input->get('date');
		$status		= $this->input->get('status');
		$limit 		= 20;
		$offset 	= $this->input->get('offset', 0, 'int');

		$refs		= $this->getReferencesByUser($user_id, false);
		$city_ids	= array(0);
		$market_ids	= array(0);
		foreach ($refs as $ref) {
			$city_ids[$ref->regency_id]		= $ref->regency_id;
			$market_ids[$ref->market_id]	= $ref->market_id;
		}
		$city_ids	= implode(',', array_map(array($db, 'quote'), $city_ids));
		$market_ids	= implode(',', array_map(array($db, 'quote'), $market_ids));

		$user_id	= $this->input->get('user_id', '', 'INT');
		$market_id	= $this->input->get('market_id', '', 'INT');
		$date		= $this->input->get('date');


		$query->select($db->quoteName(array('a.id', 'a.market_id')));
		$query->from($db->quoteName('#__gtsurveypihps_prices', 'a'));

		$query->select($db->quoteName('b.name', 'market'));
		$query->join('INNER', $db->quoteName('#__gtpihpssurvey_ref_markets', 'b').' ON '.
			$db->quoteName('a.market_id').' = '.$db->quoteName('b.id')
		);

		$query->select($db->quoteName('a.created_by', 'surveyor_id'));
		$query->select($db->quoteName('c.name', 'surveyor'));
		$query->join('INNER', $db->quoteName('#__users', 'c').' ON '.
			$db->quoteName('a.created_by').' = '.$db->quoteName('c.id')
		);

		$query->select($db->quoteName(array('a.status', 'a.date', 'a.created', 'a.modified')));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.regency_id') . ' IN ('.$city_ids.')');
		$query->where($db->quoteName('a.market_id') . ' IN ('.$market_ids.')');

		if($date) {
			$query->where($db->quoteName('a.date') . ' = '.$db->quote($date));
		}

		if($status) {
			$query->where($db->quoteName('a.status') . ' = '.$db->quote($status));
		}

		$query->order('IF('.$db->quoteName('a.modified').', '.$db->quoteName('a.modified').', '.$db->quoteName('a.created'). ') desc');
		$query->setLimit($limit, $offset * $limit);

		$db->setQuery($query);

		//echo nl2br(str_replace('#__','pihps_',$query)); die;
		return $db->loadObjectList();
	}

	public function validateData() {
		$id			= $this->input->get('id', '', 'int');
		$user_id	= $this->input->get('user_id', '', 'int');
		$prices		= $this->input->get('prices', array(), 'array');
		$status		= $this->input->get('status');
		$token		= $this->input->get('token', '', 'raw');
		$message	= $this->input->get('message', '', 'raw');
		if(!$id) {
			return 1;
		}

		$user = $this->getUser($user_id);
		if(@$user->token !== $token) {
			return 4;
		}

		$price					= new stdClass();
		$price->id				= intval($id);
		$price->status			= $status;
		$price->modified_by		= $user_id;

		if($message) {
			$price->message		= $message;
		}
		
		$price_id	= $this->saveExternal($price, 'survey_price', true);
		if(!$price_id > 0) {
			return 1;
		}

		if($status !== 'revision') {
			return 2;
		}

		$detail_ids	= $this->getDetailIDs($id);
		$details 	= array();
		foreach ($prices as $seller_id => $commodities) {
			foreach ($commodities as $commodity_id => $is_revision) {
				$detail_id 				= intval(@$detail_ids[$seller_id][$commodity_id]);
				if(!$detail_id) continue;

				$detail					= new stdClass();
				$detail->id				= $detail_id;
				$detail->is_revision 	= $is_revision;
				$details[]				= $detail;
			}
		}

		$this->saveBulk($details, 'survey_price_detail', false, true);

		return 3;
	}
}
