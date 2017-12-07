<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelRef_Holiday extends GTModelAdmin{

	protected function populateState() {
		parent::populateState();
	}
	
	public function getItem($pk = null) {
		$data		= parent::getItem();
		if(!is_object($data)) return false;
		$this->item	= $data;
		return $data;
	}

	public function getItemView() {
		$data		= parent::getItem();
		if(!is_object($data)) return false;

		$this->item	    = $data;
		return $data;
	}

	public function save($data, $return_num = false) {
		$data		= JArrayHelper::toObject($data);
		
		$data->end = (empty($data->end))? $data->start : $data->end;

		if($data->end < $data->start){
		    $temp = $data->start;
		    $data->start    = $data->end;
		    $data->end      = $temp;
		}
		
		$return = parent::save($data);
		return $return;
	}
}
