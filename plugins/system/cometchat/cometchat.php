<?php
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class plgSystemCometchat extends JPlugin
{
	function __construct(& $subject, $config)
	{
		// parent::__construct($subject, $config);
		$loc = JFactory::getURI();
		$doc = JFactory::getDocument();
		$cometchat_dir = JPATH_BASE.DS.'plugins'.DS.'cometchat';
		if(stripos($loc->getPath(),'/administrator/') === false && is_dir($cometchat_dir)) {
			$params = JPluginHelper::getPlugin('system', 'cometchat');
			$params = json_decode($params->params,true);
			$db = JFactory::getDbo();
			if(empty($params)){
				$db = JFactory::getDbo();
				$db->setQuery("SELECT params FROM #__extensions where type='plugin' && element='cometchat'");
				$params = $db->loadResult();
				$params = json_decode($params,true);
			}
			$hide_bar = false;
			if($params['hide_bar'] == 1){
				$hide_bar = true;
			}
			$user = JFactory::getUser();
			$myId = $user->id;
			$guestUser = $user->guest;
			$addCometchat = true;
			if(empty($myId)) {
				$addCometchat = false;
			} else if(!empty($user->groups)) {
				$notallowedgroups = array();
				if(!empty($params['usergroups'])){
					foreach($params['usergroups'] as $key => $value ) {
						if($value == 1) {
							$notallowedgroups[] = $key;
						}
					}
				}
				$db->setQuery("SELECT id,title FROM #__usergroups");
				$user_group_temp = array_intersect_key($db->loadAssocList('id'),$user->groups);
				$users_permitted_groups = array();
				foreach($user_group_temp as $ug)
				{
					$users_permitted_groups[] = preg_replace('/\s+/', '', strtolower($ug['title']));
				}
				if(!empty($users_permitted_groups) && $users_permitted_groups == array_intersect($users_permitted_groups,$notallowedgroups)) {
					$addCometchat = false;
				}
			}
			if($addCometchat && !$hide_bar) {
				$cometchat_style = JUri::base().'plugins/cometchat/cometchatcss.php';
				$cometchat_script = JUri::base().'plugins/cometchat/cometchatjs.php';
				$doc->addStylesheet($cometchat_style);
				$doc->addScript($cometchat_script);
			}
		}
	}
}