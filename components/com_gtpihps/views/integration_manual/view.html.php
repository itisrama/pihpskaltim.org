<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewIntegration_Manual extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/integration_manual__.js');
		
		// Get model data.
		$this->state 			= $this->get('State');
		$this->provinceOptions	= $this->get('ProvinceOptions');

		$this->integrationUrl		= JURI::root(true).'?option=com_gtpihps&task=integration.manual&province_id={province_id}&date={date}';
		$this->integrationMarketUrl	= JURI::root(true).'?option=com_gtpihps&task=integration.importMarkets&output=1';

		JText::script('COM_GTPIHPS_ALL_PROVINCES');

		parent::display($tpl);
	}

}
