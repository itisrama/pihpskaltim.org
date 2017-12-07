<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerImport_Excel extends JKControllerForm{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getModel($name = 'Price', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function send() {
		$model			= $this->getModel();
		$data			= json_decode($this->input->get('json', null, false));
		$city_id		= $this->input->get('city_id');
		$date			= $this->input->get('date');

		$notif_markets	= array();
		foreach ($data as $market_id => $details) {
			$market				= $model->getMarket2($market_id);
			$price				= new stdClass();
			$details			= array_filter(JArrayHelper::fromObject($details));

			$price->id			= 0;
			$price->city_id		= $city_id;
			$price->market_id	= $market_id;
			$price->date		= $date;
			$price->details		= $details;

			$notif_markets[]	= sprintf(JText::_('COM_JKCOMMODITY_IMPORT_SUCCESS_COMMODITY'), $market->name, count($details));
			$model->save($price);
		}

		$this->setMessage(sprintf(JText::_('COM_JKCOMMODITY_IMPORT_SUCCESS'), JHtml::date($date, 'j F Y'), implode('', $notif_markets)));
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item, false));
	}

	public function batchExcel() {
		$model			= $this->getModel();
		$model_import	= $this->getModel('Import_Excel');
		$formats		= $model_import->getFormats();

		$codes = array(
			'',
			'MKS_PB',
			'MKS_PNP',
			'MKS_TR',
			'PLP_SR',
			'PRP_LK',
			'BLK_SR',
			'BONE_SR'
		);

		foreach ($formats as &$format) {
			$format->commodities = $model_import->getCommodityNames($format->id);
		}

		unset($codes[0]);
		$formats = array_combine($codes, $formats);

		$path = JPATH_ROOT.DS.'tmp'.DS.'data';
		$dir = preg_grep('/^([^.])/', scandir($path));
		foreach ($dir as $file) {
			$filename = $path.DS.$file;
			echo "<pre>"; print_r($filename); echo "</pre>";			

			list($file, $ext) = explode('.', $file);
			list($date, $city, $market) = explode('_', $file.'__');

			if(!in_array($ext, array('xls','xlsx'))) continue;

			$code = $city.'_'.$market;
			$format = $formats[$code];

			$data = array_shift($this->readFile($filename, $format));
			foreach ($data as $market_id => $details) {
				$price				= new stdClass();
				$details			= array_filter($details);
				$price->id			= 0;
				$price->city_id		= $format->city_id;
				$price->market_id	= $market_id;
				$price->date		= $date;
				$price->details		= $details;
				$model->save($price);
			}
		}

		$this->app->close();
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
		$rs		= 1;
		$re		= $sheet->getHighestRow()+1;
		$ccoms	= explode(',', $format->commodity_column);

		// Get worksheet dimensions
		$comdb_names	= array();
		$commodities_db	= $format->commodities;
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
