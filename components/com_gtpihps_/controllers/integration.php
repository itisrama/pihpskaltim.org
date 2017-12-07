<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerIntegration extends GTControllerAdmin {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name, $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function getCurlJSON($url) {
		$proxy = '';
		$proxyauth = '';
		//$proxy = 'inetgw-proxy:8080';
		//$proxyauth = 'user:password';

		$user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

		$options = array(
			CURLOPT_CUSTOMREQUEST	=>"GET",        //set request type post or get
			CURLOPT_POST			=>false,        //set to GET
			CURLOPT_USERAGENT		=> $user_agent, //set user agent
			CURLOPT_COOKIEFILE		=>"cookie.txt", //set cookie file
			CURLOPT_COOKIEJAR		=>"cookie.txt", //set cookie jar
			CURLOPT_RETURNTRANSFER	=> true,     // return web page
			CURLOPT_HEADER			=> false,    // don't return headers
			CURLOPT_FOLLOWLOCATION	=> true,     // follow redirects
			CURLOPT_ENCODING		=> "",       // handle all encodings
			CURLOPT_AUTOREFERER		=> true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT	=> 120,      // timeout on connect
			CURLOPT_TIMEOUT			=> 120,      // timeout on response
			CURLOPT_MAXREDIRS		=> 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYPEER	=> false
		);

		if($proxy) {
			$options[CURLOPT_PROXY] = $proxy;
		}
		if($proxy && $proxyauth) {
			$options[CURLOPT_PROXYUSERPWD] = $proxyauth;
		}
		
		$ch			= curl_init($url);
		curl_setopt_array($ch, $options);
		$content	= curl_exec($ch);
		$err		= curl_errno($ch);
		$errmsg		= curl_error($ch);
		curl_close( $ch );

		return $error > 0 ? false : $content;
	}

	public function json($type, $date = null, $province_id = null, $json = true) {
		$model			= $this->getModel('Integration');
		$date			= $date ? $date : $this->input->get('date');
		$date 			= JHtml::date($date, 'Y-m-d');
		$province_id	= $province_id ? $province_id : $this->input->get('province_id');
		$province		= $model->getProvince($province_id);

		$weekofMonth	= GTHelperDate::weekOfMonth($date);
		$dateAlt 		= JHtml::date($date, 'Ym'.$weekofMonth);
		
		$types = array(
			'dotnet' => sprintf('https://www.bi.go.id/surveitest/svc/PIHPS.svc/json/marketprice?province_id=%s&period=%s', trim($province->source_id), $date),
			'dotnet2' => sprintf('https://www.bi.go.id/surveitest/svc/pihps.svc/json/sphmarketprice?province_id=%s&period=%s', trim($province->source_id2), $dateAlt),
			'mobile' => sprintf('http://survey.hargapangan.id/?option=com_gtpihpssurvey&task=service.getIntegrationPrices&province_id=%s&period=%s', trim($province->source_id), $date)
		);

		$url	= @$types[$type];
		$data 	= $this->getCurlJSON($url);
		$data	= json_decode($data);

		if($json) {
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($data);
			$this->app->close();
		} else {
			$return			= new stdClass();
			$return->url	= $url;
			$return->data	= $data;
			return $return;
		}		
	}


	public function importMarkets($close = true) {
		$model			= $this->getModel('Integration');
		$provinces		= $model->getProvinces();
		$regencies		= $model->getRegencies(null, null);
		$markets		= $model->getMarkets(null, null);

		$markets1 = array();
		$markets2 = array();
		$markets3 = array();

		foreach ($markets as $market) {
			$source_id	= trim($market->source_id);
			$source_id2	= trim($market->source_id2);
			$source_id3	= trim($market->source_id3);

			if($source_id) {
				$markets1[$source_id] = $market;
			}
			if($source_id2) {
				$markets2[$source_id2] = $market;
			}
			if($source_id3) {
				$markets3[$source_id3] = $market;
			}
		}
		$regencies1 = array();
		$regencies2 = array();
		$regencies3 = array();

		foreach ($regencies as $regency) {
			$source_id	= trim($regency->source_id);
			$source_id2	= trim($regency->source_id2);
			$source_id3	= trim($regency->source_id3);
			
			if($source_id) {
				$regencies1[$source_id] = $regency;
			}
			if($source_id2) {
				$regencies2[$source_id2] = $regency;
			}
			if($source_id3) {
				$regencies3[$source_id3] = $regency;
			}
		}
		//echo "<pre>"; print_r($regencies); echo "</pre>"; die;
		$dataMarkets = array();

		foreach ($provinces as $province) {
			$urlMarkets1	= 'https://www.bi.go.id/surveitest/svc/PIHPS.svc/json/market?province_id='.$province->source_id;
			$dataMarkets1	= @file_get_contents($urlMarkets1, 0, null, null);
			$dataMarkets1	= json_decode($dataMarkets1);

			if(is_array($dataMarkets1)) {
				foreach ($dataMarkets1 as $dataMarket) {
					$dataMarket->source_type = 1;
					$dataMarket->province_id = $province->id;
					$dataMarkets[$dataMarket->market_id.'-1-'.$dataMarket->region_id] = $dataMarket;
				}
			}
			
			$urlMarkets2	= 'https://www.bi.go.id/surveitest/svc/pihps.svc/json/sphmarket?province_id='.intval($province->source_id2);
			$dataMarkets2	= @file_get_contents($urlMarkets2, 0, null, null);
			$dataMarkets2	= json_decode($dataMarkets2);

			if(is_array($dataMarkets2)) {
				foreach ($dataMarkets2 as $dataMarket) {
					$dataMarket->source_type = 2;
					$dataMarket->province_id = $province->id;
					$dataMarkets[$dataMarket->market_id.'-2-'.$dataMarket->region_id] = $dataMarket;
				}
			}
			
			$urlMarkets3	= 'http://survey.hargapangan.id/?option=com_gtpihpssurvey&task=service.getIntegrationMarkets&province_id='.$province->source_id;
			$dataMarkets3	= @file_get_contents($urlMarkets3, 0, null, null);
			$dataMarkets3	= json_decode($dataMarkets3);

			if(is_array($dataMarkets3)) {
				foreach ($dataMarkets3 as $dataMarket) {
					$dataMarket->source_type = 3;
					$dataMarket->province_id = $province->id;
					$dataMarkets[$dataMarket->market_id.'-3-'.$dataMarket->region_id] = $dataMarket;
				}
			}
			
		}
		//echo "<pre>"; print_r($dataMarkets); echo "</pre>"; die;
		$regencyItems	= array();
		$marketItems	= array();
		$regencyItems2	= array();
		$marketItems2	= array();

		foreach ($dataMarkets as &$dataMarket) {
			switch($dataMarket->source_type) {
				case 1:
					$market		= @$markets1[$dataMarket->market_id];
					$regency	= @$regencies1[$dataMarket->region_id];
					break;
				case 2:
					$market		= @$markets2[$dataMarket->market_id];
					$regency	= @$regencies2[$dataMarket->region_id];
					break;
				case 3:
					$market		= @$markets3[$dataMarket->market_id];
					$regency	= @$regencies3[$dataMarket->region_id];
					break;

			}

			$regency_id 	= @$regency->id;
			
			if(@$dataMarket->price_type_id > 0) {
				$price_type_id	= $dataMarket->price_type_id;
			} else {
				$type_ids		= array('T' => 1, 'M' => 2, 'I' => 3);
				$price_type_id	= substr($dataMarket->market_id, 0, 1);
				$price_type_id	= @$type_ids[$price_type_id];
			}
			
			$regDesc = $dataMarket->region_desc;
			$regName = $dataMarket->region_desc;
			$regName = explode(' ', $regName);
			$regType = array_shift($regName);
			$regType = strtolower(preg_replace("/[^A-Za-z0-9?!]/",'',$regType));
			$regName = implode(' ', $regName);
			if(!in_array($regType, array('kota', 'kab'))) {
				$regName = $regDesc;
				$regDesc = 'Kota '.$regName;
				$regType = 'kota';
			}
			$regDesc = @$regency->id > 0 ? $regency->long_name : $regDesc;
			$regName = @$regency->id > 0 ? $regency->name : $regName;

			$regCode = preg_replace("/[^A-Za-z0-9?!]/",'',$dataMarket->region_desc);
			$regCode = $dataMarket->province_id.':'.strtolower($regCode);

			$regencyItem				= @$regencyItems[$regCode];
			$regencyItem				= is_object($regencyItem) ? $regencyItem : new stdClass();
			$regencyItem->id			= intval(@$regency->id);
			$regencyItem->source_id		= @$regencyItem->source_id;
			$regencyItem->source_id2	= @$regencyItem->source_id2;
			$regencyItem->source_id3	= @$regencyItem->source_id3;
			$regencyItem->province_id	= $dataMarket->province_id;
			$regencyItem->name			= $regName;
			$regencyItem->type			= $regType == 'kota' ? 'city' : 'regency';
			$regencyItem->long_name		= $regDesc;
			$regencyItem->published		= 1;

			$market_name	= ucwords(strtolower($dataMarket->market_desc));
			$marketCode		= preg_replace("/[^A-Za-z0-9?!]/",'',$market_name);
			$marketCode		= $regCode.':'.strtolower($marketCode);

			if(@$market->name == $market_name && @$market->regency_id == @$regency->id) {
				unset($markets[$market->id]);
				continue;
			}

			$marketItem					= @$marketItems[$marketCode];
			$marketItem					= is_object($marketItem) ? $marketItem : new stdClass();
			$marketItem->id				= intval(@$market->id);
			$marketItem->price_type_id	= $price_type_id;
			$marketItem->source_id		= @$marketItem->source_id;
			$marketItem->source_id2		= @$marketItem->source_id2;
			$marketItem->source_id3		= @$marketItem->source_id3;
			$marketItem->province_id	= $dataMarket->province_id;
			$marketItem->regency_id		= $dataMarket->source_type.'-'.$dataMarket->region_id;
			$marketItem->name			= $market_name;
			$marketItem->short_name		= str_replace(JText::_('COM_GTPIHPS_FIELD_MARKET').' ', '', $marketItem->name);
			$marketItem->published		= 1;

			switch($dataMarket->source_type) {
				case 1:
					$marketItem->source_id		= $dataMarket->market_id;
					$regencyItem->source_id		= $dataMarket->region_id;
					break;
				case 2:
					$marketItem->source_id2		= $dataMarket->market_id;
					$regencyItem->source_id2	= $dataMarket->region_id;
					break;
				case 3:
					$marketItem->source_id3		= $dataMarket->market_id;
					$regencyItem->source_id3	= $dataMarket->region_id;
					break;
			}

			$marketItems[$marketCode]	= $marketItem;
			$regencyItems[$regCode]		= $regencyItem;
			
			unset($markets[@$market->id]);
			unset($regencies[@$regency->id]);
		}

		//echo "<pre>"; print_r($marketItems); echo "</pre>";
		//echo "<pre>"; print_r($regencyItems); echo "</pre>";
		//die;
		
		// Save Regencies
		$model->saveBulk($regencyItems, 'regency');

		$regencies	= $model->getRegencies(null, null);
		$regencies1	= array();
		$regencies2	= array();
		$regencies3	= array();

		foreach ($regencies as $regency) {
			$source_id	= trim($regency->source_id);
			$source_id2	= trim($regency->source_id2);
			$source_id3	= trim($regency->source_id3);
			
			if($source_id) {
				$regencies1[$source_id] = $regency;
			}
			if($source_id2) {
				$regencies2[$source_id2] = $regency;
			}
			if($source_id3) {
				$regencies3[$source_id3] = $regency;
			}
		}

		foreach ($marketItems as &$marketItem) {
			list($sourceType, $regionID) = explode('-', $marketItem->regency_id);

			switch($sourceType) {
				case 1:
					$regency	= @$regencies1[$regionID];
					break;
				case 2:
					$regency	= @$regencies2[$regionID];
					break;
				case 3:
					$regency	= @$regencies3[$regionID];
					break;
			}

			$marketItem->regency_id = $regency->id;
		}

		// Save Markets
		$model->saveBulk($marketItems, 'market');

		if($close) {
			$this->app->close();
		}
	}

	public function updateReferences() {
		$model = $this->getModel('Integration');

		$model->updateProvinces(false);
		$model->updateProvinces(true);

		$model->updateRegencies(false);
		$model->updateRegencies(true);

		$model->updateMarkets(false);
		$model->updateMarkets(true);

		$this->app->close();
	}

	public function test() {
		$this->json('dotnet2', '2017-04-01', 13, false);
	}

	public function import($date = null, $province_id = null, $record = true, $type = 'all') {
		$province_id	= $province_id ? $province_id : $this->input->get('province_id');
		$date			= $date ? $date : $this->input->get('date');
		$holiday		= GTHelperDate::isHoliday($date);
		$result			= new stdClass();

		if($holiday) {
			$result->message = sprintf(JText::_('COM_GTPIHPS_INTEGRATION_HOLIDAY'), $holiday);
			$result->status = 'warning';

			if($record) {
				$this->saveLog($result, $province_id, $date, null);
			}
			
			return $result;
		}
		if(JHtml::date($date, 'w') == 6) {
			$result->message = JText::_('COM_GTPIHPS_INTEGRATION_SATURDAY');
			$result->status = 'warning';

			if($record) {
				$this->saveLog($result, $province_id, $date, null);
			}

			return $result;
		}
		if(JHtml::date($date, 'w') == 0) {
			$result->message = JText::_('COM_GTPIHPS_INTEGRATION_SUNDAY');
			$result->status = 'warning';

			if($record) {
				$this->saveLog($result, $province_id, $date, null);
			}

			return $result;
		}

		$model			= $this->getModel('Integration');
		$commodities 	= $model->getCommodities();
		$markets 		= $model->getMarkets($province_id, null);
		$regencies 		= $model->getRegencies($province_id, null);
		$masterIDs 		= $model->getMasterIDs($province_id, $date);
		$detailIDs 		= $model->getDetailIDs($province_id, $date);

		$markets1 = array();
		$markets2 = array();
		$markets3 = array();

		foreach ($markets as $market) {
			$source_id	= trim($market->source_id);
			$source_id2	= trim($market->source_id2);
			$source_id3	= trim($market->source_id3);

			if($source_id) {
				$markets1[$source_id] = $market;
			}
			if($source_id2) {
				$markets2[$source_id2] = $market;
			}
			if($source_id3) {
				$markets3[$source_id3] = $market;
			}
		}

		$commodities1 = array();
		$commodities2 = array();

		foreach ($commodities as $commodity) {
			if($commodity->source_id) {
				$commodities1[$commodity->source_id] = $commodity;
			}
			if($commodity->source_id2) {
				$commodities2[$commodity->source_id2] = $commodity;
			}
		}

		$items = array(
			'dotnet' => $this->json('dotnet', $date, $province_id, false),
			'dotnet2' => $this->json('dotnet2', $date, $province_id, false),
			'mobile' => $this->json('mobile', $date, $province_id, false)
		);
		//echo "<pre>"; print_r($items); echo "</pre>"; die;

		$notif_markets	= array();
		$masters		= array();
		$details		= array();
		$urls			= array();
		
		foreach ($items as $itemType => &$item) {
			if(!is_array(@$item->data)) {
				continue;
			}
			foreach ($item->data as $master) {
				if(!count($master->details)) continue;

				switch($itemType) {
					case 'dotnet':
						$market		= @$markets1[$master->market_id];
						break;
					case 'dotnet2':
						$market		= @$markets2[$master->market_id];
						break;
					case 'mobile':
						$market		= @$markets3[$master->market_id];
						break;
				}

				$regency 			= @$regencies[$market->regency_id];
				$master->id 		= intval(@$masterIDs[$market->id]);
				$master->date 		= $date;

				if(@$master->validated) {
					$master->created	= $master->validated;
					unset($master->validated);
				}

				if(!$market) continue;

				$master->source_type	= $itemType;
				$master->province_id	= $province_id;
				$master->regency_id		= $market->regency_id;
				$master->market_id		= $market->id;
				$count_detail 		= 0;
				foreach ($master->details as $rawDetail) {
					
					$commodity_id = @$rawDetail->commodity_id.'-'.@$rawDetail->quality_id;
					$commodity = $itemType == 'dotnet2' ? @$commodities2[$commodity_id] : @$commodities1[$commodity_id];

					if(!$commodity->id) continue;
					if(!$rawDetail->price > 0) continue;

					$detail					= new stdClass();
					$detail->id				= intval(@$detailIDs[$master->id][$commodity_id]);
					$detail->price_id		= 0;
					$detail->commodity_id	= $commodity->id;
					$detail->market_id		= $market->id;
					$detail->price			= $rawDetail->price;

					$details[$market->id.'-'.$commodity->id] = $detail;
					
					unset($detailIDs[$master->id][$commodity_id]);
					$count_detail++;
				}
				unset($master->details);
				unset($masterIDs[$market->id]);

				$masters[$market->id]		= $master;
				$notif_markets[$market->id]	= sprintf(JText::_('COM_GTPIHPS_IMPORT_SUCCESS_COMMODITY'), $regency->long_name.' - '.$market->name, $count_detail);
				$urls[$market->id]			= $item->url;
			}
			
		}
		if(count($notif_markets) > 0) {
			$masterTable = 'prices';
			$detailTable = 'price_details';

			// Save Master
			$model->saveBulk($masters, $masterTable);

			$newMasterIDs = $model->getMasterIDs($province_id, $date);
			foreach ($details as $k => &$detail) {
				$detail->price_id = @$newMasterIDs[$detail->market_id];
				unset($detail->market_id);
				if(!$detail->price_id) {
					unset($details[$k]);
				}
			}
			// Save Detail
			$model->saveBulk($details, $detailTable, false);
			// Delete remainders
			if(count($masterIDs) > 0) {
				$model->deleteExternal($masterIDs, $masterTable);
			}
			if(count($detailIDs) > 0) {
				$deldetailIDs = array();
				foreach ($detailIDs as $detailSubIDs) {
					$deldetailIDs = array_merge($deldetailIDs, $detailSubIDs);
				}
				$model->deleteExternal($deldetailIDs, $detailTable);
			}
			sort($notif_markets);
			$result->message	= sprintf(JText::_('COM_GTPIHPS_IMPORT_SUCCESS'), JHtml::date($date, 'j F Y'), implode('', $notif_markets));
			$result->status		= 'success';
		} else {
			$result->message	= JText::_('COM_GTPIHPS_INTEGRATION_JSON_EMPTY');
			$result->status		= 'warning';
		}
		
		if($record || $result->status == 'success') {
			$urls = implode(PHP_EOL, array_unique($urls));
			$this->saveLog($result, $province_id, $date, $urls);
		}

		return $result;
	}

	protected function saveLog($logdata, $province_id, $date, $url) {
		$model = $this->getModel('Integration');

		$log				= new stdClass();
		$log->id			= 0;
		$log->province_id	= $province_id;
		$log->date			= $date;
		$log->url			= $url;
		$log->type			= 'manual';
		$log->status		= $logdata->status;
		$log->log			= $logdata->message;

		$model->saveExternal($log, 'integration_log');
	}

	public function daily($manual = false) {
		$province_id = $this->input->get('province_id');
		$date		= $this->input->get('date', JHtml::date('now', 'Y-m-d'));
		$record		= $this->input->get('record');
		
		$result		= $this->import($date, $province_id, $record);
		
		if($manual) {
			echo json_encode($result);
		}

		$this->app->close();
	}

	public function weekly($manual = true) {
		$province_id = $this->input->get('province_id');
		$date		= $this->input->get('date', JHtml::date('now', 'Y-m-d'));
		$record		= $this->input->get('record');
		
		for ($i=6; $i >= 0; $i--) {
			$curdate 	= JHtml::date($date.' -'.$i.' day', 'Y-m-d');
			$result	= $this->import($curdate, $province_id, $record);
			if($manual) {
				echo json_encode($result);
			}
		}
		
		$this->app->close();
	}

	public function manual() {
		$this->daily(true);
	}

	public function batchImport() {
		$province_id = $this->input->get('province_id');
		$startdate = $this->input->get('startdate');
		$enddate = $this->input->get('enddate');
		$interval = round((strtotime($enddate) - strtotime($startdate)) / (24*60*60));

		if($province_id > 0) {
			for($i=0; $i<$interval; $i++) {
				$date = JHtml::date($startdate . '+'.$i.' day', 'Y-m-d');
				$this->import($date, $province_id, true);
			}
		} else {
			$model		= $this->getModel('Integration');
			$provinces	= $model->getProvinces();
			for($i=0; $i<$interval; $i++) {
				foreach ($provinces as $province) {
					$date = JHtml::date($startdate . '+'.$i.' day', 'Y-m-d');
					$this->import($date, $province->id, true);
				}
			}
			
		}
		
		$this->app->close();
	}
}
