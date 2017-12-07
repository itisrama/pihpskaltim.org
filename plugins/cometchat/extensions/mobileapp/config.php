<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$homepage_URL = setConfigValue('homepage_URL',"");
$adunit_id = setConfigValue('adunit_id','');
$invite_via_sms = setConfigValue('invite_via_sms','0');
$share_this_app = setConfigValue('share_this_app','0');
$login_background = setConfigValue('login_background','#FFFFFF');
$login_placeholder = setConfigValue('login_placeholder','#C2C2C9');
$login_button_pressed = setConfigValue('login_button_pressed','#002832');
$login_button_text = setConfigValue('login_button_text','#FFFFFF');
$login_foreground_text = setConfigValue('login_foreground_text','#000000');
$actionbar_color = setConfigValue('actionbar_color','#002832');
$actionbar_text_color = setConfigValue('actionbar_text_color','#FFFFFF');
$left_bubble_color = setConfigValue('left_bubble_color','#E5E5E5');
$left_bubble_text_color = setConfigValue('left_bubble_text_color','#000000');
$right_bubble_color = setConfigValue('right_bubble_color','#002832');
$right_bubble_text_color = setConfigValue('right_bubble_text_color','#FFFFFF');
$tab_highlight_color = setConfigValue('tab_highlight_color','#002832');
$firebaseauthserverkey = setConfigValue('firebaseauthserverkey','');
$mobileappOption = setConfigValue('mobileappOption','0');
$mobileappBundleid = setConfigValue('mobileappBundleid','com.inscripts.cometchat');
$useWhitelabelledapp = setConfigValue('useWhitelabelledapp','0');
$mobileappPlaystore = setConfigValue('mobileappPlaystore','https://play.google.com/store/apps/details?id=com.inscripts.cometchat&hl=en');
$mobileappAppstore = setConfigValue('mobileappAppstore','https://itunes.apple.com/in/app/cometchat/id594110077?mt=8');


/* SETTINGS END */

$oneonone_enabled = '1';
$announcement_enabled = '1';

$pushNotifications = '1';
$pushAPIKey = '';
$pushOauthSecret = '';
$pushOauthKey = '';
$notificationName = '';

define('PARSE_PUSH_URL','https://api.parse.com/1/push');
define('PARSE_APP_ID','JTsXPoBuAIgZnxkIQVcfXIY6ntiCXzTIa44L1b9i');
define('PARSE_REST_KEY','CZMS0sBrrnavRdaTmTOwWHmVXoH6h4NAmxagJuoR');

/* 1 => Phone number, 2 => Phone number with email */

$response['mobile_config']['phone_number_enabled']= '0';
$response['mobile_config']['username_password_enabled']= '1';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////