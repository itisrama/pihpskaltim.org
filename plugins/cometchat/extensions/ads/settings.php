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
<script src="../js.php?type=core&name=jquery"></script>
<script>
	$ = jQuery = jqcc;
</script>
<script type="text/javascript" language="javascript">
    function resizeWindow() {
        window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
    }
</script>
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=extension&name=ads&process=true" method="post" onSubmit="">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>Please enter your advertisement HTML code. Your advertisement can have a maximum width of 218px.</h3>
		<div>
			<div id="centernav" style="width:380px">
				<div class="title">Ad code:</div><div class="element"><textarea class="inputbox" name="adCode" id="adCode" rows=6>$adCode</textarea></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Ad Height:</div><div class="element"><input type="text" class="inputbox" name="adHeight" value="$adHeight"></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
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
	header("Location:?module=dashboard&action=loadexternal&type=extension&name=ads");
}