<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$addonfolder = str_replace(DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php','', __FILE__);
$addonarray = explode(DIRECTORY_SEPARATOR, $addonfolder);
$addonname = end($addonarray);
$addontype = rtrim(prev($addonarray),'s');

/* LANGUAGE */

${$addonname.'_language'}['title'] 					= setLanguageValue('title','Change Theme',$lang,$addontype,$addonname);
${$addonname.'_language'}['current_theme'] 			= setLanguageValue('current_theme','Current theme:',$lang,$addontype,$addonname);
${$addonname.'_language'}['select_another_theme'] 		= setLanguageValue('select_another_theme','Select another theme:',$lang,$addontype,$addonname);
${$addonname.'_language'}['no_other_theme_available']	= setLanguageValue('no_other_theme_available','No other theme available.',$lang,$addontype,$addonname);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

${$addonname.'_key_mapping'} = array(
	'100'	=>	'title',
	'0'		=>	'current_theme',
	'1'		=>	'select_another_theme',
	'2'		=>	'no_other_theme_available'
);

${$addonname.'_language'} = mapLanguageKeys(${$addonname.'_language'},${$addonname.'_key_mapping'},$addontype,$addonname);