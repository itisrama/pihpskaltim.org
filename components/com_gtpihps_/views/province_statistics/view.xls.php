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

	function display($tpl = 'excel') {
		// Get model data.
		$this->items		= $this->get('Items');
		$this->itemsAll		= $this->get('ItemsAll');
		$this->itemsRegency	= $this->get('ItemsRegency');
		$this->state		= $this->get('State');

		$layout			= $this->input->get('layout', 'default');
		$start_date		= strtotime($this->state->get('filter.start_date'));
		$end_date		= strtotime($this->state->get('filter.end_date'));

		$this->provinceList	= $this->get('ProvinceList');
		$this->regencyList	= $this->get('RegencyList');
		$this->commodity 	= $this->get('Commodity');
		$this->report_type	= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($layout));

		$this->showMarket	= $this->state->get('filter.show_market');
		if($this->showMarket) {
			$this->itemsMarket	= $this->get('ItemsMarket');
			$this->marketList	= $this->get('MarketList');
		}

		switch($layout) {
			default:
				$this->periods	= GTHelperDate::getDayPeriod($start_date, $end_date);
				break;
			case 'weekly':
				$this->periods	= GTHelperDate::getWeekPeriod($start_date, $end_date);
				break;
			case 'monthly':
				$this->periods	= GTHelperDate::getMonthPeriod($start_date, $end_date);
				break;
			case 'yearly':
				$this->periods	= GTHelperDate::getYearPeriod($start_date, $end_date);
				break;
		}

		$this->period = @reset($this->periods)->ldate . ' - ' . @end($this->periods)->ldate;

		$document    = JFactory::getDocument();
		$objPHPExcel = $document->getPhpExcelObj();

		// Set properties
		$objPHPExcel->getProperties()->setCreator($this->user->name);
		$objPHPExcel->getProperties()->setTitle(JText::_('COM_GTPIHPS_HEADER_REPORT'));

		// Rename sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($this->report_type);
		
		$this->objPHPExcel = $objPHPExcel;
		
		parent::display($tpl);
	}
}
