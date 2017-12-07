<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerJson extends JKControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);

		$model 		= $this->getModel();
		$log		= new stdClass();
		$log->post	= $_POST;
		$log->get	= $_GET;
		$model->saveServiceLog($log);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function prepareJSON($data, $obj = null) {
		$json = new stdClass();
		$json->result = $data;

		if(is_object($obj)) {
			foreach (JArrayHelper::fromObject($obj) as $field => $value) {
				$json->$field = $value;
			}
		}

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);

		$this->app->close();
	}

	protected function prepareRawJSON($data) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($data);

		$this->app->close();
	}

	public function references() {
		$model = $this->getModel();
		$regencies = $model->getRegencies();

		foreach ($regencies as $j => &$regency) {
			$markets = $model->getMarkets($regency->id);
			if (!count($markets) > 0) {
				unset($regencies[$j]);
				continue;
			}

			$regency->markets = $markets;
			unset($regency->type);
		}

		return $this->prepareJSON(array_values($regencies));
	}

	public function provinces() {
		$model = $this->getModel();
		$this->prepareJSON($model->getProvinces());
	}

	public function regencies() {
		$model = $this->getModel();
		$dates = array();
		$regencies = $model->getRegencies();
		foreach ($regencies as &$regency) {
			$dates[] = $regency->date;
			unset($regency->date);
		}
		$obj = new stdClass();
		$obj->hash = md5(max($dates));

		$this->prepareJSON($regencies, $obj);
	}

	public function markets() {
		$model = $this->getModel();
		$dates = array();
		$markets = $model->getMarkets();
		foreach ($markets as &$market) {
			$dates[] = $market->date;
			unset($market->date);
		}
		$obj = new stdClass();
		$obj->hash = md5(max($dates));

		$this->prepareJSON($markets, $obj);
	}

	public function commodities() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCommodities());
	}

	public function supplyCommodities() {
		$model = $this->getModel('Supplies');
		$this->prepareJSON($model->getCommodities());
	}

	public function commodityPrices() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCommodityPrices());
	}

	public function commodityPricePeriods() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCommodityPricePeriods());
	}

	public function regencyPrices() {
		$model = $this->getModel();
		$this->prepareJSON($model->getRegencyPrices());
	}
	
	// Firebase services for notifications.
	public function registerFirebase(){
		$model = $this->getModel();
		$this->prepareJSON($model->insertFirebase());
	}
	
	public function configureFirebase(){
		$model = $this->getModel();
		$this->prepareJSON($model->updateFirebase());
	}
	
	// Get the latest supply of a commodity in a city given a particular date.
	public function monthlySupply(){
		$model = $this->getModel('Supplies');
		
		$result	= array();
		$total	= new stdClass();
		$total->production	= 0;
		$total->consumption = 0;
		$total->traded		= 0;
		$total->city_id		= 0;
		$total->city		= 'Sulawesi Selatan';
		
		$result[] = &$total;

		$cities = $model->getRegencies(false);
		foreach($cities as $city){
			$result[$city->id] = new stdClass();
			$result[$city->id]->production	 = 0;
			$result[$city->id]->consumption  = 0;
			$result[$city->id]->traded		 = 0;
			$result[$city->id]->denomination = '';
			$result[$city->id]->net = 0;
			
			$result[$city->id]->city_id	= $city->id;
			$result[$city->id]->city	= $city->name;
		}
		
		$supplies = $model->getMonthlySupply();
		if(!empty($supplies)){
			$denomination = '';
			foreach($supplies as $supply){
				$total->production	+= $supply->production;
				$total->consumption += $supply->consumption;
				$total->traded		+= $supply->traded;
				
				$denomination = $supply->denomination;
			
				$supply->net = $supply->production - $supply->consumption + $supply->traded;

				$supply->production	 = number_format($supply->production, 0, ',', '.');
				$supply->consumption = number_format($supply->consumption, 0, ',', '.');
				$supply->traded 	 = number_format($supply->traded, 0, ',', '.');
				$supply->net 		 = number_format($supply->net, 0, ',', '.');

				$result[$supply->city_id] = $supply;
			}
			
			foreach($result as $data){
				$data->denomination = $denomination;
			}
		}
		else{
			$app	  = JFactory::getApplication();
			$category = $model->getCommodity($app->input->get('commodity_id'));
			$total->denomination = $category->denomination;
		}
		
		$total->net = $total->production - $total->consumption + $total->traded;

		$total->production	= number_format($total->production, 0, ',', '.');
		$total->consumption	= number_format($total->consumption, 0, ',', '.');
		$total->traded 	 	= number_format($total->traded, 0, ',', '.');
		$total->net 		= number_format($total->net, 0, ',', '.');
		
		$this->prepareJSON(array_values($result));
	}
	
	// Get the supply history of a commodity in a city in a year.
	public function yearlySupply(){
		$model = $this->getModel('Supplies');
		
		$empty = new stdClass();
		$empty->production	 = 0;
		$empty->consumption  = 0;
		$empty->traded		 = 0;
		$empty->net			 = 0;
		
		$result = new stdClass();
		$result->total_production	= 0;
		$result->total_consumption	= 0;
		$result->total_traded		= 0;
		$result->total_net		 	= 0;
		
		$result->result = array($empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty);
		
		$supplies = $model->getYearlySupply();
		foreach($supplies as $supply){
			$result->total_production	+= $supply->production;
			$result->total_consumption 	+= $supply->consumption;
			$result->total_traded	 	+= $supply->traded;

			$supply->net 		 = $supply->production - $supply->consumption + $supply->traded;
			$supply->production  = number_format($supply->production, 0, ',', '.');
			$supply->consumption = number_format($supply->consumption, 0, ',', '.');
			$supply->traded 	 = number_format($supply->traded, 0, ',', '.');
			$supply->net 		 = number_format($supply->net, 0, ',', '.');
			
			$result->result[$supply->month - 1] = $supply;
		}
		
		$result->total_net = $result->total_production - $result->total_consumption + $result->total_traded;

		$result->total_production 	= number_format($result->total_production, 0, ',', '.');
		$result->total_consumption	= number_format($result->total_consumption, 0, ',', '.');
		$result->total_traded 		= number_format($result->total_traded, 0, ',', '.');
		$result->total_net 			= number_format($result->total_net, 0, ',', '.');
		
		$this->prepareRawJSON($result);
	}
	
	public function monthlyTrade(){
		$model = $this->getModel('Supplies');
		
		$result	= array();
		$total	= new stdClass();
		$total->traded_in	= 0;
		$total->traded_out	= 0;
		$total->traded_net	= 0;
		$total->city_id		= 0;
		$total->city		= 'Sulawesi Selatan';
		
		$result[] = &$total;

		$cities = $model->getRegencies(false);
		foreach($cities as $city){
			$result[$city->id] = new stdClass();
			$result[$city->id]->traded_in	 = 0;
			$result[$city->id]->traded_out	 = 0;
			$result[$city->id]->traded_net	 = 0;
			$result[$city->id]->denomination = '';
			
			$result[$city->id]->city_id	= $city->id;
			$result[$city->id]->city	= $city->name;
		}
		
		$trades = $model->getMonthlyTrade();
		
		if(!empty($trades)){
			$denomination = '';
			foreach($trades as $trade){
				$denomination = $trade->denomination;

				$total->traded_in	+= $trade->traded_in;
				$total->traded_out	+= $trade->traded_out;

				$result[$trade->city_id]->traded_in		+= $trade->traded_in;
				$result[$trade->city_id]->traded_out	+= $trade->traded_out;
			}
			
			foreach($result as $data){
				$data->denomination = $denomination;
				
				$data->traded_net = $data->traded_in - $data->traded_out;
				
				$data->traded_in	 = number_format($data->traded_in, 0, ',', '.');
				$data->traded_out	 = number_format($data->traded_out, 0, ',', '.');
				$data->traded_net	 = number_format($data->traded_net, 0, ',', '.');
			}
		}
		else{
			$app	  = JFactory::getApplication();
			$category = $model->getCommodity($app->input->get('commodity_id'));
			$total->denomination = $category->denomination;
		}
		
		$this->prepareJSON(array_values($result));
	}
	
	public function cityTrades(){
		$model = $this->getModel('Supplies');
		
		$result	= array();
		$total	= new stdClass();
		$total->traded_in	= 0;
		$total->traded_out	= 0;
		$total->traded_net	= 0;
		$total->city_id		= 0;
		$total->city		= 'Total';

		$cities = $model->getRegencies(false);
		foreach($cities as $city){
			$result[$city->id] = new stdClass();
			$result[$city->id]->traded_in	 = 0;
			$result[$city->id]->traded_out	 = 0;
			$result[$city->id]->traded_net	 = 0;
			
			$result[$city->id]->city_id	= $city->id;
			$result[$city->id]->city	= $city->name;
		}
		
		$result[] = &$total;
		
		$trades = $model->getMonthlyTrade(0);
		//echo"<pre>";var_dump($trades);echo"</pre>";exit();
		
		if(!empty($trades)){
			foreach($trades as $trade){
				$total->traded_in	+= $trade->traded_in;
				$total->traded_out	+= $trade->traded_out;

				$result[$trade->partner_city_id]->traded_in		+= $trade->traded_in;
				$result[$trade->partner_city_id]->traded_out	+= $trade->traded_out;
			}
			
			foreach($result as $data){
				$data->traded_net = $data->traded_in - $data->traded_out;
				
				$data->traded_in	 = number_format($data->traded_in, 0, ',', '.');
				$data->traded_out	 = number_format($data->traded_out, 0, ',', '.');
				$data->traded_net	 = number_format($data->traded_net, 0, ',', '.');
			}
		}
		
		$this->prepareJSON(array_values($result));
	}
	
	public function provinceTrades(){
		$model = $this->getModel('Supplies');
		
		$result	= array();
		$total	= new stdClass();
		$total->traded_in	= 0;
		$total->traded_out	= 0;
		$total->traded_net	= 0;
		$total->city_id		= 0;
		$total->city		= 'Total';

		$provinces = $model->getProvinces();
		foreach($provinces as $province){
			$result[$province->id] = new stdClass();
			$result[$province->id]->traded_in	 = 0;
			$result[$province->id]->traded_out	 = 0;
			$result[$province->id]->traded_net	 = 0;
			
			$result[$province->id]->city_id	= $province->id;
			$result[$province->id]->city	= $province->name;
		}
		
		$result[] = &$total;
		
		$trades = $model->getMonthlyTrade(1);
		//echo"<pre>";var_dump($trades);echo"</pre>";exit();
		
		if(!empty($trades)){
			foreach($trades as $trade){
				$total->traded_in	+= $trade->traded_in;
				$total->traded_out	+= $trade->traded_out;

				$result[$trade->partner_province_id]->traded_in		+= $trade->traded_in;
				$result[$trade->partner_province_id]->traded_out	+= $trade->traded_out;
			}
			
			foreach($result as $data){
				$data->traded_net = $data->traded_in - $data->traded_out;
				
				$data->traded_in	 = number_format($data->traded_in, 0, ',', '.');
				$data->traded_out	 = number_format($data->traded_out, 0, ',', '.');
				$data->traded_net	 = number_format($data->traded_net, 0, ',', '.');
			}
		}
		
		$this->prepareJSON(array_values($result));
	}
	
	public function cityTradeHistory(){
		$this->tradeHistory(0);
	}
	
	public function provinceTradeHistory(){
		$this->tradeHistory(1);
	}

	private function tradeHistory($type){
		$model = $this->getModel('Supplies');
		
		$empty = new stdClass();
		$empty->traded_in	= 0;
		$empty->traded_out  = 0;
		$empty->traded_net	= 0;
		
		$result = new stdClass();
		$result->total_traded_in	= 0;
		$result->total_traded_out	= 0;
		$result->total_traded_net 	= 0;
		
		$result->result = array($empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty, $empty);
		
		$trades = $model->getTradeHistory($type);
		foreach($trades as $trade){
			$result->total_traded_in	+= $trade->traded_in;
			$result->total_traded_out 	+= $trade->traded_out;

			$trade->traded_net	= $trade->traded_in - $trade->traded_out;
			$trade->traded_in	= number_format($trade->traded_in, 0, ',', '.');
			$trade->traded_out	= number_format($trade->traded_out, 0, ',', '.');
			$trade->traded_net	= number_format($trade->traded_net, 0, ',', '.');

			$result->result[$trade->month - 1] = $trade;
		}
		
		$result->total_traded_net = $result->total_traded_in - $result->total_traded_out;

		$result->total_traded_in	= number_format($result->total_traded_in, 0, ',', '.');
		$result->total_traded_out	= number_format($result->total_traded_out, 0, ',', '.');
		$result->total_traded_net	= number_format($result->total_traded_net, 0, ',', '.');
		
		$this->prepareRawJSON($result);
	}
	
	public function authenticate($token = null){
		$app  = JFactory::getApplication();
		$cred = $token? $token : $app->input->get('credentials', '', 'RAW');
		$data = json_decode(JKHelperAuthencrypt::decrypt($cred, 'fvsROiAEvHJOA0F8', 'xZPsTNZ6nJ3s4XZrJsKk'));

		$options = array();
		$options['remember'] = false;

		$credentials = array();
		$credentials['username']  = $data->username;
		$credentials['password']  = $data->password;
		
		if($app->login($credentials, $options)){
			if($token){
				return true;
			}
			else{
				$model	= $this->getModel();
				$user	= $model->findUser($data->username);
				
				$token = new stdClass();
				$token->id		 = $user->id;
				$token->username = $user->username;
				$token->password = $data->password;

				$result = new stdClass();
				$result->result = true;
				$result->data	= $user;
				$result->data->token = JKHelperAuthencrypt::encrypt(json_encode($token), 'fvsROiAEvHJOA0F8', 'xZPsTNZ6nJ3s4XZrJsKk');
				unset($result->data->password);
			
				$this->prepareRawJSON($result);
			}
		}
		else{
			if($token){
				return false;
			}
			else{
				$result = new stdClass();
				$result->result = false;
			
				$this->prepareRawJSON($result);
			}
		}
	}
	
	public function inputPrice(){
		$app	= JFactory::getApplication();
		$token	= $app->input->get('token', '', 'RAW');
		if($this->authenticate($token)){
			$data = array();
			$data['id']			= $app->input->get('id');
			$data['date']		= $app->input->get('date');
			$data['unit_id']	= $app->input->get('unit_id', 1);
			$data['city_id']	= $app->input->get('city_id');
			$data['market_id']	= $app->input->get('market_id');
			$data['details']	= $app->input->get('details', array(), 'RAW');
			
			$model = $this->getModel('Price');
			if($model->save($data)){
				$result = new stdClass();
				$result->result = true;
				$result->message = JText::_('COM_JKCOMMODITY_PRICE_SAVE_SUCCEED');
			
				$this->prepareRawJSON($result);
			}
			else{
				$result = new stdClass();
				$result->result = false;
				$result->message = JText::_('COM_JKCOMMODITY_PRICE_SAVE_FAILED');
			
				$this->prepareRawJSON($result);
			}
		}
		else{
			$result = new stdClass();
			$result->result  = false;
			$result->message = JText::_('COM_JKCOMMODITY_INVALID_TOKEN');
		
			$this->prepareRawJSON($result);
		}
	}

	public function findPrice() {
		$model	= $this->getModel('Prices');
		$prices	= $model->findPrice();
		$result = new stdClass();
		
		if(!empty($prices)){
			$result->result = true;
			$result->data	= $prices;
		}
		else{
			$result->result  = false;
			$result->message = JText::_('COM_JKCOMMODITY_NO_PRICE');
		}
		
		$this->prepareRawJSON($result);
	}
	
	public function cometChatUsers(){
		$model = $this->getModel();
		$this->prepareJSON($model->getCometChatUsers());
	}

	public function findSupply() {
		$model	= $this->getModel('Supplies');
		$supply	= $model->findSupply();
		$result = new stdClass();
		
		if(!empty($supply)){
			$result->result = true;
			$result->data	= $supply;
		}
		else{
			$result->result = false;
			$result->data	= array();
			
			$data			= new stdClass();
			$data->id		= 0;
			$data->city_id	= 0;
			$data->date		= 0;
			
			$result->data[]	= $data;
		}
		
		$this->prepareRawJSON($result);
	}
	
	public function inputSupply(){
		$app	= JFactory::getApplication();
		$token	= $app->input->get('token', '', 'RAW');
		if($this->authenticate($token)){
			$data = array();
			$data['id']			= $app->input->get('id');
			$data['date']		= $app->input->get('date');
			$data['city_id']	= $app->input->get('city_id');
			$data['details']	= $app->input->get('details', array(), 'RAW');
			$data['trades']		= $app->input->get('trades', array(), 'RAW');
			
			$model = $this->getModel('Supply');
			if($model->save($data)){
				$result = new stdClass();
				$result->result = true;
				$result->message = JText::_('COM_JKCOMMODITY_SUPPLY_SAVE_SUCCEED');
			
				$this->prepareRawJSON($result);
			}
			else{
				$result = new stdClass();
				$result->result = false;
				$result->message = JText::_('COM_JKCOMMODITY_SUPPLY_SAVE_FAILED');
			
				$this->prepareRawJSON($result);
			}
		}
		else{
			$result = new stdClass();
			$result->result  = false;
			$result->message = JText::_('COM_JKCOMMODITY_INVALID_TOKEN');
		
			$this->prepareRawJSON($result);
		}
	}
}
