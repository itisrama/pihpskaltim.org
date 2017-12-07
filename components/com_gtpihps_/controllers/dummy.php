<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerDummy extends GTControllerForm{
	function insert() {
		$model = $this->getModel();
		
		$markets = $model->getReferences('market');
		$commodities = $model->getReferences('commodity');
		$cityPrices = array();

		foreach ($markets as $market) {
			foreach ($commodities as $commodity) {
				$cityPrices[$market->id][$commodity->id] = round(($commodity->price_sample * ((100+rand(-5, 5))/100))/100)*100;
			}
		}
		

		$date		= '2017-01-01';
		$chances	= array(0,0,1,2);
		while ($date <= '2017-10-17') {
			foreach ($markets as $market) {
				$master					= new stdClass();
				$master->id				= 0;
				$master->province_id	= $market->province_id;
				$master->regency_id		= $market->regency_id;
				$master->market_id		= $market->id;
				$master->date			= $date;

				$master_id = $model->saveExternal($master, 'price', true);

				foreach ($commodities as $commodity) {
					$basePrice = $cityPrices[$market->id][$commodity->id];
					switch($chances[array_rand($chances)]) {
						case 0:
							$basePrice = $basePrice;
							break;
						case 1:
							$basePrice = round(($basePrice * ((100+rand(0, 10))/100))/100)*100;
							break;
						case 2:
							$basePrice = round(($basePrice * ((100-rand(0, 10))/100))/100)*100;
							break;
					}

					$cityPrices[$market->id][$commodity->id] = $basePrice;

					$detail					= new stdClass();
					$detail->id 			= 0;
					$detail->price_id		= $master_id;
					$detail->commodity_id	= $commodity->id;
					$detail->price			= $basePrice;

					$model->saveExternal($detail, 'price_detail');
				}
			}


			$date = JHtml::date($date.' +1 day', 'Y-m-d');
		}

		die;
	}
}