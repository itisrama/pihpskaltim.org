<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");

$colorslist = '';
if ($handle = scandir(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'themes')) {
    foreach ($handle as $file) {
		if ($file != "." && $file != ".." && $file != "index.html" && $file != "synergy" && $file != "tapatalk" && $file != "mobile" && $file != $theme) {
            $listedcolor = $file;
            $colorname = ucfirst($listedcolor);
            $colorslist .=  <<<EOD
                    <a href="javascript:void(0);" onclick="javascript:changeTheme('{$listedcolor}')">{$colorname}</a><br/>
EOD;
        }
    }
}

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

$currenttheme = ucfirst($theme);

$themesoptions = '';
if(!empty($colorslist)) {
	$themesoptions = "<b>{$themechanger_language[1]}</b><br/><br/>{$colorslist}";
} else {
	$themesoptions = "<b>{$themechanger_language[2]}</b>";
}

$extrajs = "";
if ($sleekScroller == 1) {
	$extrajs = '<script src="../../js.php?type=core&name=scroll"></script>';
}
echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$themechanger_language[100]}</title>
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=themechanger" />
		<script src="../../js.php?type=core&name=jquery"></script>
		<script>
			$ = jQuery = jqcc;
		</script>
                 {$extrajs}
		<script>
                        $(function() {

				if (jQuery().slimScroll) {
					$('.cometchat_wrapper').slimScroll({height: '120px',allowPageScroll: false});
				}
			});
			function changeTheme(name) {
				set_cookie('theme',name);
				if(name == 'lite'){
					name = 'standard';
				}
				set_cookie('color',name);
				if (typeof(parent)!= 'undefined') {
					var controlparameters = {"type":"modules", "name":"themechanger", "method":"closeModule", "params":{}};
					controlparameters = JSON.stringify(controlparameters);
					parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				}else if(typeof(window.opener)!= 'undefined') {
					window.opener.location.reload();
					window.close();
				}
			}

			function set_cookie(name,value) {
				var today = new Date();
				today.setTime( today.getTime() );
				expires = 1000 * 60 * 60 * 24;
				var expires_date = new Date( today.getTime() + (expires) );
				document.cookie = "{$cookiePrefix}" + name + "=" +escape( value ) + ";path=/" + ";expires=" + expires_date.toGMTString();
			}

		</script>

	</head>
	<body>
		<div class="cometchat_wrapper">
			{$themechanger_language[0]} <b>$currenttheme</b><br/><br/>

			{$themesoptions}
		</div>
	</body>
</html>
EOD;
?>