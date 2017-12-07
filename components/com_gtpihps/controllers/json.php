<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerJson extends GTControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function prepareJSON($data, $obj = null) {
		$json = new stdClass();
		$json->result = $data;
		$json->message = '';
		$json->status = '';

		if(is_object($obj)) {
			foreach (JArrayHelper::fromObject($obj) as $field => $value) {
				$json->$field = $value;
			}
		}

		$date = $this->input->get('date');
		if($date) {
			$holiday = GTHelperDate::isHoliday($date);

			if($holiday) {
				$json->message = sprintf(JText::_('COM_GTPIHPS_SURVEY_HOLIDAY'), $holiday);
				$json->status = 'warning';
			} elseif(JHtml::date($date, 'w') == 6) {
				$json->message = JText::_('COM_GTPIHPS_SURVEY_SATURDAY');
				$json->status = 'warning';
			} elseif(JHtml::date($date, 'w') == 0) {
				$json->message = JText::_('COM_GTPIHPS_SURVEY_SUNDAY');
				$json->status = 'warning';
			}
		}

		header('Content-type: application/json; charset=utf-8');
		$json = json_encode($json);
		$json = str_replace(':null', ':""', $json);

		echo $json;

		$this->app->close();
	}

	/* MOBILE SERVICES
	===========================================================================================*/

	public function references() {
		$model = $this->getModel();
		$provinces = $model->getProvinces();

		foreach ($provinces as $i => &$province) {
			$regencies = $model->getRegencies($province->id);
			if (!count($regencies) > 0) {
				unset($provinces[$j]);
				continue;
			}

			foreach ($regencies as $j => &$regency) {
				$markets = $model->getMarkets($regency->id);
				if (!count($markets) > 0) {
					unset($regencies[$j]);
					continue;
				}

				$regency->name = sprintf(JText::_('COM_GTPIHPS_' . strtoupper($regency->type)), $regency->name);
				$regency->markets = $markets;
				unset($regency->type);
			}

			$province->regencies = array_values($regencies);
		}

		return $this->prepareJSON(array_values($provinces));
	}

	public function provinces() {
		$model = $this->getModel();
		$this->prepareJSON($model->getProvinces());
	}

	public function commodityImages() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCommodityImages());
	}

	public function regions() {
		$model = $this->getModel();
		$regions = $model->getRegions();
		$provinces = $model->getProvinces(true);

		foreach ($regions as &$region) {
			$region->provinces = $provinces[$region->id];
		}

		$this->prepareJSON($regions);
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
		
		$obj = new stdClass();
		if($markets){
			foreach ($markets as &$market) {
				$dates[] = $market->date;
				unset($market->date);
			}
			$obj->hash = md5(max($dates));
		}
		else{
			$obj->hash = md5(false);
		}

		$this->prepareJSON($markets, $obj);
	}

	public function categories() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCategories());
	}

	public function commodities() {
		$model = $this->getModel();
		$this->prepareJSON($model->getCommodities());
	}

	public function statNationalByProvince() {
		return $this->statProvinceByRegency();
	}

	public function statNationalByCommodity($region_type = 'province') {
		// Save Init Log
		$modelLog	= $this->getModel('Service_Log');
		$postLog	= $_POST;
		$getLog		= $_GET;
		$logID		= $modelLog->saveLog($postLog, $getLog);
		
		$this->input->set('layout', 'wtw');
		$this->input->set('is_external', true);
		$this->input->set('date_count', 2);
		
		$model			= $this->getModel('Stats_Commodity');
		$modelJson 		= $this->getModel();
		$commodity		= $modelJson->getCommodity();
		$periods		= $model->getState('filter.periods');
		$startDate 		= reset($periods);
		$endDate 		= end($periods);

		switch ($region_type) {
			default:
			case 'province':
				$prices		= $model->getItemsProv();
				$regions	= $modelJson->getProvinces();
				break;
			case 'regency':
				$prices		= $model->getItemsReg();
				$regions	= $modelJson->getRegencies();
				break;
		}

		foreach ($regions as &$region) {
			$price		= @$prices[$region->id];
			$startPrice	= @$price[$startDate->unix];
			$startPrice = GTHelperCurrency::toNumber($startPrice);
			$endPrice	= @$price[$endDate->unix];
			$endPrice 	= GTHelperCurrency::toNumber($endPrice);
			$priceDiff 	= $endPrice - $startPrice;
			$isValid 	= $startPrice && $endPrice;
			$trend 		= $priceDiff == 0 ? 'still' : ($priceDiff > 0 ? 'up' : 'down');
			$trend 		= $isValid ? $trend : 'unknown';

			$item				= new stdClass();
			$item->id			= $region->id;

			switch ($region_type) {
				default:
				case 'province':
					$item->region_id	= $region->region_id;
					$item->type			= 'province';
					break;
				case 'regency':
					$item->province_id	= $region->province_id;
					$item->type			= 'city';
					break;
				case 'market':
					$item->regency_id	= $region->regency_id;
					$item->type			= 'market';
					break;
			}

			$item->name			= $region->name;
			$item->date			= $endDate->mysql;
			$item->price		= $endPrice ? GTHelperCurrency::fromNumber($endPrice, '') : '-';
			$item->diff			= $isValid ? GTHelperCurrency::fromNumber(abs($priceDiff), '') : '-';
			$item->trend		= $trend;

			$region = $item;
		}

		$json 				= new stdClass();
		$json->commodity_id	= $commodity->id;
		$json->name			= $commodity->name;
		$json->date_start	= $startDate->mysql;
		$json->date_end		= $endDate->mysql;
		$json->prices 		= $regions;

		$this->prepareJSON($json);
	}

	public function statProvinceByRegency() {
		// Save Init Log
		$modelLog	= $this->getModel('Service_Log');
		$postLog	= $_POST;
		$getLog		= $_GET;
		$logID		= $modelLog->saveLog($postLog, $getLog);

		$market_id		= $this->input->get('market_id');
		$regency_id		= $this->input->get('regency_id');
		$province_id	= $this->input->get('province_id');

		$this->input->set('is_external', true);
		$this->input->set('date_count', 2);
		
		$model			= $this->getModel('Stats_Province');
		$modelJson 		= $this->getModel();
		$comPrices		= $model->getItemsCom();
		$catPrices		= $model->getItemsCat();
		$commodities	= $model->getCommodityList(true);
		$periods		= $model->getState('filter.periods');
		$startDate 		= reset($periods);
		$endDate 		= end($periods);

		foreach ($commodities as &$commodity) {
			$price		= $commodity->type == 'commodity' ? @$comPrices[$commodity->id] : @$catPrices[$commodity->id];
			$startPrice	= @$price[$startDate->unix];
			$startPrice = GTHelperCurrency::toNumber($startPrice);
			$endPrice	= @$price[$endDate->unix];
			$endPrice 	= GTHelperCurrency::toNumber($endPrice);
			$priceDiff 	= $endPrice - $startPrice;
			$isValid 	= $startPrice && $endPrice;
			$trend 		= $priceDiff == 0 ? 'still' : ($priceDiff > 0 ? 'up' : 'down');
			$trend 		= $isValid ? $trend : 'unknown';

			$item			= new stdClass();
			$item->id		= $commodity->level ? $commodity->id : '';
			$item->name		= $commodity->name;
			$item->denom	= $commodity->denomination;
			$item->type		= $commodity->type;
			$item->level	= $commodity->level;
			$item->price	= $endPrice ? GTHelperCurrency::fromNumber($endPrice, '') : '-';
			$item->diff		= $isValid ? GTHelperCurrency::fromNumber(abs($priceDiff), '') : '-';
			$item->trend 	= $trend;

			$commodity = $item;
		}

		$json				= new stdClass();
		$json->province_id	= "0";
		$json->regency_id	= "0";
		$json->market_id	= "0";
		$json->name			= JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES');
		$json->date			= $endDate->mysql;
		$json->notification	= '';
		$json->prices		= $commodities;

		if($market_id) {
			$market				= $modelJson->getMarket();
			$json->province_id	= $market->province_id;
			$json->regency_id	= $market->regency_id;
			$json->market_id	= $market_id;
			$json->name 		= $market->name;
		} elseif($regency_id) {
			$regency			= $modelJson->getRegency();
			$json->province_id	= $regency->province_id;
			$json->regency_id	= $regency_id;
			$json->name 		= sprintf(JText::_('COM_GTPIHPS_'.strtoupper($regency->type)), trim($regency->name));
		} elseif($province_id) {
			$province			= $modelJson->getProvince();
			$json->province_id	= $province_id;
			$json->name 		= $province->name;
		}

		$this->prepareJSON($json);
	}

	public function statProvinceByCommodity() {
		// Save Init Log
		$modelLog	= $this->getModel('Service_Log');
		$postLog	= $_POST;
		$getLog		= $_GET;
		$logID		= $modelLog->saveLog($postLog, $getLog);

		$layouts 		= array('day' => 'default', 'week' => 'weekly', 'month' => 'monthly', 'year' => 'yearly');
		$layout 		= $this->input->get('type', 'day');
		$layout 		= $layouts[$layout];

		$market_id		= $this->input->get('market_id');
		$regency_id		= $this->input->get('regency_id');
		$province_id	= $this->input->get('province_id');
		$commodity_id	= $this->input->get('commodity_id', 'cat-1');

		$this->input->set('layout', $layout);
		$this->input->set('is_external', true);
		$this->input->set('date_count', 7);
		
		$model			= $this->getModel('Stats_Province');
		$modelJson 		= $this->getModel();
		$prices			= is_numeric($commodity_id) ? $model->getItemsCom() : $model->getItemsCat();
		$prices 		= reset($prices);
		$periods		= $model->getState('filter.periods');
		$startDate 		= reset($periods);
		$endDate 		= end($periods);
		$commodity 		= $modelJson->getCommodity();

		foreach ($periods as &$period) {
			$price	= $prices[$period->unix];
			$price	= $price > 0 ? $price : '-';

			$item	= new stdClass();
			$item->unix			= $period->unix;
			$item->price		= $price;
			$item->date			= $period->mysql;
			$item->long_date	= $period->ldate;
			$item->short_date	= $period->sdate;

			$period = $item;
		}

		$json 				= new stdClass();
		$json->commodity_id	= $commodity_id;
		$json->commodity	= $commodity->name.' / '.$commodity->denomination;
		$json->province_id	= "0";
		$json->regency_id	= "0";
		$json->market_id	= "0";
		$json->name			= JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES');
		$json->date 		= $startDate->mysql;
		$json->prices 		= $periods;

		if($market_id) {
			$market				= $modelJson->getMarket();
			$json->province_id	= $market->province_id;
			$json->regency_id	= $market->regency_id;
			$json->market_id	= $market_id;
			$json->name 		= $market->name;
		} elseif($regency_id) {
			$regency			= $modelJson->getRegency();
			$json->province_id	= $regency->province_id;
			$json->regency_id	= $regency_id;
			$json->name 		= sprintf(JText::_('COM_GTPIHPS_'.strtoupper($regency->type)), trim($regency->name));
		} elseif($province_id) {
			$province			= $modelJson->getProvince();
			$json->province_id	= $province_id;
			$json->name 		= $province->name;
		}

		$this->prepareJSON($json);
	}

	public function statRegencyByCommodity() {
		return $this->statNationalByCommodity('regency');
	}
	
	public function statFluctuationByCommodity() {
		// Save Init Log
		$modelLog	= $this->getModel('Service_Log');
		$postLog	= $_POST;
		$getLog		= $_GET;
		$logID		= $modelLog->saveLog($postLog, $getLog);

		$model		= $this->getModel();
		$flucs		= $this->getFluctuationByCommodity();
		$date 		= $flucs->date;
		$periods	= $flucs->periods;
		$startDate	= reset($periods);
		$endDate	= end($periods);
		$flucs 		= $flucs->prices;
		$commodity	= $model->getCommodity();
		$provinces	= $model->getProvinces();
		$values 	= array();

		$result					= new stdClass();
		$result->commodity_id	= $commodity->id;
		$result->commodity_name	= $commodity->name.' ('.$commodity->denomination.')';
		$result->data			= array();
		$result->date			= $date;

		foreach ($provinces as $province) {
			$fluc	= @$flucs[$province->id];
			$stddev	= floatval(@$fluc->stddev);
			$value = @$fluc->value;

			$ranks_info	= array(
				array('rank' => '> -'.($stddev*2).'%', 'info' => 1),
				array('rank' => '> -'.$stddev.'%', 'info' => 2),
				array('rank' => '< '.$stddev.'%', 'info' => 3),
				array('rank' => '< '.($stddev*2).'%', 'info' => 4), 
				array('rank' => '> '.($stddev*2).'%', 'info' => 5)
			);

			$item				= new stdClass();
			$item->id			= $province->id;
			$item->is_empty		= true;
			$item->name			= $province->name;
			$item->date			= $date;
			$item->dateinfo		= $endDate->sdate2.' / '.$startDate->sdate2;
			$item->value		= floatval($value);
			$item->display		= 'N/A';
			$item->displaylong	= JText::_('COM_GTPIHPS_NO_DATA');
			$item->price_prev 	= 'N/A';
			$item->price_cur 	= 'N/A';
			$item->date_prev 	= $startDate->ldate;
			$item->date_cur 	= $endDate->ldate;
			$item->stddev 		= $stddev;
			$item->rank 		= 0;
			$item->ranks_info 	= $ranks_info;

			if(is_numeric($value)) {
				$item->is_empty		= false;
				$item->display		= $fluc->display;
				$item->displaylong	= $fluc->displaylong;
				$item->price_prev	= $fluc->start_price;
				$item->price_cur	= $fluc->end_price;
				$item->rank			= $fluc->rank;
			}

			$result->data[]	= $item;
			$values[]		= $fluc->value;
		}

		$values 			= array_filter($values, 'is_numeric');
		$result->min_value	= count($values) > 0 ? min($values) : null;
		$result->max_value	= count($values) > 0 ? max($values) : null;
		
		$this->prepareJSON($result);
	}

	public function statFluctuationByProvince() {
		// Save Init Log
		$modelLog	= $this->getModel('Service_Log');
		$postLog	= $_POST;
		$getLog		= $_GET;
		$logID		= $modelLog->saveLog($postLog, $getLog);

		$model			= $this->getModel();
		$flucs			= $this->getFluctuationByRegency();
		$date			= $flucs->date;
		$periods		= $flucs->periods;
		$startDate		= reset($periods);
		$endDate		= end($periods);
		$flucs			= $flucs->prices;
		$province		= $model->getProvince();
		$commodities	= $model->getCommodities(false);
		$values			= array();

		$result					= new stdClass();
		$result->province_id	= $province->id;
		$result->province_name	= $province->name;
		$result->data			= array();
		$result->date			= $date;

		foreach ($commodities as $commodity) {
			$fluc	= @$flucs[$commodity->id];
			$stddev	= floatval(@$fluc->stddev);
			$value	= @$fluc->value;

			$ranks_info	= array(
				array('rank' => '> -'.($stddev*2).'%', 'info' => 1),
				array('rank' => '> -'.$stddev.'%', 'info' => 2),
				array('rank' => '< '.$stddev.'%', 'info' => 3),
				array('rank' => '< '.($stddev*2).'%', 'info' => 4), 
				array('rank' => '> '.($stddev*2).'%', 'info' => 5)
			);

			$item				= new stdClass();
			$item->id			= intval($commodity->id);
			$item->is_empty		= true;
			$item->name			= $commodity->name.' / '.$commodity->denomination;
			$item->date			= $date;
			$item->dateinfo		= $endDate->sdate2.' / '.$startDate->sdate2;
			$item->value		= floatval($value);
			$item->display		= 'N/A';
			$item->displaylong	= JText::_('COM_GTPIHPS_NO_DATA');
			$item->price_prev 	= 'N/A';
			$item->price_cur 	= 'N/A';
			$item->date_prev 	= $startDate->ldate;
			$item->date_cur 	= $endDate->ldate;
			$item->stddev 		= $stddev;
			$item->rank 		= 0;
			$item->ranks_info 	= $ranks_info;

			if(is_numeric($value)) {
				$item->is_empty		= false;
				$item->display		= $fluc->display;
				$item->displaylong	= $fluc->display2;
				$item->price_prev	= $fluc->start_price;
				$item->price_cur	= $fluc->end_price;
				$item->rank			= $fluc->rank;
			}

			$result->data[]	= $item;
			$values[]		= $item->value;
		}

		$values 			= array_filter($values, 'is_numeric');
		$result->min_value	= count($values) > 0 ? min($values) : 0;
		$result->max_value	= count($values) > 0 ? max($values) : 0;
		
		$this->prepareJSON($result);
	}

	public function chatroomMembers() {
		$model = $this->getModel();
		$this->prepareJSON($model->getChatroomMembers());
	}

	public function dateReference() {
		$model = $this->getModel();

		$result = new stdClass();
		$result->date = $model->getDateReference('market');
		$result->holiday = $model->getDateReference('holiday');

		$this->prepareJSON($result);
	}

	public function latestDate() {
		$model = $this->getModel('Stats_Commodity');

		$result = new stdClass();
		$result->date = $model->getLatestDate();

		$this->prepareJSON(true, $result);
	}

	public function checkVersion() {
		$curVersion = $this->input->get('version');

		// Ignore if not live
		if(!$this->isLive(false)) {
			$this->prepareJSON(true);
		}

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTMLFile('https://play.google.com/store/apps/details?id=com.gamatechno.pihpsnasional');
		$xpath = new DOMXPath($dom);
		$query = '//div[@itemprop="softwareVersion"]';
		$entry = $xpath->query($query)->item(0);
		$lastVersion = trim($entry->nodeValue);
		
		if($lastVersion && $lastVersion != $curVersion) {
			$this->prepareJSON(false);
		} else {
			$this->prepareJSON(true);
		}
	}

	public function checkVersionItunes() {
		$curVersion = $this->input->get('version');

		// Ignore if not live
		if(!$this->isLive(false)) {
			$result = new stdClass();
			$result->version = $curVersion;
			$result->releaseNotes = '';
			$this->prepareJSON(true, $result);
		}
		
		$info = file_get_contents("http://itunes.apple.com/lookup?id=1192706955");
		$info = json_decode($info);
		$info = @$info->results;
		$info = @$info[0];

		$lastVersion = @$info->version;
		$result = new stdClass();
		$result->version = $lastVersion;
		$result->releaseNotes = @$info->releaseNotes;
		if($lastVersion && $lastVersion != $curVersion) {
			$this->prepareJSON(false, $result);
		} else {
			
			$this->prepareJSON(true, $result);
		}
	}

	public function holidays() {
		$model	= $this->getModel();
		$data	= $model->getHolidays();

		if(count($data) > 0) {
			$this->prepareJSON($data);
		} else {
			$this->prepareJSON(false);
		}
	}

	public function isLive($json = true) {
		$rootUrl = JURI::root();
		$rootUrl = explode('://', $rootUrl);
		$rootUrl = end($rootUrl);
		$rootUrl = trim($rootUrl, '/');
		$rootUrl = trim($rootUrl, 'www.');

		$isLive = $rootUrl == 'hargapangan.id';

		return $json ? $this->prepareJSON(null, null, $isLive) : $isLive;
	}

	/* WEB SERVICES
	===========================================================================================*/
	public function commodityPrices() {
		$province_id	= $this->input->get('province_id', 0);
		$price_type_id	= $this->input->get('price_type_id', 1);
		$layout			= $price_type_id == 1 ? 'default' : 'wtw';

		$this->input->set('province_id', $province_id);
		$this->input->set('is_external', true);
		$this->input->set('layout', $layout);
		$this->input->set('date_count', 8);

		$model			= $this->getModel('Stats_Province');
		$prices			= $model->getItemsCom();
		$periods		= $model->getState('filter.periods');
		$commodities	= $model->getCommodities(true);
		$endDate		= array_pop($periods);
		$startDate		= array_pop($periods);
		$items 			= array();
		foreach ($commodities as &$commodity) {
			$curPrices	= (array) @$prices[$commodity->id];
			$flucs		= array(0);
			$initPrice	= reset($curPrices);
			$initPrice	= GTHelperCurrency::toNumber($initPrice);
			unset($curPrices[key($curPrices)]);
			foreach ($curPrices as $k => &$curPrice) {
				$curPrice	= GTHelperCurrency::toNumber($curPrice);
				$flucs[]	= $curPrice - $initPrice;
				$initPrice 	= $curPrice;
			}
			$startPrice	= @$curPrices[$startDate->unix];
			$endPrice	= @$curPrices[$endDate->unix];
			$isDiff 	= $startPrice && $endPrice;
			$priceDiff	= $endPrice - $startPrice;
			$priceDiff	= $isDiff ? $priceDiff : 0;
			$percent	= round(($startPrice > 0 ? $priceDiff/$startPrice : 0) * 100, 2);
			$percent	= abs($percent);
			if(!$endPrice) continue;

			$price = new stdClass();
			if(!$isDiff) {
				$price->class	= 'price_unknown';
				$price->icon	= 'fa fa-question-circle';
				$price->status	= 'Tidak Update';
			} elseif($priceDiff < 0) {
				$price->class	= 'price_down';
				$price->icon	= 'fa fa-arrow-down';
				$price->status	= 'Turun';
			} elseif($priceDiff > 0) {
				$price->class	= 'price_up';
				$price->icon	= 'fa fa-arrow-up';
				$price->status	= 'Naik';
			} elseif($priceDiff === 0) {
				$price->class	= 'price_still';
				$price->icon	= 'fa fa-pause';
				$price->status	= 'Harga Tetap';
			}
			$priceDiff	= abs($priceDiff);

			$price->title	= $commodity->name;
			$price->price	= $endPrice ? GTHelperCurrency::fromNumber($endPrice) : 'N/A';
			$price->denom	= 'Per '.$commodity->denomination;
			$price->prices	= implode(',', $flucs);
			$price->desc	= $priceDiff ? $percent.'%'.' - '.GTHelperCurrency::fromNumber($priceDiff) : $price->status;
			$price->image	= JURI::root(true) . '/images/commodities/'.$commodity->image.'.png';

			$items[$commodity->id] = $price;
		}

		$json			= new stdClass;
		$json->date		= $endDate->ldate;
		$json->dateSQL	= $endDate->mysql;
		$json->layout	= $layout;
		$json->prices	= $items;

		header('Content-Type: application/json');
		echo json_encode($json);

		$this->app->close();
	}

	public function getRegencyDetail() {
		$date			= JHtml::date($this->input->get('date', date('d-m-Y')), 'Y-m-d');
		$province_id	= $this->input->get('province_id', 1);
		$regency_id		= $this->input->get('regency_id');
		$commodity_id	= $this->input->get('commodity_id');
		$layout			= $this->input->get('layout', 'dtd');
		$layout			= $layout == 'dtd' ? 'default' : $layout;

		$this->input->set('regency_id', $regency_id);
		$this->input->set('commodity_id', $commodity_id);
		$this->input->set('is_external', true);
		$this->input->set('layout', $layout);
		$this->input->set('date', $date);
		$this->input->set('date_count', 5);

		$model				= $this->getModel('Stats_Commodity');
		$province_prices	= $model->getItemsProv();
		$regency_prices		= $model->getItemsReg();
		$market_prices		= $model->getItemsMar();

		$provinces 			= $model->getProvinces();
		$regencies 			= $model->getRegencyList();

		//echo "<pre>"; print_r($regencies); echo "</pre>"; die;
		$markets 			= $model->getMarketList();

		$downUrl	= 'index.php?option=com_gtpihps&view=stats_commodity';
		$Itemid		= GTHelper::getMenuId($downUrl);
		$downUrl	= sprintf($downUrl.'&regency_id=%s&commodity_id=%s&date=%s&layout=%s&format=xls&is_external=1&Itemid=%s', $regency_id, $commodity_id, $date, $layout, $Itemid);
		$downUrl	= JRoute::_($downUrl, false);

		switch($layout) {
			default:
				$dates = GTHelperDate::getCountPeriod(strtotime($date), 5);
				break;
			case 'wtw':
				$dates = GTHelperDate::getCountPeriod(strtotime($date), 5, 'week');
				break;
			case 'mtm':
				$dates = GTHelperDate::getCountPeriod(strtotime($date), 5, 'month');
				break;
		}

		$data = array();

		// PROVINCES
		$province 	= $provinces[$province_id];
		$regencies 	= $regencies[$province_id];
		$provPrices	= $province_prices[$province_id];

		$item = array('<strong style="font-size:1.1em">'.strtoupper($province).'</strong>');
		foreach ($dates as $date) {
			$price	= @$provPrices[$date->unix];
			$price	= $price ? '<strong style="font-size:1.1em">'.$price.'</strong>' : '<div class="text-center"><strong>-</strong></div>';
			$item[]	= $price;
		}
		$item[] = 'province';
		$item[] = $province_id;
		$data[] = $item;
		foreach ($regencies as $regency_id => $regency) {
			$regMarkets	= $markets[$regency_id];
			$regPrices	= $regency_prices[$regency_id];
			if(!count($regMarkets) > 0) continue;
			$item = array('<strong>'.$regency.'</strong>');
			foreach ($dates as $date) {
				$price	= @$regPrices[$date->unix];
				$price	= $price ? '<strong>'.$price.'</strong>' : '<div class="text-center"><strong>-</strong></div>';
				$item[]	= $price;
			}
			$item[] = 'regency';
			$item[] = $regency_id;
			$data[] = $item;

			foreach ($regMarkets as $market_id => $market) {
				$marPrices = $market_prices[$market_id];
				$item = array('<div style="padding-left:1.4em">'.$market.'</div>');
				foreach ($dates as $date) {
					$price = @$marPrices[$date->unix];
					$price = $price ? $price : '<div class="text-center">-</div>';
					$item[] = $price;
				}
				$item[] = 'market market-'.$regency_id;
				$item[] = $market->id;
				$data[] = $item;
			}
		}

		$json = new stdClass();
		$json->dates = $dates;
		$json->prices = $data;
		$json->download_url = $downUrl;

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);
		$this->app->close();
	}

	public function getData() {
		$date			= $this->input->get('date', date('d-m-Y'));
		$data_type		= $this->input->get('data_type', 'price');
		$commodity_id	= $this->input->get('commodity_id', '1');
		$price_type_id	= $this->input->get('price_type_id', '1');
		$layout			= $this->input->get('layout', 'default');

		$model	= $this->getModel();
		$data	= $data_type == 'price' ? $this->getPrices() : $this->getFluctuationByCommodity();
		$date	= $data->date;
		$prices	= $data->prices;
		$regencies	= $model->getRegencies();
		$commodity	= $model->getCommodity();

		$areas		= array();
		$regList	= array();
		$regEmpty	= array();
		$regRefs	= array();
		$regData	= array();

		foreach ($regencies as $reg) {
			$id				= $reg->id;
			$price			= @$prices[$reg->id];
			$rank 			= intval(@$price->rank);
			$area			= new stdClass();
			$area->tooltip	= new stdClass();
			$area->name		= $reg->name;
			$area->date		= @$price->date ? $price->date : $date;
			$area->reg_id	= $reg->id;
			$area->value	= $rank;

			$priceDisplays 			= explode('|', $price->price);
			$priceDisplay 			= '<div style="font-size:1.3em">'.$priceDisplays[0].'</div>';
			$priceDisplay 			.= @$priceDisplays[1] ? '<div style="font-size:1.1em">'.$priceDisplays[1].'</div>' : null;
			$area->tooltip->content	= '<strong>'.$reg->name.'</strong><br/>';
			switch ($rank) {
				case '0':
					$area->tooltip->content	.= JText::_('COM_GTPIHPS_NO_DATA');
					break;
				case '6':
					$area->tooltip->content	.= JText::_('COM_GTPIHPS_OUTDATED');
					$area->tooltip->content	.= '<br/><small>'.$price->dateinfo.'</small>';
					break;
				default:
					$area->tooltip->content	.= $priceDisplay.'<small>'.$price->displaylong.'</small>';
					$area->tooltip->content	.= '<br/><small>'.$price->dateinfo.'</small>';
					break;
			}
			
			$isPriceNum			= is_numeric($price->value);
			$regince			= new stdClass();
			$regince->value		= $isPriceNum ? $price->value : null;
			$regince->name		= $reg->name;
			$regince->display	= $price->display ? $price->display : '-';
			$regince->id		= $id;

			if($isPriceNum) {
				$regList[] = $regince;
			} else {
				$regEmpty[] = $regince;
			}
			
			$regRefs[]						= array($id, $reg->short_name, $reg->name, $price->display);
			$areas[trim($reg->short_name)]	= $area;
			$regData[$area->value][]		= array($id, $regince->value);
		}

		sort($regList);
		sort($regEmpty);
		$tableData = array_merge($regList, $regEmpty);

		$result					= new stdClass();
		$result->commodity		= trim($commodity->name);
		$result->commodity_id	= $commodity_id;
		$result->price_type_id	= $price_type_id;
		$result->layout			= $layout;
		$result->title			= sprintf(JText::_('COM_GTPIHPS_MAP_HEAD'), $result->commodity, $commodity->denomination);
		$result->areas			= $areas;
		$result->tableData		= $tableData;
		$result->regencies		= $regRefs;
		$result->regData		= $regData;
		$result->is_percent		= in_array($data_type, array('fluctuation')) ? true : false;

		//echo "<pre>"; print_r($result); echo "</pre>"; die;
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($result);
		$this->app->close();
	}

	protected function getPrices() {
		$this->input->set('is_external', true);
		$this->input->set('date_count', 2);

		$model		= $this->getModel('Stats_Commodity');
		$prices		= $model->getItemsReg();
		$periods	= $model->getState('filter.periods');
		$dateP		= end($periods);
		$date		= $dateP->unix;
		$samples	= array();

		$today		= JHtml::date('now', 'Y-m-d');
		//$isTolerate	= JHtml::date('now', 'G') <= 12 && $dateP->mysql == $today;
		$isTolerate	= false;
		
		//echo "<pre>"; print_r($periods); echo "</pre>"; die;

		foreach ($prices as $id => &$price) {
			$curDate	= array_keys($price);
			$curDate	= $isTolerate ? end($curDate) : $date;
			$price		= $isTolerate ? end($price) : $price[$date];
			$price		= GTHelperCurrency::toNumber($price);
			$priceDisplay = GTHelperCurrency::fromNumber($price);

			$row				= new stdClass();
			$row->regency_id	= $id;
			$row->date			= JHtml::date($curDate, 'Y-m-d');
			$row->value			= $price;
			$row->price 		= $priceDisplay;
			$row->rank 			= 6;
			$row->display		= $priceDisplay;
			$row->displaylong	= JText::_('COM_GTPIHPS_OUTDATED');
			$row->dateinfo		= JText::_('COM_GTPIHPS_UPDATE').' : '.JHtml::date($date, 'd M y');

			$samples[$date][$id]	= $price;
			$price					= $row;
		}

		$priceAvgs		= $model->getItemsAll();
		$priceAvgs		= reset($priceAvgs);

		foreach ($prices as &$price) {
			if(!$price->value) continue;

			$curSamples 	= array_filter((array) @$samples[strtotime($price->date)]);
			$priceMin 		= min($curSamples);
			$priceMax 		= max($curSamples);
			$priceAvg 		= $priceAvgs[strtotime($price->date)];
			$priceAvg 		= GTHelperCurrency::toNumber($priceAvg);
			$priceStdDev	= round(GTHelperNumber::stdDev($curSamples)/50)*50;

			$price->displaylong	= 'Avg: '.GTHelperCurrency::fromNumber($priceAvg).' / StdDev: '.GTHelperCurrency::fromNumber($priceStdDev);
			$price->rank		= $price->value == $priceMin ? 1 : ceil((($price->value - $priceMin) / ($priceMax - $priceMin)) * 5);
		}
		//echo "<pre>"; print_r($prices); echo "</pre>"; die;

		$result				= new stdClass();
		$result->date		= $dateP->mysql;
		$result->prices		= $prices;
		$result->periods	= $periods;

		return $result;
	}


	protected function getFluctuationByCommodity() {	
		$data_type	= $this->input->get('data_type', 'fluctuation');
	
		$commodity_id	= $this->input->get('commodity_id', 'cat-1');
		$price_type_id	= $this->input->get('price_type_id', '1');
		$layout			= $price_type_id == 1 ? 'default' : 'wtw';
		$layout			= $this->input->get('layout', $layout);

		$this->input->set('layout', $layout);
		$this->input->set('is_external', true);
		$this->input->set('date_count', 2);
		
		$model		= $this->getModel('Stats_Commodity');
		$periods	= $model->getState('filter.periods');
		$startDate	= reset($periods);
		$startDate	= $startDate->unix;
		$endDateP	= end($periods);
		$endDate	= $endDateP->unix;
		$stddevs	= $model->getStdDev();
		$prices		= $model->getItemsReg();
		$samples	= array();

		foreach ($prices as $id => &$price) {
			$date			= array_keys($price);
			
			$startPrice		= @$price[$startDate];
			$startPrice		= GTHelperCurrency::toNumber($startPrice);
			$endPrice		= @$price[$endDate];
			$endPrice		= GTHelperCurrency::toNumber($endPrice);
			$priceDiff		= $endPrice - $startPrice;
			$isUpdated 		= $startPrice && $endPrice;
			$stddev 		= $data_type == 'fluctuation2' ? @$stddevs[0] : @$stddevs[$id];
			$stddev 		= floatval(round($stddev->stddev, 2));

			$row				= new stdClass();
			$row->regency_id	= $id;
			$row->date			= $endDate;
			$row->value			= $isUpdated ? floatval(round($priceDiff / $startPrice * 100, 2)) : null;

			$stddevPrice	= round((($stddev * $startPrice)/100)/50)*50;
			$stddevPrice	= GTHelperCurrency::fromNumber($stddevPrice);
			$startPrice		= GTHelperCurrency::fromNumber(floatval($startPrice));
			$endPrice		= GTHelperCurrency::fromNumber(floatval($endPrice));
			$priceDiff		= GTHelperCurrency::fromNumber($priceDiff);

			$row->price 		= $endPrice.'|('.$priceDiff.' / '.$row->value.'%)';
			$row->display		= $isUpdated ? $row->value.'%' : null;
			$row->dateinfo		= JHtml::date($endDate, 'd M').' / '. JHtml::date($startDate, 'd M');
			$row->stddev 		= $stddev;			
			$row->start_price	= $startPrice;
			$row->end_price		= $endPrice;
			$row->date			= JHtml::date($endDate, 'Y-m-d');
			$row->display2		= $endPrice.'<br/>('.$row->value.'%)';
			$row->display2		= $isUpdated ? $row->display2 : JText::_('COM_GTPIHPS_OUTDATED');
			$row->displaylong	= 'StdDev: '.$stddevPrice.' / '.$stddev.'%';
			$row->displaylong	= $isUpdated ? $row->displaylong : JText::_('COM_GTPIHPS_OUTDATED');
			
			if(!$isUpdated) {
				$row->rank = 6;
			} elseif($row->value < ($stddev * -1)) {
				$row->rank = 1;
			} elseif($row->value < 0) {
				$row->rank = 2;
			} elseif($row->value <= $stddev) {
				$row->rank = 3;
			} elseif($row->value < ($stddev * 2)) {
				$row->rank = 4;
			} else {
				$row->rank = 5;
			}

			$price = $row;
		}
		//echo "<pre>"; print_r($prices); echo "</pre>"; die;

		$result				= new stdClass();
		$result->date		= $endDateP->mysql;
		$result->prices		= $prices;
		$result->periods	= $periods;

		return $result;
	}

	protected function getFluctuationByRegency() {	
		$data_type	= $this->input->get('data_type', 'fluctuation');
	
		$regency_id		= $this->input->get('regency_id', '1');
		$price_type_id	= $this->input->get('price_type_id', '1');
		$layout			= $price_type_id == 1 ? 'default' : 'wtw';
		$layout			= $this->input->get('layout', $layout);

		$this->input->set('layout', $layout);
		$this->input->set('is_external', true);
		$this->input->set('date_count', 2);
		
		$model		= $this->getModel('Stats_Province');
		$periods	= $model->getState('filter.periods');
		$startDate	= reset($periods);
		$startDate	= $startDate->unix;
		$endDateP	= end($periods);
		$endDate	= $endDateP->unix;
		$stddevs	= $model->getStdDev();
		$prices		= $model->getItemsCom();
		$samples	= array();

		foreach ($prices as $id => &$price) {
			$date			= array_keys($price);
			
			$startPrice		= @$price[$startDate];
			$startPrice		= GTHelperCurrency::toNumber($startPrice);
			$endPrice		= @$price[$endDate];
			$endPrice		= GTHelperCurrency::toNumber($endPrice);
			$priceDiff		= $endPrice - $startPrice;
			$isUpdated 		= $startPrice && $endPrice;
			$stddev 		= $data_type == 'fluctuation2' ? @$stddevs[0] : @$stddevs[$id];
			$stddev 		= floatval(round($stddev->stddev, 2));

			$row				= new stdClass();
			$row->commodity_id	= $id;
			$row->date			= $endDate;
			$row->value			= $isUpdated ? floatval(round($priceDiff / $startPrice * 100, 2)) : null;
			$row->display		= $isUpdated ? $row->value.'%' : null;
			$row->dateinfo		= JHtml::date($endDate, 'd M').' / '. JHtml::date($startDate, 'd M');
			$row->stddev 		= $stddev;

			$priceDiff		= GTHelperCurrency::fromNumber($priceDiff);
			$stddevPrice	= round((($stddev * $startPrice)/100)/50)*50;
			$stddevPrice	= GTHelperCurrency::fromNumber($stddevPrice);
			$startPrice		= GTHelperCurrency::fromNumber(floatval($startPrice));
			$endPrice		= GTHelperCurrency::fromNumber(floatval($endPrice));
			
			$row->start_price	= $startPrice;
			$row->end_price		= $endPrice;
			$row->date			= JHtml::date($endDate, 'Y-m-d');
			$row->display2		= $endPrice.'<br/>('.$row->value.'%)';
			$row->display2		= $isUpdated ? $row->display2 : JText::_('COM_GTPIHPS_OUTDATED');
			$row->displaylong	= $endPrice.'<br/>('.$priceDiff.' / '.$row->value.'%)<br/> StdDev: '.$stddevPrice.' / '.$stddev.'%';
			$row->displaylong	= $isUpdated ? $row->displaylong : JText::_('COM_GTPIHPS_OUTDATED');
			
			if(!$isUpdated) {
				$row->rank = 6;
			} elseif($row->value < ($stddev * -1)) {
				$row->rank = 1;
			} elseif($row->value < 0) {
				$row->rank = 2;
			} elseif($row->value <= $stddev) {
				$row->rank = 3;
			} elseif($row->value < ($stddev * 2)) {
				$row->rank = 4;
			} else {
				$row->rank = 5;
			}

			$price = $row;
		}
		//echo "<pre>"; print_r($prices); echo "</pre>"; die;

		$result				= new stdClass();
		$result->date		= $endDateP->mysql;
		$result->prices		= $prices;
		$result->periods	= $periods;

		return $result;
	}
}
