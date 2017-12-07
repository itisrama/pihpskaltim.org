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
			} else if(JHtml::date($date, 'w') == 6) {
				$json->message = JText::_('COM_GTPIHPS_SURVEY_SATURDAY');
				$json->status = 'warning';
			} else if(JHtml::date($date, 'w') == 0) {
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
		$model = $this->getModel();
		$this->prepareJSON($model->getStatNationalByProvince());
	}

	public function statNationalByCommodity() {
		$model = $this->getModel();
		$this->prepareJSON($model->getStatNationalByCommodity());
	}

	public function statProvinceByRegency() {
		$model = $this->getModel();
		$this->prepareJSON($model->getStatProvinceByRegency());
	}

	public function statProvinceByCommodity() {
		$model = $this->getModel();
		$this->prepareJSON($model->getStatProvinceByCommodity());
	}

	public function statFluctuationByCommodity() {
		$model		= $this->getModel('Map');
		$flucs		= $model->getFluctuation(true);
		$commodity	= $model->getCommodity();
		$values 	= array();
		
		$result					= new stdClass();
		$result->commodity_id	= $commodity->id;
		$result->commodity_name	= $commodity->name.' ('.$commodity->denomination.')';
		$result->data			= array();
		foreach ($flucs as $fluc) {
			$result->data[]	= $fluc;
			$values[]		= $fluc->value;
		}
		$result->min_value	= count($values) > 0 ? min($values) : null;
		$result->max_value	= count($values) > 0 ? max($values) : null;

		$this->prepareJSON($result);
	}

	public function statFluctuationByProvince() {
		$model		= $this->getModel('Map');
		$flucs		= $model->getFluctuationByProvince();
		$province	= $model->getProvince();
		$values 	= array();
		
		$result					= new stdClass();
		$result->province_id	= $province->id;
		$result->province_name	= $province->name;
		$result->data			= array();
		foreach ($flucs as $fluc) {
			$result->data[]	= $fluc;
			$values[]		= $fluc->value;
		}
		$result->min_value	= count($values) > 0 ? min($values) : null;
		$result->max_value	= count($values) > 0 ? max($values) : null;
		
		$this->prepareJSON($result);
	}

	public function chatroomMembers() {
		$model = $this->getModel();
		$this->prepareJSON($model->getChatroomMembers());
	}

	public function statRegencyByCommodity() {
		$model = $this->getModel();
		$this->prepareJSON($model->getStatRegencyByCommodity());
	}

	public function commodityPrices() {
		$model	= $this->getModel();
		$data	= $model->getCommodityPrices();

		header('Content-Type: application/json');
		echo json_encode($data);

		$this->app->close();
	}

	public function dateReference() {
		$model = $this->getModel();

		$result = new stdClass();
		$result->date = $model->getDateReference('market');
		$result->holiday = $model->getDateReference('holiday');

		$this->prepareJSON($result);
	}

	public function checkVersion() {
		$curVersion = $this->input->get('version');

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTMLFile('https://play.google.com/store/apps/details?id=com.gamatechno.pihpsional');
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

	protected function isAPI() {
		$host = $_SERVER['SERVER_NAME'];
		return !in_array($host, array('hargapangan.id', 'www.hargapangan.id'));
	}

	public function getIntegrationPrices() {
		header('Content-type: application/json; charset=utf-8');

		if(!$this->isAPI()) {
			echo json_encode(array('return' => false));
			$this->app->close();
		}

		$model		= $this->getModel();
		$prices		= $model->getIntegrationPrices();

		echo json_encode($prices);
		$this->app->close();
	}

	public function getIntegrationMarkets() {
		header('Content-type: application/json; charset=utf-8');

		if(!$this->isAPI()) {
			echo json_encode(array('return' => false));
			$this->app->close();
		}
		
		$model		= $this->getModel();
		$markets	= $model->getIntegrationMarkets();

		echo json_encode($markets);
		$this->app->close();
	}

	public function getIntegrationCommodities() {
		header('Content-type: application/json; charset=utf-8');

		if(!$this->isAPI()) {
			echo json_encode(array('return' => false));
			$this->app->close();
		}
		
		$model		= $this->getModel();
		$markets	= $model->getIntegrationCommodities();

		echo json_encode($markets);
		$this->app->close();
	}
}
