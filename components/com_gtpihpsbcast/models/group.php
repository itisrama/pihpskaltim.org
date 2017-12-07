<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSBCastModelGroup extends GTModelAdmin {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem();
		if(!is_object($data)) return false;
		$this->item	= $data;
		return $data;
	}

	public function save($data) {
		$data	= JArrayHelper::toObject($data);
		return parent::save($data);
	}

	public function delete(&$pks) {
		return parent::delete($pks);
	}
}
