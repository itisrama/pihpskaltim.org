<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
$option = $_GET['option'];
if (empty($_GET['process'])) {
	global $getstylesheet;
	if(isset($option) && $option=='Google'){
		$data ='<div class="title">Google API key:</div><div class="element"><input type="text" class="inputbox" name="googleKey" value="'.$googleKey.'"></div><div style="clear:both;padding:10px;"></div><div class="title">Google API Secret Key:</div><div class="element"><input type="text" class="inputbox" name="googleSecret" value="'.$googleSecret.'"></div>';
	}else if(isset($option) && $option=='Facebook'){
		$data ='<div class="title">Facebook API key:</div><div class="element"><input type="text" class="inputbox" name="facebookKey" value="'.$facebookKey.'"></div><div style="clear:both;padding:10px;"></div><div class="title">Facebook API Secret Key:</div><div class="element"><input type="text" class="inputbox" name="facebookSecret" value="'.$facebookSecret.'"></div>';
	}else if(isset($option) && $option=='Twitter'){
		$data ='<div class="title">Twitter API key:</div><div class="element"><input type="text" class="inputbox" name="twitterKey" value="'.$twitterKey.'"></div><div style="clear:both;padding:10px;"></div><div class="title">Twitter API Secret Key:</div><div class="element"><input type="text" class="inputbox" name="twitterSecret" value="'.$twitterSecret.'"></div>';
	}


echo <<<EOD
<!DOCTYPE html>

$getstylesheet
</style>
<script src="../js.php?type=core&name=jquery"></script>
<script>
  $ = jQuery = jqcc;
</script>
<script type="text/javascript" language="javascript">
    function resizeWindow() {
        window.resizeTo(650, ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
    }
</script>
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=function&name=login&option={$option}&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>Please enter your {$option} application details below.</h3>
		<div>
			<div id="centernav" style="width:380px">
				{$data}
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
	header("Location:?module=dashboard&action=loadexternal&type=function&name=login&option=".$auth_mode);
}