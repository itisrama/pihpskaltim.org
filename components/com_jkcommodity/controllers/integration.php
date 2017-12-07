<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerIntegration extends JKController {
	function getJson() {
		$model = $this->getModel('integration');
		
		$prices = $model->getPrices();
		$ids = array_keys($prices);
		$ids = $ids ? $ids : array(0);
		$price_details = $model->getPriceDetails($ids);

		$return = array();
		foreach ($prices as $k => $price) {
			$price->details = $price_details[$price->id];
			unset($price->id);
			$return[] = $price;
		}
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($return);

		JFactory::getApplication()->close();
	}
}