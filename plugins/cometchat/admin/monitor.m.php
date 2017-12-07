<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$online = onlineusers();

$navigation = <<<EOD
	<div id="leftnav">
		<h1 id="online" style="font-size:70px;font-weight:bold">$online</h1>
		<span style="font-size:10px">USERS ONLINE</span>
	</div>
EOD;

if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

function index() {
	global $body;
	global $navigation;
	$overlay = '';

	$body = <<<EOD
	$navigation
        <link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Monitor</h2>
		<h3>See what users are typing in real-time on your site</h3>

		<div>
			<div id="centernav" style="width:100% !important">
				<script>
					jQuery(function () {
						jQuery.cometchatmonitor();
					});
				</script>
				<div id="data"></div>
			</div>

		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
	{$overlay}
EOD;

	template();

}

function data() {

	global $guestsMode;
	global $guestnamePrefix;

	$usertable = TABLE_PREFIX.DB_USERTABLE;
	$usertable_username = DB_USERTABLE_NAME;
	$usertable_userid = DB_USERTABLE_USERID;
	$guestpart = "";

	$criteria = "cometchat.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['timestamp'])."' and ";
	$criteria2 = 'desc';

	if($guestsMode) {
		$guestpart = "UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read,CONCAT('$guestnamePrefix',f.name) fromu, CONCAT('$guestnamePrefix',t.name) tou from cometchat, cometchat_guests f, cometchat_guests t where $criteria f.id = cometchat.from and t.id = cometchat.to) UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, f.".$usertable_username." fromu, CONCAT('$guestnamePrefix',t.name) tou from cometchat, ".$usertable." f, cometchat_guests t where $criteria f.".$usertable_userid." = cometchat.from and t.id = cometchat.to) UNION (select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, CONCAT('$guestnamePrefix',f.name) fromu, t.".$usertable_username." tou from cometchat, cometchat_guests f, ".$usertable." t where $criteria f.id = cometchat.from and t.".$usertable_userid." = cometchat.to) ";
	}

	$response = array();
	$messages = array();

	if (empty($_POST['timestamp'])) {
		$criteria = '';
		$criteria2 = 'desc limit 20';

	}

	$sql = ("(select cometchat.id id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, f.$usertable_username fromu, t.$usertable_username tou from cometchat, $usertable f, $usertable t where $criteria f.$usertable_userid = cometchat.from and t.$usertable_userid = cometchat.to ) ".$guestpart." order by id $criteria2");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$timestamp = $_POST['timestamp'];

	while ($chat = mysqli_fetch_assoc($query)) {

		if (function_exists('processName')) {
			$chat['fromu'] = processName($chat['fromu']);
			$chat['tou'] = processName($chat['tou']);
		}

		$time=$chat['sent']*1000;

		if(strpos($chat['message'], 'CC^CONTROL_') === false)
			array_unshift($messages,  array('id' => $chat['id'], 'from' => $chat['from'], 'to' => $chat['to'], 'fromu' => $chat['fromu'], 'tou' => $chat['tou'], 'message' => $chat['message'], 'time' => $time));
		elseif (strpos($chat['message'], 'sendSticker')) {
			$message = str_replace('CC^CONTROL_', '', $chat['message']);
			$message = json_decode($message);
			$category = $message->params->category;
			$key = $message->params->key;
			$image = '<img class="cometchat_stickerImage" type="image" src="'.BASE_URL.'/plugins/stickers/images/'.$category.'/'.$key.'.png">';
			array_unshift($messages,  array('id' => $chat['id'], 'from' => $chat['from'], 'to' => $chat['to'], 'fromu' => $chat['fromu'], 'tou' => $chat['tou'], 'message' => $image, 'time' => $time));
		}

		if ($chat['id'] > $timestamp) {
			$timestamp = $chat['id'];
		}
	}

	$response['timestamp'] = $timestamp;
	$response['online'] = onlineusers();

	if (!empty($messages)) {
		$response['messages'] = $messages;
	}

	header('Content-type: application/json; charset=utf-8');
	echo json_encode($response);
exit;
}