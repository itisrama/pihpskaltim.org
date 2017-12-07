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
class JKCommodityModelSupply extends JKModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getLastSupplies($date = null, $city_id = null) {
		$db			= $this->_db;
		$date		= $date ? $date : $this->input->get("date");
		$city_id	= $city_id ? $city_id : $this->input->get("city_id");
		$date 		= $this->getLatestDate($date, $city_id);

		$query = $db->getQuery(true);

		$query->select($db->quoteName('a.commodity_id', 'id'));
		$query->select($db->quoteName(array('a.production', 'a.consumption', 'a.traded')));
		$query->from($db->quoteName('#__jkcommodity_supply_detail', 'a'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_supplies', 'b').' ON '.$db->quoteName('a.supply_id').' = '.$db->quoteName('b.id'));

		$query->where($db->quoteName('b.date').' = '.$db->quote($date));
		$query->where($db->quoteName('b.published').' = '.$db->quote(1));

		if($city_id) {
			$query->where($db->quoteName('b.city_id').' = '.$db->quote($city_id));
		}

		$query->order($db->quoteName('a.commodity_id').' ASC');

		//echo "<pre>"; print_r(str_replace("#__", "tpid_", $query)); echo "</pre>";
		$db->setQuery($query);
		$supplies = $db->loadObjectList();
		
		return $supplies;
	}
	
	public function getCategory() {
		$db		= $this->_db;

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name', 'a.key', 'a.parent_id')));
		$query->from($db->quoteName('#__jkcommodity_category', 'a'));
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));
		$query->where($db->quoteName('a.type').' = '.$db->quote('consumer'));
		$query->order($db->quoteName('a.id'));

		$db->setQuery($query);
		$data = $db->loadObjectList('id');

		return $data;
	}
	
	public function getLatestDate($date = null, $city_id = null){
		$db			= $this->_db;

		$date		= $date ? $date : $this->input->get("date");
		$tomorrow	= date('Y-m-d', strtotime(date('Y-m-d').'+1 days'));
		#When making a new item, the query will be BEFORE TOMORROW.
		$date		= $date ? $date : JRequest::getVar('date', $tomorrow);
		$city_id	= $city_id ? $city_id : $this->input->get("city_id");
		
		$query = $db->getQuery(true);
		$query->select('MAX('.$db->quoteName('a.date').') AS '.$db->quoteName('date'));
		$query->from($db->quoteName('#__jkcommodity_supplies', 'a'));

		$query->where($db->quoteName('a.date').' < '.$db->quote($date));
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		if($city_id) {
			$query->where($db->quoteName('a.city_id').' = '.$db->quote($city_id));
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

	public function getCommodity(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__jkcommodity_commodity', 'a'));
		$query->join('LEFT', $db->quoteName('#__jkcommodity_category', 'b').' ON '.$db->quoteName('a.category_id').' = '.$db->quoteName('b.id'));

		$query->where($db->quoteName('a.type').' = '.$db->quote('consumer'));
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));
		$query->order($db->quoteName('b.parent_id').', '.$db->quoteName('b.id').', '.$db->quoteName('a.id'));

		$data = $this->_getList($query);
		return $data;
	}

	public function getCities(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select(array('a.id', 'a.name'));
		$query->from($db->quoteName('#__jkcommodity_city', 'a'));

		$query->where($db->quoteName('a.type').' = '.$db->quote('consumer'));
		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		$data = $this->_getList($query);
		
		$result = array();
		foreach($data as $val){
			$result[$val->id] = $val->name;
		}
		
		return $result;
	}

	public function getProvinces(){
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select(array('a.id', 'a.name'));
		$query->from($db->quoteName('#__jkcommodity_province', 'a'));

		$query->where($db->quoteName('a.published').' = '.$db->quote(1));

		$data = $this->_getList($query);
		
		$result = array();
		foreach($data as $val){
			$result[$val->id] = $val->name;
		}
		
		return $result;
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem($pk);
		if(!is_object($data)) return false;
		
		$this->item	= $data;
		return $data;
	}
	
	public function getItemDetail($pks = null, $key = 'commodity_id') {
		$pks	= $pks ? $pks : $this->getState($this->getName() . '.id');
		$pks	= is_null($pks) ? array(0) : $pks;
		$pks	= is_numeric($pks) ? array($pks) : $pks;
		
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__jkcommodity_supply_detail', 'a'));
		
		$query->where($db->quoteName('a.supply_id') . ' IN (' . implode(', ', $pks) . ')');
		$db->setQuery($query);
		$data = $db->loadObjectList($key);
		
		return $data;
	}
	
	public function getItemTrades($pks = null) {
		$pks	= $pks ? $pks : $this->getState($this->getName() . '.id');
		$pks	= is_null($pks) ? array(0) : $pks;
		$pks	= is_numeric($pks) ? array($pks) : $pks;
		
		$db		= $this->_db;
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__jkcommodity_supply_trade', 'a'));
		
		$query->where($db->quoteName('a.supply_id') . ' IN (' . implode(', ', $pks) . ')');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		$result = array();
		foreach($data as $val){
			if(!isset($result[$val->commodity_id]))
				$result[$val->commodity_id] = array();
			$result[$val->commodity_id][] = $val;
		}
		
		return $result;
	}
	
	public function getID($city_id, $date) {
		// Get a db connection.
		$db		= $this->_db;
		
		// Create a new query object.
		$query	= $db->getQuery(true);

		// Select commodities
		$query->select($db->quoteName(array('a.id')));
		$query->from($db->quoteName('#__jkcommodity_supplies', 'a'));
		$query->where($db->quoteName('a.city_id') . ' = ' . $db->quote($city_id));
		$query->where($db->quoteName('a.date') . ' = ' . $db->quote($date));

		//echo nl2br(str_replace('#__','pihps_',$query));

		$db->setQuery($query);
		return intval(@$db->loadObject()->id);		
	}

	public function save($data, $return_num = false) {
		$data = is_array($data) ? JArrayHelper::toObject($data) : $data;
		
		$prevID		= $data->id;
		$data->id	= $this->getID($data->city_id, $data->date);
		if($data->id != $prevID && $prevID > 0) {
			$this->delete(array($prevID));
		}
		
		$data->date	= JHtml::date($data->date, 'Y-m-d');
		$details	= $data->details; unset($data->details);
		$trades		= $data->trades; unset($data->trades);
	
		$return = $this->saveMaster($data, true);
		$this->saveDetail($details, $return);
		$this->saveTrade($trades, $return);

		return $return;
	}

	public function saveMaster($data, $return_num = false) {
		$data	= is_array($data) ? JArrayHelper::toObject($data) : $data;

		$return	= parent::save($data);
		$return	= $return_num ? $this->getState($this->getName() . '.id') : $return;
		return $return;
	}

	public function saveDetail($data, $supply_id = null) {
		$data		= is_object($data) ? JArrayHelper::fromObject($data) : $data;
		$supply_id	= !$supply_id ? $this->getState($this->getName() . '.id') : $supply_id;
		
		if(is_numeric($supply_id)) {
			$details = $this->getItemDetail($supply_id);
			
			foreach ($data as $commodity_id => $supply) {
				$production  = is_string($supply['production'])  ? JKHelperDocument::toNumber($supply['production'])  : $supply['production'];
				$consumption = is_string($supply['consumption']) ? JKHelperDocument::toNumber($supply['consumption']) : $supply['consumption'];
				$traded 	 = is_string($supply['traded']) ? JKHelperDocument::toNumber($supply['traded']) : $supply['traded'];
				if(!$production && !$consumption && !$traded) continue;

				$detail = new stdClass();
				$detail->id = intval(@$details[$commodity_id]->id);
				$detail->supply_id		= $supply_id;
				$detail->commodity_id	= $commodity_id;

				$detail->production  = is_numeric($production)  ? $production  : JKHelperDocument::toNumber($production);
				$detail->consumption = is_numeric($consumption) ? $consumption : JKHelperDocument::toNumber($consumption);
				$detail->traded 	 = is_numeric($traded) ? $traded : JKHelperDocument::toNumber($traded);

				parent::saveExternal($detail, 'supply_detail');

				if(isset($details[$commodity_id])) {
					unset($details[$commodity_id]);
				}
			}
			$delete_ids = array();
			foreach ($details as $item) {
				$delete_ids[] = $item->id;
			}
			parent::deleteExternal($delete_ids, 'supply_detail');
		}
	}

	public function saveTrade($data, $supply_id = null) {
		$data		= is_object($data) ? JArrayHelper::fromObject($data) : $data;
		$supply_id	= !$supply_id ? $this->getState($this->getName() . '.id') : $supply_id;
		
		if(is_numeric($supply_id)) {
			$trades	= $this->getItemTrades($supply_id);
			
			// Delete old trades.
			$delete_ids = array();
			foreach ($trades as $items) {
				foreach($items as $trade)
					$delete_ids[] = $trade->id;
			}
			if(!empty($delete_ids))
				parent::deleteExternal($delete_ids, 'supply_trade');

			// Insert new trades.
			foreach ($data as $commodity_id => $trades) {
				foreach($trades as $trade){
					if(!$trade['traded_in'] && !$trade['traded_out']) continue;

					$trade['id']			= 0;
					$trade['supply_id']		= $supply_id;
					$trade['commodity_id']	= $commodity_id;
					
					$trade = JArrayHelper::toObject($trade);
					parent::saveExternal($trade, 'supply_trade');
				}
			}
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
		parent::deleteExternal($details, 'supply_detail');

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
