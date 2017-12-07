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
	unset($_SESSION['cometchat']['cometchat_admin_user']);
	unset($_SESSION['cometchat']['cometchat_admin_pass']);
	global $body;
		$body = <<<EOD
<script>
window.location.reload();
</script>
EOD;
	template();
}