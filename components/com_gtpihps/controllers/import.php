<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerImport extends GTControllerForm{

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = 'Import', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function send() {
		$jform 			= JArrayHelper::toObject(JRequest::getVar('jform', array(), 'array', 'array'));
		$file			= GTHelperArray::toFiles(JRequest::getVar('jform', null, 'files', 'array'));
		$filename		= $file->file_excel->tmp_name;
		$start			= $jform->start_date;
		$start_date		= 'N/A';
		$end_date		= 'N/A';

		$model		= $this->getModel();
		$provinces	= $model->getProvinces();
		$categories	= $model->getCategories();

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

		foreach ($objPHPExcel->getWorksheetIterator() as $sheet) {
			$result		= $this->readWorksheet($sheet, $start, $provinces, $categories);
			$start_date	= $result->start_date;
			$end_date	= $result->end_date;
			$provinces	= $result->provinces;
			$data 		= $result->data;
			echo "<pre>"; print_r($data); echo "</pre>";
			$masterIDs	= $model->getMasterIDs($start_date, $end_date, $provinces);
			$detailIDs	= $model->getDetailIDs($start_date, $end_date, $provinces);
			
			$masters = array();
			$details = array();
			foreach ($data as $key => $items) {
				list($date, $province_id) = explode(':', $key);
				$masterID 				= @$masterIDs[$key];

				$master					= new stdClass();
				$master->id				= intval(@$masterID->id);
				$master->province_id	= $province_id;
				$master->date			= $date;
				$masters[]				= $master;

				foreach ($items as $category_id => $fluctuation) {
					$detailID 				= @$detailIDs[$key.':'.$category_id];

					$detail					= new stdClass();
					$detail->id				= intval(@$detailID->id);
					$detail->fluc_id		= $key;
					$detail->category_id	= $category_id;
					$detail->fluctuation	= $fluctuation;
					$details[]				= $detail;
				}
			}
			// Save Master
			$model->saveBulk($masters, 'flucs');

			$masterIDs = $model->getMasterIDs($start_date, $end_date, $provinces);
			foreach ($details as &$detail) {
				$masterID = @$masterIDs[$detail->fluc_id];
				if(!@$masterID->id) continue;
				$detail->fluc_id = $masterID->id;
			}

			// Save Detail
			$model->saveBulk($details, 'fluc_details', false);
		}

		$start_date = JHtml::date($start_date, 'F Y');
		$end_date = JHtml::date($end_date, 'F Y');
		$this->setMessage(sprintf(JText::_('COM_GTPIHPS_IMPORT_FLUCS_SUCCESS'), $start_date, $end_date));
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item, false));
	}

	protected function readWorksheet($sheet, $start_date, $provinces, $categories) {
		$highestRow		= $sheet->getHighestRow(); 
		$highestColumn	= PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
		$colProv		= array_map('current', $sheet->rangeToArray('A3:'.'A'.$highestRow, NULL, TRUE, FALSE));
		$colCat			= array_map('current', $sheet->rangeToArray('B3:'.'B'.$highestRow, NULL, TRUE, FALSE));

		foreach ($colProv as &$prov) {
			$prov = trim($prov);
			$prov = $provinces[$prov];
			$prov = $prov->id;
		}

		foreach ($colCat as &$cat) {
			$cat = trim($cat);
			$cat = $categories[$cat];
			$cat = $cat->id;
		}

		$data = array();
		$months = array();
		$i = 0;
		for ($col = 3; $col <= $highestColumn; $col++) {
			$column		= PHPExcel_Cell::stringFromColumnIndex($col);
			$month		= JHtml::date($start_date.' +'.$i.'month', 'Y-m-01');
			$colData	= array_map('current', $sheet->rangeToArray($column.'3:'.$column.$highestRow, NULL, TRUE, FALSE));

			$i++;
			if(!array_sum($colData) <> 0) continue;
			foreach ($colData as $colIndex => $colItem) {
				if(!$colItem <> 0) continue;
				$provID = $colProv[$colIndex];
				$catID = $colCat[$colIndex];
				$data[$month.':'.intval($provID)][$catID] = $colItem;
			}
			$months[] = $month;
		}
		$result = new stdClass();
		$result->start_date = reset($months);
		$result->end_date = end($months);
		$result->provinces = array_unique($colProv);
		$result->data = $data;

		return $result;
	}
	
}
