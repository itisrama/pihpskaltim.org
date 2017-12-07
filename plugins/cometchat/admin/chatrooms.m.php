<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'chatrooms'.DIRECTORY_SEPARATOR.'config.php');

$navigation = <<<EOD
	<div id="leftnav">
	<a href="?module=chatrooms&amp;ts={$ts}" id="chatrooms_settings">Chatrooms</a>
	<a href="?module=chatrooms&amp;action=newchatroom&amp;ts={$ts}" id="add_chatroom">Add new chatroom</a>
	<a href="?module=chatrooms&amp;action=moderator&amp;ts={$ts}" id="manage_moderators">Manage Moderators</a>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;
	global $chatroomTimeout;

	$sql = ("select * from cometchat_chatrooms order by lastactivity desc");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$chatroomlist = '';

	while ($chatroom = mysqli_fetch_assoc($query)) {

		$type = '';

		if ($chatroom['type'] == '1') {
			$type = ' (password protected)';
		} else if ($chatroom['type'] == '2') {
			$type = ' (invitation only)';
		} else if ($chatroom['type'] == '3'){
			$type = ' (private chatroom)';
		}

		$typeuser = '';


		if ($chatroom['createdby'] != 0) {
			$typeuser = ' (user created)';
		}

		$time = $chatroom['lastactivity'];

		$css = '';
		$extra = '';

		if(getTimeStamp()-$chatroom['lastactivity'] > $chatroomTimeout * 100 ) {
			$css = 'background-color:#FFE2E2';
		} else if(getTimeStamp()-$chatroom['lastactivity'] > $chatroomTimeout && getTimeStamp()-$chatroom['lastactivity'] < $chatroomTimeout * 100 ) {
			$css = 'background-color:rgba(245, 255, 0, 0.09)';
		}
		if (empty($type) || $type == ' (private chatroom)' ) {
			$extra = '<a href="../modules/chatrooms/index.php?id='.$chatroom['id'].'" target="_blank" style="margin-right:5px;"><img src="images/link.png" title="Direct link to chatroom"></a><a onclick="javascript:embed_link(\''.BASE_URL.'modules/chatrooms/index.php?id='.$chatroom['id'].'\',\'500\',\'300\');" href="#" style="margin-right:5px;"><img src="images/embed.png" title="Embed code for chatroom"></a>';
		}

		$chatroomlist .= '<li class="ui-state-default" style="'.$css.'"><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;max-width: 400px;text-overflow: ellipsis;overflow: hidden;">'.$chatroom['name'].' (ID: '. $chatroom['id'].')'.$type.$typeuser.'(<span class="chat_time" timestamp="'.$time.'"></span>)</span><p style="float:right"></p><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$extra.'<a href="?module=chatrooms&amp;action=deletechatroom&amp;data='.$chatroom['id'].'&amp;ts={$ts}"><img src="images/remove.png" title="Delete Chatroom"></a></span><div style="clear:both"></div></li>';
	}
	$errormessage = '';
	if(!$chatroomlist){
		$errormessage = '<div style="width: 480px; float: left; color: rgb(51, 51, 51);" id="no_plugin" class="">You do not have any chatrooms at the moment. To create a new chatrooms, click on \'Add new chatrooms\' from the menu.</div>';
	}
	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Chatrooms</h2>
		<h3>Displaying user created and permanent chatrooms</h3>

		<div>
			<ul id="modules_chatrooms">
        {$errormessage}
        {$chatroomlist}
			</ul>
			<div id="rightnav" style="margin-top:5px">
				<h1>Tips</h1>
				<ul id="modules_chatroomtips">
					<li>When you add a chatroom, it will be displayed live to all online chatroom users.</li>
				</ul>
				<ul id="modules_chatroomtips">
					<li><b>White</b> background rooms are active. <b>Yellow</b> background rooms are idle and not visible. <b>Red</b> background rooms are inactive.</li>
				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>
	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#chatrooms_settings").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function deletechatroom() {
	global $ts;
	if (!empty($_GET['data'])) {
		$sql = ("delete from cometchat_chatrooms where id = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($_GET['data']))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
	}

	header("Location:?module=chatrooms&ts={$ts}");
}

function newchatroom() {
	global $body;
	global $navigation;
	global $ts;

	$body = <<<EOD

	$navigation
	<form action="?module=chatrooms&action=newchatroomprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>New chatroom</h2>
		<h3>You can add permanent chatrooms using the following form</h3>

		<div>
			<div id="centernav">
				<div class="title">Chatroom:</div><div class="element"><input type="text" class="inputbox" name="chatroom" id="chatroom" required="true"/></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Type:</div><div class="element"><select id="chatroomtype" class="inputbox" name="type"><option value="0">Public room<option  value="1">Password protected room<option value="3">Private room</select></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title chatroomtitle" style="display:none">If password protected, enter password:</div><div class="element chatroomelement" style="display:none"><input id="ppassword" type="text" class="inputbox chatroompassword" name="ppassword" /></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Your chatrooms will be shown live to all online users. Double check before proceeding.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add Chatroom" class="button">&nbsp;&nbsp;or <a href="?module=chatrooms&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>
        <script type="text/javascript">
            $("#chatroomtype").change(function() {
                var selected = $("#chatroomtype :selected").val();
                    if(selected=="1") {
                        $('.chatroomtitle').show();
                        $('.chatroomelement').show();
                        $('.chatroompassword').attr('required',true);
                    } else {
                        $('.chatroomtitle').hide();
                        $('.chatroomelement').hide();
                        $('.chatroompassword').attr('required',false);
                    }
            });

	</script>
	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#add_chatroom").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function newchatroomprocess() {
	global $ts;
	$chatroom = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['chatroom']);
	$type = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['type']);
	$password = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['ppassword']);

	if (!empty($password) && ($type == 1 || $type == 2)) {
		$passwordhash = sha1($password);
	} else {
		$passwordhash = '';
	}
	$chatroom = trim($chatroom);
	if(!empty($chatroom) && (strlen(trim($password)) !== 0 || empty($passwordhash))) {
		$sql = ("insert into cometchat_chatrooms (name,createdby,lastactivity,password,type) values ('".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($chatroom))."', '0','".mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp())."','".mysqli_real_escape_string($GLOBALS['dbh'],$passwordhash)."','".mysqli_real_escape_string($GLOBALS['dbh'],$type)."')");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		header( "Location: ?module=chatrooms&ts={$ts}" );
	}
	elseif(empty($chatroom)){
		$_SESSION['cometchat']['error'] = 'Chatroom name cannot be blank.';
		header( "Location: ?module=chatrooms&action=newchatroom&ts={$ts}" );
	}else{
		$_SESSION['cometchat']['error'] = 'Password cannot start with space.';
		header( "Location: ?module=chatrooms&action=newchatroom&ts={$ts}" );
	}
}

function moderator() {
	global $ts;
	global $body;
	global $navigation;
	global $moderatorUserIDs;

	$moderatorids = '';

	foreach ($moderatorUserIDs as $b) {
		$moderatorids .= $b.',';
	}
	$moderatorids= substr($moderatorids,0,-1);
	$alert = 'Please enter valid IDs seperated by a comma. For Example: 5,234,1075 \n*Note: It should not end with a comma. It must be integer value.';
	$body = <<<EOD
	$navigation
		<form action="?module=chatrooms&action=moderatorprocess&ts={$ts}" method="post" onsubmit="return validateID()" name="modForm">
		<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
			<h2>Manage Moderators</h2>
			<h3>Moderators can kick/ban users from any chatroom. Please enter their user IDs.</h3>
			<div>
				<div id="centernav">
					<div class="title">Moderator IDs:</div><div class="element"><input type="text" class="inputbox" name="moderatorUserIDs" value="$moderatorids"> <a href="?module=chatrooms&amp;action=finduser&amp;ts={$ts}">Don`t know ID?</a></div>
					<div style="float:left;margin-top: 81px;"><input type="submit" value="Modify" class="button">&nbsp;&nbsp;or <a href="?module=chatrooms&amp;action=moderator&amp;ts={$ts}">cancel</a></div>
				</div>
				<div id="rightnav" style="margin-top:5px">
					<h1>Tips</h1>
					<ul id="modules_chatroomtips">
					<li>Please use comma to separate IDs.</li>
					<li>Moderators can kick/ban any user from any chatroom.</li>
					</ul>
				</div>
			</div>
			<div style="clear:both;padding:7.5px;"></div>
		</div>
	<div style="clear:both"></div>
	<script>
	function validateID(){
		var moderatorid = document.forms["modForm"]["moderatorUserIDs"].value;
		var valid = /^(\d+(,\d+)*)$/.test(moderatorid.trim());
		if(moderatorid == ''){
			valid = true;
		}
		if(!valid){
			alert("{$alert}");
			return false;
		}
		return true;
	}
	</script>
	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#manage_moderators").addClass('active_setting');
		});
	</script>
EOD;
	template();

}

function moderatorprocess() {
	global $ts;
	$_SESSION['cometchat']['error'] = 'Moderator list successfully modified.';
	if(!empty($_POST['moderatorUserIDs'])){
		$moderators = explode(',', $_POST['moderatorUserIDs']);
	}else{
		$moderators = array();
	}
	configeditor(array('moderatorUserIDs' => $moderators));
	removeCache('chatroom_list');
	header("Location:?module=chatrooms&action=moderator&ts={$ts}");
}

function finduser() {
	global $body;
	global $navigation;
    global $ts;

	$body = <<<EOD
	$navigation
	<script>
		function ccAutoComplete(inputbox){
			var ajax;
			var moderator = '';
			var userslist = '';
			$('#centernav #suggestions').html('');
			if(inputbox.value.trim().length > 2){
				if(ajax && ajax.readystate != 4){
		            ajax.abort();
		        }
		        ajax = $.ajax({
					type: "POST",
					url: "?module=chatrooms&action=ccautocomplete&ts={$ts}",
					data: {suggest: inputbox.value},
					success: function(data) {
						data.forEach(function(entry) {
							moderator = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=chatrooms&amp;action=makemoderatorprocess&amp;susername='+entry.username+'&amp;moderatorid='+entry.id+'&amp;ts={$ts}&amp;autosuggest=1"><img style="width: 16px;" title="Make Moderator" src="images/add_moderator.png"></a>';
			                if(entry.isModerator == '1') {
			                moderator = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=chatrooms&amp;action=removemoderatorprocess&amp;susername='+entry.username+'&amp;moderatorid='+entry.id+'&amp;ts={$ts}&amp;autosuggest=1"><img style="width: 16px;" title="Remove Moderator" src="images/remove_moderator.png"></a>';
			                }
			                userslist += '<li class="suggestion_list"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'+entry.username+' - '+entry.id+'</span>'+moderator+'<div style="clear:both"></div></li>';
						});
						$('#centernav #suggestions').append(userslist);
		            },
					dataType: 'json'
				});
			}
		}
		$(function(){
			$(this).click(function(){
				$('#centernav #suggestions').html('');
			});
		});
	</script>
	<form action="?module=chatrooms&action=searchlogs&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Find User ID</h2>
		<h3>You can search by username.</h3>

		<div>
			<div id="centernav">
				<div class="title">Username:</div><div class="element"><input onkeyup="ccAutoComplete(this)" type="text" class="inputbox" name="susername" required="true" autocomplete="off"/><div id="suggestions"></div></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
		</div>
		<div id="rightnav" style="margin-top:5px">
			<h1>Tips</h1>
			<ul id="modules_chatroomtips">
			<li>For auto-suggest, please enter atleast first 3 letters of the name.</li>
			</ul>
		</div>
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Search Entire Database" class="button">&nbsp;&nbsp;or <a href="?module=chatrooms&amp;action=moderator&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function ccautocomplete(){
	global $ts;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $navigation;
	global $body;
	global $moderatorUserIDs;
	$suggestions = array();

	if(!empty($_REQUEST['suggest'])){
		$username = $_REQUEST['suggest'];

		$sql = ("select $usertable_userid id, $usertable_username username from $usertable where $usertable_username LIKE '%".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($username))."%' limit 10");
		$query = mysqli_query($GLOBALS['dbh'],$sql);

		while ($user = mysqli_fetch_assoc($query)) {
			if(in_array($user['id'],$moderatorUserIDs)) {
				$user['isModerator'] = '1';
			}else{
				$user['isModerator'] = '0';
			}
			array_push($suggestions,$user);
		}
	}
	echo json_encode($suggestions);
}

function searchlogs() {
    global $ts;
	global $usertable_userid;
	global $usertable_username;
	global $usertable;
	global $navigation;
	global $body;
    global $moderatorUserIDs;

	$username = $_REQUEST['susername'];

	if (empty($username)) {
		// Base 64 Encoded
		$username = 'Q293YXJkaWNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgc2FmZT8NCkV4cGVkaWVuY3kgYXNrcyB0aGUgcXVlc3Rpb24gLSBpcyBpdCBwb2xpdGljPw0KVmFuaXR5IGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcG9wdWxhcj8NCkJ1dCBjb25zY2llbmNlIGFza3MgdGhlIHF1ZXN0aW9uIC0gaXMgaXQgcmlnaHQ/DQpBbmQgdGhlcmUgY29tZXMgYSB0aW1lIHdoZW4gb25lIG11c3QgdGFrZSBhIHBvc2l0aW9uDQp0aGF0IGlzIG5laXRoZXIgc2FmZSwgbm9yIHBvbGl0aWMsIG5vciBwb3B1bGFyOw0KYnV0IG9uZSBtdXN0IHRha2UgaXQgYmVjYXVzZSBpdCBpcyByaWdodC4=';
	}

	$sql = ("select $usertable_userid id, $usertable_username username from $usertable where $usertable_username LIKE '%".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($username))."%'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);

	$userslist = '';

	while ($user = mysqli_fetch_assoc($query)) {
		if (function_exists('processName')) {
			$user['username'] = processName($user['username']);
		}
                $moderator = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=chatrooms&amp;action=makemoderatorprocess&amp;susername='.$username.'&amp;moderatorid='.$user['id'].'&amp;ts='.$ts.'"><img style="width: 16px;" title="Make Moderator" src="images/add_moderator.png"></a>';
                if(in_array($user['id'],$moderatorUserIDs)) {
                    $moderator = '<a style="font-size: 11px; margin-top: 2px; margin-left: 5px; float: right; font-weight: bold; color: #0F5D7E;" href="?module=chatrooms&amp;action=removemoderatorprocess&amp;susername='.$username.'&amp;moderatorid='.$user['id'].'&amp;ts='.$ts.'"><img style="width: 16px;" title="Remove Moderator" src="images/remove_moderator.png"></a>';
                }
		$userslist .= '<li class="ui-state-default cursor_default"><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;">'.$user['username'].' - '.$user['id'].'</span>'.$moderator.'<div style="clear:both"></div></li>';
	}

	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Search results</h2>
		<h3>Please find the user id next to each username. <a href="?module=chatrooms&amp;action=finduser&amp;ts={$ts}">Click here to search again</a></h3>

		<div>
			<ul id="modules_logs">
				$userslist
			</ul>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

EOD;

	template();
}

function makemoderatorprocess() {
	global $ts;
	global $moderatorUserIDs;
	$_SESSION['cometchat']['error'] = 'Moderator list successfully modified.';

	if(isset($_GET['moderatorid'])){
		array_push($moderatorUserIDs, $_GET['moderatorid']);
	}
	configeditor(array('moderatorUserIDs' => $moderatorUserIDs));
	if(isset($_GET['autosuggest'])){
		header("Location:?module=chatrooms&action=finduser&ts={$ts}");
		exit;
	}
	header("Location:?module=chatrooms&action=searchlogs&susername={$_GET['susername']}&ts={$ts}");
}

function removemoderatorprocess() {
	global $ts;
	global $moderatorUserIDs;

	$_SESSION['cometchat']['error'] = 'Moderator list successfully modified.';
	if(($key = array_search($_GET['moderatorid'], $moderatorUserIDs)) !== false) {
		unset($moderatorUserIDs[$key]);
	}
	configeditor(array('moderatorUserIDs' => $moderatorUserIDs));
	if(isset($_GET['autosuggest'])){
		header("Location:?module=chatrooms&action=finduser&ts={$ts}");
		exit;
	}
	header("Location:?module=chatrooms&action=searchlogs&susername={$_GET['susername']}&ts={$ts}");
}