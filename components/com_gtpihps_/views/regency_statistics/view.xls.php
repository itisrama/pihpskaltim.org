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

	function display($tpl = 'excel') {
		// Get model data.
		$this->items		= $this->get('Items');
		$this->catItems		= $this->get('CatItems');
		$this->state		= $this->get('State');

		$layout			= $this->input->get('layout', 'default');
		$start_date		= strtotime($this->state->get('filter.start_date'));
		$end_date		= strtotime($this->state->get('filter.end_date'));

		$this->commodityList	= $this->get('CommodityList');
		$this->provinces		= implode(', ', $this->get('Provinces'));
		$this->regencies 		= implode(', ', $this->get('Regencies'));
		$this->markets 			= implode(', ', $this->get('Markets'));
		$this->report_type		= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($layout));

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
