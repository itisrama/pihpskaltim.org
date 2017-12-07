<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class GTPIHPSViewImport extends GTView {

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
		$this->document->addScript(GT_GLOBAL_JS . '/stats.js');
		
		// Set page title
		$this->page_title = $layout == 'preview' ? JText::_('COM_GTPIHPS_PT_IMPORT_PREVIEW') : JText::_('COM_GTPIHPS_PT_IMPORT');
		GTHelperHTML::setTitle($this->page_title);

		// Assign additional data
		$this->canDo = GTHelperAccess::getActions();

		// Add pathway
		$pathway = $this->app->getPathway();
		$pathway->addItem($this->item_title);
		
		// Check permission and display
		GTHelperAccess::checkPermission($this->canDo);

		if($layout == 'preview') {
			$this->data 			= JRequest::getVar('jform', null, 'array', 'array');
			$this->data				= JArrayHelper::toObject($this->data);

			//Retrieve file details from uploaded file, sent from upload form
			$file			= GTHelperArray::toFiles(JRequest::getVar('jform', null, 'files', 'array'));
			$filename		= $file->file_excel->tmp_name;
			$format			= $this->get('Format');
			$items			= $this->readFile($filename, $format);
			$this->items	= array();
			$this->json		= array();

			foreach ($items as $markets) {
				foreach ($markets as $market_id => $commodities) {
					$this->json[$market_id] = $commodities;
					foreach ($commodities as $commodity_id => $price) {
						$this->items[$commodity_id][$market_id] = $price;
					}
				}
			}

			$markets 		= array();
			$all_markets	= $this->get('Markets');
			$mlist			= json_decode($format->market_columns);
			foreach ($mlist as $mcolumns) {
				foreach ($mcolumns as $market_id => $column) {
					$markets[$market_id] = $all_markets[$market_id];
				}
			}

			$this->json				= htmlentities(json_encode($this->json));
			$this->region			= $this->get('Region');
			$this->regency			= $this->get('Regency');
			$this->markets			= $markets;
			$this->commodityList	= $this->get('CommodityList');
		}

		parent::display($tpl);
	}

	protected function readFile($filename, $format) {
		// Read your Excel workbook
		JLoader::import('phpexcel.Classes.PHPExcel');
		JLoader::import('phpexcel.Classes.PHPExcel.IOFactory');
		try {
			$inputFileType	= PHPExcel_IOFactory::identify($filename);
			$objReader		= PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel	= $objReader->load($filename);
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($filename,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$data		= array();
		$mcolumns	= json_decode($format->market_columns);
		foreach ($mcolumns as $index => $markets) {
			$sheet = $objPHPExcel->getSheet($index);
			$data[$index] = $this->readWorksheet($sheet, $markets, $format);
		}

		return $data;
	}

	protected function readWorksheet($sheet, $markets, $format) {
		$rs		= 0;
		$re		= 200;
		$ccoms	= explode(',', $format->commodity_column);
		$cden	= $format->denomination_column;

		// Get worksheet dimensions
		$commodities_db	= $format->read_denomination ? $this->get('CommodityNameDenoms') : $this->get('CommodityNames');
		$coms 			= array();
		foreach ($ccoms as $k => $ccom) {
			$coms[$k]	= array_map('current', $sheet->rangeToArray($ccom.$rs.':'.$ccom.$re, NULL, TRUE, FALSE));
		}
		
		$denoms_c		= array_map('current', $sheet->rangeToArray($cden.$rs.':'.$cden.$re, NULL, TRUE, FALSE));
		$commodities 	= array();
		for($i = $rs-$rs; $i<=$re-$rs; $i++) {
			foreach ($ccoms as $k => $ccom) {
				$commodities[$i] = strlen(trim($coms[$k][$i])) > 0 ? trim($coms[$k][$i]) : @$commodities[$i];
			}
		}
		$commodity_ids	= array();
		$prices			= array();
		foreach ($markets as $market_id => $cmkt) {
			$prices[$market_id]	= array_map('current', $sheet->rangeToArray($cmkt.$rs.':'.$cmkt.$re, NULL, TRUE, FALSE));
		}
		foreach ($commodities as $row => $commodity) {
			$mkt_prices = reset($prices);
			$price = floatval(preg_replace("/[^\d]/", "", $mkt_prices[$row]));
			if(!$price>0) continue;
			$commodity = $format->read_denomination ? $commodity.$denoms_c[$row] : $commodity;
			$commodity = GTHelper::cleanstr($commodity);
			$commodity_id = array_search($commodity, $commodities_db);
			if(!$commodity_id > 0) continue;
			$commodity_id = intval($commodity_id);
			$commodity_ids[$row] = $commodity_id;

			unset($commodities_db[$commodity_id.'_']);
			unset($commodities_db[$commodity_id.'__']);
			unset($commodities_db[$commodity_id.'___']);
			unset($commodities_db[$commodity_id.'____']);
		}
		$data	= array();
		foreach ($markets as $market_id => $cmkt) {
			$mkt_prices	= $prices[$market_id];
			foreach ($commodity_ids as $row => $commodity_id) {
				$price = floatval(preg_replace("/[^\d]/", "", $mkt_prices[$row]));
				if(!$price>0) continue;
				$data[$market_id][$commodity_id] = $price;
			}
		}

		return $data;
	}
}
