<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class JKCommodityViewImport_Excel extends JKView {

	var $form;
	var $state;
	var $canDo;
	var $params;
	var $item_title;

	public function ___construct($config = array()) {
		parent::__construct($config);
	}

	public function display($tpl = null) {
		// Get model data.
		$this->state		= $this->get('State');
		$this->params		= $this->state->params;
		
		$layout 			= $this->getLayout();
		$this->form			= $this->get('Form');
		$this->isNew		= true;
		
		// Load Script
		$this->document->addScriptDeclaration('var global_hlimit = 3');
		$this->document->addScript(JK_GLOBAL_JS . '/statistics.js');
		
		// Set page title
		$this->page_title = $layout == 'preview' ? JText::_('COM_JKCOMMODITY_PT_IMPORT_PREVIEW') : JText::_('COM_JKCOMMODITY_PT_IMPORT');
		JKHelperDocument::setTitle($this->page_title);

		// Assign additional data
		$this->canDo = JKHelper::getActions();

		// Add pathway
		$pathway = $this->app->getPathway();
		$pathway->addItem($this->item_title);
		
		// Check permission and display
		JKHelper::checkPermission($this->canDo);

		if($layout == 'preview') {
			$this->data				= JArrayHelper::toObject(JRequest::getVar('jform', null, 'array', 'array'));
			$this->city				= $this->get('City');
			$this->format			= $this->get('Format');
			$this->commodityList	= $this->get('CommodityList');

			$items					= $this->readFile($this->format);
			$markets_db				= $this->get('Markets');
			$this->items 			= array();
			$this->json 			= array();
			$this->markets 			= array();
			foreach ($items as $markets) {
				foreach ($markets as $market_id => $commodities) {
					$this->markets[$market_id] = $markets_db[$market_id];
					$this->json[$market_id] = $commodities;
					foreach ($commodities as $commodity_id => $price) {
						$this->items[$commodity_id][$market_id] = $price;
					}
				}
			}
			$this->json 			= htmlentities(json_encode($this->json));
			
		}
		parent::display($tpl);
	}

	protected function readFile($format) {
		//Retrieve file details from uploaded file, sent from upload form
		$file = JKHelper::arrayToFiles(JRequest::getVar('jform', null, 'files', 'array'));
		
		// Read your Excel workbook
		JLoader::import('phpexcel.Classes.PHPExcel');
		JLoader::import('phpexcel.Classes.PHPExcel.IOFactory');
		try {
			$inputFileType	= PHPExcel_IOFactory::identify($file->file_excel->tmp_name);
			$objReader		= PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel	= $objReader->load($file->file_excel->tmp_name);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($filename,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		
		$data		= array();
		$mcolumns	= json_decode($format->market_columns);
		$comms_db	= $this->get('CommodityNames');
		foreach ($mcolumns as $index => $markets) {
			$sheet = $objPHPExcel->getSheet($index);
			$data[$index] = $this->readWorksheet($sheet, $markets, $format, $comms_db);
		}
		return $data;
	}

	protected function readWorksheet($sheet, $markets, $format, $commodities_db) {
		$rs		= 1;
		$re		= $sheet->getHighestRow()+1;
		$ccoms	= explode(',', $format->commodity_column);

		// Get worksheet dimensions
		$comdb_names	= array();
		foreach ($commodities_db as $k => $commodity) {
			$comdb_names[$k]	= JKHelper::cleanstr($commodity->original_name);;
		}

		$coms 			= array();
		foreach ($ccoms as $k => $ccom) {
			$coms[$k]	= array_map('current', $sheet->rangeToArray($ccom.$rs.':'.$ccom.$re, NULL, TRUE, FALSE, TRUE));
		}
		
		$commodities 	= array();
		$commodities2 	= array();
		for($i = $rs-$rs; $i<=$re-$rs; $i++) {
			foreach ($ccoms as $k => $ccom) {
				$commodity = strlen(trim($coms[$k][$i])) > 0 ? trim($coms[$k][$i]) : @$commodities[$i];
				$commodities[$i] = JKHelper::cleanstr($commodity);
				//$commodities2[$i] = JKHelper::cleanstr(preg_replace("/\([^)]+\)/","", $commodity));
			}
		}

		$commodity_ids	= array();
		foreach ($commodities as $row => $commodity) {
			$commodity_id = array_search($commodity, $comdb_names);
			if(!$commodity_id) continue;
			
			unset($comdb_names[$commodity_id]);
			unset($comdb_names2[$commodity_id]);
			$commodity_ids[$row] = $commodity_id;
		}

		$data	= array();
		foreach ($markets as $market_id => $cmkt) {
			foreach ($commodity_ids as $row => $commodity_id) {
				$commodity_db = $commodities_db[$commodity_id];

				if($sheet->getCell($cmkt.$row)->getCalculatedValue() == '#REF!' || $sheet->getCell($cmkt.$row)->getCalculatedValue() == '#VALUE!') {
					$price = $sheet->getCell($cmkt.$row)->getOldCalculatedValue();
				} else {
					$price = $sheet->getCell($cmkt.$row)->getCalculatedValue();
				}

				if(!intval($price)>0) continue;
				$data[$market_id][$commodity_id] = $price * $commodity_db->multiplier;
			}
		}
		
		return $data;
	}
}
