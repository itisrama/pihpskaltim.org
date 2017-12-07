<?php

/**
 * @package     GT Component
 * @author      Yudhistira Ramadhan
 * @link        http://gt.web.id
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;


class GTPIHPSModelService_Log extends GTModelAdmin{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function populateState() {
		parent::populateState();
	}

	public function getForm($data = array(), $loadData = true, $control = 'jform') {
		$form = parent::getForm($data, $loadData, $control);
		return $form;
	}

	public function getItem($pk = null) {
		$data		= parent::getItem($pk);
		return $data;
	}

	public function getItemView($pk = null) {
		$data		= parent::getItem($pk);
		if(!is_object($data)) return false;

		$this->item	= $data;
		return $data;
	}

	public function save($data) {
		return parent::dave($data);
	}

	public function saveLog($post, $get, $output = array(), $id = 0) {
		//return true;
		
		$task = @$post['task'] ? $post['task'] : @$get['task'];

		unset($post['option']);
		unset($get['option']);

		$serviceLog				= new stdClass();
		$serviceLog->id			= $id;
		$serviceLog->name		= $task;
		$serviceLog->get		= count($get) > 0 ? GTHelper::httpQuery($get, true) : null;
		$serviceLog->post		= count($post) > 0 ? GTHelper::httpQuery($post, true) : null;
		$serviceLog->get_json	= count($get) > 0 ? json_encode($get) : null;
		$serviceLog->post_json	= count($post) > 0 ? json_encode($post) : null;
		$serviceLog->output		= count($output) > 0 ? json_encode($output) : null;

		return $this->saveExternal($serviceLog, 'service_log', true);
		
	}
	

	public function delete($pks) {
		return parent::delete($pks);
	}
}
