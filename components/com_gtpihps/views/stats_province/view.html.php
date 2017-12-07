<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewStats_Province extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/stats.js');
		$this->document->addScript(GT_GLOBAL_JS . '/stats_province.js');
		
		// Get model data.
		$this->itemsCom		= $this->get('ItemsCom');
		$this->itemsCat		= $this->get('ItemsCat');

		$this->state		= $this->get('State');
		$this->commodities	= $this->get('Commodities');
		$this->periods		= $this->state->get('filter.periods');

		$this->layout			= $this->input->get('layout', 'default');
		$this->price_type_id	= $this->menu->params->get('price_type_id');
		$start_date				= strtotime($this->state->get('filter.start_date'));
		$end_date				= strtotime($this->state->get('filter.end_date'));

		// Form Layout
		$formData					= new stdClass();
		$formData->state			=& $this->state;
		$formData->commodityOptions	= $this->get('CommodityOptions');
		$formData->layout			= $this->layout;
		$formData->provinceOptions	= $this->get('ProvinceOptions');
		$formData->regencyOptions	= $this->get('RegencyOptions');
		$formData->marketOptions	= $this->get('MarketOptions');
		$formData->price_type_id 	= $this->price_type_id;
		
		$this->formData				= $formData;
		$this->formLayout			= new JLayoutFile('stats_province.form');

		// Table Layout
		$tableData					= new stdClass();
		$tableData->layout			= $this->layout;
		$tableData->price_type_id	= $this->price_type_id;
		$tableData->itemsCom		=& $this->itemsCom;
		$tableData->itemsCat		=& $this->itemsCat;
		$tableData->commodityList	= $this->get('CommodityList');
		$tableData->provinces		= implode(', ', $this->get('Provinces'));
		$tableData->regencies		= implode(', ', $this->get('Regencies'));
		$tableData->markets			= implode(', ', $this->get('Markets'));
		$tableData->report_type		= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($this->layout));
		$tableData->periods			= $this->periods;
		$tableData->period			= @reset($tableData->periods)->ldate . ' - ' . @end($tableData->periods)->ldate;
		
		$this->tableData			= $tableData;
		$this->tableLayout		= new JLayoutFile('stats_province.table');

		parent::display($tpl);
	}

}
