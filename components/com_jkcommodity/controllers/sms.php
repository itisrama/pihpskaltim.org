<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityControllerSMS extends JKController {
	function check() {
		$dirInc	= '/home/tpid-ntt.org/sms/incoming/';
		$dirOut	= '/home/tpid-ntt.org/sms/outgoing/';
		foreach(scandir($dirInc) as $file) {
			if(!is_file($dirInc.$file)) continue;
			$incSMS		= file($dirInc.$file);
			$start_read	= 0;
			$number		= null;
			$message	= array();
			foreach($incSMS as $line) {
				// get mobile number
				if(is_numeric(strpos($line, 'From:'))) {
					$number = str_replace('From: ', '', $line);
				}
				// get message
				if($start_read) {
					$message[] = $line;
				}
				if(strlen($line) < 2) {
					$start_read = 1;
				}
			}
			$message	= strtolower(trim(implode(' ', $message)));
			// Delete Incoming File
			unlink($dirInc.$file);
			$reply 		= $this->reply($message);
			
			//echo "<pre>"; print_r($reply); echo "</pre>";
			if(!is_null($reply->msg)) {
				// Send Reply
				$msgReply	= array();
				$msgReply[]	= "To: $number";
				$msgReply[]	= $reply;
				
				$msgReply	= implode(PHP_EOL, $msgReply);

				// Write Send File
				$handle = fopen($dirOut.date('Ymd.His').'.txt', "w");
				fwrite($handle, $msgReply);
				fclose($handle);
			}
			
		}
		$this->app->close();
	}

	function checkWeb() {
		$sms 	= $_GET['sms'];
		$reply 	= $this->reply($sms);

		echo $reply;
		$this->app->close();
	}

	function reply($message) {
		$message	= explode('#', strtolower($message));
		
		$command	= !isset($message[0]) ? 'n/a' : $message[0];
		$param1		= !isset($message[1]) ? 'n/a' : $message[1];
		$param2		= !isset($message[2]) ? 'n/a' : $message[2];
		$reply		= null;
		switch($command) {
			case 'hk':
			case 'harga':
				$reply = $this->replyConsumer($param1, $param2);
				break;
			case 'hp':
				$reply = $this->replyProducer($param1);
				break;
			case 'info':
				$reply = $this->replyInfo($param1);
				break;
		}

		return $reply;
	}
	
	function replyConsumer($commodity, $city) {
		$model	= $this->getModel('sms');
		$price	= $model->getConsumer($commodity, $city);
			
		// Generate SMS only if price data found
		if($price) {
			$date	= date('j/m/Y', strtotime($price['date']));
			$city	= $price['city'];
			// Generate SMS;
			$sms	= array();
			$sms[]	= "Harga Rata-Rata Konsumen di $city per $date";
			foreach($price['commodities'] as $commodity) {
				$sms[] = '';
				$sms[] = strtoupper($commodity['name'].' / '.$commodity['denomination']);
				foreach($commodity['markets'] as $market) {
					$price	= JKHelperDocument::toCurrency($market['price']);
					$mName	= $market['name'];
					$sms[]	= "$mName : $price";
				}
			}
			// Stitch SMS together
			$sms	= implode(PHP_EOL, $sms);
		} else {
			$sms	= 'Maaf, data Harga Konsumen yang diminta tidak dapat ditemukan. Mohon ulangi lagi dengan komoditas dan kota yang valid.';
		}

		return $sms;
	}
	
	function replyProducer($commodity) {
		$model	= $this->getModel('sms');
		$price	= $model->getProducer($commodity);
			
		// Generate SMS only if price data found
		if($price) {
			$date	= date('j/m/Y', strtotime($price['date']));
			$city	= $price['city'];
			// Generate SMS;
			$sms	= array();
			$sms[]	= "Harga Rata-Rata Produsen per $date";
			foreach($price['commodities'] as $commodity) {
				$sms[] = '';
				$sms[] = strtoupper($commodity['name'].' / '.$commodity['denomination']);
				foreach($commodity['cities'] as $city) {
					$price	= JKHelperDocument::toCurrency($city['price']);
					$cName	= $city['name'];
					$sms[]	= "$cName : $price";
				}
			}
			// Stitch SMS together
			$sms	= implode(PHP_EOL, $sms);
		} else {
			$sms	= 'Maaf, data Harga Produsen yang diminta tidak dapat ditemukan. Mohon ulangi lagi dengan komoditas yang valid.';
		}

		return $sms;
	}
	
	function replyInfo($type) {
		$sms = '';
		return $sms;
	}
	
	function randomizeString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
}