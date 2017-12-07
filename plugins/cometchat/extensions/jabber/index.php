<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

$domain = '';
if (!empty($_GET['basedomain'])) {
	$domain = $_GET['basedomain'];
}
$caller = '';
if (!empty($_GET['caller'])) {
	$caller = $_GET['caller'];
}

$embed = '';
$embedcss = '';
$close = 'window.close();';
$before = 'window.opener';
$before2 = 'window.top';

if (!empty($_GET['embed']) && $_GET['embed'] == 'web') {
	$embed = 'web';
	$before = 'parent';
	$before2 = 'parent';
	$embedcss = 'embed';
	$close = "parent.closeCCPopup('jabber');";
}

if (!empty($_GET['embed']) && $_GET['embed'] == 'desktop') {
	$embed = 'desktop';
	$before = 'parentSandboxBridge';
	$before2 = 'parentSandboxBridge';
	$embedcss = 'embed';
	$close = "parentSandboxBridge.closeCCPopup('jabber');";
}

if (!empty($_GET['session'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('session','sessiondata');
	</script>
EOD;
	exit;
}
if (!empty($_GET['sessiongtalk'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('sessiongtalk','sessiondatagtalk');
	</script>
EOD;
	exit;
}
if (!empty($_GET['error'])) {
	echo <<<EOD
	<script>
	{$before2}.location.href = location.href.replace('error','Denied');
	</script>
EOD;
	exit;
}
if (!empty($_GET['Denied'])) {
	echo <<<EOD
	<script>
	{$close}
	</script>
EOD;
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>
			<?php echo $jabber_language[0];?><?php echo $jabber_language[16];?><?php echo $jabber_language[12];?>
			<?php echo $jabber_language[15];?>
		</title>
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=extension&name=jabber" />
		<script src="../../js.php?type=core&name=jquery"></script>
		<script src="../../js.php?type=extension&name=jabber"></script>
		<script>
		 	$ = jQuery = jqcc;
			var before = "<?php echo $before;?>";
			var before2 = "<?php echo $before2;?>";
			var close = "<?php echo $close;?>";
			var domain = "<?php echo $domain;?>";
		</script>
	</head>
	<body>
		<form name="upload" onsubmit="return login();">
			<div class="cometchat_wrapper">
				<div class="container_title <?php echo $embedcss;?>"><?php echo $jabber_language[1];?></div>
				<div class="container_body <?php echo $embedcss;?>">
				<?php
					if(empty($_GET['sessiondata']) && empty($_GET['sessiondatagtalk']) ):
				?>
					<div style="margin: 0px auto;width: 149px;">
						<script>
							String.prototype.replaceAll=function(s1, s2) {return this.split(s1).join(s2)};
							var currenttime = new Date();
							currenttime = parseInt(currenttime.getTime());
							document.write('<iframe src="<?php echo $cometchatServer;?>gtalk.jsp?cometserver=<?php echo $cometchatServer; ?>&time='+currenttime+'&id=<?php echo $gtalkAppId;?>&r='+location.href.replaceAll('&','AND').replaceAll('?','QUESTION')+'" frameborder="0" border="0" width="149" height="22"></iframe>');
						</script>
					</div>
				<?php
					else:
						if(isset($_GET['sessiondatagtalk'])):
					?>
					<div class="container_body_1">
						<span><?php echo $jabber_language[7];?></span>
					</div>
					<script>
						$(function() {
							login_gtalk('<?php echo $_GET["sessiondatagtalk"];?>','<?php echo $_GET["username"];?>','<?php echo $caller;?>');
						});
					</script>
				<?php
						endif;
					endif;
				?>
					<div style="clear:both"></div>
				</div>
			</div>
		</form>
	</body>
</html>