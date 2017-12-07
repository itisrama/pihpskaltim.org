<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

$isLookback	= $this->price_type_id == 1 || $this->layout != 'default';

$objPHPExcel = $this->objPHPExcel;

// Styles
$style			= new stdClass();
$style->center	= array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$style->right	= array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$style->left	= array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT));
$style->bold	= array('font'  => array('bold'  => true));

$rowNum = 1;

// Title
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':K'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_HEADER_REPORT'));
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum)->getFont()->setSize(14);
$objPHPExcel->getActiveSheet()->getRowDimension($rowNum)->setRowHeight('30px');

$rowNum += 2;

// Filter period
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':B'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_PERIOD'));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$rowNum.':E'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowNum, $this->period);

$rowNum++;

// Filter commodity
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':B'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_COMMODITY'));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$rowNum.':E'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowNum, $this->commodity);

$rowNum++;

// Filter report type
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':B'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_REPORT_TYPE'));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$rowNum.':E'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowNum, $this->report_type);

$rowNum += 2;

$endColumns = PHPExcel_Cell::stringFromColumnIndex(count($this->periods)+1);

// Header stylin
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$endColumns.$rowNum)->applyFromArray($style->center);
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$endColumns.$rowNum)->applyFromArray($style->bold);

// Header writin
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_NUM'));
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, JText::_('COM_GTPIHPS_FIELD_PROVINCE'));

foreach ($this->periods as $i => $period) {
	$column = PHPExcel_Cell::stringFromColumnIndex($i+2);
	$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $period->sdate);
}

// Wrapping
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$endColumns.$rowNum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$endColumns.$rowNum)->getAlignment()->setWrapText(true);

$rowDataStart = $rowNum+1;

$objPHPExcel->getActiveSheet()->freezePane('C'.$rowDataStart);

// Add the data
$i = 1;

$rowNum++;
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, GTHelperNumber::toRoman($i));
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, strtoupper(JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES')));

$item = (array) @$this->itemsAll[0];

$colNum = 2;
foreach($this->periods as $date){
	$column = PHPExcel_Cell::stringFromColumnIndex($colNum);
	$price	= $isLookback ? @$item[$date->unix] : GTHelper::getRecentPrice($date->unix, $item);
	
	if($price > 0){
		$price 	= GTHelperCurrency::toNumber($price);
		$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "-"_-;_-@_-');
	}
	else{
		$price = '-';
		$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->center);
	}

	$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $price);
	$colNum++;
}
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$column.$rowNum)->applyFromArray($style->bold);
$i++;

foreach($this->provinceList as $province_id => $province){
	$regencies	= (array) $this->regencyList[$province_id];
	$showMarket	= $this->showMarket && count($regencies) > 0;

	$province = $showMarket ? strtoupper($province) : $province;
	$rowNum++;
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, GTHelperNumber::toRoman($i));
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, $province);
	
	$item = (array) @$this->itemsProv[$province_id];

	$colNum = 2;
	foreach($this->periods as $date){
		$column = PHPExcel_Cell::stringFromColumnIndex($colNum);
		$price	= $isLookback ? @$item[$date->unix] : GTHelper::getRecentPrice($date->unix, $item);

		if($price > 0){
			$price 	= GTHelperCurrency::toNumber($price);
			$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "-"_-;_-@_-');
		}
		else{
			$price = '-';
			$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->center);
		}

		$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $price);
		$colNum++;
	}
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$column.$rowNum)->applyFromArray($style->bold);
	$i++;

	$j = 1;
	foreach($this->regencyList[$province_id] as $regency_id => $regency){
		$rowNum++;
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, $j);
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, $regency);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$rowNum)->getAlignment()->setIndent(1);
		
		$item = (array) @$this->itemsReg[$regency_id];

		$colNum = 2;
		foreach($this->periods as $date){
			$column = PHPExcel_Cell::stringFromColumnIndex($colNum);
			$price	= $isLookback ? @$item[$date->unix] : GTHelper::getRecentPrice($date->unix, $item);
			
			if($price > 0){
				$price 	= GTHelperCurrency::toNumber($price);
				$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "-"_-;_-@_-');
			}
			else{
				$price = '-';
				$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->center);
			}

			$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $price);
			$colNum++;
		}
		if($showMarket) {
			$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum.':'.$column.$rowNum)->applyFromArray($style->bold);
		}
		$j++;

		if($showMarket) {
			$k = 'a';
			foreach($this->marketList[$regency_id] as $market_id => $market){
				$rowNum++;
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, $k);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, $market);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$rowNum)->getAlignment()->setIndent(2);
				
				$item = (array) @$this->itemsMar[$market_id];

				$colNum = 2;
				foreach($this->periods as $date){
					$column = PHPExcel_Cell::stringFromColumnIndex($colNum);
					$price	= $isLookback ? @$item[$date->unix] : GTHelper::getRecentPrice($date->unix, $item);
					
					if($price > 0){
						$price 	= GTHelperCurrency::toNumber($price);
						$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "-"_-;_-@_-');
					}
					else{
						$price = '-';
						$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->center);
					}

					$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $price);
					$colNum++;
				} 
				$k++;
			}
		}
	}
}

$objPHPExcel->getActiveSheet()->getStyle('A'.$rowDataStart.':'.$endColumns.$rowNum)->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('A'.$rowDataStart.':'.'A'.$rowNum)->applyFromArray($style->center);

// Borders the table
$styleBorder = array(
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.($rowDataStart-1).':'.$endColumns.$rowNum)->applyFromArray($styleBorder);
$objPHPExcel->getActiveSheet()->getStyle('A'.($rowDataStart-1).':'.$endColumns.($rowDataStart-1))->applyFromArray(
	array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK)))
);
$objPHPExcel->getActiveSheet()->getStyle('A'.($rowDataStart-1).':'.$endColumns.$rowNum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

$nColumns = count($this->periods)+2;
for($i=0;$i < $nColumns;$i++) {
    $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}
?>
