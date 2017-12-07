<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerSurvey extends GTControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->getViewItem($urlQueries = array());
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function prepareJSON($data, $result = true, $message = null) {
		$json = new stdClass();
		$json->result = $result;
		$json->data = $data;

		if($message) {
			$json->message = $message;
		}

		header('Content-type: application/json; charset=utf-8');
		echo json_encode($json);

		$this->app->close();
	}

	public function login($json = true) {
		$username	= $this->input->get('username', '', 'username');
		$password	= $this->input->get('password', '', 'raw');
		$token		= $this->input->get('token', '', 'raw');

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

		$authenticate	= JAuthentication::getInstance();
		$response		= $authenticate->authenticate($credentials, $options);
		$user			= JFactory::getUser($response->username);

		$status			= $response->status === JAuthentication::STATUS_SUCCESS;
		$model 			= $this->getModel();
		$surveyUser		= $model->getUser($user->id);
		if($status && @$surveyUser->id) {
			$referencesUser	= $model->getReferencesByUser($user->id);
			$model->updateToken($surveyUser->id, $token);

			$data				= new stdClass();
			$data->id			= $user->id;
			$data->displayname	= $user->name;
			$data->username		= $user->username;
			$data->type			= $surveyUser->type;
			$data->cities		= $referencesUser;
			$this->prepareJSON($data);
		} else {
			$this->prepareJSON(null, false, 'Kombinasi Username dan Password salah');
		}
	}

	public function testFirebase() {
		$reg_id = $this->input->get('reg_id', '', 'raw');
		$message = $this->input->get('message', '', 'raw');
		$user_type = $this->input->get('user_type');

		$this->sendToFirebase($reg_id, $message, $user_type, true);
		$this->app->close();
	}

	protected function sendToFirebase($reg_ids, $message, $user_type = 'surveyor', $printed = false) {
		$reg_ids = is_array($reg_ids) ? $reg_ids : array($reg_ids);

		define( 'API_ACCESS_KEY', 'AIzaSyBJOMP-RTFgycrnlb2pbNXdFWHnhP41B40' );

		$msg = array(
			'body'		=> $message,
			'click_action' => 'NOTIFSURVEYOR', //NOTIFVALIDATOR
			'title'		=> 'Capturing Data PIHPS Nasional',
			'vibrate'	=> 1,
			'sound'		=> 2
		);

		$fields = array(
			'registration_ids'	=> $reg_ids,
			'notification'		=> $msg,
			'data'				=> array('type' => $user_type)
		);

		$headers = array(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch);
		curl_close( $ch );
		
		if ($printed) {
			echo $result;
		}
	}

	public function checkData() {
		$model = $this->getModel();
		$data = $model->getPrice();

		if($data->id > 0) {
			$this->prepareJSON($data, true, 'Data sudah terisi');
		} else {
			$this->prepareJSON(null, false, 'Data belum terisi');
		}
	}

	public function getItems() {
		$model		= $this->getModel();
		$prices		= $model->getPrices();

		if(count($prices) > 0) {
			$this->prepareJSON($prices, true);
		} else {
			$this->prepareJSON(null, false, 'Belum ada data yang dapat ditampilkan');
		}
	}

	public function getItem() {
		$model			= $this->getModel();
		$sellers		= $model->getSellers();
		$categories		= $model->getCommodityCategories();
		$commodities	= $model->getCommodities();
		
		$price			= $model->getPrice();
		$priceOld		= $model->getPrice(true);
		$detail 		= $model->getPriceDetail(@$price->id);
		$detailOld 		= $model->getPriceDetail(@$priceOld->id);

		foreach ($sellers as $seller) {
			$selected	= explode(',', $seller->commodities);
			$coms		= $model->prepareCommodities($categories, $commodities, $selected);

			foreach ($coms as &$com) {
				$price_now			= @$detail[$seller->id][$com->id];
				$price_then			= @$detailOld[$seller->id][$com->id];
				
				$com->price_then	= intval(@$price_then->price);
				$com->price_now		= intval(@$price_now->price);
				$com->status 		= intval(@$price_now->is_revision);
			}

			$seller->commodities = $coms;
		}

		$data			= new stdClass();
		$data->id		= intval(@$price->id);
		$data->message 	= @$price->message;
		$data->type		= @$price->id > 0 ? (@$price->status == 'revision' ? 'revision' : 'edit') : 'new';
		$data->sellers	= $sellers;

		$this->prepareJSON($data);
	}

	public function submit() {
		$model			= $this->getModel();

		switch($model->submit()) {
			case 1:
				$this->prepareJSON(null, true, 'Data berhasil disimpan');
				break;
			case 2:
				$this->prepareJSON(null, false, 'Data gagal disimpan');
				break;
			case 3:
				$this->prepareJSON(null, 'failed', 'Sesi habis, silakan login ulang');
				break;
		}
	}

	public function getSurveys() {
		$model		= $this->getModel();
		$surveys	= $model->getSurveys();

		if(count($surveys) > 0) {
			$this->prepareJSON($surveys, true);
		} else {
			$this->prepareJSON(null, false, 'Belum ada data submisi yang dapat ditampilkan');
		}
	}

	public function getSurveyDetail() {
		$model	= $this->getModel();
		$price	= $model->getSurvey();
		$detail = $model->getPriceDetail(@$price->id);

		$this->input->set('user_id', $price->surveyor_id);
		$this->input->set('market_id', $price->market_id);
		$this->input->set('date', $price->date);

		$sellers		= $model->getSellers();
		$categories		= $model->getCommodityCategories();
		$commodities	= $model->getCommodities();
		
		$priceOld		= $model->getPrice(true);
		$detailOld 		= $model->getPriceDetail(@$priceOld->id);

		foreach ($sellers as $seller) {
			$selected	= explode(',', $seller->commodities);
			$coms		= $model->prepareCommodities($categories, $commodities, $selected);

			foreach ($coms as &$com) {
				$price_now			= @$detail[$seller->id][$com->id];
				$price_then			= @$detailOld[$seller->id][$com->id];
				
				$com->status		= intval(@$price_now->is_revision);
				$com->price			= intval(@$price_now->price);

				$com->price_then	= intval(@$price_then->price);
				$com->price_now		= intval(@$price_now->price);
				$com->diff			= null;
				$com->percent		= null;

				if($com->price_now > 0 && $com->price_then) {
					$com->diff		= $com->price_now - $com->price_then;
					$com->trend		= $com->diff > 0 ? 'up' : ($com->diff < 0 ? 'down' : 'still');
					$com->diff		= abs($com->diff);
					$com->percent	= round(($com->diff / $com->price_then) * 100, 2).'%';
				} else {
					$com->trend		= 'unknown';
				}
				
			}

			$seller->commodities = $coms;
		}

		$price->sellers	= $sellers;

		$this->prepareJSON($price);
	}

	public function validate() {
		$model = $this->getModel();

		switch($model->validateData()) {
			case 1:
				$this->prepareJSON(null, false, 'Data gagal disimpan');
				break;
			case 2:
				$this->prepareJSON(null, true, 'Data sudah berhasil divalidasi tanpa revisi');
				break;
			case 3:
				$this->prepareJSON(null, true, 'Permintaan revisi sudah berhasil dikirim');
				break;
			case 4:
				$this->prepareJSON(null, 'failed', 'Sesi habis, silakan login ulang');
				break;
		}
	}
}
