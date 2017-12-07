<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewRegency_Statistics extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/statistics.js');
		$this->document->addScript(GT_GLOBAL_JS . '/regency_statistics.js');
		
		// Get model data.
		$this->items		= $this->get('Items');
		$this->catItems		= $this->get('CatItems');
		$this->state		= $this->get('State');
		$this->commodities	= $this->get('Commodities');

		$layout			= $this->input->get('layout', 'default');
		$start_date		= strtotime($this->state->get('filter.start_date'));
		$end_date		= strtotime($this->state->get('filter.end_date'));

		// Form Layout
		$formData					= new stdClass();
		$formData->state			=& $this->state;
		$formData->commodityOptions	= $this->get('CommodityOptions');
		
		$formData->regencyOptions	= $this->get('RegencyOptions');
		$formData->marketOptions	= $this->get('MarketOptions');
		$formData->price_type_id 	= $this->menu->params->get('price_type_id');
		$this->formData				= $formData;
		$this->formLayout			= new JLayoutFile('reports.form_regency');

		// Table Layout

		$tableData					= new stdClass();
		$tableData->price_type_id	= $this->menu->params->get('price_type_id');
		$tableData->items			=& $this->items;
		$tableData->catItems		=& $this->catItems;
		$tableData->commodityList	= $this->get('CommodityList');
		$tableData->provinces 		= implode(', ', $this->get('Provinces'));
		$tableData->regencies 		= implode(', ', $this->get('Regencies'));
		$tableData->markets 		= implode(', ', $this->get('Markets'));
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
		$this->tableLayout	= new JLayoutFile('reports.table_regency');

		parent::display($tpl);
	}

}
