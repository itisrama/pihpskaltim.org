<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSBCastControllerScheduler extends GTControllerForm
{
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->getViewItem($urlQueries = array());
	}

	public function checkSchedule() {
		$this->app->close();
	}

	public function dailySchedule() {
		$model = $this->getModel();
		$ids = $model->getIDs();
		
		foreach ($ids as $id) {
			$this->sendNow($id, false);
		}

		$this->app->close();
	}

	public function sendNow($id = null, $return = true) {
		$id = $id ? $id : $this->input->get('id');
		$model = $this->getModel();
		$item = $model->getItem($id);
		$members = $model->getMembers($item->group_ids);
		$contact_ids = array();
		foreach ($members as $member) {
			$contact_ids += explode(',', $member->members);
		}
		
		$contact_ids = array_unique($contact_ids);
		$contacts = $model->getContacts($contact_ids);

		$sms = $this->getSMSPrices($model, $item, $item->type);
		foreach ($contacts as $contact) {
			foreach ($sms as $sms_text) {
				$masking = new stdClass();
				$msisdn = str_replace(array(" ", "-"), "", $contact->phone);
				$msisdn = ltrim($msisdn, '0');
				$msisdn = ltrim($msisdn, '+');
				$msisdn = '62'. ltrim($msisdn, '62');

				$masking->id = 0;
				$masking->name = $contact->name;
				$masking->msisdn = $msisdn;
				$masking->message = $sms_text;

				$sms_text = urlencode($sms_text);
				$masking_url = "http://sms.egov.co.id/?option=com_gtsms&task=message.outgoing&msisdn=$msisdn&message=$sms_text&modem=GSM2&category_id=9";
				$masking->transaction_id = file_get_contents($masking_url);
				

				$model->saveExternal($masking, 'outgoing');
			}
		}
		
		if($return) {
			$this->setMessage(JText::_('COM_GTPIHPSBCAST_SMS_SENT'));

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($id, 'id'), false
				)
			);
		}
	}

	public function getSMSPrices($model, $item, $type) {
		$commodity_ids = $item->commodity_ids;
		$market_ids = $item->market_ids;
		$city_name = $model->getCity($market_ids);

		$date = $model->getLatestDate($market_ids);
		$prices = $model->getPrices($market_ids, $commodity_ids, $date, $type);
		$commodity_name = $model->getCommodityName(reset($commodity_ids));
		$count = 1;
		$limit = 155;
		$sms = array();
		$header = $type == 'commodity' ? 'Harga %s ' : 'Harga %s - ' . $commodity_name . ' ';
		$header = sprintf($header, $city_name);
		$header .= JHtml::date($date, 'd/m/Y') . ' ';

		$limit -= strlen($header)+2;
		$sms[$count][] = $header;
		$sms[$count][] = '';
		foreach ($prices as $price) {
			$row = $price->name . ' = ' . GTHelperCurrency::fromNumber(round($price->price/50)*50, '');
			$limit -= strlen($row)+1;
			if($limit < 0) {
				$limit = 155;
				$count++;
			}
			$sms[$count][] = $row;
		}

		if($limit-29 < 0) {
			$count++;
		}
		$sms[$count][] = '';
		$sms[$count][] = 'Info lengkap: hargapangan.id';

		foreach ($sms as $k => $item) {
			$sms_item = implode(PHP_EOL, $item);
			$sms[$k] = count($sms) > 1 ? '('.$k.')'.PHP_EOL.$sms_item : $sms_item;
		}

		return $sms;
	}

	public function test() {
		$model = $this->getModel();
		$price = $model->getPrices(array(), array(), '2015-04-17');
		$this->app->close();
	}
}
