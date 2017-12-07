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

	$dy = '';
	$dn = '';

	$errorMsg = '';
	$innercontent = '"';
	$googleapi = $bingapi = '';

	if ($useGoogle == 1) {
		$dy = "checked";
	} else {
		$dn = "checked";
	}

	if(!checkcURL()) {
		$errorMsg = "<h2 id='errormsg' style='font-size: 11px; color: rgb(255, 0, 0);'>cURL extension is disabled on your server. Please contact your webhost to enable it. cURL is required for Translate Conversations.</h2>";
		$innercontent = ';display:none;"';
	}

echo <<<EOD
<!DOCTYPE html>

$getstylesheet
<form style="height:100%" action="?module=dashboard&action=loadexternal&type=module&name=realtimetranslate&process=true" method="post">
<div id="content" style="width:auto">
		<h2>Settings</h2>
		<h3>Please refer to our online <a href="https://support.cometchat.com/documentation/php/admin/modules/real-time-translate-2/" target="_blank">documentation</a> for information on how to setup this service.</h3>
		<div>
			{$errorMsg}
			<div id="centernav" style="width:380px {$innercontent}">
				<h3 id="noticemsg" style="padding: 7px;border: 1px solid #ccc;border-radius: 10px;"></h3>
				<div class="title">Use Google Translate API:</div><div class="element"><input type="radio" name="useGoogle" value="1" $dy>Yes <input type="radio" $dn name="useGoogle" value="0" >No</div>
				<div style="clear:both;padding:10px;"></div>
				<div id="bingapi" style="display:none;">
					<div class="title">Bing Client ID:</div>
					<div class="element">
						<input type="text" class="inputbox" name="bingClientID" value="{$bingClientID}">
					</div>
					<div style="clear:both;padding:10px;"></div>
					<div class="title">Bing Client Secret:</div><div class="element">
						<input type="text" class="inputbox" name="bingClientSecret" value="{$bingClientSecret}">
					</div>
				</div>
				<div id="googleapi" style="display:none;"><div class="title">Google Key:</div><div class="element"><input type="text" class="inputbox" name="googleKey" value="{$googleKey}"></div></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
		</div>
		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
</div>
</form>
<script type="text/javascript" src="../js.php?admin=1"></script>
            <script type="text/javascript" language="javascript">
				var googlenotice = 'For Google Translate API, please add the Google Key below.',
					bingnotice = 'Some translations may not appear correctly when using Bing Translate due to their API. Also, this option does not support translation on mobile app. We recommend using the Google Translate option.';
					var useGoogle = "{$useGoogle}";
					if(useGoogle == '1') {
						$('#googleapi').show();
						$('#noticemsg').text(googlenotice);
					}else {
						$('#bingapi').show();
						$('#noticemsg').text(bingnotice);
					}
				$('input[name=useGoogle]').change(function(){
					if($(this).val() == '1') {
						$('#bingapi').hide();
						$('#googleapi').show();
						$('#noticemsg').text(googlenotice);
					}else {
						$('#googleapi').hide();
						$('#bingapi').show();
						$('#noticemsg').text(bingnotice);
					}
				});
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
	header("Location:?module=dashboard&action=loadexternal&type=module&name=realtimetranslate");
}