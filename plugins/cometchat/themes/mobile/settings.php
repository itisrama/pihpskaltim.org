<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

if (empty($_GET['process'])) {
	global $getstylesheet;
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
	$alchkd = '';
	$zchkd = '';
	$ochkd = '';
if ($confirmOnAllMessages == 2) {
    $confirmOnAllMessagesYes = '';
    $confirmOnAllMessagesNo = '';
	$ochkd = "selected";
    $confirmNever = 'checked="checked"';
}else if ($confirmOnAllMessages == 1) {
    $confirmOnAllMessagesYes = 'checked="checked"';
    $confirmOnAllMessagesNo = '';
	$zchkd = "selected";
    $confirmNever = '';
} else {
    $confirmOnAllMessagesNo = 'checked="checked"';
	$alchkd = "selected";
    $confirmOnAllMessagesYes = '';
    $confirmNever = '';
}
if($enableMobileTab == 1){
    $enableMobileTabYes = 'checked="checked"';
    $enableMobileTabNo = '';
}else{
    $enableMobileTabNo = 'checked="checked"';
    $enableMobileTabYes = '';
}

echo <<<EOD
<!DOCTYPE html>
{$getstylesheet}
	<form style="height:100%" action="?module=dashboard&action=loadthemetype&type=theme&name=mobile&process=true" method="post">
		<div id="content" style="width:auto">
			<h2>Settings</h2>
			<div>
				<h3>You can enable/disable Mobile theme. Allow notification for all the messages</h3>
				<div id="centernav" style="width:380px">
					<div class="title long">Enable Mobile theme : </div>
					<div class="element topped">
						<input type="radio" $enableMobileTabYes value="1" name="enableMobileTab">Yes <input type="radio" $enableMobileTabNo value="0" name="enableMobileTab">No
					</div>
					<div style="clear:both;padding:10px;"></div>
					<div class="title long">New messages notification : </div>
					<div class="element topped">
						<select name="confirmOnAllMessages" id="pluginTypeSelector">
							<option  value="1" $zchkd>Always </option>
							<option  value="0" $alchkd>Once</option>
							<option  value="2" $ochkd>Never</option>
						</select>
						<div style="clear:both;padding:10px;"></div>
					</div>
				</div>
			<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a></div>
	</form>
	<script type="text/javascript" src="../js.php?admin=1"></script>
	<script type="text/javascript" language="javascript">
		setTimeout(function(){
            resizeWindow();
        },200);
		function resizeWindow() {
			window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
		}
	</script>
EOD;
} else {
	configeditor(array('mobile_settings' => $_POST));
	header("Location:?module=dashboard&action=loadthemetype&type=theme&name=mobile");
}