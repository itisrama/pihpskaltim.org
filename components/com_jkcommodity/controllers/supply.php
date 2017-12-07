<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerSupply extends JKControllerForm {

	function loadLatestSupplies(){
		$mainframe =& JFactory::getApplication();
		$model = $this->getModel('Supply');

		$data = $model->getLastSupplies();
		if(isset($data[""])){
		    $data = null;
		}
		echo json_encode($data);
		
		$mainframe->close();
	}
}
