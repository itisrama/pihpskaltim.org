<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	</div>
EOD;

function index() {
	global $body;
	global $currentversion;
	$stats = '';

	$onlineusers = onlineusers();

	$sql = ("select count(id) totalmessages from cometchat");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$r = mysqli_fetch_assoc($query);
	$totalmessages = $r['totalmessages'];
            if(empty($totalmessages)){$totalmessages='0';}
	$now = getTimeStamp()-60*60*24;

		$sql = ("select count(id) totalmessages from cometchat where sent >= '".mysqli_real_escape_string($GLOBALS['dbh'],$now)."'");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$r = mysqli_fetch_assoc($query);
	$totalmessagest = $r['totalmessages'];
	$stats = <<<EOD

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
		<h1 style="font-size: 50px; font-weight: bold;">$onlineusers</h1>
		<span style="font-size: 10px;">USERS CHATTING</span>
	</div>

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
		<h1 style="font-size: 50px; font-weight: bold;">$totalmessages</h1>
		<span style="font-size: 10px;">TOTAL MESSAGES</span>
	</div>

	<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;width:100px;">
		<h1 style="font-size: 50px; font-weight: bold;">$totalmessagest</h1>
		<span style="font-size: 10px;">MESSAGES SENT IN THE LAST 24 HOURS</span>
	</div>

EOD;

	$detectchangepass = 'Below are quick statistics of your site. Be sure to frequently change your administrator password.';

	if ( ADMIN_USER == 'cometchat' && ADMIN_PASS == 'cometchat') {
		$detectchangepass = '<span style="color:#ff0000">Warning: Default administrator username/password detected. Please go to settings and change the username and password.</span>';
	}

	if (empty($totalmessages)) {
		$totalmessages = 0;
	}

		$body = <<<EOD
<h2>Welcome</h2>
<h3>$detectchangepass</h3>


	<div style="float:left">

		{$stats}
		<div style="clear:both;padding:10px;"></div>

		<div style="float:left;padding-right:20px;border-right:1px dotted #cccccc;margin-right:20px;">
			<h1 style="font-size: 70px; font-weight: bold;">$currentversion</h1>
			<span style="font-size: 10px;">YOUR COMETCHAT VERSION</span>
		</div>

		<div style="clear:both;padding:20px;"></div>

		<div style="width:450px;font-family:helvetica;line-height:1.4em;font-size:14px;">
			<span style="font-weight:bold;">Love CometChat?</span><br/>Take a minute to <a href="https://www.cometchat.com/reviews/write/" target="_blank">write us a testimonial</a> :)
		</div>


	</div>
	<div style="float:right">
		<iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FCometChat%2F&tabs=timeline&width=500&height=300&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId=143961562477205" width="500" height="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
	</div>


<div style="clear:both"></div>

EOD;
	template();
}

function loadexternal() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}

function loadthemetype() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}

function themeembedcodesettings() {
	global $getstylesheet;
	if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php')) {
		$generateembedcodesettings = 1;
		include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$_GET['type'].'s'.DIRECTORY_SEPARATOR.$_GET['name'].DIRECTORY_SEPARATOR.'settings.php');
	} else {
echo <<<EOD
$getstylesheet
<form>
<div id="content">
		<h2>No configuration required</h2>
		<h3>Sorry there are no settings to modify</h3>
		<input type="button" value="Close Window" class="button" onclick="javascript:window.close();">
</div>
</form>
EOD;
	}
}