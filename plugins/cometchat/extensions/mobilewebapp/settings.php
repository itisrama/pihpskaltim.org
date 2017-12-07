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

	$curl = 0;
	$errorMsg = '';

echo <<<EOD
<!DOCTYPE html>

<html>
<head>
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
</head>

<body>
	<form style="height:100%;" action="?module=dashboard&action=loadexternal&type=extension&name=mobilewebapp&process=true" method="post">
	<div id="content" style="width:auto;">
			<h2>Settings</h2><br />
			
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
</body>
</html>
EOD;
} else {
	configeditor($_POST);
	header("Location:?module=dashboard&action=loadexternal&type=extension&name=mobilewebapp");
}