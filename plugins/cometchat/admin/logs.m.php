<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	<a href="?module=logs&amp;ts={$ts}" id="chat_logs">One-on-one Chat</a>
	<a href="?module=logs&amp;action=chatroomlog&amp;ts={$ts}" id="chatroom_logs">Chatrooms</a>
	</div>
EOD;

if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

function index() {
	global $body;
	global $navigation;
    global $ts;
	$overlay = '';

	$body = <<<EOD
	{$navigation}
	<form action="?module=logs&action=searchlogs&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Search One-on-one Chat</h2>
		<h3>You can search by username or user ID. Please fill in atleast one field below.</h3>

		<div>
			<div id="centernav">
				<div class="title">User ID:</div><div class="element"><input type="text" class="inputbox" name="userid"></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Username:</div><div class="element"><input type="text" class="inputbox" name="susername"></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_logtips">
					<li>This feature is resource intensive. Please use judiciously.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Search Logs" class="button">&nbsp;&nbsp;or <a href="?module=logs&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chat_logs").addClass('active_setting');
		});
	</script>

	{$overlay}

EOD;

	template();

}

function searchlogs() {
    global $ts;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $navigation;
	global $body;
	global $guestsMode;
	global $guestnamePrefix;

	$userid = $_POST['userid'];
	$username = $_POST['susername'];

	if (empty($username)) {
		// Base 64 Encoded
		$username = 'Q293YXJkaWNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgc2FmZT8NCkV4cGVkaWVuY3kgYXNrcyB0aGUgcXVlc3Rpb24gLSBpcyBpdCBwb2xpdGljPw0KVmFuaXR5IGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcG9wdWxhcj8NCkJ1dCBjb25zY2llbmNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcmlnaHQ/DQpBbmQgdGhlcmUgY29tZXMgYSB0aW1lIHdoZW4gb25lIG11c3QgdGFrZSBhIHBvc2l0aW9uDQp0aGF0IGlzIG5laXRoZXIgc2FmZSwgbm9yIHBvbGl0aWMsIG5vciBwb3B1bGFyOw0KYnV0IG9uZSBtdXN0IHRha2UgaXQgYmVjYXVzZSBpdCBpcyByaWdodC4=';
	}

	$guestpart = "";

	if($guestsMode) {
		$guestpart = "union (select cometchat_guests.id, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',cometchat_guests.name) username from cometchat_guests where cometchat_guests.name LIKE '%".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($username))."%' or cometchat_guests.id = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($userid))."')";
	}

	$sql = ("(select ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." id, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username from ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." where ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." LIKE '%".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($username))."%' or ".$usertable_userid." = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($userid))."') ".$guestpart." ");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$userslist = '';
	$no_users = '';

	while ($user = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$user['username'] = processName($user['username']);
		}

		$userslist .= '<li class="ui-state-default" onclick="javascript:logs_gotouser(\''.$user['id'].'\');"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$user['username'].'</span><div style="clear:both"></div></li>';
	}

	if(!$userslist){
		$no_users .= '<div id="no_plugin" style="width: 480px;float: left;color: #333333;">No results found</div>';
	}

	$body = <<<EOD
	{$navigation}

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Logs</h2>
		<h3>Please select a user from below. <a href="?module=logs&amp;ts={$ts}">Click here to search again</a></h3>

		<div>
			<ul id="modules_logs">
				{$no_users}
				{$userslist}
			</ul>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chat_logs").addClass('active_setting');
		});
	</script>

EOD;

	template();
}

function viewuser() {
	global $ts;
	global $body;
	global $navigation;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $guestsMode;
	global $guestnamePrefix;

	$userid = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['data']);

	$guestpart = "";

	if($userid < '10000000') {
		$sql = ("select ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username from ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." where ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	} else {
		$sql = ("select concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',name) username from cometchat_guests where cometchat_guests.id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	}
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$usern = mysqli_fetch_assoc($query);

	if($guestsMode) {
		$guestpart = " union (select distinct(f.id) id, concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',f.name) username  from cometchat m1, cometchat_guests f where (f.id = m1.from and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') or (f.id = m1.to and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'))";
	}

	$sql = ("(select distinct(f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid).") id, f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username  from cometchat m1, ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f where (f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.from and m1.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') or (f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = m1.to and m1.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."')) ".$guestpart." order by username asc");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$userslist = '';
	$no_users = '';

	if (function_exists('processName')) {
		$usern['username'] = processName($usern['username']);
	}

	while ($user = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$user['username'] = processName($user['username']);
		}

		$userslist .= '<li class="ui-state-default" onclick="javascript:logs_gotouserb(\''.$userid.'\',\''.$user['id'].'\');"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$user['username'].'</span><div style="clear:both"></div></li>';
	}

	if(!$userslist){
		$no_users .= '<div id="no_plugin" style="width: 480px;float: left;color: #333333;">No results found</div>';
	}

	$body = <<<EOD
	{$navigation}
	<form action="?module=logs&action=newlogprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Log for {$usern['username']}</h2>
		<h3>Select a user between whom you want to view the conversation.</h3>

		<div>
			<ul id="modules_logs">
				{$no_users}
				{$userslist}
			</ul>
		</div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chat_logs").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function viewuserconversation() {
	global $ts;
	global $body;
	global $navigation;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $guestnamePrefix;

	$userid = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['data']);
	$userid2 = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['data2']);

	if($userid < '10000000') {
		$sql = ("select ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username from ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." where ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	} else {
		$sql = ("select concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',name) username from cometchat_guests where cometchat_guests.id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");
	}
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$usern = mysqli_fetch_assoc($query);

	if($userid2 < '10000000') {
		$sql = ("select ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username from ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." where ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid2)."'");
	} else {
		$sql = ("select concat('".mysqli_real_escape_string($GLOBALS['dbh'],$guestnamePrefix)."',name) username from cometchat_guests where cometchat_guests.id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid2)."'");
	}
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$usern2 = mysqli_fetch_assoc($query);

	$sql = ("(select m.*  from cometchat m where  (m.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid2)."') or (m.to = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and m.from = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid2)."'))
	order by id desc");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	if (function_exists('processName')) {
			$usern['username'] = processName($usern['username']);
			$usern2['username'] = processName($usern2['username']);
	}

	$userslist = '';

	while ($chat = mysqli_fetch_assoc($query)) {
		$time = $chat['sent'];

		if ($userid == $chat['from']) {
			$uname = $usern['username'];
		} else {
			$uname = $usern2['username'];
		}

		if(strpos($chat['message'], 'CC^CONTROL_') === false)
		$userslist .= '<li class="ui-state-default"><span>'.$uname.'&nbsp;:</span><span style="font-size:11px;margin-top:2px;margin-left:5px;width:auto;">&nbsp; '.$chat['message'].'</span><span style="font-size:11px;float:right;width:auto;overflow:hidden;margin-top:2px;margin-left:10px;"><span class="chat_time" timestamp="'.$time.'"></span></span><div style="clear:both"></div></li>';
	}

	$body = <<<EOD
	{$navigation}
        <link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
	<form action="?module=logs&action=newlogprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Log between {$usern['username']} and {$usern2['username']}</h2>
		<h3>To see other conversations of {$usern['username']}, <a href="?module=logs&amp;action=viewuser&amp;data={$userid}&amp;ts={$ts}">click here</a></h3>

		<div>
			<ul id="modules_logslong" class="cometchat_logs_view">
				{$userslist}
			</ul>
		</div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chat_logs").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function chatroomLog() {
	global $navigation;
	global $body;

	$sql = ("select * from cometchat_chatrooms order by lastactivity desc");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$chatroomlog = '';

	while ($chatroom = mysqli_fetch_assoc($query)) {

		$chatroomlog .= '<li class="ui-state-default" onclick="javascript:logs_gotochatroom(\''.$chatroom['id'].'\');"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$chatroom['name'].'</span><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">(ID:'.$chatroom['id'].')</span><div style="clear:both"></div></li>';
	}

        $errormessage = "";
        if(empty($chatroomlog)){
            $errormessage = '<div id="no_module" style="width: 480px;float: left;color: #333333;">No chatroom available.</div>';
        }

	$body = <<<EOD
	{$navigation}

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Chatrooms</h2>
		<h3>View chatrooms logs for active rooms.</h3>

		<div>
			<h5 style="font-size: 12px; margin-bottom: 5px;">Please select a chatroom:</h5>
			<ul id="modules_logs">
                                {$errormessage}
                                {$chatroomlog}
			</ul>
		</div>
		<div id="rightnav">
			<h1>Warning</h1>
			<ul id="modules_logtips">
				<li>This feature is resource intensive. Please use judiciously.</li>
			</ul>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chatroom_logs").addClass('active_setting');
		});
	</script>

EOD;

	template();
}


function viewuserchatroomconversation() {
	global $ts;
	global $body;
	global $navigation;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $guestsMode;
	global $guestnamePrefix;

	if(!empty($guestnamePrefix)){ $guestnamePrefix .= '-'; }

	if($guestsMode) {
		$usertable = "(select ".$usertable_userid.", ".$usertable_username."  from ".$usertable." union select id ".$usertable_userid.",concat('".$guestnamePrefix."',name) ".$usertable_username." from cometchat_guests)";
	}

	$chatroomid = mysqli_real_escape_string($GLOBALS['dbh'],$_GET['data']);

	$sql = ("select name chatroomname from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$chatroomn = mysqli_fetch_assoc($query);

	$sql = ("select cometchat_chatroommessages.*, f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_username)." username  from cometchat_chatroommessages join ".mysqli_real_escape_string($GLOBALS['dbh'],$usertable)." f on cometchat_chatroommessages.userid = f.".mysqli_real_escape_string($GLOBALS['dbh'],$usertable_userid)." where chatroomid = '".mysqli_real_escape_string($GLOBALS['dbh'],$chatroomid)."' order by id desc LIMIT 200");

	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$chatroomlog = '';

	while ($chat = mysqli_fetch_assoc($query)) {

		if (function_exists('processName')) {
			$chatroomn['chatroomname'] = processName($chatroomn['chatroomname']);
		}
		$time = $chat['sent'];
		if(strpos($chat['message'], 'CC^CONTROL_') === false)
		$chatroomlog .= '<li class="ui-state-default"><span style="font-size: 11px; float: left; margin-top: 2px; margin-left: 0px; width: auto; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; padding: 0px; text-align: center;">'.$chat["username"].': </span><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;width:auto;">&nbsp; '.$chat['message'].'</span><span style="font-size:11px;float:right;width:auto;overflow:hidden;margin-top:2px;margin-left:10px;"><span class="chat_time" timestamp="'.$time.'"></span></span><div style="clear:both"></div></li>';
	}


	$body = <<<EOD
	{$navigation}
        <link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
	<form action="?module=logs&action=newlogprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Log of  in {$chatroomn['chatroomname']} chatroom</h2>
		<h3>To see other conversations of  in other chatrooms, <a href="?module=logs&amp;action=chatroomlog&amp;ts={$ts}">click here</a></h3>

		<div>
			<ul id="modules_logslong" class="cometchat_logs_view">
				{$chatroomlog}
			</ul>
		</div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chatroom_logs").addClass('active_setting');
		});
	</script>

EOD;

	template();
}