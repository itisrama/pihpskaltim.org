<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

$phpexcel = $this->phpexcel;
//Setup header row
$column_index = 0;
$row_index = 7;

$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index, $this->row_header);
$style = $phpexcel->getActiveSheet()->getStyleByColumnAndRow($column_index, $row_index);
$style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$style->getFont()->setSize(12);
$style->getFont()->setBold(true);
foreach ($this->columns as $column) {
	$column_index++;
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index, $column->name);
	$style = $phpexcel->getActiveSheet()->getStyleByColumnAndRow($column_index, $row_index);
	$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$style->getFont()->setSize(12);
	$style->getFont()->setBold(true);
}

foreach ($this->rows as $row) {
	$column_index = 0;
	$row_index++;
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index, $row->name);
	$style = $phpexcel->getActiveSheet()->getStyleByColumnAndRow($column_index, $row_index);
	if(!is_numeric($row->id)) {
		$style->getFont()->setBold(true);
	}
	if($this->layout == 'period') {
		$style->getNumberFormat()->setFormatCode('[$-F800]dddd, mmmm dd, yyyy');
		$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
	}
	$style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	foreach ($this->columns as $column) {
		$column_index++;
		$cell_val = isset($this->data[$row->id][$column->id]) ? $this->data[$row->id][$column->id] : (is_numeric($row->id) ? '-' : NULL );
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index, $cell_val);
		$style = $phpexcel->getActiveSheet()->getStyleByColumnAndRow($column_index, $row_index);
		
		if(isset($this->data[$row->id][$column->id])) {
			$style->getNumberFormat()->setFormatCode('_-Rp* #,##0_-;-Rp* #,##0_-;_-Rp* "0"_-;_-@_-');;
		} else {
			$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		}
		$style->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}
}

//Set autosizes on the columns
for ($i = 0; $i <= $column_index; $i++) {
	$phpexcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}
//Calulate the column widths to attempt to autosize them
$phpexcel->getActiveSheet()->calculateColumnWidths();
for ($i = 0; $i <= $column_index; $i++) {
	$phpexcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(false);
	$calculatedWidth = $phpexcel->getActiveSheet()->getColumnDimensionByColumn($i)->getWidth();
	$phpexcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth((int) $calculatedWidth);
}
$phpexcel->getActiveSheet()->calculateColumnWidths();

// Set Header
$column_index = 0;
$row_index = 1;

$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index, strtoupper(JText::_('COM_JKCOMMODITY_HEADER_REPORT')));
$style = $phpexcel->getActiveSheet()->getStyleByColumnAndRow($column_index, $row_index++);
$style->getFont()->setSize(12);
$style->getFont()->setBold(true);

$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index++, JText::_('COM_JKCOMMODITY_LABEL_PERIOD') . ' : ' . $this->header->period);
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index++, JText::_('COM_JKCOMMODITY_LABEL_CITY') . ' : ' . $this->header->city);
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index++, JText::_('COM_JKCOMMODITY_LABEL_MARKET') . ' : ' . $this->header->market);
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($column_index, $row_index++, JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE') . ' : ' . $this->header->layout);
