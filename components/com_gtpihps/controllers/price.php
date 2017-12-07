<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerPrice extends GTControllerForm{

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = 'Price', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function loadRegencies() {
		$model		= $this->getModel();
		$options	= $model->getRegencyOptions();
		
		echo JHtml::_('select.options', $options);
		$this->app->close();
	}

	public function loadMarkets() {
		$model		= $this->getModel();
		$options	= $model->getMarketOptions();
		
		echo JHtml::_('select.options', $options);
		$this->app->close();
	}

	public function loadLastPrices() {
		$model		= $this->getModel();
		$prices		= $model->getLatestPrices();
		
		echo json_encode($prices);
		$this->app->close();
	}

}
