<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* ADVANCED */
$cms = "joomla";
define('SET_SESSION_NAME','');			// Session name
define('SWITCH_ENABLED','0');
define('INCLUDE_JQUERY','1');
define('FORCE_MAGIC_QUOTES','0');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* DATABASE */

if(!file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'configuration.php')){
	if(!file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'configuration.php')){
		echo "Please check if CometChat is installed in the correct directory.<br /> The 'cometchat' folder should be placed at <JOOMLA_HOME_DIRECTORY>/cometchat";
		exit;
	}
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'configuration.php');
}else{
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'configuration.php');
}
$config = new JConfig;
$secret = $config->secret;

// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW
// DO NOT EDIT DATABASE VALUES BELOW

define('DB_SERVER',				$config->host							);
define('DB_PORT',				"3306"									);
define('DB_USERNAME',				$config->user							);
define('DB_PASSWORD',				$config->password						);
define('DB_NAME',				$config->db								);

$table_prefix = $config->dbprefix;									// Table prefix(if any)
$db_usertable = 'users';							// Users or members information table name
$db_usertable_userid = 'id';						// UserID field in the users or members table
$db_usertable_name = 'name';					// Name containing field in the users or members table
$db_avatartable = ' ';
$db_avatarfield = ' '.$table_prefix.$db_usertable.'.'.$db_usertable_name.' ';
$db_linkfield = ' '.$table_prefix.$db_usertable.'.'.$db_usertable_userid.' ';

/*COMETCHAT'S INTEGRATION CLASS USED FOR SITE AUTHENTICATION */

class Integration{

	function __construct(){
		if(!defined('TABLE_PREFIX')){
			$this->defineFromGlobal('table_prefix');
			$this->defineFromGlobal('db_usertable');
			$this->defineFromGlobal('db_usertable_userid');
			$this->defineFromGlobal('db_usertable_name');
			$this->defineFromGlobal('db_avatartable');
			$this->defineFromGlobal('db_avatarfield');
			$this->defineFromGlobal('db_linkfield');
		}
	}

	function defineFromGlobal($key){
		if(isset($GLOBALS[$key])){
			define(strtoupper($key), $GLOBALS[$key]);
			unset($GLOBALS[$key]);
		}
	}


	function getUserID() {
		global $secret;
		$userid = 0;
		if (!empty($_SESSION['basedata']) && $_SESSION['basedata'] != 'null') {
			$_REQUEST['basedata'] = $_SESSION['basedata'];
		}

		if (!empty($_REQUEST['basedata'])) {

			if (function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
				$key = "";
				if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
					$key = KEY_A.KEY_B.KEY_C;
				}
				$uid = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(rawurldecode($_REQUEST['basedata'])), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
				if (intval($uid) > 0) {
					$userid = $uid;
				}
			} else {
				$userid = $_REQUEST['basedata'];
			}
		}
		$hash = md5(md5($secret.'site'));

		if (!empty($_COOKIE[$hash]) && (empty($userid) || $userid == "null")) {
			$sql = "SELECT userid FROM ".TABLE_PREFIX."session where session_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$_COOKIE[$hash])."'";
			$res = mysqli_query($GLOBALS['dbh'],$sql);
			$row = mysqli_fetch_assoc($res);
			$userid = $row['userid'];
		}

		$userid = intval($userid);
		return $userid;
	}

	function chatLogin($userName,$userPass) {
		$userid = 0;
		global $guestsMode;

		if (filter_var($userName, FILTER_VALIDATE_EMAIL)) {
			$sql = ("SELECT * FROM ".TABLE_PREFIX.DB_USERTABLE." WHERE email = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
		} else {
			$sql = ("SELECT * FROM ".TABLE_PREFIX.DB_USERTABLE." WHERE username = '".mysqli_real_escape_string($GLOBALS['dbh'],$userName)."'");
		}
		$result = mysqli_query($GLOBALS['dbh'],$sql);
		$row = mysqli_fetch_assoc($result);
		$checkpass = 0;
		$hash = $row['password'];

		if (substr($hash, 0, 4) == '$2a$' || substr($hash, 0, 4) == '$2y$'){
			if (substr($hash, 0, 4) == '$2y$'){
				$type = '$2y$';
			}
			else{
				$type = '$2a$';
			}
			$hash = $type . substr($hash, 4);
			$checkpass = (crypt($userPass, $hash) === $hash);
		}

		if (substr($hash, 0, 3) == '$1$'){
			$checkpass = (crypt($userPass, $hash) === $hash);
		}
		if (preg_match('#[a-z0-9]{32}:[A-Za-z0-9]{32}#', $hash) === 1){
			$checkpass = md5($userPass . substr($hash, 33)) == substr($hash, 0, 32);
		}
		if ($checkpass) {
			$userid = $row['id'];
			$sql = ("select params,enabled FROM ".TABLE_PREFIX."extensions where type='plugin' && element='cometchat'");
			$query = mysqli_query($GLOBALS['dbh'], $sql);
			$result = mysqli_fetch_assoc($query);
			if($result != null){
				if($result['enabled'] == 1){
					$sql = ("select a.group_id as group_id, b.title as title from ".TABLE_PREFIX."user_usergroup_map a INNER JOIN ".TABLE_PREFIX."usergroups b on a.group_id = b.id where user_id = ".$userid);
					$query = mysqli_query($GLOBALS['dbh'], $sql);
					while($row = mysqli_fetch_assoc($query)){
						$user_group_id = $row['group_id'];
						$user_group_title[] = $row['title'];
					}
					foreach($user_group_title as $ug){
						$users_permitted_groups[] = preg_replace('/\s+/', '', strtolower($ug));
					}
					$params = $result['params'];
					$params = json_decode($params,true);
					$notallowedgroups = array();
					if(!empty($params['usergroups'])){
						foreach($params['usergroups'] as $key => $value ) {
							if($value == 1) {
								$notallowedgroups[] = $key;
							}
						}
					}
					if(!empty($users_permitted_groups)){
						$users_permitted_grp = array_intersect($users_permitted_groups,$notallowedgroups);
						if(!empty($users_permitted_grp))
							return 0;
					}
				}else{
					return 0;
				}
			}
		}

		if(!empty($userName) && !empty($_REQUEST['social_details'])) {
			$social_details = json_decode($_REQUEST['social_details']);
			$userid = socialLogin($social_details);
		}
		if(!empty($_REQUEST['guest_login']) && $userPass == "CC^CONTROL_GUEST" && $guestsMode == 1){
			$userid = getGuestID($userName);
		}
		if(!empty($userid) && isset($_REQUEST['callbackfn']) && $_REQUEST['callbackfn'] == 'mobileapp'){
			$sql = ("insert into cometchat_status (userid,isdevice) values ('".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."','1') on duplicate key update isdevice = '1'");
			mysqli_query($GLOBALS['dbh'], $sql);
		}
		if ($userid && function_exists('mcrypt_encrypt') && defined('ENCRYPT_USERID') && ENCRYPT_USERID == '1') {
			$key = "";
			if( defined('KEY_A') && defined('KEY_B') && defined('KEY_C') ){
				$key = KEY_A.KEY_B.KEY_C;
			}
			$userid = rawurlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $userid, MCRYPT_MODE_CBC, md5(md5($key)))));
		}

		return $userid;
	}

	function getFriendsList($userid,$time) {
		global $hideOffline;
		$offlinecondition = '';
		if ($hideOffline) {
			$offlinecondition = "where ((cometchat_status.lastactivity > (".mysqli_real_escape_string($GLOBALS['dbh'],$time)."-".((ONLINE_TIMEOUT)*2).")) OR cometchat_status.isdevice = 1) and (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')";
		}
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link, " .DB_AVATARFIELD. " avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." ".$offlinecondition."  order by username asc");

		return $sql;
	}

	function getFriendsIds($userid) {
		$sql = ("select ".TABLE_PREFIX."friends.friend_user_id friendid from ".TABLE_PREFIX."friends where ".TABLE_PREFIX."friends.initiator_user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and is_confirmed = 1 union select ".TABLE_PREFIX."friends.initiator_user_id friendid from ".TABLE_PREFIX."friends where ".TABLE_PREFIX."friends.friend_user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' and is_confirmed = 1");

		return $sql;
	}

	function getUserDetails($userid) {
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link, " .DB_AVATARFIELD. " avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		return $sql;
	}

	function getActivechatboxdetails($userids) {
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username,  ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." link, ".DB_AVATARFIELD." avatar, cometchat_status.lastactivity lastactivity, cometchat_status.lastseen lastseen, cometchat_status.lastseensetting lastseensetting, cometchat_status.status, cometchat_status.message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid ".DB_AVATARTABLE." where ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." IN (".$userids.")");

		return $sql;
	}

	function getUserStatus($userid) {
		$sql = ("select cometchat_status.message, cometchat_status.status from cometchat_status where userid = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."'");

		return $sql;
	}

	function fetchLink($link) {
		return '';
	}

	function getAvatar($userid) {
		return '';
	}

	function getTimeStamp() {
		return time();
	}

	function processTime($time) {
		return	$time;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/* HOOKS */

	function hooks_message($userid,$to,$unsanitizedmessage,$dir) {

	}

	function hooks_forcefriends() {

	}

	function hooks_updateLastActivity($userid) {

	}

	function hooks_statusupdate($userid,$statusmessage) {

	}

	function hooks_activityupdate($userid,$status) {

	}

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* LICENSE */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'license.php');
$x = "\x62a\x73\x656\x34\x5fd\x65c\157\144\x65";
eval($x('JHI9ZXhwbG9kZSgnLScsJGxpY2Vuc2VrZXkpOyRwXz0wO2lmKCFlbXB0eSgkclsyXSkpJHBfPWludHZhbChwcmVnX3JlcGxhY2UoIi9bXjAtOV0vIiwnJywkclsyXSkpOw'));

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
