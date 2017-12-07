<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'translate.php');

if (!checkcURL(1)) {
	echo "<div style='background:white;height: 100%;'>Please ask your webhost to install libcurl for PHP and configure it for HTTPs as well</div>"; exit;
}

if (empty($bingClientID) && empty($googleKey)) {
	echo "<div style='background:white;'>Please configure this module using CometChat Administration Panel.</div>"; exit;
}

$translatingtext = '';

if (!empty($_COOKIE[$cookiePrefix.'lang'])) {
	$translatingtext = '<div class="current">'.$realtimetranslate_language[1].strtoupper($_COOKIE[$cookiePrefix.'lang']).' | <a href="javascript:void(0);" onclick="javascript:stoptranslating()">'.$realtimetranslate_language[2].'</a></div>';
}

$languagescode = '';
$languages = translate_languages();

foreach ($languages as $code => $name) {
	if ($useGoogle == 0) {
        if($code == 'zh-CHS') {$name = 'Chinese (Simpl)';}elseif($code == 'zh-CHT') {$name = 'Chinese (Trad)';}
    }
	$languagescode .= '<li id="'.$code.'">'.$name.'</li>';
}

$extrajs = "";
if ($sleekScroller == 1) {
	$extrajs = '<script src="../../js.php?type=core&name=scroll"></script>';
}

echo <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>{$realtimetranslate_language[100]}</title>
<meta name="viewport" content="user-scalable=1,width=device-width, initial-scale=1.0" />
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="-1">
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=realtimetranslate" />
<script src="../../js.php?type=core&name=jquery"></script>
<script>
	$ = jQuery = jqcc;
</script>
{$extrajs}

<script>

$(function() {

	if (jQuery().slimScroll) {
		$('.cometchat_wrapper').slimScroll({height: '310px',allowPageScroll: false});
		$(".cometchat_wrapper").css("height","290px");
	}

	$("li").click(function() {
		$('.current').hide();
		var info = $(this).attr('id');

		document.cookie = '{$cookiePrefix}lang='+info+';path=/';

		$('.languages').hide();
		$('.translating').show();
		setTimeout(function() {
		try {
			if (parent.jqcc.cometchat.ping() == 1) {
				parent.jqcc.cometchat.closeModule('realtimetranslate');
			}
		} catch (e) { }

		$('.languages').show();
		$('.translating').hide();

		window.location.reload();

		},3000);
	});
});

function stoptranslating() {
	document.cookie = '{$cookiePrefix}lang=;path=/';
	$('.current').hide();
}

</script>

</head>
<body style="margin: 0px;">
<div style="width:100%;margin:0 auto;margin-top: 0px;height: 100%;overflow-y: auto;">

<div class="cometchat_wrapper">
{$translatingtext}
<div style="clear:both"></div>
<ul class="languages">
{$languagescode}
</ul>

<div class="translating">{$realtimetranslate_language[0]}</div>

<div style="clear:both"></div>
</div>
</div>
</body>
</html>
EOD;
?>