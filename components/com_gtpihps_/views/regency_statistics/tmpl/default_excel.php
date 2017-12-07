<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

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

// Filter regency
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':B'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_REGENCY'));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$rowNum.':E'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowNum, ($this->regencies)? $this->regencies : JText::_('COM_GTPIHPS_ALL_REGENCIES'));

$rowNum++;

// Filter market
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowNum.':B'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, JText::_('COM_GTPIHPS_FIELD_MARKET'));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$rowNum.':E'.$rowNum);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowNum, ($this->markets)? $this->markets : JText::_('COM_GTPIHPS_ALL_MARKETS'));

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
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, JText::_('COM_GTPIHPS_FIELD_COMMODITY').'('.trim(GTHelperCurrency::$symbol).')');

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
$catNum = 0;
$comNum = 0;
foreach($this->commodityList as $commodity){
	if(is_numeric($commodity->value)) {
		$comNum++;
		$item = @$this->items[$commodity->value];
		$number = $comNum;
	} elseif(strpos($commodity->value, 'cat') == 0) {
		$catNum++; $comNum = 0;
		$category_id = end(explode('-', $commodity->value));
		$item = @$this->catItems[$category_id];
		$number = GTHelperNumber::toRoman($catNum);
	}

	$rowNum++;
	
	$level = substr_count($commodity->text, str_repeat('&nbsp;', 6));

	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowNum, $number);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rowNum)->applyFromArray($style->center);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowNum, str_replace("&nbsp;", '', $commodity->text));
	$objPHPExcel->getActiveSheet()->getStyle('B'.$rowNum)->getAlignment()->setIndent($level);

	$i++;

	

	$j = 2;
	foreach($this->periods as $date){
		$price		= @$item[$date->unix];

		$column = PHPExcel_Cell::stringFromColumnIndex($j);
	
		if($price > 0){
			$value 	= GTHelperCurrency::toNumber($price);
			$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "-"_-;_-@_-');
		} else{
			$value = '-';
			$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->center);
		}

		if(!is_numeric($commodity->value)) {
			$objPHPExcel->getActiveSheet()->getStyle($column.$rowNum)->applyFromArray($style->bold);
		}
		
		$objPHPExcel->getActiveSheet()->SetCellValue($column.$rowNum, $value);
		$j++;
	}
	if(!is_numeric($commodity->value)) {
		$objPHPExcel->getActiveSheet()->getStyle('B'.$rowNum.':'.$column.$rowNum)->applyFromArray($style->bold);
	}
}

$objPHPExcel->getActiveSheet()->getStyle('A'.$rowDataStart.':'.$endColumns.$rowNum)->getAlignment()->setWrapText(true);

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
