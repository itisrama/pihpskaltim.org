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
</style>
<script src="../js.php?type=core&name=jquery"></script>
<script type="text/javascript" language="javascript">
	$ = jQuery = jqcc;
    function resizeWindow() {
        window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
    }
</script>
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=extension&name=jabber&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>If you would like to use your own GTalk application for GTalk Connect please fill in the values below. If not leave them blank.</h3>
		<div>
			<div id="centernav" style="width:380px">
				<div class="title">GTalk App ID:</div><div class="element"><input type="text" class="inputbox" name="gtalkAppId" value="$gtalkAppId"></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">GTalk App Secret Key:</div><div class="element"><input type="text" class="inputbox" name="gtalkSecretKey" value="$gtalkSecretKey"></div>
				<div style="clear:both;padding:10px;"></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
		</div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
</div>
</form>
<script type="text/javascript" language="javascript">
	$(function() {
		setTimeout(function(){
				resizeWindow();
			},200);
	});
</script>
EOD;
} else {
	configeditor($_POST);
	header("Location:?module=dashboard&action=loadexternal&type=extension&name=jabber");
}