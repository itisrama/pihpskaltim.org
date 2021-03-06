<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerRegency_Statistics extends GTControllerAdmin {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function loadRegencies() {
		$model		= $this->getModel('Regency_Statistics');
		$options	= $model->getRegencyOptions();
		
		echo JHtml::_('select.options', $options);
		$this->app->close();
	}

	public function loadMarkets() {
		$model		= $this->getModel('Regency_Statistics');
		$options	= $model->getMarketOptions();
		
		echo JHtml::_('select.options', $options);
		$this->app->close();
	}
}
