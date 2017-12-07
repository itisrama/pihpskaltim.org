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

	function display($tpl = 'excel') {
		// Get model data.
		$this->itemsAll		= $this->get('ItemsAll');
		$this->itemsProv	= $this->get('ItemsProv');
		$this->itemsReg		= $this->get('ItemsReg');
		$this->state		= $this->get('State');
		$this->periods		= $this->state->get('filter.periods');

		$this->layout			= $this->input->get('layout', 'default');
		$this->price_type_id	= $this->menu->params->get('price_type_id');
		$start_date				= strtotime($this->state->get('filter.start_date'));
		$end_date				= strtotime($this->state->get('filter.end_date'));

		$this->provinceList	= $this->get('ProvinceList');
		$this->regencyList	= $this->get('RegencyList');
		$this->commodity 	= $this->get('Commodity');
		$this->report_type	= JText::_('COM_GTPIHPS_OPTION_LAYOUT_'.strtoupper($this->layout));
		$this->showMarket	= $this->state->get('filter.show_market');
		
		if($this->showMarket) {
			$this->itemsMar		= $this->get('ItemsMar');
			$this->marketList	= $this->get('MarketList');
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
