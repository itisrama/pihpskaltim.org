<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
	include (dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
function index() {
	$baseUrl = BASE_URL;
	global $userid;
	global $broadcastmessage_language;
	global $language;
	global $embed;
	global $embedcss;
	global $guestsMode;
	global $basedata;
	global $sleekScroller;
	global $inviteContent;
	if (!empty($_REQUEST['basedata'])) {
		$basedata = $_REQUEST['basedata'];
	}
	$caller="";
	if(!empty($_REQUEST['caller'])){
		$caller = $_REQUEST['caller'];
	}
	$popoutmode = 0;
	if(empty($userid)){
		echo <<<EOD
		    <!DOCTYPE html>
			<html>
				<head></head>
				<body style="margin:0px">
					<div style="background: #FFF;font-family: Tahoma,Verdana,Arial,'Bitstream Vera Sans',sans-serif;font-size: 12px;padding: 10px;" class="cometchat_wrapper">
					$broadcastmessage_language[7]
					</div>
				<body>
			</html>
EOD;
		exit;
	}
	if(!empty($_GET['popoutmode'])){
		$popoutmode = 1;
	}

	userSelection(1);

	$embedcss = 'web';

	$extrajs = "";
	if ($sleekScroller == 1) {
		$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
	}

	if($popoutmode==1){
		$addmsg = 1;
	}else{
		$addmsg = 0;
	}

echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$broadcastmessage_language[100]}</title>
		<meta name="viewport" content="user-scalable=1,width=device-width, initial-scale=1.0" />
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<script src="../../js.php?type=core&name=jquery"></script>
		<script src="../../js.php?type=module&name=broadcastmessage&basedata={$basedata}"></script>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=broadcastmessage" />
		{$extrajs}
	</head>
	<body>
		<div style="background: #FFF;">
			<div class="cometchat_wrapper">
				<div>
					<div class="cometchat_broadcastMessage" >
						<div class="cc_broadcasttopbar" >
							<div id="cc_selectallusers" onclick="cc_selectallusers()">{$broadcastmessage_language[5]}</div>
							<div id="cc_deselectallusers" onclick="cc_deselectallusers()">{$broadcastmessage_language[6]}</div>
							<div class="cc_separator" style=" display: inline-block; margin-left: 3px;margin-right: 3px;"> | </div>
							<div id="cc_refreshbroadcastusers">{$broadcastmessage_language[1]}</div>
						</div>
						<div id="cometchat_broadcastsearchbar" style="display: block;">
							<input type="text" name="cometchat_broadcastsearch" class="cometchat_broadcastsearch" id="cometchat_broadcastsearch" placeholder="{$broadcastmessage_language[4]}">
						</div>
						<div class="inviteuserboxes" id="inviteuserboxes">
							{$inviteContent}
						</div>

						<div class="broadcastMessage_textarea_container" >
							<div class="cometchat_tabcontentsubmit"></div>
							<div class="cometchat_tabcontentinput">
									<textarea class="cometchat_textarea" addmsg="{$addmsg}" caller="{$caller}" id="cometchat_broadcastMessage_textarea" placeholder="{$broadcastmessage_language[11]}"></textarea>
							</div>
							</div>
							<div id="ccbroadcastsucc" class="ccbroadcastnotif">{$broadcastmessage_language[8]}</div>
							<div id="ccbroadcastuserrel" class="ccbroadcastnotif">{$broadcastmessage_language[10]}</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
EOD;
}

function userSelection($silent = 0) {
	$baseUrl = BASE_URL;
	global $userid;
	global $broadcastmessage_language;
	global $language;
	global $embed;
	global $embedcss;
	global $guestsMode;
	global $basedata;
	global $sleekScroller;
	global $inviteContent;
	global $chromeReorderFix;
	global $hideOffline;
	global $plugins;
	$status['available'] = $language[30];
	$status['busy'] = $language[31];
	$status['offline'] = $language[32];
	$status['invisible'] = $language[33];
	$status['away'] = $language[34];
	$time = getTimeStamp();

	$onlineCacheKey = 'all_online';
	if($userid > 10000000){
		$onlineCacheKey .= 'guest';
	}

	if (!is_array($buddyList = getCache($onlineCacheKey))) {
		$buddyList = array();
		$sql = getFriendsList($userid,$time);
		if($guestsMode){
	    	$sql = getGuestsList($userid,$time,$sql);
		}
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		while ($chat = mysqli_fetch_assoc($query)) {
			if (((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') || $chat['isdevice'] == 1) {
				if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
					$chat['status'] = 'available';
				}
			} else {
				$chat['status'] = 'offline';
			}

			$avatar = getAvatar($chat['avatar']);

			if (!empty($chat['username'])) {
				if (function_exists('processName')) {
					$chat['username'] = processName($chat['username']);
				}
				if ($chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 's' => $chat['status']);
				}
			}
		}
	}

	if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
		$tempBuddyList = array();
		if (!is_array($friendIds = getCache('friend_ids_of_'.$userid))) {
			$friendIds=array();
			$sql = getFriendsIds($userid);
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) == 1 ){
				$buddy = mysqli_fetch_assoc($query);
				$friendIds = explode(',',$buddy['friendid']);
			}else{
				while($buddy = mysqli_fetch_assoc($query)){
					$friendIds[]=$buddy['friendid'];
				}
			}
			setCache('friend_ids_of_'.$userid,$friendIds, 30);
		}
		foreach($friendIds as $friendId) {
			$friendId = $chromeReorderFix.$friendId;
			if (isset($buddyList[$friendId])) {
				$tempBuddyList[$friendId] = $buddyList[$friendId];
			}
		}
		$buddyList = $tempBuddyList;
	}

	if (function_exists('hooks_forcefriends') && is_array(hooks_forcefriends())) {
		$buddyList = array_merge(hooks_forcefriends(),$buddyList);
	}

	$blockList = array();
	if (in_array('block',$plugins)) {

		$blockedIds = getBlockedUserIDs();

		foreach ($blockedIds as $bid) {
			array_push($blockList,$bid);
			if (isset($buddyList[$chromeReorderFix.$bid])) {
				unset($buddyList[$chromeReorderFix.$bid]);
			}
		}
	}

	if (isset($buddyList[$chromeReorderFix.$userid])) {
		unset($buddyList[$chromeReorderFix.$userid]);
	}

	if(empty($silent)){
		$buddyOrder = array();
		$buddyGroup = array();
		$buddyStatus = array();
		$buddyName = array();
		$buddyGuest = array();
		foreach ($buddyList as $key => $row) {
			if (empty($row['g'])) { $row['g'] = ''; }
			$buddyGroup[$key]  = strtolower($row['g']);
			$buddyStatus[$key] = strtolower($row['s']);
			$buddyName[$key] = strtolower($row['n']);
			if ($row['g'] == '') {
				$buddyOrder[$key] = 1;
			} else {
				$buddyOrder[$key] = 0;
			}
			$buddyGuest[$key] = 0;
			if ($row['id']>10000000) {
				$buddyGuest[$key] = 1;
			}
		}
		array_multisort($buddyOrder, SORT_ASC, $buddyGroup, SORT_STRING, $buddyStatus, SORT_STRING, $buddyGuest, SORT_ASC, $buddyName, SORT_STRING, $buddyList);
		$response['buddyList'] = $buddyList;
		$response['status'] = $status;
	}else{
		$s['available'] = '';
		$s['away'] = '';
		$s['busy'] = '';
		$s['offline'] = '';
		foreach ($buddyList as $buddy) {
			$s[$buddy['s']] .= '<div class="invite_1"><div class="invite_2" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><img height=30 width=30 src="'.$buddy['a'].'" /></div><div class="invite_3" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><span class="invite_name">'.$buddy['n'].'</span><br/><span class="invite_5">'.$status[$buddy['s']].'</span></div><input type="checkbox" name="to[]" value="'.$buddy['id'].'" id="check_'.$buddy['id'].'" class="invite_4" /></div>';
		}

		$inviteContent = '';
		$invitehide = '';
		$inviteContent = $s['available']."".$s['away']."".$s['offline'];
		if(empty($inviteContent)) {
			$inviteContent = '<div style= "padding-top:6px">'.$broadcastmessage_language[2].'</div>';
			$invitehide = 'style="display:none;"';
		}
	}
	if(empty($silent)){
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'].'('.json_encode($response).')';
	}else{
		return $inviteContent;
	}

}

function sendbroadcast() {
	global $userid;
	global $bannedUserIDs;
	global $bannedUserIPs;
	$message = $_REQUEST['message'];
	$broadcast_toids = (explode(",",$_REQUEST['to']));
	$message = sanitize($_REQUEST['message']);
	$broadcast = array();
	if (!in_array($userid,$bannedUserIDs) && !in_array($_SERVER['REMOTE_ADDR'],$bannedUserIPs)) {
		for ($i=0; $i <sizeof($broadcast_toids) ; $i++) {
			$tempMsg = array('to' => $broadcast_toids[$i],'message' => $message, 'dir' => 0 );
			array_push($broadcast, $tempMsg);
		}

		$_REQUEST['broadcast'] = 1;
		$response = broadcastMessage($broadcast,$broadcast_toids);
		if (isset($_GET['callback'])) {
			header('content-type: application/json; charset=utf-8');
			sendCCResponse($_GET['callback'].'('.json_encode($response).')');
		} else {
			sendCCResponse(json_encode($response));
		}
		if(USE_COMET == '1'){
		    publishCometMessages($broadcast,$response[0]['id']);
		}
		foreach ($response as $rkey => $rvalue) {
			pushMobileNotification($rvalue['from'],$rvalue['id'],$_SESSION['cometchat']['user']['n'].": ".$rvalue['m']);
		}
	}
}


$allowedActions = array('index','sendbroadcast','userSelection');
$action = 'index';

if (!empty($_GET['action']) && function_exists($_GET['action']) && in_array($_GET['action'],$allowedActions)) {
       $action = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['action']);
}
call_user_func($action);