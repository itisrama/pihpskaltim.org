<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerAjax extends JKController {
	public function prices() {
		$key 		= $_GET['key'];
		$key 		= str_replace(' ', '+', $key);
		if(in_array($key, array('+E8XJLfMO0tHMT9IWN9yB/oYb58=', 'D6KglwkneBiZIyPQVyhOrrIzTno='))) {
			$model 		= $this->getModel('ajax');
			$date_now	= $model->getLatestDate();
			$date_yest	= $model->getLatestDate($date_now);
			$prices 	= $model->getPrices($date_now, $date_yest);

			$respond			= new stdClass();
			$respond->status 	= 'authorized';
			$respond->date		= JFactory::getDate($date_now)->toUnix();
			$respond->prices	= $prices;
		} else {
			$respond			= new stdClass();
			$respond->status 	= 'unauthorized';
		}
		
		header('Content-Type: application/json');
		echo json_encode($respond);

		JFactory::getApplication()->close();
	}

	public function commodityPrices() {
		$input 		= JFactory::getApplication()->input;
		$model		= $this->getModel('ajax');

		$city_id	= $input->get('city_id', 0);

		$data 		= $model->getCommodityPrices($city_id);

		header('Content-Type: application/json');
		echo json_encode($data);

		JFactory::getApplication()->close();
	}
}