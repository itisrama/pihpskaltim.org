<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once( JK_ADMIN_HELPERS . DS . 'date.php' );

class JKCommodityViewReport extends JKView {

	function __construct() {
		parent::__construct();
	}
	
	function display($tpl = 'excel') {
		// Get the user requesting this view
		$user = JFactory::getUser();
		
		// Get and set the document properties
		$document = &JFactory::getDocument();
		$date = new JDate();
		$document->setName(JText::_('COM_JKCOMMODITY_FIELDSET_PRICE_REPORT') . ' ' . $date->format('Y-m-d'));
		$download_title = JText::sprintf('DOWNLOAD TITLE', JText::_('USERS'), $user->name);
		$document->setTitle($download_title);
		
		//Get the PHPExcel object to set some properties
		$phpexcel = & $document->getPhpExcelObj();
		$phpexcel->getProperties()->setCreator($user->name)->setLastModifiedBy($user->name);
		$phpexcel->setActiveSheetIndex(0);
		$phpexcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C' . $download_desc);
		$phpexcel->getActiveSheet()->setTitle(JText::_('COM_JKCOMMODITY_FIELDSET_PRICE_REPORT'));
				
		// Set Dates
		$start_date_unix = strtotime(JRequest::getVar('start_date', date('d-m-Y', strtotime('-10 day', time()))));
		$end_date_unix = strtotime(JRequest::getVar('end_date', date('d-m-Y', time())));
		if($start_date_unix > $end_date_unix) {
			list($start_date_unix, $end_date_unix) = array($end_date_unix, $start_date_unix);
		}
		if($start_date_unix > time()) {
			$start_date_unix = strtotime('-10 day', time());
		}
		if($end_date_unix > time()) {
			$end_date_unix = time();
		}
		$layout = JRequest::getCmd('layout');
		switch($layout) {
			default:
				$period = JKHelperDate::getDayPeriod($start_date_unix, $end_date_unix);
				break;
			case 'weekly':
				$period = JKHelperDate::getWeekPeriod($start_date_unix, $end_date_unix);
				break;
			case 'monthly':
				$period = JKHelperDate::getMonthPeriod($start_date_unix, $end_date_unix);
				break;
			case 'yearly':
				$period = JKHelperDate::getYearPeriod($start_date_unix, $end_date_unix);
				break;
		}
		$start_date = date('d-m-Y', $start_date_unix);
		$end_date = date('d-m-Y', $end_date_unix);
		JRequest::setVar('start_date', $start_date);
		JRequest::setVar('end_date', $end_date);
		
		// Set Commodity List
		$commodity	= $this->get('Commodity');
		$categories = $this->get('Category');
		$categories_el = array();
		foreach($categories as $item) {
			$categories_el[intval($item->parent_id)][$item->id] = $item;
		}
		$commodities_el = array();
		$category_ids = array();
		foreach($commodity as $item) {
			$commodities_el[intval($item->category_id)][$item->id] = $item;
			$category_ids[] = $item->category_id;
		}
		$selected_category = array(); 
		foreach(array_unique($category_ids) as $category_id) {
			$category = $categories[$category_id];
			$selected_category[] = $category_id;
			$selected_category[] = $category->parent_id;
			while(isset($categories[$category->parent_id])) {
				$category = $categories[$category->parent_id];
				$selected_category[] = $category->parent_id;
			}
		}
		$selected_category = array_filter(array_unique($selected_category));
		$selected_category = $selected_category ? $selected_category : array(0);
		$commodity_list = JKHelperDocument::prepareCommodity($categories_el[0], $categories_el, $commodities_el, 0, 0, $selected_category);
		foreach($commodity_list as $k => $item) {
			$item->id = $item->value;
			$item->name = str_replace('&nbsp;', ' ', $item->text);
			$commodity_list[$k] = $item;
		}
		// Set Tabel Header Columns & Rows for different layouts
		switch($layout) {
			default:
				foreach($period as $k=>$item) {
					$item->id = $item->unix;
					$item->name = $item->sdate;
					$period[$k] = $item;
				}
				$row_header = JText::_('COM_JKCOMMODITY_LABEL_COMMODITY');
				$columns	= $period;
				$rows		= $commodity_list;
				break;
			case 'market':
				$row_header = JText::_('COM_JKCOMMODITY_LABEL_COMMODITY');
				$columns	= $this->get('Market');
				$rows		= $commodity_list;
				break;
			case 'period':
				$row_header = JText::_('COM_JKCOMMODITY_LABEL_DATE');
				foreach($period as $k=>$item) {
					$item->id = $item->unix;
					$item->name = PHPExcel_Shared_Date::PHPToExcel($item->unix);
					$period[$k] = $item;
				}
				$columns	= $commodity;
				$rows		= $period;
				break;
		}
		
		// Load Header
		$this->header = JKHelperDocument::getReportHeader($period, $layout);
		
		// Set array of data for different layouts
		$data = array();
		$raw_data = $this->get('Data');
		
		foreach($raw_data as $item) {
			$price = round($item->price/50)*50;
			switch($layout) {
				case 'market':
					$data[$item->commodity_id][$item->market_id] = $price;
					break;
				case 'period':
					$data[strtotime($item->date)][$item->commodity_id] = $price;
					break;
				default:
					$data[$item->commodity_id][strtotime($item->date)] = $price;
					break;
			}
		}
		// Set variables
		$this->data			= $data;
		$this->columns		= $columns;
		$this->row_header	= $row_header;
		$this->rows			= $rows;
		$this->state		= $this->get('State');
		$this->params		= $this->state->params;		
		$this->form			= $this->get('Form');
		$this->return_url	= base64_encode(JURI::getInstance()->toString());
		$this->phpexcel		= $phpexcel;
		$this->start_date	= $start_date;
		$this->end_date		= $end_date;
		$this->layout		= $layout;
		//Display the results
		
		parent::display($tpl);
	}

}