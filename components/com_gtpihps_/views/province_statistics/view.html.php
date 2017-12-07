<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewProvince_Statistics extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/statistics.js');
		$this->document->addScript(GT_GLOBAL_JS . '/province_statistics.js');
		
		// Get model data.
		$this->items		= $this->get('Items');
		$this->itemsAll		= $this->get('ItemsAll');
		$this->itemsRegency	= $this->get('ItemsRegency');

		$this->state		= $this->get('State');
		$this->provinces	= $this->get('Provinces');

		$layout			= $this->input->get('layout', 'default');
		$start_date		= strtotime($this->state->get('filter.start_date'));
		$end_date		= strtotime($this->state->get('filter.end_date'));

		// Form Layout
		$formData					= new stdClass();
		$formData->state			=& $this->state;
		$formData->commodityOptions	= $this->get('CommodityOptions');
		$formData->regencyOptions	= $this->get('RegencyOptions');
		
		$this->formData				= $formData;
		$this->formLayout			= new JLayoutFile('reports.form_province');

		// Table Layout
		$tableData					= new stdClass();
		$tableData->showMarket		= $this->state->get('filter.show_market');
		$tableData->items			=& $this->items;
		$tableData->itemsAll		=& $this->itemsAll;
		$tableData->itemsRegency	=& $this->itemsRegency;
		$tableData->provinceList	= $this->get('ProvinceList');
		$tableData->regencyList		= $this->get('RegencyList');
		$tableData->commodity 		= $this->get('Commodity');

		if($tableData->showMarket) {
			$this->itemsMarket		= $this->get('ItemsMarket');
			$tableData->itemsMarket	=& $this->itemsMarket;
			$tableData->marketList	= $this->get('MarketList');
		}

		$tableData->report_type		= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($layout));
		switch($layout) {
			default:
				$tableData->periods	= GTHelperDate::getDayPeriod($start_date, $end_date);
				break;
			case 'weekly':
				$tableData->periods	= GTHelperDate::getWeekPeriod($start_date, $end_date);
				break;
			case 'monthly':
				$tableData->periods	= GTHelperDate::getMonthPeriod($start_date, $end_date);
				break;
			case 'yearly':
				$tableData->periods	= GTHelperDate::getYearPeriod($start_date, $end_date);
				break;
		}

		$tableData->period	= @reset($tableData->periods)->ldate . ' - ' . @end($tableData->periods)->ldate;
		$this->tableData	= $tableData;
		$this->tableLayout	= new JLayoutFile('reports.table_province');

		parent::display($tpl);
	}

}
