<?php

/* TIMEZONE SPECIFIC INFORMATION (DO NOT TOUCH) */

date_default_timezone_set('UTC');

$currentversion = '6.1.0';

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SOFTWARE SPECIFIC INFORMATION (DO NOT TOUCH) */

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'integration.php')) {
  include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'integration.php');
}
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'environment.php')) {
  include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'environment.php');
}
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'cometchat_shared.php');
if(defined('CC_INSTALL')){
  return;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*Pull values from database if cache is not present*/

global $settings;
settingsCacheConnect();

global $languages;
getLanguageVar();

global $colors;
getColorVars();

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$cookiePrefix = setConfigValue('cookiePrefix','cc_');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* CCAUTH START */

define('USE_CCAUTH', setConfigValue('USE_CCAUTH','0'));

$ccactiveauth = setConfigValue('ccactiveauth',array('Facebook','Google','Twitter'));

$guestsMode = setConfigValue('guestsMode','0');
$guestnamePrefix = setConfigValue('guestnamePrefix','Guest');
$guestsList = setConfigValue('guestsList','3');
$guestsUsersList = setConfigValue('guestsUsersList','3');

/* CCAUTH END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* GOOGLE ANALYTICS START */

$gatrackerid=setConfigValue('gatrackerid','');

/* GOOGLE ANALYTICS END */

global $integration;

if(USE_CCAUTH == '1'){
  include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'ccauth.php');
  $integration = new CCAuth();
  $guestsMode = '0';
}else{
  $integration = new Integration();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* BASE URL START */

define('BASE_URL',setConfigValue('BASE_URL','/cometchat/'));

/* BASE URL END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* LANGUAGE START */

$lang = setConfigValue('lang','en');

/* LANGUAGE END */

if (!empty($_COOKIE[$cookiePrefix."lang"])) {
  $lang = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix . "lang"]);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$trayicon = array();
$trayicon['home'] = array('home','Home','/','','','','','','');
$trayicon['chatrooms'] = array('chatrooms','Chatrooms','modules/chatrooms/index.php','_popup','600','300','','1','1');
$trayicon['announcements'] = array('announcements','Announcements','modules/announcements/index.php','_popup','280','300','','1','');
$trayicon['games'] = array('games','Single Player Games','modules/games/index.php','_popup','465','300','','1','');
$trayicon['share'] = array('share','Share This Page','modules/share/index.php','_popup','350','50','','1','');
$trayicon['scrolltotop'] = array('scrolltotop','Scroll To Top','javascript:jqcc.cometchat.scrollToTop();','','','','','','');

$trayicon = setConfigValue('trayicon',$trayicon);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* PLUGINS START */

$plugins = array('smilies','clearconversation');
$plugins = setConfigValue('plugins',$plugins);

/* PLUGINS END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* EXTENSIONS START */

$extensions = array('mobileapp','desktop');
$extensions = setConfigValue('extensions',$extensions);

/* EXTENSIONS END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* CHATROOMPLUGINS START */

$crplugins = array('style','filetransfer','smilies');
$crplugins = setConfigValue('crplugins',$crplugins);

/* CHATROOMPLUGINS END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'smilies'.DIRECTORY_SEPARATOR.'config.php');

/* SMILEYS START */

$uploaded_smileys = array (
);
$uploaded_smileys = setConfigValue('uploaded_smileys',$uploaded_smileys);
/* SMILEYS END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* EMOJI START */

$smileys = array_merge($uploaded_smileys,$emojis);

/* EMOJI END */

$smileys_sorted = $smileys;
krsort($smileys_sorted);
uksort($smileys_sorted, "cmpsmileyskey");
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* BANNED START */

$bannedWords = setConfigValue('bannedWords',array());
$bannedUserIDs = setConfigValue('bannedUserIDs',array());
$bannedUserIPs = setConfigValue('bannedUserIPs',array());
$bannedMessage = setConfigValue('bannedMessage','Sorry, you have been banned from using this service. Your messages will not be delivered.');

/* BANNED END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADMIN START */

define('ADMIN_USER',setConfigValue('ADMIN_USER','cometchat'));
define('ADMIN_PASS',setConfigValue('ADMIN_PASS','cometchat'));

/* ADMIN END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$hideOffline = setConfigValue('hideOffline','1');     // Hide offline users in Who's Online list?
$autoPopupChatbox = setConfigValue('autoPopupChatbox','0');     // Auto-open chatbox when a new message arrives
$messageBeep = setConfigValue('messageBeep','1');     // Beep on arrival of message from new user?
$beepOnAllMessages = setConfigValue('beepOnAllMessages','1');     // Beep on arrival of all messages?
$minHeartbeat = setConfigValue('minHeartbeat','3000');      // Minimum poll-time in milliseconds (1 second = 1000 milliseconds)
$maxHeartbeat = setConfigValue('maxHeartbeat','12000');     // Maximum poll-time in milliseconds
$searchDisplayNumber = setConfigValue('searchDisplayNumber','10');      // The number of users in Whos Online list after which search bar will be displayed
$thumbnailDisplayNumber = setConfigValue('thumbnailDisplayNumber','40');      // The number of users in Whos Online list after which thumbnails will be hidden
$typingTimeout = setConfigValue('typingTimeout','10000');     // The number of milliseconds after which typing to will timeout
$idleTimeout = setConfigValue('idleTimeout','300');     // The number of seconds after which user will be considered as idle
$displayOfflineNotification = setConfigValue('displayOfflineNotification','1');     // If yes, user offline notification will be displayed
$displayOnlineNotification = setConfigValue('displayOnlineNotification','1');     // If yes, user online notification will be displayed
$displayBusyNotification = setConfigValue('displayBusyNotification','1');     // If yes, user busy notification will be displayed
$notificationTime = setConfigValue('notificationTime','5000');      // The number of milliseconds for which a notification will be displayed
$announcementTime = setConfigValue('announcementTime','15000');     // The number of milliseconds for which an announcement will be displayed
$scrollTime = setConfigValue('scrollTime','1');     // Can be set to 800 for smooth scrolling when moving from one chatbox to another
$armyTime = setConfigValue('armyTime','0');     // If set to yes, time will be shown in 24-hour clock format
$disableForIE6 = setConfigValue('disableForIE6','0');     // If set to yes, CometChat will be hidden in IE6
$hideBar = setConfigValue('hideBar','0');     // Hide bar for non-logged in users?
$disableForMobileDevices = setConfigValue('disableForMobileDevices','1');     // If set to yes, CometChat bar will be hidden in mobile devices
$startOffline = setConfigValue('startOffline','0');     // Load bar in offline mode for all first time users?
$fixFlash = setConfigValue('fixFlash','0');     // Set to yes, if Adobe Flash animations/ads are appearing on top of the bar (experimental)
$lightboxWindows = setConfigValue('lightboxWindows','1');     // Set to yes, if you want to use the lightbox style popups
$sleekScroller = setConfigValue('sleekScroller','1');     // Set to yes, if you want to use the new sleek scroller
$desktopNotifications = setConfigValue('desktopNotifications','1');     // If yes, Google desktop notifications will be enabled for Google Chrome
$windowTitleNotify = setConfigValue('windowTitleNotify','1');     // If yes, notify new incoming messages by changing the browser title
$floodControl = setConfigValue('floodControl','0');     // Chat spam control in milliseconds (Disabled if set to 0)
$windowFavicon = setConfigValue('windowFavicon','0');     // If yes, Update favicon with number of messages (Supported on Chrome, Firefox, Opera)
$prependLimit = setConfigValue('prependLimit','10');      // Number of messages that are fetched when load earlier messages is clicked
$blockpluginmode = setConfigValue('blockpluginmode','0');     // If set to yes, show blocked users in Who's Online list
$lastseen = setConfigValue('lastseen','0');     // If set to yes, users last seen will be shown


/* SETTINGS END */

$notificationsFeature = setConfigValue('notificationsFeature',1);      // Set to yes, only if you are using notifications

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* APIKEY START */

$apikey = setConfigValue('apikey','');      // API key for RESTful APIs for User Management on custom coded sites

/* APIKEY END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* MEMCACHE START */

define('MEMCACHE',setConfigValue('MEMCACHE','1'));       // Set to 0 if you want to disable caching and 1 to enable it.
define('MC_SERVER',setConfigValue('MC_SERVER','localhost'));  // Set name of your memcache  server
define('MC_PORT',setConfigValue('MC_PORT','11211'));      // Set port of your memcache  server
define('MC_USERNAME',setConfigValue('MC_USERNAME',''));           // Set username of memcachier  server
define('MC_PASSWORD',setConfigValue('MC_PASSWORD',''));           // Set password your memcachier  server
define('MC_NAME',setConfigValue('MC_NAME','files'));      // Set name of caching method if 0 : '', 1 : memcache, 2 : files, 3 : memcachier, 4 : apc, 5 : wincache, 6 : sqlite & 7 : memcached

/* MEMCACHE END */
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* COLOR START */

$color = setConfigValue('color','glass');

/* COLOR END */

$color_original = $color;

if (!empty($_COOKIE[$cookiePrefix."color"])) {
  $color = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix."color"]);
}

if (!empty($_REQUEST["cc_theme"]) && ($_REQUEST["cc_theme"] == 'synergy')) {
  $color = preg_replace("/[^A-Za-z0-9\-]/", '', $_REQUEST["cc_theme"]);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* THEME START */

$theme = setConfigValue('theme','glass');

/* THEME END */

$theme_original = $theme;

if (!empty($_COOKIE[$cookiePrefix."theme"])) {
  $theme = preg_replace("/[^A-Za-z0-9\-]/", '', $_COOKIE[$cookiePrefix."theme"]);
}

if (!empty($_REQUEST["cc_theme"])) {
  $theme = preg_replace("/[^A-Za-z0-9\-]/", '', $_REQUEST["cc_theme"]);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DISPLAYSETTINGS START */

define('DISPLAY_ALL_USERS',setConfigValue('DISPLAY_ALL_USERS','1'));

/* DISPLAYSETTINGS END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DISABLEBAR START */

define('BAR_DISABLED',setConfigValue('BAR_DISABLED','0'));

/* DISABLEBAR END */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* COMET START */

define('USE_COMET',setConfigValue('USE_COMET','0'));        // Set to 0 if you want to disable transport service and 1 to enable it.
define('KEY_A',setConfigValue('KEY_A',''));
define('KEY_B',setConfigValue('KEY_B',''));
define('KEY_C',setConfigValue('KEY_C',''));
define('IS_TYPING',setConfigValue('IS_TYPING','0'));        // Set to 0 if you want to disable is Typing... feature and 1 to enable it.
define('MESSAGE_RECEIPT',setConfigValue('MESSAGE_RECEIPT','0'));  // Set to 0 if you want to disable message receipts feature and 1 to enable it.
define('TRANSPORT',setConfigValue('TRANSPORT','cometservice'));
define('CS_TEXTCHAT_SERVER',setConfigValue('CS_TEXTCHAT_SERVER',''));

/* COMET END */

define('COMET_CHATROOMS',setConfigValue('COMET_CHATROOMS','1'));
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

define('AWS_STORAGE',setConfigValue('AWS_STORAGE','0'));
/*AWS Keys and bucket URL*/
if(!defined('AWS_ACCESS_KEY')) {
  define('AWS_ACCESS_KEY',setConfigValue('AWS_ACCESS_KEY',''));
}
if(!defined('AWS_SECRET_KEY')) {
  define('AWS_SECRET_KEY',setConfigValue('AWS_SECRET_KEY',''));
}
if(!defined('AWS_BUCKET')) {
  define('AWS_BUCKET',setConfigValue('AWS_BUCKET',''));
}
$aws_bucket_url = setConfigValue('aws_bucket_url',AWS_BUCKET);
/*Add client's id in bucket URL if it cloud*/
$bucket_path = isset($client)?$client.'/':'';

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADVANCED */

define('REFRESH_BUDDYLIST',setConfigValue('REFRESH_BUDDYLIST','60'));   // Time in seconds after which the user's "Who's Online" list is refreshed
define('DISABLE_SMILEYS',setConfigValue('DISABLE_SMILEYS','0'));      // Set to 1 if you want to disable smileys
define('DISABLE_LINKING',setConfigValue('DISABLE_LINKING','0'));      // Set to 1 if you want to disable auto linking
define('DISABLE_YOUTUBE',setConfigValue('DISABLE_YOUTUBE','1'));      // Set to 1 if you want to disable YouTube thumbnail
define('CACHING_ENABLED',setConfigValue('CACHING_ENABLED','0'));      // Set to 1 if you would like to cache CometChat
define('GZIP_ENABLED',setConfigValue('GZIP_ENABLED','0'));       // Set to 1 if you would like to compress output of JS and CSS
define('DEV_MODE',setConfigValue('DEV_MODE','0'));         // Set to 1 only during development
define('ERROR_LOGGING',setConfigValue('ERROR_LOGGING','0'));      // Set to 1 to log all errors (error.log file)
define('ONLINE_TIMEOUT',USE_COMET?REFRESH_BUDDYLIST*2:($maxHeartbeat/1000*2.5));
                    // Time in seconds after which a user is considered offline
define('DISABLE_ANNOUNCEMENTS',setConfigValue('DISABLE_ANNOUNCEMENTS','0'));  // Reduce server stress by disabling announcements
define('DISABLE_ISTYPING',setConfigValue('DISABLE_ISTYPING','1'));     // Reduce server stress by disabling X is typing feature
define('CROSS_DOMAIN',setConfigValue('CROSS_DOMAIN','0'));       // Do not activate without consulting the CometChat Team
if (CROSS_DOMAIN == 0){
  define('ENCRYPT_USERID', '1');      //Set to 1 to encrypt userid
}else{
  define('ENCRYPT_USERID', '0');
  define('CC_SITE_URL', setConfigValue('CC_SITE_URL',''));         // Enter Site URL only if Cross Domain is enabled.
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Pulls the language file if found

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'en.php');

if (!defined('DB_AVATARFIELD')) {
  define('DB_AVATARTABLE','');
  define('DB_AVATARFIELD',"''");
}

$channelprefix = (preg_match('/www\./', $_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:'www.'.$_SERVER['HTTP_HOST'];
$channelprefix = md5($channelprefix.BASE_URL);

if(defined('TAPATALK')&&TAPATALK==1){
  global $integration;
  $integration->hooks_setTapatalk($plugins);
}