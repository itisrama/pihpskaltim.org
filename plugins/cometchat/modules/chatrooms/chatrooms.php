<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

if ($_REQUEST['basedata']) {
	$basedata = rawurlencode($_REQUEST['basedata']);
} else {
	$basedata = null;
}
$embed = '';
$embedcss = '';
$close = "setTimeout('window.close();',2000);";
$response = array();
if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$embedcss = 'embed';
	$close = "setTimeout('closePopup();',2000);";
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$embedcss = 'embed';
	$close = "parentSandboxBridge.closeCCPopup('invite');";
}

if ($userid == 0 || in_array($userid,$bannedUserIDs)) {
	$response['logout'] = 1;
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($response);
	exit;
}elseif(!empty($_REQUEST['initialize'])){
	$response['userid'] = $userid;
}

if(!empty($_GET['action']) && $_GET['action'] == 'sendmessage'){
	$_GET['action'] = 'sendChatroomMessage';
}

if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'updateChatroomMessages'){
	global $lastMessages;
	getChatroomData($_REQUEST['chatroomid'], $_REQUEST['prepend'], $lastMessages);
}

function heartbeat() {
	global $response;
	global $userid;
	global $chatrooms_language;
	global $chatroomTimeout;
	global $lastMessages;
	global $cookiePrefix;
	global $allowAvatar;
	global $moderatorUserIDs;
	global $guestsMode, $crguestsMode, $guestnamePrefix;
    global $chromeReorderFix;
    global $showChatroomUsers;

	$usertable = TABLE_PREFIX.DB_USERTABLE;
	$usertable_username = DB_USERTABLE_NAME;
	$usertable_userid = DB_USERTABLE_USERID;

	$time = getTimeStamp();
	$chatroomList = array();
	$crreadmessages = array();
	$joinedrooms = array();

	if(!isset($_REQUEST['force'])){
		$force = 0;
	} else {
		$force = $_REQUEST['force'];
	}

	if(isset($_REQUEST['crreadmessages'])){
		$crreadmessages = $_REQUEST['crreadmessages'];
	}

	if(!empty($_REQUEST['v']) && !empty($crreadmessages)){
		$crreadmessages = json_decode($crreadmessages, true);
	}

	if(!empty($joinedrooms)){
		foreach($crreadmessages as $chatroomid => $unreadMessages){
			if(!in_array($chatroomid, $joinedrooms)){
				unset($crreadmessages[$chatroomid]);
			}
		}
	}

	if (empty($_SESSION['cometchat']['cometchat_lastlactivity']) || ($time-$_SESSION['cometchat']['cometchat_lastlactivity'] >= REFRESH_BUDDYLIST/4)) {
		$sql = updateLastActivity($userid);
        if (function_exists('hooks_updateLastActivity')) {
            hooks_updateLastActivity($userid);
     	}
        $query = mysqli_query($GLOBALS['dbh'],$sql);

        if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		$_SESSION['cometchat']['cometchat_lastlactivity'] = $time;
	}

	if ((empty($_SESSION['cometchat']['cometchat_chatroomslist'])) || $force==1 || (!empty($_SESSION['cometchat']['cometchat_chatroomslist']) && ($time-$_SESSION['cometchat']['cometchat_chatroomslist'] > REFRESH_BUDDYLIST))) {

		if(!is_array($cachedChatrooms = getCache('chatroom_list'))|| ($force==1)) {
			$cachedChatrooms = array();
			if($showChatroomUsers == 1){
				$sqlPart = "(SELECT count(cometchat_chatrooms_users.userid) online FROM cometchat_chatrooms_users where cometchat_chatrooms_users.chatroomid = cometchat_chatrooms.id and isbanned<>'1')";
			} else {
				$sqlPart = '0';
			}
			$sql = ("select DISTINCT cometchat_chatrooms.id, cometchat_chatrooms.name, cometchat_chatrooms.type, cometchat_chatrooms.password, cometchat_chatrooms.lastactivity, cometchat_chatrooms.invitedusers, cometchat_chatrooms.createdby, ".$sqlPart." online from cometchat_chatrooms order by name asc");
			$query = mysqli_query($GLOBALS['dbh'],$sql);

			while ($chatroom = mysqli_fetch_assoc($query)) {
				$cachedChatrooms[$chromeReorderFix.$chatroom['id']] = array('id' => $chatroom['id'], 'name' => urldecode($chatroom['name']), 'online' => $chatroom['online'], 'type' => $chatroom['type'], 'password' => $chatroom['password'], 'lastactivity' => $chatroom['lastactivity'], 'createdby' => $chatroom['createdby'], 'invitedusers' => $chatroom['invitedusers']);
			}
			setCache('chatroom_list',$cachedChatrooms,30);
		}

		foreach($cachedChatrooms as $key=>$chatroom) {
			if((($chatroom['createdby'] == 0 || ($chatroom['createdby'] <> 0 && $time - $chatroom['lastactivity'] < $chatroomTimeout)) || $chatroom['createdby'] == $userid) && ($chatroom['type'] <> 3)) {
				$userList = explode(',', $chatroom['invitedusers']);
				$s = 0;
				if ($chatroom['createdby'] != $userid) {
					if(in_array($userid,$moderatorUserIDs)){
						$s = 2;
					}
				} else {
					$s = 1;
				}
				if($chatroom['type'] == 2 && !in_array($userid, $userList) && $chatroom['createdby'] != $userid){
					continue;
				} else {
					$chatroomList[$chromeReorderFix.$chatroom['id']] = array('id' => $chatroom['id'], 'name' => $chatroom['name'], 'online' => $chatroom['online'], 'type' => $chatroom['type'], 'i' => $chatroom['password'], 's' => $s, 'createdby' => $chatroom['createdby']);
				}
			}
		}

		$_SESSION['cometchat']['cometchat_chatroomslist'] = $time;

		$clh = md5(serialize($chatroomList));
		if((empty($_POST['clh'])) || (!empty($_POST['clh']) && $clh != $_POST['clh']) || ($force == 1)) {
			$response['chatrooms'] = $chatroomList;
			$response['clh'] = $clh;
		}
	}

	if(!empty($_REQUEST['initialize']) && $_REQUEST['initialize'] == '1'){
	    unset($_SESSION['cometchat']['cometchat_joinedchatroomids']);
	}

	if($force == 1 && empty($_SESSION['cometchat']['cometchat_joinedchatroomids'])){
		$joinedChatroomIds = array();
		$sql = ("select DISTINCT cometchat_chatrooms.id from cometchat_chatrooms where cometchat_chatrooms.id IN (select cometchat_chatrooms_users.chatroomid from cometchat_chatrooms_users where cometchat_chatrooms_users.userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."')");

		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		while ($result = mysqli_fetch_assoc($query)) {
			$joinedChatroomIds[] = $result['id'];
		}
		$_SESSION['cometchat']['cometchat_joinedchatroomids'] = $joinedChatroomIds;
	}

	if(isset($_SESSION['cometchat']['cometchat_joinedchatroomids'])){
		$joinedrooms = $_SESSION['cometchat']['cometchat_joinedchatroomids'];
	}

	if(!empty($_REQUEST['initialize']) && $_REQUEST['initialize']==1 && !empty($joinedrooms) || (!empty($_POST['currentroom']) && $force == 1 && USE_COMET == 1 && COMET_CHATROOMS == 1)){
		$LastMessageIdList = array();
		$implodedChatrooms = implode(',',$joinedrooms);
		$sql = ("select max(cometchat_chatroommessages.id) id, cometchat_chatroommessages.chatroomid from cometchat_chatroommessages where cometchat_chatroommessages.chatroomid IN (".mysqli_real_escape_string($GLOBALS['dbh'],$implodedChatrooms).") group by cometchat_chatroommessages.chatroomid");
		$query = mysqli_query($GLOBALS['dbh'],$sql);

		while ($result = mysqli_fetch_assoc($query)) {
			$LastMessageIdList[$chromeReorderFix.$result['chatroomid']] = $result['id'];
			if(!isset($crreadmessages[$result['chatroomid']])){
				$crreadmessages[$result['chatroomid']] = $result['id'];
			}
		}

		if (!empty($LastMessageIdList)) {
			$response['chatroomList'] = $LastMessageIdList;
		}

		if (USE_COMET == 1 && COMET_CHATROOMS == 1) {
			$cometresponse = array();
			foreach($joinedrooms as $key => $chatroomid){
				$key = '';
	            if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
	                $key = KEY_A.KEY_B.KEY_C;
	            }

	            $cometresponsedata = array(
		            'chatroomid' => $chatroomid,
		            'cometid' => md5('chatroom_'.$chatroomid.$key),
		            'userid' => $userid
	            );
	            array_push($cometresponse, $cometresponsedata);
			}
			$response['subscribeChatrooms'] = $cometresponse;
		}
	}

	if (count($joinedrooms) > 0 || (!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']=='mobileapp')) {
		$messages = array();
		$moremessages = array();

		if(!is_array($users = getCache('chatrooms_users'.$_POST['currentroom'])) || ($force == 1)) {
			$users = array();
			$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.isdevice isdevice, cometchat_chatrooms_users.isbanned from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid inner join cometchat_chatrooms_users on  ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." =  cometchat_chatrooms_users.userid ". DB_AVATARTABLE ." where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['currentroom'])."' and ('".mysqli_real_escape_string($GLOBALS['dbh'],$time)."' - cometchat_status.lastactivity < ".ONLINE_TIMEOUT.") group by userid order by username asc");
			if($guestsMode && $crguestsMode){
				$sql = getChatroomGuests($_POST['currentroom'],$time,$sql);
			}

			$query = mysqli_query($GLOBALS['dbh'],$sql);

			while ($chat = mysqli_fetch_assoc($query)) {
				if (function_exists('processName')) {
					$chat['username'] = processName($chat['username']);
				}
				$avatar = '';
				if($allowAvatar) {
					$avatar = getAvatar($chat['avatar']);
				}

				$users[$chromeReorderFix.$chat['userid']] = array('id' => (int)$chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 'b' => $chat['isbanned']);
			}
			setCache('chatrooms_users'.$_POST['currentroom'],$users,30);
		}

		if (empty($_SESSION['cometchat']['cometchat_chatroom_'.$_POST['currentroom']])) {
			$_SESSION['cometchat']['cometchat_chatroom_'.$_POST['currentroom']] = array();
		}

		$ulh = md5(serialize($users));

		if ((empty($_POST['ulh'])) || (!empty($_POST['ulh']) && $ulh != $_POST['ulh'])) {
			$response['ulh'] = $ulh;
			if (!empty($users)) {
				$response['users'] = $users;
			}
		}
		$limit = $lastMessages;

		if(!empty($crreadmessages)){
			foreach($joinedrooms as $key => $chatroomid){
				if(!isset($crreadmessages[$chatroomid])){
					$crreadmessages[$chatroomid] = 0;
				}
			}

			foreach($crreadmessages as $key => $value){
				if(!in_array($key, $joinedrooms)){
					unset($crreadmessages[$key]);
				}
			}
		}

		if($force == 1 && !empty($_SESSION['cometchat']['cometchat_chatroom_'.$_POST['currentroom']]) && !(USE_COMET == 1 && COMET_CHATROOMS == 1)){
			$messages = getChatroomData($_POST['currentroom']);
			$messages = array_reverse($messages);
		} else {
			$guestpart = "";
			$limitClause = " limit ".mysqli_real_escape_string($GLOBALS['dbh'],$limit)." ";
			$timestampCondition = "";

			foreach ($crreadmessages as $chatroomid => $lastmessageid) {
				if(((!empty($_REQUEST['initialize']) && $_REQUEST['initialize']==1) || (!empty($lastmessageid) && $force == 1)) && (USE_COMET == 1 && COMET_CHATROOMS == 1)){
					$lastmessageid = $lastmessageid-$lastMessages;
				}
				if(!empty($_SESSION['cometchat']['chatrooms_'.$chatroomid.'_clearId']) && empty($_SESSION['cometchat']['cometchat_chatroom_'.$chatroomid])){
 					$timestampCondition .= " (cometchat_chatroommessages.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' and cometchat_chatroommessages.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$_SESSION['cometchat']['chatrooms_'.$chatroomid.'_clearId'])."') or";
 				}else{
					$timestampCondition .= " (cometchat_chatroommessages.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' and cometchat_chatroommessages.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$lastmessageid)."') or";
 				}
			}

			if(count($crreadmessages) > 0){
				$timestampCondition = rtrim($timestampCondition,"or");
				$timestampCondition = $timestampCondition." and ";
				$limitClause = '';
			}
			if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']=='mobileapp' && empty($_REQUEST['v']) || !empty($_REQUEST['currentroom'])){
				$timestampCondition = "";
				if ($_POST['timestamp'] != 0) {
					$timestampCondition = " cometchat_chatroommessages.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['currentroom'])."' and cometchat_chatroommessages.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['timestamp'])."' and ";
					$limitClause = "";
				} else {
					$timestampCondition = "cometchat_chatroommessages.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['currentroom'])."' and ";
					$limitClause = " limit ".mysqli_real_escape_string($GLOBALS['dbh'],$limit)." ";
				}
			}

			if ($guestsMode && $crguestsMode) {
				$guestpart = " UNION select DISTINCT cometchat_chatroommessages.id id, cometchat_chatroommessages.message, cometchat_chatroommessages.chatroomid, cometchat_chatroommessages.sent, CONCAT('".$guestnamePrefix."',m.name) `from`, cometchat_chatroommessages.userid fromid, m.id userid from cometchat_chatroommessages join cometchat_guests m on m.id = cometchat_chatroommessages.userid where ".$timestampCondition." cometchat_chatroommessages.message not like 'banned_%' and cometchat_chatroommessages.message not like 'kicked_%' and cometchat_chatroommessages.message not like 'deletemessage_%' ";
			}

			if (empty($crreadmessages) && empty($_POST['currentroom'])){
				$sql = ("select cometchat_chatroommessages.id id from cometchat_chatroommessages where false");
			} else{
				$sql = ("select DISTINCT cometchat_chatroommessages.id id, cometchat_chatroommessages.message, cometchat_chatroommessages.chatroomid, cometchat_chatroommessages.sent, m.$usertable_username `from`, cometchat_chatroommessages.userid fromid, m.$usertable_userid userid from cometchat_chatroommessages join $usertable m on m.$usertable_userid = cometchat_chatroommessages.userid  where ". $timestampCondition ." cometchat_chatroommessages.message not like 'banned_%' and cometchat_chatroommessages.message not like 'kicked_%' and cometchat_chatroommessages.message not like 'deletemessage_%' ". $guestpart." order by id desc ".$limitClause);
			}

			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) > 0) {
				while ($chat = mysqli_fetch_assoc($query)) {
					if (function_exists('processName')) {
						$chat['from'] = processName($chat['from']);
					}

					if ($lastMessages == 0 && $_POST['timestamp'] == 0) {
						$chat['message'] = '';
					}

					if ($userid == $chat['userid']) {
						$chat['from'] = $chatrooms_language[6];

					} else {

						if (!empty($_COOKIE[$cookiePrefix.'lang']) && !(strpos($chat['message'],"CC^CONTROL_")>-1)) {

							$translated = text_translate($chat['message'],'',$_COOKIE[$cookiePrefix.'lang']);

							if ($translated != '') {
								$chat['message'] = strip_tags($translated).' <span class="untranslatedtext">('.$chat['message'].')</span>';
							}
						}
					}

					array_unshift($messages,array('id' => $chat['id'], 'from' => $chat['from'], 'chatroomid' => $chat['chatroomid'], 'fromid' => $chat['fromid'], 'message' => $chat['message'], 'sent' => ($chat['sent'])));

					$_SESSION['cometchat']['cometchat_chatroom_'.$chat['chatroomid']][$chat['id']] = array('id' => $chat['id'], 'chatroomid' => $chat['chatroomid'], 'from' => $chat['from'],'fromid' => $chat['fromid'], 'message' => $chat['message'], 'sent' => ($chat['sent']));
				}
			}
		}

		if (!empty($messages)) {
			$response['messages'] = $messages;
		}

		$sql = '';
		if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']=='mobileapp' && !empty($_POST['currentroom'])){
			$sql = ("select password from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['currentroom'])."'");
		} else {
			if(!empty($_COOKIE[$cookiePrefix.'crstate'])){
				$cr_state = json_decode($_COOKIE[$cookiePrefix.'crstate'], true);
				if($cr_state['open'] != ''){
					$sql = ("select password from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$cr_state['open'])."'");
				}
			}
		}

		if($sql && $_POST['currentroom'] > 0){
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if($room = mysqli_fetch_assoc($query)){
				if (!empty($room['password']) && (empty($_POST['currentp']) || ($room['password'] != $_POST['currentp']))) {
					$response['users'] = array();
					$response['messages'] = array();
				}
			}else{
				$response['error'] = "ROOM_DOES_NOT_EXISTS";
			}
		}
	}

	header('Content-type: application/json; charset=utf-8');
	echo json_encode($response);
}

function createchatroom() {

	global $userid;
	$name = $_POST['name'];
	$name = str_replace("%27","'", $name);

	$password = $_POST['password'];
	$type = $_POST['type'];

	$sql = ("select name from cometchat_chatrooms where name = '".mysqli_real_escape_string($GLOBALS['dbh'],$name)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if(mysqli_num_rows($query) == 0) {
		if ($userid > 0) {
			$time = getTimeStamp();
			if (!empty($password)) {
				$password = sha1($password);
			} else {
				$password = '';
			}

			$sql = ("insert into cometchat_chatrooms (name,createdby,lastactivity,password,type) values ('".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($name))."', '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".getTimeStamp()."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($password))."','".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($type))."')");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			$currentroom = mysqli_insert_id($GLOBALS['dbh']);

			$sql = ("insert into cometchat_chatrooms_users (userid,chatroomid) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],$currentroom)."') on duplicate key update chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$currentroom)."'");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			//Store chatroom name in session for push notifications
			$_SESSION['cometchat']['chatroom']['n'] = sanitize_core(base64_encode(urldecode($_POST['name'])));
			$_SESSION['cometchat']['chatroom']['id'] = $currentroom;
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($_SESSION['cometchat']['chatroom']);
			exit(0);
		}
	} else {
		echo "0";
		exit;
	}
}

function deletechatroom(){
	global $userid;
	global $moderatorUserIDs;
	global $cookiePrefix;

	$createdby = " and createdby != 0 ";

	if (!empty($_POST['id'])) {
		if(!in_array($userid, $moderatorUserIDs)){
			$createdby .= " and createdby = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($userid))."' ";
		}
		$joinedChatroomIds = $_SESSION['cometchat']['cometchat_joinedchatroomids'];
	    $key = array_search($_POST['id'],$joinedChatroomIds);
		if($key!==false){
			unset($joinedChatroomIds[$key]);
		}
		$_SESSION['cometchat']['cometchat_joinedchatroomids'] = $joinedChatroomIds;

		$sql = ("delete from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($_POST['id']))."' ".$createdby);
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		echo mysqli_affected_rows($GLOBALS['dbh']);
		exit;
	}
	removeCache('chatroom_list');
	echo 0;
}

function renamechatroom(){
	global $userid;
	if(!empty($_REQUEST['id']) && !empty($_REQUEST['cname'])){
		$sql = ("select name from cometchat_chatrooms where name = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['cname'])."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if(mysqli_num_rows($query) >= 1) {
			echo 0;
			exit;
		}

		if(!empty($_POST['cc_sdk'])){
			$sql = ("select createdby from cometchat_chatrooms where id ='".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['id'])."'");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			$row = mysqli_fetch_assoc($query);
			if(!($userid == $row['createdby'] || in_array($userid, $moderatorUserIDs))){
				echo 2;
				exit;
			}
		}

		$sql = ("update cometchat_chatrooms set name = '".mysqli_real_escape_string($GLOBALS['dbh'],rawurldecode($_POST['cname']))."' where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['id'])."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		echo 1;
	}
}

function checkpassword() {
	global $userid;
	global $cookiePrefix;
	global $moderatorUserIDs;
	global $channelprefix;
	$response = array();
	$chatroomname = '';
	$_SESSION['cometchat']['isModerator'] = 0;
	$id = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['id']);
	$joinedChatroomIds = array();

	if(!empty($_SESSION['cometchat']['cometchat_joinedchatroomids'])) {
		$joinedChatroomIds = $_SESSION['cometchat']['cometchat_joinedchatroomids'];
	}

	if((!empty($_REQUEST['type']) && $_REQUEST['type']==1) && empty($_REQUEST['silent']) && !in_array($id, $joinedChatroomIds)) {
		$response['s'] = 'REQUIRED_PASSWORD';
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($response);
		exit;
	}
	if(!empty($_REQUEST['password'])) {
		$password = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['password']);
	}

	if(!empty($_REQUEST['id']) && !empty($_REQUEST['name'])) {
		$sql = ("select cometchat_chatrooms.name name from cometchat_chatrooms where id ='".mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['id'])."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$result = mysqli_fetch_assoc($query);
		if($_REQUEST['name'] != $result['name']){
			$chatroomname = $result['name'];
		}
	}

	$sql = ("select * from cometchat_chatrooms_users where userid ='".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' and isbanned = '1'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if(mysqli_num_rows($query) == 1){
		$response['s'] = 'BANNED';
		$responseLeg = 2;

		if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']<>'mobileapp'){
			echo $responseLeg;
		} else{
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($response);
		}
		exit;
	}
	if ($userid > 0) {
		$sql = ("select * from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['id'])."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if($room = mysqli_fetch_assoc($query)){
			if (!empty($room['password']) && (empty($_REQUEST['password']) || ($room['password'] != $_REQUEST['password']))) {
				$response['s'] = 'INVALID_PASSWORD';
				$responseLeg = "0";

				if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']<>'mobileapp'){
					echo $responseLeg;
				} else{
					header('Content-type: application/json; charset=utf-8');
					echo json_encode($response);
				}
				exit;

			} else {
				removeCache('chatrooms_users'.$id);
				removeCache('chatroom_list');

				$sql = ("delete from cometchat_chatroommessages where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' and (message like '%kicked_".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."')");
    			$query = mysqli_query($GLOBALS['dbh'],$sql);

				$sql = ("insert into cometchat_chatrooms_users (userid,chatroomid,isbanned) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],$id)."','0') on duplicate key update chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				if ($room['createdby'] == $userid || in_array($userid,$moderatorUserIDs)) {
					$_SESSION['cometchat']['isModerator'] = 1;
				}
				$key = '';
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}

				$response=array('s' => 'JOINED',
					'chatroomname' => $chatroomname,
					'timestamp' => 0,
					'cometid' => md5('chatroom_'.$id.$key),
					'owner' => ($room['createdby'] == $userid?"1":"0"),
					'userid' => $userid,
					'ismoderator' => $_SESSION['cometchat']['isModerator'],
					'push_channel' => 'C_'.md5($channelprefix."CHATROOM_".$id.BASE_URL)
					);
				$responseLeg = md5('chatroom_'.$id.$key)."^".($room['createdby'] == $userid?"1":"0")."^".$userid."^".$_SESSION['cometchat']['isModerator'];
				//Store chatroom name in session for push notifications
				$_SESSION['cometchat']['chatroom']['n'] = base64_encode($room['name']);
				$_SESSION['cometchat']['chatroom']['id'] = $id;

				if(!in_array($id, $joinedChatroomIds)) {
					array_push($joinedChatroomIds,$id);
					$_SESSION['cometchat']['cometchat_joinedchatroomids'] = $joinedChatroomIds;
				}
			}
		}else{
			$response['s'] = 'INVALID_CHATROOM';
			$responseLeg = '3';
		}
		if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']<>'mobileapp'){
			echo $responseLeg;
		} else{
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($response);
		}

	}
}

function invite() {
	global $userid;
	global $chatrooms_language;
	global $language;
	global $embed;
	global $embedcss;
    global $guestsMode;
	global $basedata;
	global $cookiePrefix;
    global $chromeReorderFix;
	global $hideOffline;
	global $plugins;

	$status['available'] = $language[30];
	$status['busy'] = $language[31];
	$status['offline'] = $language[32];
	$status['invisible'] = $language[33];
	$status['away'] = $language[34];

	$cc_theme = '';
	if(!empty($_GET['cc_theme'])){
		$cc_theme = '&cc_theme='.$_GET['cc_theme'];
	}
	if(!isset($_REQUEST['force'])){
		$force = 0;
	} else {
		$force = $_REQUEST['force'];
	}

	$id = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['roomid']);
	$inviteid = $_GET['inviteid'];
	$roomname = $_GET['roomname'];
	$popoutmode = $_GET['popoutmode'];

	$time = getTimeStamp();
	$invitedusers = array();
	$sql = ("select cometchat_chatrooms.type, cometchat_chatrooms.invitedusers from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$result = mysqli_fetch_assoc($query);
	$chatroomType = $result['type'];
	if($chatroomType == 2) {
		$invitedusers = array_filter(explode(',', $result['invitedusers']));
	}

	$sql = ("select GROUP_CONCAT(cometchat_chatrooms_users.userid) bannedusers from cometchat_chatrooms_users where isbanned=1 and chatroomid='".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' ");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$result = mysqli_fetch_assoc($query);
	$bannedUsers = explode(',',$result['bannedusers']);

	$onlineCacheKey = 'all_online';
	if($userid > 10000000){
		$onlineCacheKey .= 'guest';
	}

	if (!is_array($buddyList = getCache($onlineCacheKey))|| ($force == 1)) {
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

				if (!(in_array($chat['userid'],$bannedUsers)) && $chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 's' => $chat['status']);
				}
			}
		}
	}

	if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
		$tempBuddyList = array();
		if (!is_array($friendIds = getCache('friend_ids_of_'.$userid))|| ($force == 1)) {
			$friendIds=array();
			$sql = getFriendsIds($userid);
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) == 1 ){
				$buddy = mysqli_fetch_assoc($query);
				$friendIds = explode(',',$buddy['friendid']);
			}else {
				while($buddy = mysqli_fetch_assoc($query)){
					$friendIds[]=$buddy['friendid'];
				}
			}
			setCache('friend_ids_of_'.$userid,$friendIds, 30);
		}
		foreach($friendIds as $friendId) {
			$friendId = $chromeReorderFix.$friendId;
			if (!empty($buddyList[$friendId])) {
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

	$chatroomUserList = getChatroomUserIDs($id);
	foreach ($chatroomUserList as $cid) {
		if (isset($buddyList[$chromeReorderFix.$cid])) {
			unset($buddyList[$chromeReorderFix.$cid]);
		}
	}

	$s['available'] = '';
	$s['away'] = '';
	$s['busy'] = '';
	$s['offline'] = '';
	foreach ($buddyList as $buddy) {
		$invitedusers_class = '';
		$tooltip = '';
		if($buddy['id'] != $userid){
			if($chatroomType == 2 && count($invitedusers) > 0 && in_array($buddy['id'], $invitedusers)){
				$invitedusers_class = 'invitedusers';
				$tooltip = 'title="'.$chatrooms_language[73].'"';
			}
			$s[$buddy['s']] .= '<div class="invite_1 '.$invitedusers_class.'" '.$tooltip.' onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><div class="invite_2"><img height=30 width=30 src="'.$buddy['a'].'" /></div><div class="invite_3"><span class="invite_name">'.$buddy['n'].'</span><br/><span class="invite_5">'.$status[$buddy['s']].'</span></div><input type="checkbox" name="invite[]" value="'.$buddy['id'].'" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;" id="check_'.$buddy['id'].'" class="invite_4" /></div>';
		}
	}

	$inviteContent = '';
	$invitehide = '';
	$inviteContent = $s['available']."".$s['away']."".$s['offline'];
	if(empty($inviteContent)) {
		$inviteContent = $chatrooms_language[45];
		$invitehide = 'style="display:none;"';
	}

	echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$chatrooms_language[22]}</title>
		<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms{$cc_theme}" />
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
			$ = jQuery = jqcc;
			$(function(){
				$('.invite_1').click(function() {
				    var checked = $( "input:checked" ).length;
				    if(checked > 0){
				    	$('.invitebutton').attr("disabled", false);
				    }else{
				    	$('.invitebutton').attr("disabled", true);
				    }
				});
			});
		</script>
	</head>
	<body>
		<form method="post" action="chatrooms.php?action=inviteusers&embed={$embed}&basedata={$basedata}">
			<div class="cometchat_wrapper">
				<div class="container_title {$embedcss}">{$chatrooms_language[21]}</div>
				<div class="container_body {$embedcss}">
					{$inviteContent}
					<div style="clear:both"></div>
				</div>
				<div class="container_sub {$embedcss}" {$invitehide}>
					<input type=submit value="{$chatrooms_language[20]}" class="invitebutton" disabled />
				</div>
				<script>
					$(document).ready(function(){
						var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
						if(mobileDevice){
							$('.container_body').css('height',$(window).height()-($('.container_title').outerHeight(true)+$('.container_sub').outerHeight(true)+30));
							$('body').css('overflow','scroll');
						}
					});
				</script>
			</div>
			<input type="hidden" name="roomid" value="{$id}" />
			<input type="hidden" name="inviteid" value="{$inviteid}" />
			<input type="hidden" name="roomname" value="{$roomname}" />
		</form>
	</body>
</html>
EOD;
}

function inviteusers() {
	global $chatrooms_language;
	global $close;
	global $embedcss;
	$post_roomid = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomid']);
	$post_inviteid = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['inviteid']);
	$post_roomname = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomname']);

	if(!empty($_REQUEST['invite'])){
		$blockedIds = getBlockedUserIDs();
		foreach ($blockedIds as $bid) {
			if(in_array($bid, $_REQUEST['invite'])){
				unset($_REQUEST['invite'][array_search($bid, $_REQUEST['invite'])]);
			}
		}
		foreach ($_REQUEST['invite'] as $user) {
			$chatroomroomname = base64_decode(rawurldecode($_POST['roomname']));
			$response = sendMessage($user,"{$chatrooms_language[18]}{$chatroomroomname}. <a href=\"javascript:jqcc.cometchat.joinChatroom('{$post_roomid}','{$post_inviteid}','{$post_roomname}')\">{$chatrooms_language[19]}</a>",1);
			addUsersToChatroom($post_roomid, $user);
			$processedMessage = $_SESSION['cometchat']['user']['n'].": "."has invited you to join ".$_SESSION['cometchat']['chatroom']['n'];
			if($response != ''){
				pushMobileNotification($user,$response['id'],$processedMessage);
			}
		}
	}

	echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$chatrooms_language[18]}</title>
		<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms" />
		<script type="text/javascript">
			function closePopup(name){
				var controlparameters = {'type':'modules', 'name':'chatrooms', 'method':'closeCCPopup', 'params':{'name':'invite'}};
				controlparameters = JSON.stringify(controlparameters);
				if(typeof(parent) != 'undefined' && parent != null && parent != self){
					parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				} else {
					window.close();
				}
			}
		</script>
	</head>
	<body onload="{$close}">
		<div class="cometchat_wrapper">
			<div class="container_title {$embedcss}">{$chatrooms_language[21]}</div>
			<div class="container_body {$embedcss}">
				{$chatrooms_language[16]}
				<div style="clear:both"></div>
			</div>
		</div>
	</body>
</html>
EOD;
}



function passwordBox() {

	global $chatrooms_language;
	global $embedcss;

	$close = 'setTimeout("window.close()",1000);';
	if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
		$close = 'setTimeout("closePopup();",1000);';
	}

	$id = $_REQUEST['id'];
	$name = $_REQUEST['name'];
	$silent = $_REQUEST['silent'];
	$cc_theme = '';
	$noBar = 0;
	if(!empty($_GET['cc_theme'])){
		$cc_theme = '&cc_theme='.$_GET['cc_theme'];
	}
	if(!empty($_GET['noBar'])){
		$noBar = $_GET['noBar'];
	}

	$options=" <input type=button id='passwordBox' class='invitebutton' value='$chatrooms_language[19]' /><input type=button id='close' class='invitebutton' onclick=$close value='$chatrooms_language[51]' />";

echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$name}</title>
		<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms{$cc_theme}" />
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
		  $ = jQuery = jqcc;
		</script>
		<script type="text/javascript">
		function closePopup(name){
			var controlparameters = {'type':'modules', 'name':'chatrooms', 'method':'closeCCPopup', 'params':{'name':'passwordBox'}};
			controlparameters = JSON.stringify(controlparameters);
			if(typeof(parent) != 'undefined' && parent != null && parent != self){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			} else {
				window.close();
			}
		}
		function cccheckPass() {
			password = $('#chatroomPass').val();
			var controlparameters = {"type":"modules", "name":"cometchat", "method":"checkChatroomPass", "params":{"id":"{$id}", "name":"{$name}", "silent":"{$silent}", "password":password, "clicked":1, "encryptPass":1, "noBar":"{$noBar}"}};
    		controlparameters = JSON.stringify(controlparameters);
    		if(window.opener==null || window.opener==''){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			}else{
				window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
			}
		}
		$(function() {
			var controlparameters = {'type':'module', 'name':'chatrooms', 'method':'resizeCCPopup', 'params':{"id":"passwordBox", "width":110, "height":320}};
        	controlparameters = JSON.stringify(controlparameters);
        	if(typeof(window.opener) == null){
        		window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        	}else{
        		parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        	}
			$('#passwordBox').click(function(e) {
				cccheckPass();
				{$close}
			});

			$('#chatroomPass').keyup(function(e) {
				if(e.keyCode == 13) {
					cccheckPass();
					{$close}
				}
			});
		});
		</script>
	</head>
	<body>
		<div class="container passwordBox_container">
			<div class="container_title {$embedcss}">{$name}</div>
			<div style="overflow:hidden; height:55px !important;" class="container_body {$embedcss}">
			<div class="passwordbox_body">{$chatrooms_language[8]}</div>
			<input style="width: 98%;margin-top: 8px;" id="chatroomPass" type="password" name="pwd" autofocus/>
				<div style="clear:both"></div>
			</div>
			<div align="right" class="container_sub {$embedcss}">{$options}</div>
		</div>
	</body>
</html>
EOD;
}

function loadChatroomPro() {

	global $chatrooms_language;
	global $language;
	global $embed;
	global $embedcss;
	global $userid;
	global $moderatorUserIDs;
	global $lightboxWindows;
	global $showchatbutton;
	global $chromeReorderFix;

	$close = 'setTimeout("window.close()",2000);';
	$callbackfn='';
	if(!empty($_REQUEST['callbackfn'])){
		$callbackfn=$_REQUEST['callbackfn'];
	}
	if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
		$close = 'parent.closeCCPopup("loadChatroomPro");';
	}
	$callerWindow = '';
	if(!empty($_REQUEST['caller'])){
		$callerWindow = $_REQUEST['caller'];
	}

	$id = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['roomid']);
	$cc_theme = '';
	if(!empty($_GET['cc_theme'])){
		$cc_theme = '&cc_theme='.$_GET['cc_theme'];
	}
	$uid = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['inviteid']);
	$owner = $_GET['owner'];
	$apiAccess = 0;
	if(!empty($_GET['apiAccess']) && $_GET['apiAccess'] != 'undefined'){
		$apiAccess = $_GET['apiAccess'];
	}

	$options = "";
	$status_area = "";
	$popoutmode = $_GET['popoutmode'];

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

				if (!(in_array($chat['userid'],$bannedUsers)) && $chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar, 's' => $chat['status']);
				}
			}
		}
	}
	if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
		$tempBuddyList = array();
		if (!is_array($friendIds = getCache('friend_ids_of_'.$userid))|| ($force == 1)) {
			$friendIds=array();
			$sql = getFriendsIds($userid);
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if(mysqli_num_rows($query) == 1 ){
				$buddy = mysqli_fetch_assoc($query);
				$friendIds = explode(',',$buddy['friendid']);
			}else {
				while($buddy = mysqli_fetch_assoc($query)){
					$friendIds[]=$buddy['friendid'];
				}
			}
			setCache('friend_ids_of_'.$userid,$friendIds, 30);
		}
		foreach($friendIds as $friendId) {
			$friendId = $chromeReorderFix.$friendId;
			if (!empty($buddyList[$friendId])) {
				$tempBuddyList[$friendId] = $buddyList[$friendId];
			}
		}
		$buddyList = $tempBuddyList;
	}

	if (function_exists('hooks_forcefriends') && is_array(hooks_forcefriends())) {
		$buddyList = array_merge(hooks_forcefriends(),$buddyList);
	}

	if($apiAccess && ($showchatbutton == '0' || ($showchatbutton == '1' && array_key_exists($chromeReorderFix.$uid, $buddyList)))){
		$options="<input type=button class='invitebutton chat' caller='".$callerWindow."' uid=".$uid." value='".$chatrooms_language[43]."' />";
	}

	if($owner == 1 || in_array($userid,$moderatorUserIDs)) {
		$sql = ("select createdby from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' limit 1");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		$room = mysqli_fetch_assoc($query);

		if(!in_array($uid,$moderatorUserIDs) && $uid != $room['createdby']) {
			$options = "<input type=button id='cc_kick' value='".$chatrooms_language[40]."' uid = ".$uid." class='invitebutton kickBan'/>
			<input type=button id='cc_ban' value='".$chatrooms_language[41]."' uid = ".$uid." class='invitebutton kickBan' />".$options;
		}
	}

	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$sql = getUserDetails($uid);

	if($uid>10000000) {
		$sql = getGuestDetails($uid);
	}

	$res = mysqli_query($GLOBALS['dbh'],$sql);
	$result = mysqli_fetch_assoc($res);
	$link = fetchLink($result['link']);
	$avatar = getAvatar($result['avatar']);
	$status = $result['status'];
	$statusMessage = $result['message'];
	$isDevice = $result['isdevice'];
	$icon = '';

	if($statusMessage==null && $status == 'available'){
		$statusMessage = $language[30];
	} else if($statusMessage==null && $status == 'away'){
		$statusMessage = $language[34];
	}else if($statusMessage==null && $status == 'busy'){
		$statusMessage = $language[31];
	}else if($statusMessage==null && $status == 'offline'){
		$statusMessage = $language[32];
	}else if($statusMessage==null && $status == 'invisible'){
		$statusMessage = $language[33];
	}

	$usercontentstatus = $status;
    if($isDevice==1){
       $usercontentstatus = 'mobile cometchat_mobile_'.$status;
       $icon = '<div class="cometchat_dot"></div>';
    }

	$status_area = '<span class="cometchat_userscontentdot cometchat_userscontentdot_synergy cometchat_'.$usercontentstatus.' cometchat_'.$usercontentstatus.'_synergy">'.$icon.'</span><span class="status_messagearea">'.$statusMessage.'</span>';

	if($link != '' && $uid < 10000000 && $callbackfn!='desktop') {
		$options .= " <input type=button class='invitebutton' onClick=javascript:window.open('".$link."');".$close." value='".$chatrooms_language[42]."' />";
	}

echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$result['username']}</title>
		<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
		  $ = jQuery = jqcc;
		</script>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms{$cc_theme}" />
		<script>
		$('.kickBan').live('click',function(){
		    var uid = $(this).attr('uid');
		    var method = $(this).attr('id');
		    if(method == 'cc_kick'){
		    	action = 'kickChatroomUser';
		    } else {
		    	action = 'banChatroomUser';
		    }
	        var controlparameters = {"type":"modules", "name":"cometchat", "method":action, "params":{"uid":uid, "allowed":"1", "chatroommode":"1"}};
	        controlparameters = JSON.stringify(controlparameters);
	        if(typeof(parent) != 'undefined' && parent != null && parent != self){
	        	parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	            var controlparameters = {'type':'plugins', 'name':'chatrooms', 'method':'closeCCPopup', 'params':{'name':'loadChatroomPro'}};
                controlparameters = JSON.stringify(controlparameters);
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	        } else {
	        	window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
	        	window.close();
			}

		});

		$('.chat').live('click',function(){
			var uid = $(this).attr('uid');
			var caller = $(this).attr('caller');
			var callbackfn="<?php echo $callbackfn; ?>";
			var controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{"uid":uid, "chatroommode":"0", "caller":caller}};
			if(callbackfn){
				controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{"uid":uid, "chatroommode":"0"}};
			}
	        controlparameters = JSON.stringify(controlparameters);
	        if(typeof(parent) != 'undefined' && parent != null && parent != self){
	            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	            var controlparameters = {'type':'plugins', 'name':'chatrooms', 'method':'closeCCPopup', 'params':{'name':'loadChatroomPro'}};
                controlparameters = JSON.stringify(controlparameters);
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	        } else {
	            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
	            window.close();
	        }
		});
		</script>
	</head>
	<body>
		<form method="post">
			<div class="cometchat_wrapper">
				<div class="container_title {$embedcss}">{$result['username']}</div>
				<div class="container_body {$embedcss}" style='height:auto'>
					<div class="chatroom_avatar"><img src="{$avatar}" height="50px" width="50px" /></div>
					<div class="status_container">
						<div class="status_area">{$status_area}</div>
						<div class="control_buttons">{$options}</div>
					</div>
					<div style='clear:both'></div>
				</div>
			</div>
		</form>
		<script type='text/javascript'>
			if(typeof $ != 'undefined')
			$(document).ready(function(){
				sum = 0;
				$('.control_buttons input').each(function(i,o){
					sum += $(o).outerWidth(false);
				});
				setTimeout(function(){
					window.resizeTo(sum+140, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight+10));
					//140 = container.padding(10*2) + avatar(50) + buttons.margin-left(20) + buttons.margin-right(20) + 30 (container margin(2%)+ inter-button spacing(taking worst case scenario))
					//10 = container.margin(5*2)
				},500);
				var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
				if(typeof(parent) != 'undefined' && !mobileDevice){
					var controlparameters = {'type':'module', 'name':'chatrooms', 'method':'resizeCCPopup', 'params':{"id":"loadChatroomPro", "height":sum+178, "width":96}};
                	controlparameters = JSON.stringify(controlparameters);
                	if(typeof(window.opener) == null){
                		window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
                	}else{
                		parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                	}
				}
				//Height 80 = container_body.height(50) + embed.padding(10*2) + container.margin(5*2)

			});
		</script>
	</body>
</html>
EOD;
}

function leavechatroom() {
	global $userid;
	global $cookiePrefix;
    if (empty($_REQUEST['banflag'])) {
        $sql = ("delete from cometchat_chatrooms_users where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['currentroom'])."' and isbanned != 1 ");
        $query = mysqli_query($GLOBALS['dbh'],$sql);
    }
    $joinedChatroomIds = $_SESSION['cometchat']['cometchat_joinedchatroomids'];
    $key = array_search($_POST['currentroom'],$joinedChatroomIds);
	if($key!==false){
		unset($joinedChatroomIds[$key]);
	}
	$_SESSION['cometchat']['cometchat_joinedchatroomids'] = $joinedChatroomIds;

    removeCache('chatrooms_users'.$_POST['currentroom']);
	removeCache('chatroom_list');

	unset($_SESSION['cometchat']['cometchat_chatroom_'.$_POST['currentroom']]);
	unset($_SESSION['cometchat']['cometchat_chatroomslist']);
	unset($_SESSION['cometchat']['isModerator']);
	echo $_POST['currentroom'];
}

function kickUser() {
    global $cookiePrefix;
	$kickid = $_REQUEST['kickid'];
	$id = $_REQUEST['currentroom'];
	if (empty($_REQUEST['kick']) && empty($_SESSION['cometchat']['isModerator']) ) {
		echo 0;
		exit;
	}

	$sql = ("delete from cometchat_chatrooms_users where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$kickid)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$controlparameters = array('type' => 'modules', 'name' => 'chatroom', 'method' => 'kicked', 'params' => array('id' => $kickid));
	$controlparameters = json_encode($controlparameters);
	sendChatroomMessage($id,'CC^CONTROL_'.$controlparameters,0);
	removeCache('chatrooms_users'.$id);
	removeCache('chatroom_list');
	echo 1;
}

function banUser() {
	global $cookiePrefix;
	$banid = $_REQUEST['banid'];
	$id = $_REQUEST['currentroom'];
	$popoutmode	= $_REQUEST['popoutmode'];
	if (empty($_REQUEST['ban']) && empty($_SESSION['cometchat']['isModerator']) ) {
		echo 0;
		exit;
	}

	$sql = ("update cometchat_chatrooms_users set isbanned = '1' where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$banid)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	addUsersToChatroom($id, $banid, 1);

	$controlparameters = array('type' => 'modules', 'name' => 'chatroom', 'method' => 'banned', 'params' => array('id' => $banid));
	$controlparameters = json_encode($controlparameters);
	sendChatroomMessage($id,'CC^CONTROL_'.$controlparameters,0);
	removeCache('chatrooms_users'.$id);
	removeCache('chatroom_list');
	echo 1;
}

function unban() {
	global $userid;
	global $chatrooms_language;
	global $language;
	global $embed;
	global $embedcss;
	global $guestsMode;
	global $basedata;
    global $chromeReorderFix;

	$status['available'] = $language[30];
	$status['busy']		 = $language[31];
	$status['offline']	 = $language[32];
	$status['invisible'] = $language[33];
	$status['away']		 = $language[34];

	if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp'){
    	$id = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['roomid']);
    }else{
    	$id 		= mysqli_real_escape_string($GLOBALS['dbh'],$_GET['roomid']);
		$inviteid 	= mysqli_real_escape_string($GLOBALS['dbh'],$_GET['inviteid']);
		$roomname 	= mysqli_real_escape_string($GLOBALS['dbh'],$_GET['roomname']);
		$popoutmode = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['popoutmode']);
    }

	$cc_theme = '';
	if(!empty($_GET['cc_theme'])){
		$cc_theme = '&cc_theme='.$_GET['cc_theme'];
	}
	$time = getTimeStamp();
	$buddyList = array();
	$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.isdevice isdevice, cometchat_status.status, cometchat_status.message from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid right join cometchat_chatrooms_users on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." =cometchat_chatrooms_users.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." <> '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat_chatrooms_users.chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' and cometchat_chatrooms_users.isbanned ='1' group by userid order by username asc");
	if ($guestsMode) {
		$sql = getChatroomBannedGuests($id,$time,$sql);
   	}

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	while ($chat = mysqli_fetch_assoc($query)) {

		$avatar = getAvatar($chat['avatar']);

		if (!empty($chat['username'])) {
			if (function_exists('processName')) {
				$chat['username'] = processName($chat['username']);
			}

			$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'a' => $avatar);
		}
	}
	if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp'){
		$response['unban'] = $buddyList;
		echo json_encode($response);
		exit;
	}

	$s['count'] = '';

	foreach ($buddyList as $buddy) {

		$s['count'] .= '<div class="invite_1" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;"><div class="invite_2"><img height=30 width=30 src="'.$buddy['a'].'" /></div><div class="invite_3"><span class="invite_name">'.$buddy['n'].'</span><br/></div><input type="checkbox" onclick="javascript:document.getElementById(\'check_'.$buddy['id'].'\').checked = document.getElementById(\'check_'.$buddy['id'].'\').checked?false:true;" name="unban[]" value="'.$buddy['id'].'" id="check_'.$buddy['id'].'" class="invite_4" /></div>';
	}

	if($s['count'] == ''){
		$s['count'] = $chatrooms_language[44];
	}
	echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$chatrooms_language[21]}</title>
		<meta name="viewport" content="user-scalable=0,width=device-width, height=device-heigth minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms{$cc_theme}" />
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
			$ = jQuery = jqcc;
			$(function(){
				$('.invite_1').click(function() {
				    var checked = $( "input:checked" ).length;
				    if(checked > 0){
				    	$('.invitebutton').attr("disabled", false);
				    }else{
				    	$('.invitebutton').attr("disabled", true);
				    }
				});
			});
		</script>
	</head>
	<body>
		<form method="post" action="chatrooms.php?action=unbanusers&embed={$embed}&basedata={$basedata}&popoutmode={&popoutmode}">
			<div class="cometchat_wrapper">
				<div class="container_title {$embedcss}">{$chatrooms_language[21]}</div>
				<div class="container_body {$embedcss}">
					{$s['count']}
					<div style="clear:both"></div>
				</div>
				<div class="container_sub {$embedcss}">
					<input type=submit value="Unban Users" class="invitebutton" disabled />
				</div>
			</div>
			<input type="hidden" name="roomid" value="{$id}" />
			<input type="hidden" name="inviteid" value="{$inviteid}" />
			<input type="hidden" name="roomname" value="{$roomname}" />
		</form>
	</body>
</html>
EOD;
}

function unbanusers() {

	global $chatrooms_language;
	global $close;
	global $embedcss;

	if (empty($_SESSION['cometchat']['isModerator']) ) {
		echo 0;
		exit;
	}

	if (!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp'){
		$post_roomid = $_REQUEST['roomid'];
		$post_inviteid = $_REQUEST['inviteid'];
		$post_roomname = $_REQUEST['roomname'];
		$temp = $_REQUEST['unban'];

		$temp2 = substr($temp, 1);
		$temp2 = substr($temp2, 0, strlen($temp2)-1);

		$users = explode(",",$temp2);
		print_r($users);

		if (!empty($_REQUEST['unban'])) {
			foreach($users as $user) {
				$sql = ("delete from cometchat_chatrooms_users where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$user)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$post_roomid)."'");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				$sql = ("delete from cometchat_chatroommessages where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$post_roomid)."' and (message like '%banned_".mysqli_real_escape_string($GLOBALS['dbh'],$user)."')");
	    		$query = mysqli_query($GLOBALS['dbh'],$sql);
				$chatroomroomname = $post_roomname;
				sendMessage($user,"{$chatrooms_language[18]}{$chatroomroomname}. <a href=\"javascript:jqcc.cometchat.joinChatroom('{$post_roomid}','{$post_inviteid}','{$post_roomname}')\">{$chatrooms_language[19]}</a>",1);
			}
			echo 1;
			exit;
		}
	}else{
		if(!empty($_POST['unban'])){
			foreach ($_POST['unban'] as $user) {
				$sql = ("delete from cometchat_chatrooms_users where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$user)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomid'])."'");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				$sql = ("delete from cometchat_chatroommessages where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomid'])."' and (message like '%banned_".mysqli_real_escape_string($GLOBALS['dbh'],$user)."')");
	    		$query = mysqli_query($GLOBALS['dbh'],$sql);
				$post_roomid = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomid']);
				$post_inviteid = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['inviteid']);
				$post_roomname = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['roomname']);
				$chatroomroomname = base64_decode(rawurldecode($post_roomname));
				addUsersToChatroom($post_roomid, $user);
				sendMessage($user,"{$chatrooms_language[18]}{$chatroomroomname}. <a href=\"javascript:jqcc.cometchat.joinChatroom('{$post_roomid}','{$post_inviteid}','{$post_roomname}')\">{$chatrooms_language[19]}</a>",1);
			}
		}
	}

	echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$chatrooms_language[18]}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=chatrooms" />
		<script type="text/javascript">
			function closePopup(){
				var controlparameters = {'type':'modules', 'name':'chatrooms', 'method':'closeCCPopup', 'params':{'name':'invite'}};
				controlparameters = JSON.stringify(controlparameters);
				if(typeof(parent) != 'undefined' && parent != null && parent != self){
					parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				} else {
					window.close();
				}
			}
		</script>
	</head>
	<body onload="{$close}">
		<div class="cometchat_wrapper">
			<div class="container_title {$embedcss}">{$chatrooms_language[21]}</div>
			<div class="container_body {$embedcss}">
				{$chatrooms_language[16]}
				<div style="clear:both"></div>
			</div>
		</div>
	</body>
</html>
EOD;
}

function deleteChatroomMessage() {
	$id = $_REQUEST['currentroom'];
	$delid = $_REQUEST['delid'];
	global $allowdelete;
	global $userid;
    $deleteflag = 0;

    if (!empty($_SESSION['cometchat']['isModerator'])) {
        $deleteflag = 1;
    } elseif (empty($allowdelete)){
        $sql = ("select userid from cometchat_chatroommessages where id='".mysqli_real_escape_string($GLOBALS['dbh'],$delid)."'");
        $query = mysqli_query($GLOBALS['dbh'],$sql);
        $row = mysqli_fetch_assoc($query);
        if ($row['userid'] == $userid) {
            $deleteflag = 1;
        }
    }
	if (empty($deleteflag)) {
		echo 0;
		exit;
	} else {
            sendCCResponse(1);
        }
	$del = $delid;

    $sql = ("delete from cometchat_chatroommessages where id='".mysqli_real_escape_string($GLOBALS['dbh'],$del)."' and chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$controlparameters = array('type' => 'modules', 'name' => 'chatroom', 'method' => 'deletemessage', 'params' => array('id' => $delid));
	$controlparameters = json_encode($controlparameters);
	sendChatroomMessage($id,'CC^CONTROL_'.$controlparameters,0);
}

function addUsersToChatroom($roomid, $inviteid, $remove = 0) {

	if(empty($roomid) || empty($inviteid)) { return; }

	$sql = ("select * from cometchat_chatrooms where id='".mysqli_real_escape_string($GLOBALS['dbh'],$roomid)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
	$row = mysqli_fetch_assoc($query);

	if($row['type'] != 2) { return; }

	$userList = array();
	$implodedUserList = '';
	$updateInvitedUsers = 0;

	if(!($invitedusers = $row['invitedusers'])) {
		$invitedusers = '';
	}
	$userList = array_filter(explode(',', $invitedusers));
	if(!in_array($inviteid, $userList) && $remove == 0){
		$userList[] = $inviteid;
		$updateInvitedUsers = 1;
	}
	if($remove == 1){
		$key = array_search($inviteid,$userList);
		if($key!==false){
		    unset($userList[$key]);
		    $updateInvitedUsers = 1;
		}
	}
	if($updateInvitedUsers == 1){
		$implodedUserList = implode(',',$userList);
		$sql = ("update cometchat_chatrooms set invitedusers = '".mysqli_real_escape_string($GLOBALS['dbh'],$implodedUserList)."' where id='".mysqli_real_escape_string($GLOBALS['dbh'],$roomid)."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
	}
}

function getChatroomDetails() {
	$id = $_REQUEST['id'];
	$sql = ("select * from cometchat_chatrooms where cometchat_chatrooms.id = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$chatroom = mysqli_fetch_assoc($query);
	$response = array('id' => $chatroom['id'], 'name' => $chatroom['name'], 'type' => $chatroom['type'], 'password' => $chatroom['password'], 'createdby' => $chatroom['createdby'], 'invitedusers' => $chatroom['invitedusers']);
	echo json_encode($response);
}

function getChatroomUserIDs($chatroomid) {
	$chatroomusers = array();
	$sql = ("select userid chatroomusers from cometchat_chatrooms_users where isbanned=0 and chatroomid='".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' ");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	while ($result = mysqli_fetch_assoc($query)) {
		array_push($chatroomusers, $result['chatroomusers']);
	}
	return $chatroomusers;
}

$allowedActions = array('sendChatroomMessage','heartbeat','createchatroom','deletechatroom','checkpassword','invite','inviteusers','unban','unbanusers','passwordBox','loadChatroomPro','leavechatroom','kickUser','banUser','deleteChatroomMessage','getChatroomDetails','renamechatroom');

if (!empty($_GET['action']) && in_array($_GET['action'],$allowedActions)) {
	call_user_func($_GET['action']);
}