<?php
/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."cometchat_init.php");

$response = array();
$messages = array();
$lastPushedAnnouncement = 0;
$processFurther = 1;

$status['available'] = $language[30];
$status['busy'] = $language[31];
$status['offline'] = $language[32];
$status['invisible'] = $language[33];
$status['away'] = $language[34];

if (empty($_REQUEST['activeChatboxIds'])) {
	$_REQUEST['activeChatboxIds'] = 0;
}

if (empty($_REQUEST['f'])) {
	$_REQUEST['f'] = 0;
}

if ($userid > 0) {
	if (!empty($_REQUEST['chatbox'])) {
		getChatboxData($_REQUEST['chatbox']);
	} else {
		if(!empty($_REQUEST['readmessages'])){
			$sqlpart="";
			if(gettype($_REQUEST['readmessages']) == 'string'){
				$_REQUEST['readmessages'] = json_decode(str_replace(' ', '', $_REQUEST['readmessages']));
			}
			foreach($_REQUEST['readmessages'] as $from=>$lastreadmessageid){
				if(empty($_SESSION['cometchat']['lastreadmessageid']['cometchat_user_'.$from]) || ($lastreadmessageid>$_SESSION['cometchat']['lastreadmessageid']['cometchat_user_'.$from])){
					$lastreadsessionid['cometchat_user_'.$from] = $lastreadmessageid;
					$sqlpart.= " (`from` = '".mysqli_real_escape_string($GLOBALS['dbh'],$from)."' and `id` <= '".mysqli_real_escape_string($GLOBALS['dbh'],$lastreadmessageid)."') OR";
				}
			}
			if(!empty($sqlpart)){
				$sqlpart = rtrim($sqlpart,"OR");
				$sql = ("update cometchat set `read` = '1' where `to`= '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and (".$sqlpart.") and `read` = '0'");
				if(mysqli_query($GLOBALS['dbh'],$sql)){
					$_SESSION['cometchat']['lastreadmessageid'] = $lastreadsessionid;
				}
				if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
			}
		}
		if (!empty($_REQUEST['status'])) {
			setStatus($_REQUEST['status']);
		}
		if (!empty($_REQUEST['initialize']) && $_REQUEST['initialize'] == 1) {

			if (USE_COMET == 1) {
				$key = '';
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}
				$response['cometid']['id'] = md5($userid.$key);
				$comet = new Comet(KEY_A,KEY_B);
				if(method_exists($comet, 'processChannel')){
					$response['cometid']['id'] = processChannel($response['cometid']['id']);
				}

				if (empty($_SESSION['cometchat']['cometmessagesafter'])) {
					$_SESSION['cometchat']['cometmessagesafter'] = getTimeStamp().'999';
				}
				$response['initialize'] = 0;
				$response['init'] = '1';

			} else {

				$sql = ("select max(id) as id from cometchat");
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
				$result = mysqli_fetch_assoc($query);

				$response['init'] = '1';
				$response['initialize'] = '0';
				if(!empty($result['id'])){
					$response['initialize'] = $result['id'];
				}
			}

			getStatus();

			if (!empty($_COOKIE[$cookiePrefix.'state'])) {
				$states = explode(':',urldecode($_COOKIE[$cookiePrefix.'state']));
				$states[2] = trim($states[2]);
				if(!empty($states[2]) && is_numeric($states[2])){
					$openChatboxIds = explode(',',$states[2]);
					foreach ($openChatboxIds as $openChatboxId) {
						getChatboxData($openChatboxId);
					}
				}
			}
			$response['st'] = time();
		}

		if (!empty($_REQUEST['buddylist']) && $_REQUEST['buddylist'] == 1 && $processFurther) { getBuddyList(); }

		getLastTimestamp();
		if (defined('DISABLE_ISTYPING') && DISABLE_ISTYPING != 1 && $processFurther) { typingTo(); }
		if (defined('DISABLE_ANNOUNCEMENTS') && DISABLE_ANNOUNCEMENTS != 1 && $processFurther) { checkAnnoucements(); }

		if ($processFurther) {
			fetchMessages();
		}
	}

        $time = getTimeStamp();

	if ($processFurther) {
		if (empty($_SESSION['cometchat']['cometchat_lastlactivity']) || ($time-$_SESSION['cometchat']['cometchat_lastlactivity'] >= REFRESH_BUDDYLIST/4)) {
			$sql = updateLastActivity($userid);
            if (function_exists('hooks_updateLastActivity')) {
                hooks_updateLastActivity($userid);
         	}
            $query = mysqli_query($GLOBALS['dbh'],$sql);
			if(empty($_SESSION['cometchat']['user']) ||(!empty($_SESSION['cometchat']['user']) && $_SESSION['cometchat']['user']['s'] != 'invisible')){
	        	$sql = updateLastSeen($userid);
	        	$query = mysqli_query($GLOBALS['dbh'],$sql);
	    	}

			if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
			$_SESSION['cometchat']['cometchat_lastlactivity'] = $time;
		}
		if (!empty($_REQUEST['typingto']) && $_REQUEST['typingto'] != 0 && DISABLE_ISTYPING != 1) {
			$sql = ("insert into cometchat_status (userid,typingto,typingtime) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['typingto'])."','".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."') on duplicate key update typingto = '".mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['typingto'])."', typingtime = '".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."'");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		}
    }
} else {
	$response['loggedout'] = '1';
	if (!empty($_COOKIE[$cookiePrefix.'guest'])) {
		$response['logout_message'] = $language[107];
		setcookie($cookiePrefix.'guest','',time()-3600,'/');
	}
	setcookie($cookiePrefix.'state','',time()-3600,'/');
	setcookie($cookiePrefix.'crstate','',time()-3600,'/');
	unset($_SESSION['cometchat']);
}

function getLastTimestamp() {
	if (empty($_REQUEST['timestamp'])) {
		$_REQUEST['timestamp'] = 0;
	}

	if ($_REQUEST['timestamp'] == 0) {
		foreach ($_SESSION['cometchat'] as $key => $value) {
			if (substr($key,0,15) == "cometchat_user_") {
				if (!empty($_SESSION['cometchat'][$key]) && is_array($_SESSION['cometchat'][$key])) {
					$temp = end($_SESSION['cometchat'][$key]);
					if (!empty($temp['id']) && $_REQUEST['timestamp'] < $temp['id']) {
						$_REQUEST['timestamp'] = $temp['id'];
					}
				}
			}
		}

		if ($_REQUEST['timestamp'] == 0) {
			$sql = ("select id from cometchat order by id desc limit 1");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
			$chat = mysqli_fetch_assoc($query);
			if(!empty($chat['id'])){
				$_REQUEST['timestamp'] = $chat['id'];
			}
		}
	}

}

function getBuddyList() {
	global $response;
	global $userid;
	global $db;
	global $status;
	global $hideOffline;
	global $plugins;
	global $guestsMode;
	global $cookiePrefix;
    global $chromeReorderFix;
    global $blockpluginmode;
    global $bannedUserIDs;

	$time = getTimeStamp();

	if ((empty($_SESSION['cometchat']['cometchat_buddytime'])) || ($_REQUEST['initialize'] == 1)  || ($_REQUEST['f'] == 1)  || (!empty($_SESSION['cometchat']['cometchat_buddytime']) && ($time-$_SESSION['cometchat']['cometchat_buddytime'] >= REFRESH_BUDDYLIST || MEMCACHE <> 0))) {

		if ($_REQUEST['initialize'] == 1 && !empty($_SESSION['cometchat']['cometchat_buddyblh']) && ($time-$_SESSION['cometchat']['cometchat_buddytime'] < REFRESH_BUDDYLIST) && !(defined('TAPATALK'))) {

			$response['buddylist'] = $_SESSION['cometchat']['cometchat_buddyresult'];
			$response['blh'] = $_SESSION['cometchat']['cometchat_buddyblh'];

		} else {
			$onlineCacheKey = 'all_online';
			if($userid > 10000000){
				$onlineCacheKey .= 'guest';
			}
			if (!is_array($buddyList = getCache($onlineCacheKey)) || ($_REQUEST['f'] == 1) || (defined('TAPATALK'))) {
				$buddyList = array();
				$sql = getFriendsList($userid,$time);
				if ($guestsMode) {
					$sql = getGuestsList($userid,$time,$sql);
				}
				if(!empty($_REQUEST['activeChatboxIds'])){
					$activeChatboxIds = "'".str_replace(",", "','", $_REQUEST['activeChatboxIds'])."'";
					$sql =  getActivechatboxdetails($activeChatboxIds)." UNION ".$sql;
				}
				$query = mysqli_query($GLOBALS['dbh'],$sql);
				if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

				while ($chat = mysqli_fetch_assoc($query)) {
					if(in_array($chat['userid'],$bannedUserIDs)) {
						continue;
 					}
					if (((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) || $chat['isdevice'] == 1) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') {
						if (($chat['status'] != 'busy' && $chat['status'] != 'away')) {
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

					if (function_exists('processName')) {
						$chat['username'] = processName($chat['username']);
					}

					if(empty($chat['isdevice'])){
						$chat['isdevice'] = "0";
					}
					if (empty($chat['grp'])) {
						$chat['grp'] = '';
					}

					if (empty($chat['ch'])) {
						if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
								$key = KEY_A.KEY_B.KEY_C;
						}
						$chat['ch'] = md5($chat['userid'].$key);
					}
					if(defined('TAPATALK')){
						global $integration;
						$chat['message'] = $integration->hooks_processMessageBuddylist($chat['message']);
					}
					if (!empty($chat['username']) && ($hideOffline == 0 || ($hideOffline == 1 && $chat['status'] != 'offline')) || in_array($chat['userid'],explode(",",$_REQUEST['activeChatboxIds']))) {
						$buddyList[$chromeReorderFix.$chat['userid']] = array('id' => $chat['userid'], 'n' => $chat['username'], 'l' => $link,  'a' => $avatar, 'd' => $chat['isdevice'], 's' => $chat['status'], 'm' => $chat['message'], 'g' => $chat['grp'], 'ls' => $chat['lastseen'], 'lstn' => $chat['lastseensetting'], 'ch' => $chat['ch']);
					}

				}
				setCache($onlineCacheKey,$buddyList,30);

			}

			if (DISPLAY_ALL_USERS == 0 && MEMCACHE <> 0 && USE_CCAUTH == 0) {
				$tempBuddyList = array();
				if (!is_array($friendIds = getCache('friend_ids_of_'.$userid) || ($_REQUEST['f'] == 1) )) {
					$friendIds = array();
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

			$blockList = array();
			if (in_array('block',$plugins)) {
				if($blockpluginmode == 1){
					$blockedIds = getBlockedUserIDs(1);
				} else {
					$blockedIds = getBlockedUserIDs();
				}
				foreach ($blockedIds as $bid) {
					array_push($blockList,$bid);
					if (!empty($buddyList[$chromeReorderFix.$bid])) {
						if($blockpluginmode == 1){
							if(defined('TAPATALK') && (!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp')){
								$buddyList[$chromeReorderFix.$bid]['s'] = 'banned';
							}else{
								$buddyList[$chromeReorderFix.$bid]['s'] = 'blocked';
							}
						}else{
							unset($buddyList[$chromeReorderFix.$bid]);
						}
					}
				}
			}


			if (!empty($buddyList[$chromeReorderFix.$userid])) {
	            if(empty($_SESSION['cometchat']['user'])||(!empty($_SESSION['cometchat']['user']) && $_SESSION['cometchat']['user']['s'] <> $buddyList[$chromeReorderFix.$userid]['s'])){
	                array_merge($_SESSION['cometchat']['user'],$buddyList[$chromeReorderFix.$userid]);
	            }
	            unset($buddyList[$chromeReorderFix.$userid]);
	        }

			if (function_exists('hooks_forcefriends') && is_array(hooks_forcefriends())) {
				$buddyList = array_merge(hooks_forcefriends(),$buddyList);
			}

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

			if(!(defined('TAPATALK'))){
				array_multisort($buddyOrder, SORT_ASC, $buddyGroup, SORT_STRING, $buddyStatus, SORT_STRING, $buddyGuest, SORT_ASC, $buddyName, SORT_STRING, $buddyList);
			}

			$_SESSION['cometchat']['cometchat_buddytime'] = $time;

			$blh = md5(serialize($buddyList));

			if((empty($_REQUEST['blh'])) || (!empty($_REQUEST['blh']) && $blh != $_REQUEST['blh']) || ($_REQUEST['f'] == 1)) {
				$response['buddylist'] = $buddyList;
				$response['blh'] = $blh;
			}

			$_SESSION['cometchat']['cometchat_buddyresult'] = $buddyList;
			$_SESSION['cometchat']['cometchat_buddyblh'] = $blh;
		}
	}
}

function fetchMessages() {
	global $response;
	global $userid;
	global $db;
	global $messages;
	global $cookiePrefix;
	global $chromeReorderFix;
	$timestamp = 0;

	if (USE_COMET == 1 && empty($_REQUEST['initialize'])) { return; }

	$sqlpart = array('','','','','');
	$whereclause = array('','');

	if(empty($_REQUEST['v3'])){
		if(!empty($_REQUEST['receivedunreadmessages'])){
			if(gettype($_REQUEST['receivedunreadmessages']) == 'string'){
				$_REQUEST['receivedunreadmessages'] = json_decode(str_replace(' ', '', $_REQUEST['receivedunreadmessages']));
			}
			foreach($_REQUEST['receivedunreadmessages'] as $from=>$lastunreadmessageid){
				$sqlpart[0].= " (cometchat.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$from)."' and cometchat.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$lastunreadmessageid)."') OR ";
				$sqlpart[1].= " (cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$from)."' and cometchat.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$lastunreadmessageid)."') OR ";
				$sqlpart[2].= "'".$from."',";
			}
			if(!empty($sqlpart[0])){
				$sqlpart[0] = " cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction <> 2 and ( ".rtrim($sqlpart[0],"OR ")." )";
				$sqlpart[1] = " cometchat.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction <> 1 and (
									".rtrim($sqlpart[1],"OR ")." )";
				$sqlpart[3] = " and cometchat.from not in (".rtrim($sqlpart[2],",").")";
				$sqlpart[2] = " and cometchat.to not in (".rtrim($sqlpart[2],",").")";
				$whereclause[0] = " ( ".$sqlpart[0]." ) or ( ".$sqlpart[1]." ) or ";
			}
		}
		$sqlpart[3] = " ( cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction <> 2 ".$sqlpart[3]. " ) ";
		$sqlpart[2] = " ( cometchat.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction = 2 ".$sqlpart[2]." ) ";
		$sqlpart[4] = " cometchat.read <> 1 and ";


		$whereclause[1] = "( ".$sqlpart[4]." ( ".$sqlpart[3]." or ".$sqlpart[2]." ) )";

		$sql = ("select cometchat.id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, cometchat.direction from cometchat where ( ".$whereclause[0].$whereclause[1]." ) and cometchat.direction <> 3 order by cometchat.id");
	}else{
		$sql = ("select cometchat.id, cometchat.from, cometchat.to, cometchat.message, cometchat.sent, cometchat.read, cometchat.direction from cometchat where ((cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction <> 2) or (cometchat.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.direction <> 1)) and (cometchat.id > '".mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['timestamp'])."' or (cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.read <> 1)) and cometchat.direction <> 3 order by cometchat.id");
	}
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	while ($chat = mysqli_fetch_assoc($query)) {
		$self = 0;
		$old = 0;
		if ($chat['from'] == $userid) {
			$chat['from'] = $chat['to'];
			$self = 1;
			$old = 1;
		}
		if ($chat['read'] == 1) {
			$old = 1;
		}
		if ((!empty($_REQUEST[$cookiePrefix.'lang'])||!empty($_COOKIE[$cookiePrefix.'lang'])) && $self == 0 && $old == 0 && strpos($chat['message'],'CC^CONTROL_') === false) {
			if(!empty($_REQUEST[$cookiePrefix.'lang'])){
				$translated = text_translate($chat['message'],'',$_REQUEST[$cookiePrefix.'lang']);
			}
			if(!empty($_COOKIE[$cookiePrefix.'lang'])){

				$translated = text_translate($chat['message'],'',$_COOKIE[$cookiePrefix.'lang']);
			}
			if ($translated != '') {
				if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn']=='mobileapp'){
					$chat['message'] = strip_tags($translated).' ('.$chat['message'].')';
				} else {
					$chat['message'] = strip_tags($translated).' <span class="untranslatedtext">('.$chat['message'].')</span>';
				}
			}
		}

		if(!empty($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp' && defined('TAPATALK')){
			global $integration;
			$chat['message'] = $integration->hooks_processMessage($chat['message']);
		}

		$messages[$chromeReorderFix.$chat['id']] = array('id' => $chat['id'], 'from' => $chat['from'], 'message' => $chat['message'], 'self' => $self, 'old' => $old, 'sent' => ($chat['sent']));

		if (empty($SESSION['cometchat']['cometchat_user'.$chat['from']][$chromeReorderFix.$chat['id']]['id'])) {
			$_SESSION['cometchat']['cometchat_user_'.$chat['from']][$chromeReorderFix.$chat['id']] = array('id' => $chat['id'], 'from' => $chat['from'], 'message' => $chat['message'], 'self' => $self, 'old' => 1, 'sent' => ($chat['sent']));
		}
		$timestamp = $chat['id'];
	}

	if ( !empty($messages) && ( !empty($_REQUEST['callbackfn']) && ($_REQUEST['callbackfn'] == 'mobileapp' || $_REQUEST['callbackfn'] == 'mobilewebapp') && empty($_REQUEST['v'])) ) {
		$sql = ("update cometchat set cometchat.read = '1' where cometchat.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and cometchat.id <= '".mysqli_real_escape_string($GLOBALS['dbh'],$timestamp)."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
	}
}

function typingTo() {
	global $response;
	global $userid;
	global $db;
	global $messages;
	$timestamp = 0;
	if (USE_COMET == 1) { return; }
	$sql = ("select GROUP_CONCAT(userid, ',') as tt from cometchat_status where typingto = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and ('".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."'-typingtime < 10)");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
	$chat = mysqli_fetch_assoc($query);
	if (!empty($chat['tt'])) {
		$response['tt'] = $chat['tt'];
	} else {
		$response['tt'] = '';
	}
}

function checkAnnoucements() {
	global $response;
	global $userid;
	global $db;
	global $messages;
	global $cookiePrefix;
	global $notificationsFeature;
	global $notificationsClub;

	$timestamp = 0;
	if(!empty($_REQUEST[$cookiePrefix.'an'])){
		$_COOKIE[$cookiePrefix.'an'] = $_REQUEST[$cookiePrefix.'an'];
	}
	if ($notificationsFeature) {

		$sql = ("select count(id) as count from cometchat_announcements where `to` = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and  `recd` = '0'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		$count = mysqli_fetch_assoc($query);
		$count = $count['count'];

		if ($count > 0) {
			$sql = ("select id,announcement,time from cometchat_announcements where `to` = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and  `recd` = '0' order by id desc limit 1");
			$query = mysqli_query($GLOBALS['dbh'],$sql);
			if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
			$announcement = mysqli_fetch_assoc($query);

			if (!empty($announcement['announcement'])) {
				$sql = ("update cometchat_announcements set `recd` = '1' where `id` <= '".mysqli_real_escape_string($GLOBALS['dbh'],$announcement['id'])."' and `to`  = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
				$query = mysqli_query($GLOBALS['dbh'],$sql);

				$response['an'] = array('id' => $announcement['id'], 'm' => $announcement['announcement'],'t' => $announcement['time'], 'o' => $count);
				return;
			}
		}
	}

	if (!is_array($announcement = getCache('latest_announcement'))|| ($_REQUEST['f'] == 1)) {
		$announcement=array();
		$sql = ("select id,announcement an,time t from cometchat_announcements where `to` = '0' or `to` = '-1' order by id desc limit 1");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		if($announcement = mysqli_fetch_assoc($query)) {
			setCache('latest_announcement',$announcement,3600);
		}
	}
	if (!empty($announcement['an']) && (empty($_COOKIE[$cookiePrefix.'an']) || (!empty($_COOKIE[$cookiePrefix.'an']) && $_COOKIE[$cookiePrefix.'an'] < $announcement['id']))) {
		$response['an'] = array('id' => $announcement['id'], 'm' => $announcement['an'],'t' => $announcement['t']);
	}
}

header('Content-type: application/json; charset=utf-8');

if (isset($response['initialize'])) {
	$initialize = $response['initialize'];
	unset($response['initialize']);
	$response['initialize'] = $initialize;
}

if (!empty($messages)) {
	$response['messages'] = $messages;
}

$useragent = (!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : '';
if(phpversion()>='4.0.4pl1'&&(strstr($useragent,'compatible')||strstr($useragent,'Gecko'))){
	if(extension_loaded('zlib')&&GZIP_ENABLED==1 && !in_array('ob_gzhandler', ob_list_handlers())){
		ob_start('ob_gzhandler');
	}else{
		ob_start();
	}
}else{
	ob_start();
}
if (!empty($_GET['callback'])) {
	echo $_GET['callback'].'('.json_encode($response).')';
} else {
	echo json_encode($response);
}
exit;