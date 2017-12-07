<?php

class FireBasePushNotification {

	private $pushPayload;
	private $firebasenotificationUrl;
	private $firebaseauthserverkey;
	private $messageType;

	public function __construct($firebaseauthserverkey) {
		$this->firebasenotificationUrl = 'https://fcm.googleapis.com/fcm/send';
		$this->firebaseauthserverkey = $firebaseauthserverkey;
	}

	public function sendNotification($channel, $messageData, $isChatroom = '0', $isAnnouncement = '0',$isWRTC = '0') {
		if(function_exists('curl_version') && !empty($channel) && $messageData ) {
			$messageType="O";
			$notificationData = array();
			$soundfile = 'default';

			if ($isAnnouncement == '1') {
				$messageType = "A";
				$notificationData['isANN'] = $isAnnouncement;
				$messageData['m'] = strip_tags($messageData['m']);
			}else{
				if($isChatroom == '1') {
					$messageType = "C";
					$notificationData['isCR'] = $isChatroom;
				} elseif($isWRTC != '0'){
					$soundfile = 'avpushsound.wav';
					$messageDataSplit = explode('_#wrtcgrp_',$messageData['m']);
					$notificationData['grp'] = $messageDataSplit[0];
					$messageData['m'] = $messageDataSplit[1];
					if($isWRTC == 'AC'){
						$messageType = "O_AC";
					}elseif($isWRTC == 'AVC'){
						$messageType = "O_AVC";
					}
				}
				$channel = "C_".$channel;
				$breaks = array("<br />","<br>","<br/>");
				$messageData['m'] = htmlspecialchars_decode(strip_tags(str_ireplace($breaks, "\n", $messageData['m'])));
			}

			$notificationData = array_merge(array('alert' => $messageData['m'],'t' => $messageType,'m' => $messageData,'action' => "PARSE_MSG",'sound' => $soundfile),$notificationData);
			$notificationData = array('text' => $messageData['m']);

			$pushPayload = json_encode(array( "to" => '/topics/'.$channel, "notification" => $notificationData));
			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,$this->firebasenotificationUrl);
			curl_setopt($curl,CURLOPT_PORT,443);
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$pushPayload);
			curl_setopt($curl,CURLOPT_HTTPHEADER,
				array(
					"Authorization: key=" .$this->firebaseauthserverkey ,
					"Content-Type: application/json"
					));
			$response = curl_exec($curl);

			if(!$response) {
				echo ('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
			}

			curl_close($curl);
			return $response;
		} else {
			echo "Missing or invalid parameters.";
		}
	}
}

?>