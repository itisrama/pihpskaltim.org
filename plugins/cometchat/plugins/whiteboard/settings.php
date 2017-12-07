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
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=plugin&name=whiteboard&process=true" method="post">
<div id="content" style="width:auto">
		<h2>White Board Settings</h2><br/>

		<div style="clear:both;padding:10px;"></div>

		<div class="title">Width:</div><div class="element"><input type="text" class="inputbox" name="whitebWidth" value="$whitebWidth"></div>
		<div style="clear:both;padding:10px;"></div>
		<div class="title">Height:</div><div class="element"><input type="text" class="inputbox" name="whitebHeight" value="$whitebHeight"></div>
		<div style="clear:both;padding:10px;"></div>

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
	header("Location:?module=dashboard&action=loadexternal&type=plugin&name=whiteboard");
}