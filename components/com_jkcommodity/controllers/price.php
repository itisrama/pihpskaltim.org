<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerPrice extends JKControllerForm {

	function loadMarket(){
		$mainframe =& JFactory::getApplication();
		$model = $this->getModel('price');
		$data = $model->getMarket();
		
		echo json_encode($data);
		
		$mainframe->close();
	}
	
	function loadLatestPrices(){
		$mainframe =& JFactory::getApplication();
		$model = $this->getModel('price');

		$data = $model->getLastPrices();
		if(isset($data[""])){
		    $data = null;
		}
		echo json_encode($data);
		
		$mainframe->close();
	}
}
