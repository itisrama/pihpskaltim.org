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
class JKCommodityModelPrice extends JKModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getLastPrices($date = null, $city_id = null, $market_id = null, $unit_id = null) {
		$db			= $this->_db;

		$date		= $date ? $date : $this->input->get("date");
		$city_id	= $city_id ? $city_id : $this->input->get("city_id");
		$market_id	= $market_id ? $market_id : $this->input->get("market_id");
		$unit_id	= $unit_id ? $unit_id : $this->input->get("unit_id");

		$date 		= $this->getLatestDate($date, $city_id, $market_id, $unit_id);
		
		$query = "
			SELECT
				ROUND(AVG(pricedt.`price`),0) AS price,
				pricedt.`commodity_id` AS `id`
			FROM #__jkcommodity_price AS price
			LEFT JOIN #__jkcommodity_price_detail AS pricedt
				ON price.`id` = pricedt.`price_id`
			WHERE price.`date` = '$date' 
				AND price.`published` = 1
		";
		if($city_id) {
			$query .= " AND price.city_id = $city_id";
		}
		if($market_id) {
			$query .= " AND price.market_id = $market_id";
		}
		if($unit_id) {
			$query .= " AND price.unit_id = $unit_id";
		}
		$query .= "
			GROUP BY pricedt.`commodity_id`, price.`date`
			ORDER BY pricedt.`commodity_id` ASC, price.`date` ASC
		";
		//echo "<pre>"; print_r(str_replace("#__", "tpid_", $query)); echo "</pre>";
		$db->setQuery($query);
		$prices = $db->loadObjectList('id');
		
		return $prices;
	}
	
	public function getCategory() {
		$db		= $this->_db;
		$query = "
			SELECT `id`, `name`, `key`, `parent_id`
			FROM #__jkcommodity_category
			WHERE `published` = '1' 
			AND `type` = 'consumer'
			ORDER BY `id`
		";
		$db->setQuery($query);
		$data = $db->loadObjectList('id');

		return $data;
	}
	
	public function getLatestDate($date = null, $city_id = null, $market_id = null, $unit_id = null){
		$db			= $this->_db;

		$date		= $date ? $date : $this->input->get("date");
		$tomorrow	= date('Y-m-d', strtotime(date('Y-m-d').'+1 days'));
		#When making a new item, the query will be BEFORE TOMORROW.
		$date		= $date ? $date : JRequest::getVar('date', $tomorrow);
		
		$city_id	= $city_id ? $city_id : $this->input->get("city_id");
		$market_id	= $market_id ? $market_id : $this->input->get("market_id");
		$unit_id	= $unit_id ? $unit_id : $this->input->get("unit_id");
		
		$query = "
			SELECT MAX(`date`) AS `date` 
			FROM #__jkcommodity_price 
			WHERE `date` < '$date'
				AND `published` = 1
		";
		if($city_id) {
			$query .= " AND city_id = $city_id";
		}
		if($market_id) {
			$query .= " AND market_id = $market_id";
		}
		if($unit_id) {
			$query .= " AND unit_id = $unit_id";
		}
		//echo "<pre>"; print_r(str_replace("#__", "tpid_", $query)); echo "</pre>";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		if($result) {
			$result = array_shift($result);
			$result = $result->date;
		}

		return $result;
	}
		
	public function getMarket(){
		$city_id = JRequest::getVar('city_id');
		$query = "
			SELECT `id`, `name`, `city_id`
			FROM #__jkcommodity_market
			WHERE `published` = 1
		";
		/* Before privilege stuff was made.
        if($city_id) {
			$city_id = (array) $city_id;
			JArrayHelper::toInteger($city_id);
			$city_id = $city_id > 0 ? implode(',', $city_id) : 0;
			$query .= "WHERE `city_id` IN ($city_id) AND `published` = 1";
		} else {
			$query .= "WHERE `published` = 1";
		}*/
		
		if(is_array($city_id)){
    		JArrayHelper::toInteger($city_id);
            if($city_id && !in_array(0, $city_id)) {
			    $city_list = implode(',', $city_id);
        		$query .= "AND `city_id` IN ($city_list)";
		    }
		    elseif(!JKHelperAccess::isAdmin()){
                $city_list = JKHelperPrivilege::getPrivileges();
        		$query .= "AND `city_id` IN ($city_list)";
		    }
		}
		else{
		    $city_list = $city_id;
    		$query .= "AND `city_id` IN ($city_list)";
		}

		$data = $this->_getList($query);
		return $data;
	}

	public function getCommodity(){
		$query = "
			SELECT cm.*
			FROM #__jkcommodity_commodity AS cm
			JOIN #__jkcommodity_category AS ct ON cm.category_id = ct.id
			WHERE cm.`published` = '1'
			AND cm.`type` = 'consumer'
			ORDER BY ct.parent_id, ct.id, cm.id
		";
		$data = $this->_getList($query);
		return $data;
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem($pk);
		if(!is_object($data)) return false;
		//$data->date = JHtml::date($data->date, 'd-m-Y');
		$this->item	= $data;
		return $data;
	}
	
	public function getItemDetail($pks = null, $key = 'commodity_id') {
		$pks	= $pks ? $pks : $this->getState($this->getName() . '.id');
		$pks	= is_null($pks) ? array(0) : $pks;
		$pks	= is_numeric($pks) ? array($pks) : $pks;
		
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select('pcd.*');
		$query->from('#__jkcommodity_price_detail AS pcd');
		
		$query->where($db->quoteName('pcd.price_id') . ' IN (' . implode(', ', $pks) . ')');
		$db->setQuery($query);
		$data = $db->loadObjectList($key);
		
		return $data;
	}

	public function getMarket2($market_id) {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.city_id')));
		$query->from($db->quoteName('#__jkcommodity_market', 'a'));
		
		$query->select($db->quoteName('b.name', 'city'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_city', 'b').' ON '.$db->quoteName('a.city_id').' = '.$db->quoteName('b.id'));
		
		$query->where($db->quoteName('a.id') . ' = '. intval($market_id));

		$db->setQuery($query);
		return $db->loadObject();
	}

	public function getFirebaseKeys() {
		// Get a db connection.
		$db = $this->_db;

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.key')));
		$query->from($db->quoteName('#__jkcommodity_firebase_keys', 'a'));
		
		$query->where($db->quoteName('a.active') . ' = '. $db->quote(1));

		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getID($market_id, $date) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id')));
		$query->from($db->quoteName('#__jkcommodity_price', 'a'));
		$query->where($db->quoteName('a.market_id') . ' = ' . $db->quote($market_id));
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));

		//echo nl2br(str_replace('#__','pihps_',$query));

		$db->setQuery($query);
		return intval(@$db->loadObject()->id);		
	}

	public function save2($data) {
		$db		= $this->_db;
		$data['date'] = date('Y-m-d', strtotime($data['date']));
		$return = parent::save($data);
		$price_id = $this->getState($this->getName().'.id');
		$this->input = JFactory::getApplication()->input;
		$raw_data	= $this->input->post->get('jform', array(), 'array');
		$price_data	= $raw_data['commodities'];
		
		foreach($price_data as $commodity_id => $price) {
			$price = JKHelperDocument::toNumber($price);
			#echo"<pre>";var_dump($price);echo"</pre>";
			if(intval($price) > 0) {
				$query = "
					INSERT INTO #__jkcommodity_price_detail (price_id, commodity_id, price)
					VALUES ('$price_id','$commodity_id','$price')
					ON DUPLICATE KEY UPDATE
					price = VALUES(price)
				";
				$price_data[$commodity_id] = JKHelperDocument::toNumber($price);
			} else {
				$query = "DELETE FROM #__jkcommodity_price_detail WHERE price_id = '$price_id' AND commodity_id = '$commodity_id'";
			}
			$db->setQuery($query);
			$db->query();
			#echo"<pre>";var_dump($query);echo"</pre>";
		}
		#exit;

		return $return;
	}

	public function save($data, $return_num = false) {
		$data = is_array($data) ? JArrayHelper::toObject($data) : $data;
		
		$market		 = $this->getMarket2($data->market_id);
		$commodities = $this->getCommodity();
		
		$commodity_names = array();
		foreach($commodities as $commodity){
			$commodity_names[$commodity->id] = $commodity->name;
		}
		
		// Firebase
		foreach($data->details as $commodity_id => $detail){
			$last_price = (!empty($data->last_details))? $data->last_details->$commodity_id : null;
			
			if(!empty($last_price) && !empty($detail)){
				$new_price = JKHelperDocument::toNumber($detail);
				if($new_price >= 1.1 * $last_price){
					$keys  = $this->getFirebaseKeys();
					$users = array();
					
					foreach($keys as $key){
						$users[] = $key->key;
					}
					
					$body = sprintf(
						JText::_('COM_JKCOMMODITY_EWS_MESSAGE'),
						$commodity_names[$commodity_id],
						$market->name,
						$market->city,
						(($new_price/$last_price) * 100).'%',
						JKHelperDocument::toCurrency($last_price),
						$detail
					);
					
		            $msg = array(
						'body'		=> $body,
		                'title'		=> JText::_('COM_JKCOMMODITY_EARLY_WARNING_SYSTEM'),
		                'vibrate'	=> 1,
		                'sound'		=> 2
		            );
		            
		            $fields = array(
		                'registration_ids'	=> $users,
		                'notification'    	=> $msg,
		                'data'				=> array('message' => $body)
		            );
		             
		            $headers = array (
		            	'Authorization: key=AIzaSyCBHZEzBr05z4ZcoAlsIBl-02KcNpJNhmo',
		            	'Content-Type: application/json'
		            );
		             
		            $ch = curl_init();
		            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		            curl_setopt($ch, CURLOPT_POST, true);
		            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		            $result = curl_exec($ch);
		            curl_close($ch);
		            
		            //echo $result;
				}
			}
		}
		unset($data->last_details);
		
		$prevID		= $data->id;
		$data->id	= $this->getID($data->market_id, $data->date);
		if($data->id != $prevID && $prevID > 0) {
			$this->delete(array($prevID));
		}
		
		$data->date	= JHtml::date($data->date, 'Y-m-d');
		$details	= $data->details; unset($data->details);
	
		$return = $this->saveMaster($data);
		$this->saveDetail($details);

		return $return;
	}

	public function saveMaster($data, $return_num = false) {
		$data	= is_array($data) ? JArrayHelper::toObject($data) : $data;

		$return	= parent::save($data);
		$return	= $return_num ? $this->getState($this->getName() . '.id') : $return;
		return $return;
	}

	public function saveDetail($data, $price_id = null) {
		$data		= is_object($data) ? JArrayHelper::fromObject($data) : $data;
		$price_id	= !$price_id ? $this->getState($this->getName() . '.id') : $price_id;
		
		if(is_numeric($price_id)) {
			$details	= $this->getItemDetail($price_id);
			$i			= 0;
			foreach ($data as $commodity_id => $price) {
				$price = is_string($price) ? JKHelperDocument::toNumber($price) : $price;
				if(!$price) continue;

				$detail = new stdClass();
				$detail->id = intval(@$details[$commodity_id]->id);
				$detail->price_id = $price_id;
				$detail->commodity_id = $commodity_id;

				$detail->price = is_numeric($price) ? $price : JKHelperDocument::toNumber($price);

				parent::saveExternal($detail, 'price_detail');

				if(isset($details[$commodity_id])) {
					unset($details[$commodity_id]);
				}
				$i++;
			}
			$delete_ids = array();
			foreach ($details as $item) {
				$delete_ids[] = $item->id;
			}
			parent::deleteExternal($delete_ids, 'price_detail');
		}
	}
	
	public function findCommodity($name, $prevId = 0) {
		$db		= $this->_db;
		$query	= "SELECT id FROM #__jkcommodity_commodity WHERE LOWER(alt_name) = '$name' AND id <> $prevId";
		$db->setQuery($query);
		$data = $db->loadObject();
		return $data ? $data->id : 0;
	}

	public function delete(&$pks) {
		$details = $this->getItemDetail($pks, 'id');
		$details = array_keys($details);
		parent::deleteExternal($details, 'price_detail');

		return parent::delete($pks);
	}
	
	public function store($data, $table = '') {
		$table = $this->getTable($table);
		$table->reset();

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->id;
	}
}
