<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewWidget extends GTView {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		$this->input->set('tmpl', 'component');

		// Load Script
		$this->document->addScript(GT_ADMIN_JS . '/jquery.sparkline.min.js');
		$this->document->addScript(GT_GLOBAL_JS . '/widget.js');

		// Get model data.
		
		$this->items			= $this->get('Items');
		$this->itemsCat			= $this->get('ItemsCat');
		$this->commodityList	= $this->get('CommodityList');
		$this->location			= $this->get('Location');
		$this->dates 			= $this->get('LatestDates');

		parent::display($tpl);
	}

}
