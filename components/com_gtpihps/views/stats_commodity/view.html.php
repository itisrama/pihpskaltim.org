<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewStats_Commodity extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/stats.js');
		$this->document->addScript(GT_GLOBAL_JS . '/stats_commodity.js');
		
		// Get model data.
		$this->itemsAll		= $this->get('ItemsAll');
		$this->itemsProv	= $this->get('ItemsProv');
		$this->itemsReg		= $this->get('ItemsReg');
		$this->state		= $this->get('State');
		$this->provinces	= $this->get('Provinces');
		$this->periods		= $this->state->get('filter.periods');

		$this->layout			= $this->input->get('layout', 'default');
		$this->price_type_id	= $this->menu->params->get('price_type_id');
		$start_date				= strtotime($this->state->get('filter.start_date'));
		$end_date				= strtotime($this->state->get('filter.end_date'));

		// Form Layout
		$formData					= new stdClass();
		$formData->state			=& $this->state;
		$formData->commodityOptions	= $this->get('CommodityOptions');
		$formData->provinceOptions	= $this->get('ProvinceOptions');
		$formData->regencyOptions	= $this->get('RegencyOptions');
		$formData->price_type_id 	= $this->price_type_id;
		$formData->layout			= $this->layout;
		
		$this->formData				= $formData;
		$this->formLayout			= new JLayoutFile('stats_commodity.form');

		// Table Layout
		$tableData					= new stdClass();
		$tableData->layout			= $this->layout;
		$tableData->price_type_id	= $this->price_type_id;
		$tableData->showMarket		= $this->state->get('filter.show_market');
		$tableData->itemsAll		=& $this->itemsAll;
		$tableData->itemsProv		=& $this->itemsProv;
		$tableData->itemsReg		=& $this->itemsReg;
		$tableData->provinceList	= $this->get('ProvinceList');
		$tableData->regencyList		= $this->get('RegencyList');
		$tableData->commodity 		= $this->get('Commodity');

		if($tableData->showMarket) {
			$this->itemsMar			= $this->get('itemsMar');
			$tableData->itemsMar	=& $this->itemsMar;
			$tableData->marketList	= $this->get('MarketList');
		}
		
		$tableData->report_type	= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($this->layout));
		$tableData->periods		= $this->periods;
		$tableData->period		= @reset($tableData->periods)->ldate . ' - ' . @end($tableData->periods)->ldate;
		$this->tableData		= $tableData;
		$this->tableLayout		= new JLayoutFile('stats_commodity.table');

		parent::display($tpl);
	}

}
