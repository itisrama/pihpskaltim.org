<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewMobile extends GTView {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		$this->input->set('tmpl', 'component');

		// Load Script
		$this->document->addScript(GT_ADMIN_JS . '/mobile-detect.min.js');
		$this->document->addScript(GT_GLOBAL_JS . '/mobile_redirect.js');

		parent::display($tpl);
	}

}
