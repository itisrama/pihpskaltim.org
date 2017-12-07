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
	public $integrationType = 'live';

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

		$urlMobile = array(
			'live' => 'http://survey.hargapangan.id/?option=com_gtpihpssurvey&task=service.getIntegrationPrices&province_id=%s&period=%s',
			'dotnet' => 'http://10.195.17.10:91/Service/getIntegrationPrices?province_id=%s&period=%s',
			'local' => 'http://localhost/pihps/nasional_survey/?option=com_gtpihpssurvey&task=service.getIntegrationPrices&province_id=%s&period=%s'
		);
		$urlMobile = $urlMobile[$this->integrationType];
		
		$types = array(
			'dotnet' => sprintf('https://www.bi.go.id/surveitest/svc/PIHPS.svc/json/marketprice?province_id=%s&period=%s', trim($province->source_id), $date),
			'dotnet2' => sprintf('https://www.bi.go.id/surveitest/svc/pihps.svc/json/sphmarketprice?province_id=%s&period=%s', trim($province->source_id2), $dateAlt),
			'mobile' => sprintf($urlMobile, trim($province->source_id), $date)
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
		$priceTypes		= $model->getPriceTypes();
		$regencies		= $model->getRegencies(null, null, true);
		$markets 		= $model->getMarkets(null, null, true);
		$markets2 		= $model->getMarkets2(null, null, true);
		$output 		= $this->input->get('output', 0);
		$dataMarkets = array();

		foreach ($provinces as $province) {
			if($this->integrationType == 'live') {
				$urlMarkets1	= 'https://www.bi.go.id/surveitest/svc/PIHPS.svc/json/market?province_id='.$province->source_id;
				$dataMarkets1	= @file_get_contents($urlMarkets1, 0, null, null);
				$dataMarkets1	= json_decode($dataMarkets1);

				if(is_array($dataMarkets1)) {
					foreach ($dataMarkets1 as $dataMarket) {
						$dataMarket->source_type = 1;
						$dataMarket->province = $province->name;
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
						$dataMarket->province = $province->name;
						$dataMarket->province_id = $province->id;
						$dataMarkets[$dataMarket->market_id.'-2-'.$dataMarket->region_id] = $dataMarket;
					}
				}
			}

			$urlMobile = array(
				'live' => 'http://survey.hargapangan.id/?option=com_gtpihpssurvey&task=service.getIntegrationMarkets&province_id=',
				'dotnet' => 'http://10.195.17.10:91/Service/getIntegrationMarkets?province_id=',
				'local' => 'http://localhost/pihps/nasional_survey/?option=com_gtpihpssurvey&task=service.getIntegrationMarkets&province_id='
			);
			$urlMobile = $urlMobile[$this->integrationType];

			$urlMarkets3	= $urlMobile.$province->source_id;
			$dataMarkets3	= @file_get_contents($urlMarkets3, 0, null, null);
			$dataMarkets3	= json_decode($dataMarkets3);

			if(is_array($dataMarkets3)) {
				foreach ($dataMarkets3 as $dataMarket) {
					$dataMarket->source_type = 3;
					$dataMarket->province = $province->name;
					$dataMarket->province_id = $province->id;
					$dataMarkets[$dataMarket->market_id.'-3-'.$dataMarket->region_id] = $dataMarket;
				}
			}
			
		}

		$regencyItems	= array();
		$marketItems	= array();
		$marSrcItems	= array();
		$priceTypeCodes = array('T', 'M', 'I', 'P');
		
		foreach ($dataMarkets as &$dataMarket) {
			$regSrcID	= $dataMarket->source_type.'_'.$dataMarket->region_id;
			$regency	= @$regencies[$regSrcID];
			$priceType2	= substr($dataMarket->market_id, 0, 1);
			$priceType 	= @$dataMarket->price_type_id ? $priceTypeCodes[$dataMarket->price_type_id - 1] : $priceType2;
			$priceType	= @$priceTypes[$priceType];
			
			$regDesc = trim($dataMarket->region_desc);
			$regName = trim($dataMarket->region_desc);
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

			$regCode = preg_replace("/[^A-Za-z0-9?!]/",'',$regDesc);
			$regCode = $dataMarket->province_id.':'.strtolower($regCode);

			$regencyItem	= @$regencyItems[$regCode];
			$regencyItem	= is_object($regencyItem) ? $regencyItem : new stdClass();
			$regSourceID	= @$regency->source_id ? @$regency->source_id : @$regencyItem->source_id;
			$regSourceID2	= @$regency->source_id2 ? @$regency->source_id2 : @$regencyItem->source_id2;
			$regSourceID3	= @$regency->source_id3 ? @$regency->source_id3 : @$regencyItem->source_id3;

			$regencyItem->id			= intval(@$regency->id);
			$regencyItem->source_id		= $regSourceID;
			$regencyItem->source_id2	= $regSourceID2;
			$regencyItem->source_id3	= $regSourceID3;
			$regencyItem->province_id	= $dataMarket->province_id;
			$regencyItem->name			= $regName;
			$regencyItem->type			= $regType == 'kota' ? 'city' : 'regency';
			$regencyItem->long_name		= $regDesc;
			$regencyItem->published		= 1;

			switch($dataMarket->source_type) {
				case 1:
					$regencyItem->source_id = $dataMarket->region_id;
					break;
				case 2:
					$regencyItem->source_id2 = $dataMarket->region_id;
					break;
				case 3:
					$regencyItem->source_id3 = $dataMarket->region_id;
					break;
			}

			if(@$priceType->id == 1) {
				$market_name	= ucwords(strtolower(trim($dataMarket->market_desc)));
				$short_name		= str_replace(JText::_('COM_GTPIHPS_FIELD_MARKET').' ', '', $market_name);
			} else {
				$market_name 	= @$priceType->short_name.' '.$regDesc;
				$short_name 	= @$priceType->code.' '.$regDesc;
			}
			
			$marketCode	= preg_replace("/[^A-Za-z0-9?!]/",'',$market_name);
			$marketCode	= $regCode.':'.strtolower($marketCode);
			$marSrcID	= $dataMarket->source_type.'_'.$dataMarket->market_id;
			$market		= @$priceType->id == 1 ? @$markets[$marSrcID] : @$markets2[$marketCode];

			$marketItem					= @$marketItems[$marketCode];
			$marketItem					= is_object($marketItem) ? $marketItem : new stdClass();
			$marketItem->id				= intval(@$market->id);
			$marketItem->price_type_id	= @$priceType->id;
			$marketItem->province_id	= $dataMarket->province_id;
			$marketItem->regency_id		= $regSrcID;
			$marketItem->name			= $market_name;
			$marketItem->short_name		= $short_name;
			$marketItem->published		= 1;
			$marketItem->province		= $dataMarket->province;
			$marketItem->regency		= $regDesc;

			$marSrcItem					= new stdClass();
			$marSrcItem->market_id		= $marketCode;
			$marSrcItem->source_type	= $dataMarket->source_type;
			$marSrcItem->source_id		= $dataMarket->market_id;

			$marketItems[$marketCode]	= $marketItem;
			$regencyItems[$regCode]		= $regencyItem;
			$marSrcItems[$marSrcID]		= $marSrcItem;
			
			unset($markets[@$market->id]);
			unset($regencies[@$regency->id]);
		}
		//echo "<pre>"; print_r($regencyItems); echo "</pre>"; die;
		// Save Regencies
		$model->saveBulk($regencyItems, 'regency');
		$regencies = $model->getRegencies(null, null, true);
		
		$marketMsgs = array();
		foreach ($marketItems as &$marketItem) {
			$regency = $regencies[$marketItem->regency_id];
			$marketItem->regency_id = $regency->id;
			if(!$marketItem->id > 0) {
				$provID = sprintf('%03d', $marketItem->province_id);
				$marketMsgs[$provID.'|'.$marketItem->province.'|'.$marketItem->regency][] = $marketItem->name;
			}
			unset($marketItem->province);
			unset($marketItem->regency);
		}

		// Save Markets
		$model->saveBulk($marketItems, 'market');
		$markets = $model->getMarkets2(null, null, true);


		foreach ($marSrcItems as &$marSrcItem) {
			$market = $markets[$marSrcItem->market_id];
			$marSrcItem->market_id = $market->id;
		}

		// Save Market Sources
		$model->saveBulk($marSrcItems, 'market_source', false);

		if($output) {
			$msg = new stdClass();
			if(count($marketMsgs)) {
				ksort($marketMsgs);
				$messages = JText::_('COM_GTPIHPS_IMPORT_MARKET_SUCCESS');
				foreach ($marketMsgs as $marketMsgsKey => $marketMsg) {
					list($provID, $prov, $regency) = explode('|', $marketMsgsKey);
					$messages .= '<ul><li><strong>'.$prov.' - '.$regency.'</strong>';
					$messages .= '<ul style="padding:0 1em"><li>'.implode('</li><li>', $marketMsg).'</li></ul>';
					$messages .= '</li></ul>';
				}
				$msg->message = $messages;
				$msg->status = 'reference';
			} else {
				$msg->message = JText::_('COM_GTPIHPS_IMPORT_MARKET_EMPTY');
				$msg->status = 'reference_e';
			}
			echo json_encode($msg);
		}
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

	protected function import($date = null, $province_id = null, $type = 'all') {
		$province_id	= $province_id ? $province_id : $this->input->get('province_id');
		$date			= $date ? $date : $this->input->get('date');
		$holiday		= GTHelperDate::isHoliday($date);
		$result			= new stdClass();

		if($holiday) {
			$result->message = sprintf(JText::_('COM_GTPIHPS_INTEGRATION_HOLIDAY'), $holiday);
			$result->status = 'skip';

			//$this->saveLog($result, 0, $date, null);
			return $result;
		}
		if(JHtml::date($date, 'w') == 6) {
			$result->message = JText::_('COM_GTPIHPS_INTEGRATION_SATURDAY');
			$result->status = 'skip';

			//$this->saveLog($result, 0, $date, null);
			return $result;
		}
		if(JHtml::date($date, 'w') == 0) {
			$result->message = JText::_('COM_GTPIHPS_INTEGRATION_SUNDAY');
			$result->status = 'skip';

			//$this->saveLog($result, $province_id, $date, null);
			return $result;
		}

		$model			= $this->getModel('Integration');
		$commodities 	= $model->getCommodities();
		$markets 		= $model->getMarkets($province_id, null);
		$regencies 		= $model->getRegencies($province_id, null);
		$masterIDs 		= $model->getMasterIDs($province_id, $date);
		$detailIDs 		= $model->getDetailIDs($province_id, $date);
		$delMasterIDs	= $masterIDs;
		$delDetailIDs	= $detailIDs;

		$markets1	= array();
		$markets2	= array();
		$markets3	= array();

		foreach ($markets as $market) {
			$source_type	= $market->source_type;
			$source_id		= $market->source_id;

			switch ($source_type) {
				case '1':
					$markets1[$source_id] = $market;
					break;
				case '2':
					$markets2[$source_id] = $market;
					break;
				case '3':
					$markets3[$source_id] = $market;
					break;
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

		$items = $this->integrationType == 'live' ? array(
			'dotnet' => $this->json('dotnet', $date, $province_id, false),
			'dotnet2' => $this->json('dotnet2', $date, $province_id, false),
			'mobile' => $this->json('mobile', $date, $province_id, false)
		) : array(
			'mobile' => $this->json('mobile', $date, $province_id, false)
		);
		
		$masters		= array();
		$details		= array();
		$urls			= array();
		$marketNames	= array();
		
		foreach ($items as $itemType => &$item) {
			$data = (array) @$item->data;
			foreach ($data as $master) {
				if(!count($master->details)) continue;

				switch($itemType) {
					case 'dotnet':
						$market = @$markets1[$master->market_id];
						break;
					case 'dotnet2':
						$market = @$markets2[$master->market_id];
						break;
					case 'mobile':
						$market = @$markets3[$master->market_id];
						break;
				}
				
				if(!is_object($market)) continue;
				
				$mItem					= new stdClass();
				$mItem->id				= 0;
				$mItem->date			= $date;
				$mItem->source_type		= $itemType;
				$mItem->price_type_id	= $market->price_type_id;
				$mItem->province_id		= $province_id;
				$mItem->regency_id		= $market->regency_id;
				$mItem->market_id		= $market->id;
				$mItem->inputted 		= null;
				$mItem->validated 		= null;

				if(@$master->validated) {
					$mItem->inputted	= @$master->inputted ? $master->inputted : $master->validated;
					$mItem->validated	= $master->validated;
					$mItem->created		= $master->validated;
					
				} elseif(@$master->created) {
					$mItem->created		= $master->created;
				}

				foreach ($master->details as $rawDetail) {
					$commodity_id = @$rawDetail->commodity_id.'-'.@$rawDetail->quality_id;
					$commodity = $itemType == 'dotnet2' ? @$commodities2[$commodity_id] : @$commodities1[$commodity_id];

					if(!$commodity->id) continue;
					if(!$rawDetail->price > 0) continue;

					$details[$market->id.'_'][$commodity->id.'_'][] = $rawDetail->price;
				}
				
				$regency						= $regencies[$market->regency_id];
				$masters[$market->id.'_']		= $mItem;
				$marketNames[$market->id.'_']	= $regency->long_name.' - '.$market->name;
			}

			$urlStatus			= new stdClass();
			$urlStatus->url		= $item->url;
			$urlStatus->status	= count($data) > 0 ? 'success' : 'empty';
			$urls[]				= $urlStatus;
		}

		$commodity_ids	= array_keys($model->getCommodities());		
		$lastData		= $model->getData($province_id, $date);
		$lastMasters	= array();
		$lastDetails	= array();


		foreach ($lastData as $lastItem) {
			$market_id			= $lastItem->market_id;
			$commodity_id		= $lastItem->commodity_id;
			$price				= $lastItem->price;
			$price_id_source	= $lastItem->price_id_source;

			unset($lastItem->price_id_source);
			unset($lastItem->commodity_id);
			unset($lastItem->price);

			$lastMasters[$market_id.'_'] = $lastItem;
			$lastDetails[$market_id.'_'][$commodity_id.'_'] = $price.'|'.$price_id_source;
		}

		$notif_markets	= array();
		foreach ($masters as $master) {
			$market_id 		= $master->market_id.'_';
			$marketName		= $marketNames[$market_id];
			$detail			= $details[$market_id];
			$commodityCount	= count((array) $detail);

			if(!$commodityCount > 0) {
				continue;
			}
			$notif_markets[$master->market_id] = sprintf(JText::_('COM_GTPIHPS_IMPORT_SUCCESS_COMMODITY'), $marketName, $commodityCount);
		}

		$insertMasters	= array_merge($lastMasters, $masters);
		$insertDetails	= array();

		if(count($insertMasters) > 0) {
			$masterTable = 'prices';
			$detailTable = 'price_details';

			foreach ($insertMasters as &$insertMaster) {
				$insertMaster->id = intval(@$masterIDs[$insertMaster->market_id]);
				unset($delMasterIDs[$insertMaster->market_id]);
			}

			// Save Master
			$model->saveBulk($insertMasters, $masterTable);
			$newMasterIDs = $model->getMasterIDs($province_id, $date);
			foreach ($insertMasters as $market_id => $master) {
				$price_id	= @$newMasterIDs[intval($market_id)];
				if(!is_numeric($price_id)) continue;
				
				$curDetail1	= (array) @$lastDetails[$market_id];
				$curDetail2	= (array) @$details[$market_id];
				foreach ($curDetail2 as &$price) {
					$price = round(array_sum($price) / count($price) / 50)*50;
				}

				$curDetail 	= array_merge($curDetail1, $curDetail2);				
				foreach ($curDetail as $curComID => $curPrice) {
					list($curPrice, $priceIDSrc) = explode('|', $curPrice.'|');
					$curPrice	= floatval($curPrice);
					$priceIDSrc	= intval($priceIDSrc);

					if(!$curPrice > 0) continue;
					
					$market_id		= intval($market_id);
					$commodity_id	= intval($commodity_id);

					$detail						= new stdClass();
					$detail->id					= intval(@$detailIDs[$market_id][$curComID]);
					$detail->price_id			= $price_id;
					$detail->price_id_source	= $priceIDSrc > 0 ? $priceIDSrc : null;
					$detail->commodity_id		= $curComID;
					$detail->price				= $curPrice;

					unset($delDetailIDs[$market_id][$curComID]);
					$insertDetails[$market_id.'_'.$curComID] = $detail;
				}
			}

			// Save Detail
			$model->saveBulk($insertDetails, $detailTable, false);
		}

		if(count($masters) > 0) {
			// Delete remainders
			if(count($delMasterIDs) > 0) {
				$model->deleteExternal($delMasterIDs, $masterTable);
			}
			$delDetailIDs = array_map('current', $delDetailIDs);
			if(count($delDetailIDs) > 0) {
				$model->deleteExternal($delDetailIDs, $detailTable);
			}
		}

		if(count($notif_markets) > 0) {
			sort($notif_markets);
			$result->message	= sprintf(JText::_('COM_GTPIHPS_IMPORT_SUCCESS'), JHtml::date($date, 'j F Y'), implode('', $notif_markets));
			$result->status		= 'success';
		} else {
			$result->message	= JText::_('COM_GTPIHPS_INTEGRATION_JSON_EMPTY');
			$result->status		= 'warning';
		}
		
		$urls = json_encode($urls);
		$this->saveLog($result, $province_id, $date, $urls);

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
		
		$result		= $this->import($date, $province_id);
		
		if($manual) {
			echo json_encode($result);
		}

		$this->app->close();
	}

	public function weekly($manual = true) {
		$province_id = $this->input->get('province_id');
		$date		= $this->input->get('date', JHtml::date('now', 'Y-m-d'));
		
		for ($i=6; $i >= 0; $i--) {
			$curdate 	= JHtml::date($date.' -'.$i.' day', 'Y-m-d');
			$result	= $this->import($curdate, $province_id);
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
				$this->import($date, $province_id);
			}
		} else {
			$model		= $this->getModel('Integration');
			$provinces	= $model->getProvinces();
			for($i=0; $i<$interval; $i++) {
				foreach ($provinces as $province) {
					$date = JHtml::date($startdate . '+'.$i.' day', 'Y-m-d');
					$this->import($date, $province->id);
				}
			}
			
		}
		
		$this->app->close();
	}
}
