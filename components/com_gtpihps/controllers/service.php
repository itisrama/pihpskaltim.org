<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerService extends GTControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function getIntegrationMarkets() {
		$model		= $this->getModel();
		$markets	= $model->getIntegrationMarkets();

		if(!$this->input->get('debugdb')) {
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($markets);
		}
		$this->app->close();
	}

	public function getIntegrationPrices() {
		$model		= $this->getModel();
		$prices		= $model->getIntegrationPrices();

		if(!$this->input->get('debugdb')) {
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($prices);
		}
		$this->app->close();
	}
}
