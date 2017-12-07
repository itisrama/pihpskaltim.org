<?php

defined('JPATH_PLATFORM') or die;
//Include PHPExcel classes
JLoader::import('phpexcel.Classes.PHPExcel');
JLoader::import('phpexcel.Classes.PHPExcel.IOFactory');
class JDocumentXLS extends JDocument {

	private $_name = 'export';
	private $_phpexcel = null;

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : __construct
	  Purpose     : Class Constructor
	  Parameter   : $options
	  Returns     : None
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function __construct($options = array()) {		
		parent::__construct($options);

		//set mime type
		$this->_mime = 'application/vnd.ms-excel';

		//set document type
		$this->_type = 'xls';
	}

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : getName
	  Purpose     : To get the current filename for the excel file
	  Parameter   : None
	  Returns     : String
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function getName() {
		return $this->_name;
	}

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : getPhpExcelObj
	  Purpose     : To get the PHPExcel object for use
	  Parameter   : None
	  Returns     : Object
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function &getPhpExcelObj() {
		if (!$this->_phpexcel)
			$this->_phpexcel = new PHPExcel();
		return $this->_phpexcel;
	}

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : render
	  Purpose     : To render the document
	  Parameter   : @param boolean  $cache    If true, cache the output
	  Parameter   : @param array   $params   Associative array of attributes
	  Returns     : String
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function render($cache = false, $params = array()) { //Write out response headers
		//Set workbook properties to some defaults if not currently set
		//Currently all these properties are not set for the Excel5 (xls) writer but are here in case future versions do
		$objPhpExcel = & $this->getPhpExcelObj();
		$config = new JConfig();
		$workbook_properties = $objPhpExcel->getProperties();
		if (!$workbook_properties->getCategory())
			$workbook_properties->setCategory('Exported Report From ' . $config->sitename);
		if ($workbook_properties->getCompany() == 'Microsoft Corporation' && $config->sitename)
			$workbook_properties->setCompany($config->sitename);
		if ($workbook_properties->getCreator() == 'Unknown Creator' && $config->sitename)
			$workbook_properties->setCreator($config->sitename);
		if (!$workbook_properties->getDescription())
			$workbook_properties->setDescription($this->getDescription());
		if (!$workbook_properties->getLastModifiedBy())
			$workbook_properties->setLastModifiedBy($config->sitename);
		if (!$workbook_properties->getSubject())
			$workbook_properties->setSubject($this->getTitle());
		if ($workbook_properties->getTitle() == 'Untitled Spreadsheet' && $this->getTitle())
			$workbook_properties->setTitle($this->getTitle());
		$objPhpExcel->setProperties($workbook_properties);

		//Get the Excel 5 type IO object to write out the binary document
		$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');		

		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		// It will be called file.xls
		header('Content-Disposition: attachment; filename="'.$this->getName().'.xls"');

		$objWriter->save('php://output');
	}

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : setPhpExcelObj
	  Purpose     : To set the PHPExcel object for use
	  Parameter   : $objPhpExcel
	  Returns     : None
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function setPhpExcelObj($objPhpExcel) {
		$this->_phpexcel = $objPhpExcel;
	}

	/* ------------------------------------------------------------------------------------------------------------
	  Method Name : setName
	  Purpose     : To set the current filename for the excel file
	  Parameter   : $name
	  Returns     : None
	  Revisions   :
	  ------------------------------------------------------------------------------------------------------------ */

	function setName($name) {
		$this->_name = JFilterOutput::stringURLSafe($name);
	}

}