<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerInflasi extends JKController
{
	var $inflasi;
	function __construct(){
		$this->inflasi = $this->getModel('inflasi');
		parent::__construct();
	}
	function save() {
		$db = JFactory::getDBO();
		$model = $this->getModel('inflasi');
		
		$ihk = JRequest::getVar('ihk');
		$bulan = JRequest::getVar('periodeBulan');
		$tahun = JRequest::getVar('periodeTahun');
		
		$inflasi = new stdClass();
		foreach ($ihk as $id=>$data) {
			$inflasi->refid = $id;
			$inflasi->ihk = (empty($data)) ? 0 : $data;
			$inflasi->periode = $tahun.'-'.$bulan.'-01';
			
			$query = "SELECT id FROM #__jkcommodity_inflasi WHERE periode = '{$tahun}-{$bulan}-01' AND refid = {$id}";
			$db->setQuery($query);
			$row = $db->loadObject();
			
			if(empty($row)) {
				$model->store($inflasi);
			}
		}
		
		$return = base64_decode(JRequest::getVar('return_url'));
		$this->setRedirect($return, 'Tersimpan');
	}
}