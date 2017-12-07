<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

if (empty($_GET['process'])) {
	global $getstylesheet;
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

if ($allowUsers == 1) {
	$allowUsersYes = 'checked="checked"';
	$allowUsersNo = '';
} else {
	$allowUsersNo = 'checked="checked"';
	$allowUsersYes = '';
}

if ($allowGuests == 1) {
	$allowGuestsYes = 'checked="checked"';
	$allowGuestsNo = '';
} else {
	$allowGuestsNo = 'checked="checked"';
	$allowGuestsYes = '';
}

if ($allowDelete == 1) {
	$allowDeleteYes = 'checked="checked"';
	$allowDeleteNo = '';
} else {
	$allowDeleteNo = 'checked="checked"';
	$allowDeleteYes = '';
}

if ($hideEnterExit == 1) {
	$hideEnterExitYes = 'checked="checked"';
	$hideEnterExitNo = '';
} else {
	$hideEnterExitNo = 'checked="checked"';
	$hideEnterExitYes = '';
}

if ($showChatroomUsers == 1) {
	$showChatroomUsersYes = 'checked="checked"';
	$showChatroomUsersNo = '';
} else {
	$showChatroomUsersNo = 'checked="checked"';
	$showChatroomUsersYes = '';
}

if ($messageBeep == 1) {
	$messageBeepYes = 'checked="checked"';
	$messageBeepNo = '';
} else {
	$messageBeepNo = 'checked="checked"';
	$messageBeepYes = '';
}

if ($allowAvatar == 1) {
	$allowAvatarYes = 'checked="checked"';
	$allowAvatarNo = '';
} else {
	$allowAvatarNo = 'checked="checked"';
	$allowAvatarYes = '';
}

if ($crguestsMode == 1) {
	$crguestsModeYes = 'checked="checked"';
	$crguestsModeNo = '';
} else {
	$crguestsModeNo = 'checked="checked"';
	$crguestsModeYes = '';
}

if ($newMessageIndicator == 1) {
	$newMessageYes = 'checked="checked"';
	$newMessageNo = '';
} else {
	$newMessageNo = 'checked="checked"';
	$newMessageYes = '';
}
$pcb = '';
if(defined('DISPLAY_ALL_USERS') && DISPLAY_ALL_USERS == '0') {
	if($showchatbutton == 1) {
		$showchatbuttonYes = 'checked="checked"';
		$showchatbuttonNo = '';
	}else {
		$showchatbuttonNo = 'checked="checked"';
		$showchatbuttonYes = '';
	}
	$pcb = '<div class="title long">Show private chat for friends only</div><div class="element"><input type="radio" name="showchatbutton" value="1" '.$showchatbuttonYes.'>Yes <input type="radio" name="showchatbutton" value="0"'.$showchatbuttonNo.'>No</div><div style="clear:both;padding:10px;"></div>';
}

echo <<<EOD
<!DOCTYPE html>
$getstylesheet
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=module&name=chatrooms&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>If you are unsure about any value, please skip them</h3>
		<div>
			<div id="centernav" style="width:380px">
				<div class="title long">The number of seconds after which a user created chatroom will be removed if no activity</div><div class="element toppad"><input type="text" class="inputbox short" name="chatroomTimeout" value="$chatroomTimeout"></div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Number of messages that are fetched when load earlier messages is clicked</div><div class="element toppad"><input type="text" class="inputbox short" name="lastMessages" value="$lastMessages"></div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">If yes, users can create chatrooms</div><div class="element"><input name="allowUsers" value="1" $allowUsersYes type="radio">Yes <input name="allowUsers" $allowUsersNo value="0" type="radio">No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">If yes, guests can create chatrooms</div><div class="element"><input name="allowGuests" value="1" $allowGuestsYes type="radio">Yes <input name="allowGuests" $allowGuestsNo value="0" type="radio">No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">If yes, users can delete his own message in chatroom</div><div class="element"><input name="allowDelete" value="1" $allowDeleteYes type="radio">Yes <input name="allowDelete" $allowDeleteNo value="0" type="radio">No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">If yes, user avatars will be displayed in chatrooms</div><div class="element toppad"><input name="allowAvatar" $allowAvatarYes value="1"   type="radio">Yes <input name="allowAvatar" value="0" type="radio" $allowAvatarNo>No</div>

				<div class="title long">If yes, guests can access chatrooms (Guest chat needs to be enabled)</div><div class="element toppad"><input name="crguestsMode" $crguestsModeYes value="1"   type="radio">Yes <input name="crguestsMode" value="0" type="radio" $crguestsModeNo>No</div>

				<div class="title long">If yes, enter/exit messages of users will not be shown</div><div class="element toppad"><input name="hideEnterExit" $hideEnterExitYes value="1"   type="radio">Yes <input name="hideEnterExit" value="0" type="radio" $hideEnterExitNo>No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">If yes, show total number of online users in chatrooms</div><div class="element toppad"><input name="showChatroomUsers" $showChatroomUsersYes value="1"   type="radio">Yes <input name="showChatroomUsers" value="0" type="radio" $showChatroomUsersNo>No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Minimum poll-time in milliseconds (1 second = 1000 milliseconds)</div><div class="element toppad"><input type="text" class="inputbox short" name="minHeartbeat" value="$minHeartbeat"></div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Maximum poll-time in milliseconds</div><div class="element"><input type="text" class="inputbox short" name="maxHeartbeat" value="$maxHeartbeat"></div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Auto enter chatroom ID</div><div class="element"><input type="text" class="inputbox short" name="autoLogin" value="$autoLogin"></div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Beep on new messages</div><div class="element toppad"><input name="messageBeep" $messageBeepYes value="1"   type="radio">Yes <input name="messageBeep" value="0" type="radio" $messageBeepNo>No</div>
				<div style="clear:both;padding:10px;"></div>

				<div class="title long">Show indicator on new messages</div><div class="element"><input name="newMessageIndicator" $newMessageYes value="1"   type="radio">Yes <input name="newMessageIndicator" value="0" type="radio" $newMessageNo>No</div>
				<div style="clear:both;padding:10px;"></div>
				{$pcb}
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
</div>
</form>
 <script type="text/javascript" src="../js.php?admin=1"></script>
            <script type="text/javascript" language="javascript">
                $(function() {
					setTimeout(function(){
							resizeWindow();
						},200);
				});
				function resizeWindow() {
                    window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
                }
            </script>
EOD;
} else {
	configeditor($_POST);
	header("Location:?module=dashboard&action=loadexternal&type=module&name=chatrooms");
}