<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
		<a href="?module=extensions&amp;ts={$ts}" class="active_setting">Live extensions</a>
	</div>
EOD;

function index() {
	global $body;
	global $extensions;
	global $navigation;
    global $ts;
    $extensions_core = setConfigValue('extensions_core',array());

	$extensionslist = '';
	$extensiondata = '';

	foreach ($extensions_core as $extension => $extensioninfo) {
		if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension)) {
	        $titles[$extension] = $extensioninfo;
	        if($extension!='desktop' && $extension!='mobileapp'){
				$extensionhref = 'href="?module=extensions&amp;action=addextension&amp;data='.$extension.'&amp;ts='.$ts.'"';
				if (in_array($extension, $extensions)) {
					$extensionhref = 'href="javascript: void(0)" style="opacity: 0.5;cursor: default;"';
				}
				$extensiondata = '<span style="font-size:11px;float:right;margin-top:2px;margin-right:5px;"><a '.$extensionhref.' id="'.$extension.'">add</a></span>';
                if($extensioninfo == 'mobileapp_title') {
                    $extensiondata = '';
                }

				$extensionslist .= '<li class="ui-state-default"><img src="../extensions/'.$extension.'/icon.png" style="margin:0;margin-right:5px;float:left;"></img><span style="font-size:11px;float:left;margin-top:2px;margin-left:5px;width:100px">'.$extensioninfo.'</span>'.$extensiondata.'<div style="clear:both"></div></li>';
			}
		}
	}

	$activeextensionsdata = '';
	$activeextensions = '';
	$no_extensions = '';
	$no = 0;

	foreach ($extensions as $ti) {
		$title = ucwords($ti);
        if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$ti.DIRECTORY_SEPARATOR.'settings.php')) {
			$activeextensionsdata = '<a href="javascript:void(0)" onclick="javascript:extensions_configextension(\''.$ti.'\')" style="margin-right:5px"><img src="images/config.png" title="Configure Extension"></a>';
		}

		if(isset($extensions_core[$ti])) {
			$title = $titles[$ti];
		}

		++$no;
		if($title != 'Mobileapp' && $title != 'Desktop'){
			$activeextensionsdata .= '<a href="javascript:void(0)" onclick="javascript:extensions_removeextension(\''.$no.'\')"><img src="images/remove.png" title="Remove Extension" rel="'.$extension.'"></a>';
        }

		$activeextensions .= '<li class="ui-state-default" id="'.$no.'" d1="'.$ti.'" rel="'.$ti.'"><img src="../extensions/'.$ti.'/icon.png" style="margin:0;margin-top:2px;margin-right:5px;float:left;"></img><span style="font-size:11px;float:left;margin-top:3px;margin-left:5px;" id="'.$ti.'_title">'.stripslashes($title).'</span><span style="font-size:11px;float:right;margin-top:0px;margin-right:5px;">'.$activeextensionsdata.'</span><div style="clear:both"></div></li>';
	}

	if(!$activeextensions){
		$no_extensions .= '<div id="no_plugin" style="width: 480px;float: left;color: #333333;">You do not have any extensions activated at the moment. To activate a extension, please add the extension from the list of available extensions.</div>';
	}
	else{
		$activeextensions = '<ul id="modules_liveextensions">'.$activeextensions.'</ul>';
	}


	$body = <<<EOD
	$navigation

	<div id="rightcontent" style="float:left;width:720px;border-left:1px dotted #ccc;padding-left:20px;">
		<h2>Live Extensions</h2>
		<h3>Extensions add additional features to CometChat.</h3>

		<div>
			$no_extensions
			$activeextensions
			<div id="rightnav" style="margin-top:5px">
				<h1>Available extensions</h1>
				<ul id="modules_availableextensions">
				$extensionslist
				</ul>
			</div>
		</div>

		<div style="clear:both;padding:7.5px;"></div>
	</div>

	<div style="clear:both"></div>

EOD;

	template();

}

function addextension() {
    global $ts;
	global $extensions;

	if (!empty($_GET['data'])) {
		if($_GET['data'] === 'jabber'){
		//	$_SESSION['cometchat']['error'] = "You need to update the domain at www.cometchat.com/my otherwise Facebook/Gtalk won\'t work.";
		}
		array_push($extensions, $_GET['data']);
		configeditor(array('extensions' => $extensions));

		$_SESSION['cometchat']['error'] = 'Extension successfully activated!';
	}

	

	header("Location:?module=extensions&ts={$ts}");
}

function updateorder() {
	if (!empty($_POST['order'])) {
		configeditor(array('extensions' => $_POST['order']));
	} else {
		configeditor(array('extensions' => array()));
	}
	echo "1";
}