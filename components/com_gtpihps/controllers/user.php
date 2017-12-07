<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerUser extends GTControllerForm {
	
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->getViewItem($urlQueries = array('id'));
	}

	public function login($json = true) {
		$username = $this->input->get('username', '', 'USERNAME');
		$password = $this->input->get('password', '', 'RAW');

		if(!($username && $password)) {
			$json = new stdClass();
			$json->result = false;
			
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($json);

			$this->app->close();
		}

		// Get the log in options.
		$options				= array();
		$options['remember']	= false;
		$options['return']		= null;

		// Get the log in credentials.
		$credentials				= array();
		$credentials['username']	= $username;
		$credentials['password']	= $password;

		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);
		$token = GTHelper::getUserToken($response->username);
		$user = JFactory::getUser($response->username);
		
		$result = new stdClass();
		$result->status = $response->status === JAuthentication::STATUS_SUCCESS;
		$result->msg = $result->status ? 'success' : 'failed';
		$result->username = $result->status ? $user->username : '';
		$result->name = $result->status ? $user->name : '';
		$result->token = $result->status ? $token : '';

		if($json) {
			$json = new stdClass();
			$json->result = $result;
			
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($json);

			$this->app->close();
		} else {
			return $result;
		}
	}

	public function checkGroup() {
		$group_id = $this->input->get('group_id');
		
		$user = $this->login(false);
		if($user->status) {
			$groups = JFactory::getUser($user->username)->groups;
			
			$result = in_array($group_id, $groups);
		} else {
			$result = false;
		}

		$json = new stdClass();
		$json->result = $result;
		
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);

		$this->app->close();
	}

	public function checkChatroom() {
		$user = $this->login(false);
		if($user->status) {
			$model		= $this->getModel('Json');
			$user_id	= (int) JUserHelper::getUserId($user->username);	
			$result		= $model->checkChatroomMember($user_id);
		} else {
			$result = false;
		}

		$json = new stdClass();
		$json->result = $result;
		
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);

		$this->app->close();
	}
}
