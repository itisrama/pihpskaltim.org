<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."cometchat_init.php");

$response = array();
$messages = array();

$status['available'] = $language[30];
$status['busy'] = $language[31];
$status['offline'] = $language[32];
$status['invisible'] = $language[33];
$status['away'] = $language[34];

if (!empty($_REQUEST['userid'])) {
	$fetchid = $_REQUEST['userid'];
} else {
	$fetchid = $userid;
}

$fetchid = intval($fetchid);
$time = getTimeStamp();
$sql = getUserDetails($fetchid);

if ($guestsMode && $fetchid >= 10000000) {
	$sql = getGuestDetails($fetchid);
}

$query = mysqli_query($GLOBALS['dbh'],$sql);

if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

$chat = mysqli_fetch_assoc($query);

if ((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT || $chat['isdevice'] == 1) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') {
	if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
		$chat['status'] = 'available';
	}
} else {
	$chat['status'] = 'offline';
}

if ($chat['message'] == null) {
	$chat['message'] = $status[$chat['status']];
}

$link = fetchLink($chat['link']);
$avatar = getAvatar($chat['avatar']);

if(empty($chat['ch'])) {
	if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
		$key = KEY_A.KEY_B.KEY_C;
	}
	$chat['ch'] = md5($chat['userid'].$key);
}

if (function_exists('processName')) {
	$chat['username'] = processName($chat['username']);
}

$response =  array('id' => $chat['userid'], 'n' => $chat['username'], 'l' => $link, 'd' => $chat['isdevice'],'a' => $avatar, 's' => $chat['status'], 'm' => $chat['message'], 'ch' => $chat['ch'], 'ls' => $chat['lastseen'], 'lstn' => $chat['lastseensetting']);

header('Content-type: application/json; charset=utf-8');
if (!empty($_GET['callback'])) {
	echo $_GET['callback'].'('.json_encode($response).')';
} else {
	echo json_encode($response);
}
exit;