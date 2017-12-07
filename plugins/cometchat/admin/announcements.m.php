<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	<a href="?module=announcements&amp;ts={$ts}" id="announcement">Announcements</a>
	<a href="?module=announcements&amp;action=newannouncement&amp;ts={$ts}" id="add_announcement">Add new announcement</a>
	</div>
EOD;

function index() {
	global $body;
	global $navigation;

	$sql = ("select id,announcement,time,`to` from cometchat_announcements where `to` = 0 or `to` = '-1'  order by id desc");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	if (defined('DEV_MODE') && DEV_MODE == '1') { echo mysqli_error($GLOBALS['dbh']); }

	$announcementlist = '';

	while ($announcement = mysqli_fetch_assoc($query)) {
		$time = $announcement['time'];

		$announcementlist .= '<li class="ui-state-default"><span style="font-size:11px;word-wrap:break-word;margin-top:3px;margin-left:5px;">'.htmlspecialchars  ($announcement['announcement']).' (<span class="chat_time" timestamp="'.$time.'"></span>)</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a href="?module=announcements&amp;action=deleteannouncement&amp;data='.$announcement['id'].'&amp;ts={$ts}"><img src="images/remove.png" title="Delete Announcement"></a></span><div style="clear:both"></div></li>';
	}
        $errormessage = '';
        if(!$announcementlist){
            $errormessage = '<div id="no_module" style="width: 480px;float: left;color: #333333;">You do not have any announcements at the moment. To create a new announcement, click on \'Add new Announcement\'.</div>';
        }


	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Announcements</h2>
		<h3>Announcements are displayed live to your users. You can also add HTML code to your announcements, push advertisements in real-time.</h3>

		<div>
			<ul id="modules_announcements">
                        {$errormessage}
                        {$announcementlist}
			</ul>
			<div id="rightnav" style="margin-top:5px">
				<h1>Tips</h1>
				<ul id="modules_announcementtips">
					<li>When you add an announcement, it will be displayed live to all logged in online users. If there are more than one new announcements, the last announcement will be displayed.</li>

				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#modules_livemodules").sortable({ connectWith: 'ul' });
			$("#modules_livemodules").disableSelection();
			$("#leftnav").find('a').removeClass('active_setting');
			$("#announcement").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function deleteannouncement() {
    global $ts;
	if (!empty($_GET['data'])) {
		$sql = ("delete from cometchat_announcements where id = '".mysqli_real_escape_string($GLOBALS['dbh'],sanitize_core($_GET['data']))."'");
		$query = mysqli_query($GLOBALS['dbh'],$sql);
		removeCache('latest_announcement');
	}

	header("Location:?module=announcements&ts={$ts}");
}

function newannouncement() {
	global $body;
	global $navigation;
        global $ts;

	$body = <<<EOD
	$navigation
	<form action="?module=announcements&action=newannouncementprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>New announcement</h2>
		<h3>HTML code is allowed for announcements</h3>

		<div>
			<div id="centernav">
				<div class="titlefull">Announcement:</div>
				<div style="clear:both;padding:10px;"></div>
				<div style="clear:both;padding:5px;"><textarea name="announcement" rows=20 style="width:400px" required="true"></textarea></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title" style="width:170px">Show only to logged-in users?</div><div class="element"><input type="radio" name="sli" value="1" checked>Yes <input type="radio" name="sli" value="0" >No</div>
				<div style="clear:both;padding:10px;"></div>
			</div>
			<div id="rightnav">
				<h1>Warning</h1>
				<ul id="modules_availablemodules">
					<li>Your message will be shown live to all online users. Double check before proceeding.</li>
			<li>Users who have not logged-in will not be able to see the announcement in real-time.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add Announcement" class="button">&nbsp;&nbsp;or <a href="?module=announcements&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#add_announcement").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function newannouncementprocess() {
    global $ts;
	$zero = '0';
	if ($_POST['sli'] == 0) {
		$zero = '-1';
	}
	$message = mysqli_real_escape_string($GLOBALS['dbh'],$_POST['announcement']);
	$sent = mysqli_real_escape_string($GLOBALS['dbh'],getTimeStamp());
	$zero = mysqli_real_escape_string($GLOBALS['dbh'],$zero);
	$message = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $message);

	if (strpos($message, '<img') === false) {
		$img = strpos($message,"<img")+strlen("<img");
		$img = strpos(substr($message,$img,strpos($message,">",$img)-$img),"http");
		if($img === false) {
			$reg_exUrl = "/<a.*?<\/a>(*SKIP)(*F)|(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			if(preg_match($reg_exUrl, $message, $url)) {
				$message = preg_replace($reg_exUrl, "<a href=".$url[0]." target=\"_blank\">$url[0]</a>", $message);
			}
		}
	}

	$sql = ("insert into cometchat_announcements (announcement,time,`to`) values ('".$message."', '".$sent."','".$zero."')");
	$query = mysqli_query($GLOBALS['dbh'],$sql);
	$insertedid = mysqli_insert_id($GLOBALS['dbh']);
	pushMobileAnnouncement($zero,$sent,$message,1,$insertedid);

	removeCache('latest_announcement');
	header( "Location: ?module=announcements&ts={$ts}" );
}
