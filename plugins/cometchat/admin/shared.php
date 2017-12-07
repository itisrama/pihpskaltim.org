<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

function themeslist() {
	$themes = array();

	if ($handle = opendir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file) && file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'cometchat.css')) {
				$themes[] = $file;
			}
		}
		closedir($handle);
	}


	return $themes;
}

function configeditor ($config) {
	global $dbh;
	global $client;
	$insertvalues = '';
	$key_type;
	foreach ($config as $name => $value) {
		if($name == strtoupper($name)){
			$key_type = 0;
		}else if(!is_array($value)){
			$key_type = 1;
		}else{
			$key_type = 2;
			$value = serialize($value);
		}
		$insertvalues .= ("('".mysqli_real_escape_string($dbh,$name)."', '".mysqli_real_escape_string($dbh,$value)."', {$key_type}),");
	}
	$insertvalues = rtrim($insertvalues,',');
	if(!empty($insertvalues)){
		$sql = ("replace into `cometchat_settings` (`setting_key`,`value`, `key_type`) values ".$insertvalues);
		$query = mysqli_query($dbh,$sql);
	}
	removeCachedSettings($client.'settings');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client);
	}
}

function languageeditor($lang){
	global $dbh;
	global $client;
	if(empty($lang['lang_key']) || empty($lang['name']) || empty($lang['code']) || empty($lang['type'])){
		return 0;
	}
	$sql = ("insert into `cometchat_languages` set `lang_key` = '".mysqli_real_escape_string($dbh,$lang['lang_key'])."', `lang_text` = '".mysqli_real_escape_string($dbh,$lang['lang_text'])."', `code` = '".mysqli_real_escape_string($dbh,$lang['code'])."', `type` = '".mysqli_real_escape_string($dbh,$lang['type'])."', `name` = '".mysqli_real_escape_string($dbh,$lang['name'])."' on duplicate key update `lang_key` = '".mysqli_real_escape_string($dbh,$lang['lang_key'])."', `lang_text` = '".mysqli_real_escape_string($dbh,$lang['lang_text'])."', `code` = '".mysqli_real_escape_string($dbh,$lang['code'])."', `type` = '".mysqli_real_escape_string($dbh,$lang['type'])."', `name` = '".mysqli_real_escape_string($dbh,$lang['name'])."'");
	$query = mysqli_query($dbh,$sql);
	removeCachedSettings($client.'cometchat_language');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client);
	}
}

function coloreditor($data,$color_name){
	global $dbh;
	global $client;
	$insertvalues = '';
	foreach ($data as $name => $value) {
		$insertvalues .= ("('".mysqli_real_escape_string($dbh,$name)."', '".mysqli_real_escape_string($dbh,$value)."', '".mysqli_real_escape_string($dbh,$color_name)."'),");
	}
	$insertvalues = rtrim($insertvalues,',');
	if(!empty($insertvalues)){
		$sql = ("replace into `cometchat_colors` (`color_key`,`color_value`, `color`) values ".$insertvalues);
		$query = mysqli_query($dbh,$sql);
	}
	removeCachedSettings($client.'cometchat_color');
	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client)){
		clearcache(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$client);
	}
}

function createslug($title,$rand = false) {
	$slug = preg_replace("/[^a-zA-Z0-9]/", "", $title);
	if ($rand) { $slug .= rand(0,9999); }
	return strtolower($slug);
}

function extension($filename) {
	return pathinfo($filename, PATHINFO_EXTENSION);
}

function deletedirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!deleteDirectory($dir . "/" . $item)) return false;
            };
        }
    return rmdir($dir);
}

function pushMobileAnnouncement($zero,$sent,$message,$isAnnouncement = '0',$insertedid){
	global $userid;
	global $firebaseauthserverkey;

	if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."FireBasePushNotification.php")){
		include_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."FireBasePushNotification.php");

		$announcementpushchannel = '';

		if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."config.php")){
			include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."announcements".DIRECTORY_SEPARATOR."config.php");
		}

		if(!empty($isAnnouncement)){
			$rawMessage = array("m" => $message, "sent" => $sent, "id" => $insertedid);
		}
		$pushnotifier = new FireBasePushNotification($firebaseauthserverkey);
		$pushnotifier->sendNotification($announcementpushchannel, $rawMessage, 0, 1);
	}

}

$getstylesheet = <<<EOD
<body><title>CometChat</title></body>
<style>
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-weight: inherit;
	font-style: inherit;
	font-size: 100%;
	font-family: inherit;
	vertical-align: baseline;
}
body {font-size: 10px; font-family: arial, san-serif;}
html {
	 overflow-y: scroll;
	 overflow-x: hidden;
}
#content {
	-moz-border-radius-bottomleft:5px;
	-moz-border-radius-bottomright:5px;
	-moz-border-radius-topleft:5px;
	-moz-border-radius-topright:5px;
	background-color:#EEEEEE;
	width:350px;
	padding:10px;
	margin:0;
}
form{
	padding: 20px !important;
}
h1{
	color:#333333;
	font-size:110%;
	padding-left:10px;
	padding-bottom:10px;
	padding-top:5px;
	font-weight:bold;
	border-bottom:1px solid #ccc;
	margin-bottom:10px;
	margin-left:10px;
	margin-right:10px;
	text-transform: uppercase;
}
h2 {
	color:#333333;
	font-size:160%;
	font-weight:bold;
}

h3 {
	color:#333333;
	font-size:110%;
	border-bottom:1px solid #ccc;
	padding-bottom:10px;
	margin-bottom:17px;
	padding-top:4px;
}
.button {
	border:1px solid #76b6d2;
	padding:4px;
	background:#76b6d2;
	color:#fff;
	font-weight:bold;
	font-size:10px;
	font-family:arial;
	text-transform:uppercase;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	padding-left:10px;
	padding-right:10px;
}
.inputbox {
	border:1px solid #ccc;
	padding:4px;
	background:#fff;
	color:#333;
	font-weight:bold;
	font-size:10px;
	font-family:arial;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	padding-left:10px;
	padding-right:10px;
	width: 200px;
}
.title {
	padding-top: 4px;
	text-align: right;
	padding-right:10px;
	font-size: 12px;
	width: 100px;
	float:left;
	color: #333;
}

.long {
	width: 200px;
}
.short {
	width: 100px;
}
.toppad {
	margin-top:7px;
}
.element {
	float:left;
}
a {
	color: #0f5d7e;
}

form #centernav {
	width: 475px !important;
}

form #content {
	margin: 0 !important;
}
form #centernav .titlelong {
	width: 210px !important;
}
form #centernav .title {
	width: 210px !important;
}
</style>
<link href="../css.php?admin=1" media="all" rel="stylesheet" type="text/css" />
EOD;
?>