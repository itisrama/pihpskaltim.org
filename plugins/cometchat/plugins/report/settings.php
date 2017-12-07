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

echo <<<EOD
<!DOCTYPE html>

$getstylesheet
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=plugin&name=report&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>Please enter the e-mail where you would like to send the incident reports</h3>
		<div>
			<div id="centernav" style="width:380px">
				<div class="title">E-mail:</div><div class="element"><input type="text" class="inputbox" name="reportEmail" value="$reportEmail"></div>
				<div style="clear:both;padding:10px;"></div>
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
	header("Location:?module=dashboard&action=loadexternal&type=plugin&name=report");
}