<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerSelectize extends JKControllerAdmin {
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'Selectize', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function getItems() {
		$model		= $this->getModel();
		$items		= $model->searchItems();
		echo json_encode($items);
		$this->app->close();
	}

	public function getItemsByParent() {
		$model		= $this->getModel();
		$items		= $model->searchItemsByParent();
		echo json_encode($items);
		$this->app->close();
	}
}
