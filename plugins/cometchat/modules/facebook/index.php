<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")){
	include (dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<title>{$facebook_language[100]}</title>
		<meta name="viewport" content="user-scalable=1,width=device-width, initial-scale=1.0" />
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<script src="../../js.php?type=core&name=jquery"></script>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=facebook" />
	</head>
	<body>
		<div id="fb-root"></div>
		<script>
			(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<div class="fb-page" data-href="{$pageUrl}" data-width="500px" data-height="460px" data-tabs="timeline" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"></div></div>
		<script>
			if(jqcc('.fb-page').outerWidth(false) < jqcc('body').outerWidth(false)){
				location.reload();
			}
		</script>
	</body>
</html>
EOD;
?>