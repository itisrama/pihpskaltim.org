<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	<a href="?module=modules" id="live_modules">Live modules</a>
	<a href="?module=modules&amp;action=createmodule" id="add_module">Add custom tray icon</a>
	</div>
EOD;

function index() {
	global $body;
	global $trayicon;
	global $navigation;
    global $ts;
    $modules_core = setConfigValue('modules_core',array());
	if (empty($trayicon)) {
		$trayicon = array();
	}
	if((defined('TAPATALK'))||(defined('TAPATALK')&&TAPATALK==1)){
		unset($modules_core['themechanger']);
		if(!empty($trayicon['themechanger'])){
			unset($trayicon['themechanger']);
		}
	}
	$moduleslist = '';
        foreach ($modules_core as $module => $moduleinfo) {
        	if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module)) {
				$titles[$module] = $moduleinfo;
				$modulehref = 'href="javascript: void(0)" style="opacity: 0.5;cursor: default;"';

	            if (empty($trayicon[$module])) {
	            	$modulehref = 'href="?module=modules&amp;action=addmodule&amp;data='.base64_encode("\$trayicon['".$module."']=array('".implode("','", $moduleinfo)."');").'&amp;ts='.$ts.'"';
	            }
		    	$moduleslist .= '<li class="ui-state-default"><img src="../modules/'.$module.'/icon.png" style="margin:0;margin-right:5px;float:left;"></img><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;width:120px">'.$moduleinfo[1].'</span><span style="font-size:11px;float:right;margin-top:2px;margin-right:5px;"><a '.$modulehref.' id="'.$module.'" >add</a></span><div style="clear:both"></div></li>';
	    	}
        }

        $livetrayicons = '';
        foreach ($trayicon as $trayitem => $ti) {
			if (empty($ti[2])) { $ti[2] = ''; }
			if (empty($ti[3])) { $ti[3] = ''; }
			if (empty($ti[4])) { $ti[4] = ''; }
			if (empty($ti[5])) { $ti[5] = ''; }
			if (empty($ti[6])) { $ti[6] = ''; }
			if (empty($ti[7])) { $ti[7] = ''; }
			if (empty($ti[8])) { $ti[8] = ''; $showhide = 'Show'; $opacity='1'; } else { $showhide = 'Hide'; $opacity='0.5';}
			$config = '';

			if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$ti[0].DIRECTORY_SEPARATOR.'settings.php')) {
				$config = '<a href="javascript:void(0)" onclick="javascript:modules_configmodule(\''.$ti[0].'\')" style="margin-left:5px;"><img src="images/config.png" title="Configure Module"></a>';
			} else {
				$config = '<img src="images/blank.gif" width="16" height="16" style="margin-left:5px;">';
			}

			if ($ti[3] == '_lightbox') {
				$popup = '<a href="javascript:void(0)" onclick="javascript:modules_showpopup(this,\''.$ti[0].'\')" style="margin-left:5px;"><img style="opacity:0.5" src="images/lightbox.png" title="Open module as popup (default)"></a>';
			} else if ($ti[3] == '_popup') {
				$popup = '<a href="javascript:void(0)" onclick="javascript:modules_showpopup(this,\''.$ti[0].'\')" style="margin-left:5px;"><img style="opacity:1"  src="images/lightbox.png" title="Open module in a lightbox"></a>';
			} else {
				$popup = '<img src="images/blank.gif" width="16" height="16" style="margin-left:5px;">';
			}

			$title = stripslashes($ti[1]);

			if (!empty($ti[7])) {
	 			$visible = "style=\"margin-left:5px;visibility:visible;\"";
			} else {
				$visible = "style=\"margin-left:5px;visibility:hidden;\"";
			}

			if (!empty($ti[9])) {
				$custom = $ti[9];
			} else {
				$custom = 0;
			}
			$livetrayicons .= '<li class="ui-state-default" id="'.$ti[0].'" d1="'.addslashes($ti[1]).'" d2="'.$ti[2].'" d3="'.$ti[3].'" d4="'.$ti[4].'" d5="'.$ti[5].'" d6="'.$ti[6].'" d7="'.$ti[7].'" d8="'.$ti[8].'" ><img src="../modules/'.$ti[0].'/icon.png" style="margin:0;margin-top:2px;margin-right:5px;float:left;width:16px;"></img><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti[0].'_title">'.$title.'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;"><a onclick="javascript:modules_showtext(this,\''.$ti[0].'\');" href="javascript:void(0)" style="margin-left:5px;"><img src="images/text.png" style="opacity:'.$opacity.';" title="'.$showhide.' the module title in the chatbar"></a>'.$popup.'<a onclick="javascript:embed_link(\''.BASE_URL.''.$ti[2].'\',\''.$ti[4].'\',\''.$ti[5].'\');" href="javascript:void(0)" '.$visible.'><img src="images/embed.png" title="Generate Embed Code"></a> '.$config.'<a href="javascript:void(0)" onclick="javascript:modules_removemodule(\''.$ti[0].'\',\''.$custom.'\')" style="margin-left:5px;"><img src="images/remove.png" title="Remove Module"></a></span><div style="clear:both"></div></li>';
	}
        $errormessage = '';
        if(!$livetrayicons){
            $errormessage = '<div id="no_module" style="width: 480px;float: left;color: #333333;">You do not have any Module activated at the moment. To activate a module, please add the module from the list of available modules.</div>';
        }

	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Live Modules</h2>
		<h3>Use your mouse to change the order in which the modules appear on the bar (left-to-right). You can add available modules from the right.</h3>

		<div>
			<ul id="modules_livemodules">
                                {$livetrayicons}
                                {$errormessage}
			</ul>
			<div id="rightnav" style="margin-top:5px">
				<h1>Available modules</h1>
				<ul id="modules_availablemodules">
                                {$moduleslist}
				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="button" onclick="javascript:modules_updateorder()" value="Update order" class="button">&nbsp;&nbsp;or <a href="?module=modules&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#modules_livemodules").sortable({ connectWith: 'ul' });
			$("#modules_livemodules").disableSelection();
			$("#leftnav").find('a').removeClass('active_setting');
			$("#live_modules").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function updateorder() {
	if (!empty($_POST['order'])) {
		configeditor(array('trayicon' => $_POST['order']));
	}else{
		configeditor(array('trayicon' => array()));
	}
	echo "1";
}

function addmodule() {
	global $ts;
	global $trayicon;
	if (!empty($_GET['data'])) {
		$data = base64_decode($_GET['data']);
		eval($data);
		configeditor(array('trayicon' => $trayicon));
	}

	$_SESSION['cometchat']['error'] = 'Module successfully activated!';
	header("Location:?module=modules&ts={$ts}");
}

function createmodule() {
	global $body;
	global $navigation;
    global $ts;

	$body = <<<EOD
	$navigation
	<script>
		function EmbedType() {
			if ($("#embed_type option:selected").val() == "link") {
				$("#link").slideDown("slow");
				$("#embed").slideUp("fast");
			} else {
				$("#link").slideUp("fast");
				$("#embed").slideDown("slow");
			}
		}
	</script>
	<form action="?module=modules&action=createmoduleprocess&ts={$ts}" method="post" enctype="multipart/form-data">
	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Add custom tray icon</h2>
		<h3>The maximum height for the icon is 16px</h3>

		<div>
			<div id="centernav">
				<div class="title">Title:</div><div class="element"><input type="text" class="inputbox" name="title"></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Icon:</div><div class="element"><input type="file" class="inputbox" name="file"></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Embed Type:</div>
				<div class="element">
					<select id="embed_type" class="inputbox" name="embed_type" onchange="EmbedType()">
						<option value="link" selected>Link</option>
						<option value="embed">Embed Code</option>
					</select>
				</div>
				<div style="clear:both;padding:10px;"></div>
				<div id="link"><div class="title">Link:</div><div class="element"><input type="text" class="inputbox" name="link" value="http://www.cometchat.com"></div>
				<div style="clear:both;padding:10px;"></div></div>
				<div id="embed" style="display:none;"><div class="title">Embed code:</div><div class="element"><textarea name="embeded_code" class="inputbox" rows=10 style="width:250px;"></textarea></div>
				<div style="clear:both;padding:10px;"></div></div>

				<div class="title">Type:</div><div class="element"><select class="inputbox" name="type"><option value="">Same window<option  value="_blank">New window<option  value="_popup">Pop-up<option  value="_lightbox">Lightbox (same window popup)</select></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="titlefull">If type is pop-up, please enter the width and height</div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Width:</div><div class="element"><input type="text" class="inputbox" name="width" value="300"></div>
				<div style="clear:both;padding:10px;"></div>
				<div class="title">Height:</div><div class="element"><input type="text" class="inputbox" name="height" value="200"></div>
				<div style="clear:both;padding:10px;"></div>
			</div>
			<div id="rightnav">
				<h1>Tip</h1>
				<ul id="modules_availablemodules">
					<li>It is best to use PNG format for your icons. Set transparency on for your icons.</li>
 				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
		<input type="submit" value="Add custom tray icon" class="button">&nbsp;&nbsp;or <a href="?module=modules&amp;ts={$ts}">cancel</a>
	</div>

	<div style="clear:both"></div>

	<script type="text/javascript">
		$(function() {
			$("#leftnav").find('a').removeClass('active_setting');
			$("#add_module").addClass('active_setting');
		});
	</script>

EOD;

	template();

}

function createmoduleprocess() {
    global $ts;
    global $trayicon;
	$extension = '';
	$error = '';

	$modulename = createslug($_POST['title'],true);

	if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/png"))) {
		if ($_FILES["file"]["error"] > 0) {
			$error = "Module icon incorrect. Please try again.";
		} else {
			if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $modulename)) {
				unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $modulename);
			}

			$extension = extension($_FILES["file"]["name"]);
			if (!move_uploaded_file($_FILES["file"]["tmp_name"], dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $modulename)) {
				$error = "Unable to copy to temp folder. Please CHMOD temp folder to 777.";
			}
		}
	} else {
		$error = "Module icon not found. Please try again.";
	}

	if (empty($_POST['title'])) {
		$error = "Module title is empty. Please try again.";
	}

	if (!empty($_POST['embed_type'])) {
		if ($_POST['embed_type'] == 'link') {
			if (empty($_POST['link'])) {
				$error = "Module link is empty. Please try again.";
			}
		} else {
			if (empty($_POST['embeded_code'])) {
				$error = "Module embed code is empty. Please try again.";
			}
		}
	}

	if (!empty($error)) {
		$_SESSION['cometchat']['error'] = $error;
		header("Location: ?module=modules&action=createmodule&ts={$ts}");
		exit;
	}

	mkdir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modulename, 0777);

	copy(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $modulename,dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modulename.DIRECTORY_SEPARATOR.'icon.png');

	unlink(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."temp" .DIRECTORY_SEPARATOR. $modulename);

	if (!empty($_POST['embeded_code'])) {
		$filePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modulename.DIRECTORY_SEPARATOR.'index.html';
		$createFile = fopen($filePath, 'w');
		fwrite($createFile, $_POST['embeded_code']);
		fclose($createFile);

		$code = "\$trayicon['".$modulename."'] = array('".$modulename."','".addslashes(addslashes(addslashes(str_replace('"','',ucfirst($_POST['title'])))))."','modules/".$modulename."/index.html','".$_POST['type']."','".$_POST['width']."','".$_POST['height']."','','1','','1');";

	} else {
		$code = "\$trayicon['".$modulename."'] = array('".$modulename."','".addslashes(addslashes(addslashes(str_replace('"','',ucfirst($_POST['title'])))))."','".$_POST['link']."','".$_POST['type']."','".$_POST['width']."','".$_POST['height']."','','','','0');";
	}
	eval($code);
	configeditor(array('trayicon' => $trayicon));
	header("Location:?module=modules&ts={$ts}");

}

function removecustommodules () {

	if (!empty($_REQUEST['module'])) {
		$dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.$_REQUEST['module'];
		$files = scandir($dir);

		foreach ($files as $num => $fname){
			if (file_exists("$dir/$fname")) {
				@unlink("$dir/$fname");
			}
		}
		rmdir("$dir");
	}

}