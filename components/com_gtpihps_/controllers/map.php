<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerMap extends GTControllerForm{

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = 'Map', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function getDefaultCommodityID() {
		$model = $this->getModel();
		$commodity_id = $model->getDefaultCommodityID();

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($commodity_id);
		$this->app->close();
	}
	public function getRegencyDetail() {
		$model			= $this->getModel();
		$date			= JHtml::date($this->input->get('date', date('d-m-Y')), 'Y-m-d');
		$regency_id	= $this->input->get('regency_id');
		$commodity_id	= $this->input->get('commodity_id');

		$downUrl	= 'index.php?option=com_gtpihps&view=regency_statistics';
		$Itemid		= GTHelper::getMenuId($downUrl);
		$downUrl	= sprintf($downUrl.'&regency_id=%s&commodity_id=%s&date=%s&layout=default&format=xls&Itemid=%s', $regency_id, $commodity_id, $date, $Itemid);
		$downUrl	= JRoute::_($downUrl, false);

		$dates	= GTHelperDate::getDayPeriod2(strtotime($date), 5);
		$start	= reset($dates);
		$end	= end($dates);

		$this->input->set('start_date', $start->mysql);
		$this->input->set('end_date', $end->mysql);

		$regencies	= $model->getRegencyPrices();
		$regencies	= $model->getRegencyPrices();
		
		$data = array();

		if($regency_id > 0) {
			$regency 	= $regencies[$regency_id];
			$regencies 	= $regencies[$regency_id];
			$markets	= $model->getMarketPrices();

			$regPrices	= $regency->prices;
			$item = array('<strong style="font-size:1.1em">'.strtoupper($regency->name).'</strong>');
			foreach ($dates as $date) {
				$price	= @$regPrices[$date->mysql];
				$price	= $price ? '<strong style="font-size:1.1em">'.GTHelperCurrency::fromNumber($price, '').'</strong>' : '<div class="text-center"><strong>-</strong></div>';
				$item[]	= $price;
			}
			$item[] = 'regency';
			$item[] = $regency->id;
			$data[] = $item;
			foreach ($regencies as $regency) {
				$regMarkets	= $markets[$regency->id];
				$regPrices	= $regency->prices;
				if(!count($regMarkets) > 0) continue;
				$item = array('<strong>'.$regency->name.'</strong>');
				foreach ($dates as $date) {
					$price	= @$regPrices[$date->mysql];
					$price	= $price ? '<strong>'.GTHelperCurrency::fromNumber($price, '').'</strong>' : '<div class="text-center"><strong>-</strong></div>';
					$item[]	= $price;
				}
				$item[] = 'regency';
				$item[] = $regency->id;
				$data[] = $item;

				foreach ($regMarkets as $market) {
					$marPrices = $market->prices;
					$item = array('<div style="padding-left:1.4em">'.$market->name.'</div>');
					foreach ($dates as $date) {
						$price = @$marPrices[$date->mysql];
						$price = $price ? GTHelperCurrency::fromNumber($price, '') : '<div class="text-center">-</div>';
						$item[] = $price;
					}
					$item[] = 'market market-'.$regency->id;
					$item[] = $market->id;
					$data[] = $item;
				}
			}
		} else {
			$regencies = $model->getRegencyPrices();
			$allPrices = $model->getAllPrices();

			$item = array('<strong style="font-size:1.1em">'.strtoupper(JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES')).'</strong>');
			foreach ($dates as $date) {
				$price	= @$allPrices[$date->mysql];
				$price	= $price ? '<strong style="font-size:1.1em">'.GTHelperCurrency::fromNumber($price, '').'</strong>' : '<div class="text-center"><strong>-</strong></div>';
				$item[]	= $price;
			}
			$item[] = 'regency';
			$item[] = 0;
			$data[] = $item;
			foreach ($regencies as $regency) {
				$regRegencies	= $regencies[$regency->id];
				$regPrices	= $regency->prices;
				if(!count($regRegencies) > 0) continue;
				$item = array('<strong>'.$regency->name.'</strong>');
				foreach ($dates as $date) {
					$price	= @$regPrices[$date->mysql];
					$price	= $price ? '<strong>'.GTHelperCurrency::fromNumber($price, '').'</strong>' : '<div class="text-center"><strong>-</strong></div>';
					$item[]	= $price;
				}
				$item[] = 'regency';
				$item[] = $regency->id;
				$data[] = $item;
				
				foreach ($regRegencies as $regency) {
					$marPrices = $regency->prices;
					$item = array('<div style="padding-left:1.4em">'.$regency->name.'</div>');
					foreach ($dates as $date) {
						$price = @$marPrices[$date->mysql];
						$price = $price ? GTHelperCurrency::fromNumber($price, '') : '<div class="text-center">-</div>';
						$item[] = $price;
					}
					$item[] = 'market market-'.$regency->id;
					$item[] = $regency->id;
					$data[] = $item;
				}
			}			
		}
		
		//echo "<pre>"; print_r($data); echo "</pre>";
		
		$json = new stdClass();
		$json->dates = $dates;
		$json->prices = $data;
		$json->download_url = $downUrl;

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);
		$this->app->close();
	}

	public function getData() {
		
		$date		= $this->input->get('date', date('d-m-Y'));
		$data_type 	= $this->input->get('data_type', 'price');;
		
		$model		= $this->getModel();

		switch ($data_type) {
			case 'price':
				$data = $model->getPrice();
				break;
			default:
				$data = $model->getFluctuation();
				break;
		}
		
		$regencies	= $model->getRegencies();
		$commodity	= $model->getCommodity();

		$areas		= array();
		$regList	= array();
		$regEmpty	= array();
		$regRefs	= array();
		$regData	= array();

		foreach ($regencies as $reg) {
			$item			= $data[$reg->id];
			$area			= new stdClass();
			$area->tooltip	= new stdClass();
			$area->name 	= $reg->name;
			$area->date 	= $item->date;
			$area->reg_id 	= $reg->id;
			$area->value	= floatval($item->rank);
			$area->tooltip->content	= '<strong>'.$reg->name.'</strong><br/>';
			
			switch($item->rank) {
				case 0:
					$area->tooltip->content	.= JText::_('COM_GTPIHPS_NO_DATA');
					break;
				case 6:
					$area->tooltip->content	.= JText::_('COM_GTPIHPS_OUTDATED');
					break;
				default:
					//$area->tooltip->content	.= $item->displaylong.'<br/>';
					$area->tooltip->content	.= $item->dateinfo;
					break;
			}
			
			$areas[trim($reg->short_name)] = $area;

			$regency			= new stdClass();
			$regency->value	= is_numeric($item->value) ? $item->value : null;
			$regency->display	= $item->display ? $item->display : '-';
			$regency->name		= $reg->name;
			$regency->id		= $reg->id;

			if(!is_null($regency->value)) {
				$regList[] = $regency;
			} else {
				$regEmpty[] = $regency;
			}

			$id = intval($reg->id);
			$regRefs[] = array($id, $reg->short_name, $reg->name);

			$regData[$area->value][] = array($id, $regency->value);
		}

		sort($regList);
		sort($regEmpty);
		$tableData = array_merge($regList, $regEmpty);

		$result					= new stdClass();
		$result->commodity		= trim($commodity->name);
		$result->commodity_id	= $commodity->id;
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

}
