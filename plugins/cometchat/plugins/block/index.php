<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

if ($p_<1) exit;

if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'block') {
	$blockedIds=getBlockedUserIDs();
	$id = mysqli_real_escape_string($GLOBALS['dbh'],$_REQUEST['to']);
	if(!in_array($id, $blockedIds)){

		$sql = "insert into cometchat_block (fromid, toid) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','".mysqli_real_escape_string($GLOBALS['dbh'],$id)."')";
		$query = mysqli_query($GLOBALS['dbh'],$sql);

		removeCache('blocked_id_of_'.$userid);
		removeCache('blocked_id_of_'.$id);
		removeCache('blocked_id_of_receive_'.$userid);
		removeCache('blocked_id_of_receive_'.$id);

		$response = array();
		$response['id'] = $id;
		$error = mysqli_error($GLOBALS['dbh']);
		if (!empty($error)) {
			$response['result'] = "0";
			header('content-type: application/json; charset=utf-8');
			$response['error'] = mysqli_error($GLOBALS['dbh']);
			echo $_REQUEST['callback'].'('.json_encode($response).')';
			exit;
		}

		$response['result'] = "1";

		if (!empty($_REQUEST['callback']) || !empty($_REQUEST['callbackfn'])) {
			header('content-type: application/json; charset=utf-8');
			if(empty($_REQUEST['callbackfn']) || $_REQUEST['callbackfn']<>'mobileapp'){
				echo $_REQUEST['callback'].'('.json_encode($response).')';
			} else {
				echo json_encode($response);
			}
		}
	}else{
		$response['result'] = "2";
		if (!empty($_REQUEST['callback']) || !empty($_REQUEST['callbackfn'])) {
			header('content-type: application/json; charset=utf-8');
			if(empty($_REQUEST['callbackfn']) || $_REQUEST['callbackfn']<>'mobileapp'){
				echo $_REQUEST['callback'].'('.json_encode($response).')';
			} else {
				echo json_encode($response);
			}
		}
	}

} else if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'unblock') {
	if(empty($_REQUEST['id'])){
		$id = intval($_REQUEST['to']);
	} else {
		$id = intval($_REQUEST['id']);
	}
	$embed = '';
	$embedcss = '';

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
	}

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
		$embed = 'desktop';
		$embedcss = 'embed';
	}

	$sql = "delete from cometchat_block where toid = '".mysqli_real_escape_string($GLOBALS['dbh'],$id)."' and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'";
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$affectedRows = mysqli_affected_rows($GLOBALS['dbh']);
	removeCache('blocked_id_of_'.$userid);
	removeCache('blocked_id_of_'.$id);
	removeCache('blocked_id_of_receive_'.$userid);
	removeCache('blocked_id_of_receive_'.$id);
	$response = array();
	$response['id'] = $id;
	$error = mysqli_error($GLOBALS['dbh']);

	if(empty($_REQUEST['callbackfn']) || $_REQUEST['callbackfn']<>'mobileapp'){
		if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }
		$ts = time();
		header("Location: index.php?basedata={$_REQUEST['basedata']}&embed={$embed}&ts={$ts}\r\n");
		exit;
	} else {
		header('content-type: application/json; charset=utf-8');
		if (!empty($error)) {
			$response['result'] = "0";
			$response['error'] = mysqli_error($GLOBALS['dbh']);
		}else if($affectedRows == 0){
			$response['result'] = "0";
			$response['error'] = 'NOT_A_BLOCKED_USER';
		} else {
			$response['result'] = "1";
		}
		echo json_encode($response);
		exit;
	}
} else {

	$embed = '';
	$embedcss = '';

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
		$embed = 'web';
		$embedcss = 'embed';
	}

	if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
		$embed = 'desktop';
		$embedcss = 'embed';
	}

	$usertable = TABLE_PREFIX.DB_USERTABLE;
	$usertable_username = DB_USERTABLE_NAME;
	$usertable_userid = DB_USERTABLE_USERID;
	$body = '';
	$number = 0;
	$guestpart = '';
	if($guestsMode == 1){
		$guestpart = " UNION (select distinct(m.id) `id`, concat('".$guestnamePrefix."',m.name) `name` from cometchat_block, cometchat_guests m where m.id = toid and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."')";
	}

	$sql = ("(select distinct(m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid).") `id`, m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." `name` from cometchat_block, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." m where m.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = toid and fromid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') ".$guestpart."");

	$query = mysqli_query($GLOBALS['dbh'],$sql);


	if(empty($_REQUEST['callbackfn']) || $_REQUEST['callbackfn']<>'mobileapp'){
		while ($chat = mysqli_fetch_assoc($query)) {
			if (function_exists('processName')) {
				$chat['name'] = processName($chat['name']);
			}

			++$number;

		$body = <<<EOD
 $body
<div class="chat">
			<div class="chatrequest"><b>{$number}</b></div>
			<div class="chatmessage">{$chat['name']}</div>
			<div class="chattime"><a href="?action=unblock&amp;id={$chat['id']}&amp;basedata={$_REQUEST['basedata']}&amp;embed={$embed}">{$block_language[4]}</a></div>
			<div style="clear:both"></div>
</div>

EOD;
		}

	if ($number == 0) {
		$body = <<<EOD
 $body
<div class="chat">
			<div class="chatrequest">&nbsp;</div>
			<div class="chatmessage">{$block_language[6]}</div>
			<div class="chattime">&nbsp;</div>
			<div style="clear:both"></div>
</div>

EOD;
	}



echo <<<EOD
	<!DOCTYPE html>
	<html>
	<head>
	<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>{$block_language[3]}</title>
	<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=plugin&name=block" />
	<script src="../../js.php?type=core&name=jquery" type="text/javascript"></script>
	<script src="../../js.php?type=core&name=scroll" type="text/javascript"></script>
	<script>
		jqcc(document).ready(function() {
		   jqcc('.container_body').slimScroll({scroll: '1'});
		   jqcc('.container_body').slimScroll({height: jqcc(".container_body").css('height')});
		});
		function buddyListRefresh(){
			var controlparameters = {"type":"core", "name":"cometchat", "method":"chatHeartbeat", "params":{}};
			controlparameters = JSON.stringify(controlparameters);
			if(window.top != window.self){
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			} else {
				window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
			}
		}
	</script>
	</head>
	<body onload="buddyListRefresh()">
	<div class="cometchat_wrapper">
	<div class="container_title {$embedcss}" >{$block_language[3]}</div>

	<div class="container_body {$embedcss}">

	$body

	</div>
	</div>
	</div>
	</body>
	</html>
EOD;
	} else {
	$response = array();
	while ($chat = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$blockedName = processName($chat['name']);
		} else {
			$blockedName = $chat['name'];
		}
		$blockedID = $chat['id'];
		$response[$blockedID] = array('id'=>$blockedID,'name'=>$blockedName);
	}
	if(empty($response)){
		$response = json_decode('{}');
	}
	echo json_encode($response);
	}
}
