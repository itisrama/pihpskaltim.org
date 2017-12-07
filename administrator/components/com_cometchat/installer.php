<?php

jimport('joomla.installer.installer' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class com_cometchatInstallerScript {

	function install() {
		$tmp = JPATH_ROOT.DS."tmp" ;
		$plugName = "cc_sys_plugin";
		$archivename = dirname( __FILE__).DS."admin".DS.$plugName.".zip";
		$tmpdir = $tmp.DS.$plugName;
		if(is_dir($tmpdir)) {
			JFolder::delete($tmpdir);
		}
		JFolder::create($tmpdir,0777);
		$extractdir = JPath::clean($tmpdir);
		$archivename = JPath::clean($archivename);
		if ($adapter = JArchive::getAdapter('zip'))
		{
			$result = $adapter->extract($archivename, $extractdir);
			echo "Extract";
		}
		$type = JInstallerHelper::detectType($extractdir);
		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $extractdir;
		$package['type'] = $type;
		$installer = new JInstaller();
		$installer->_overwrite = true;
		if (!$installer->install($package['dir'])) {
			return false;
		}
		$db = JFactory::getDBO();
		$query = "update #__plugins set published = 1 where name = 'Cometchat' and element = 'CometChat'";
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$query = "update #__extensions set enabled = 1 where name = 'Cometchat' and element = 'CometChat'";
		}
		$db->setQuery($query);
		$result = $db->query();
		return true;
	}

	public function uninstall() {
		$db = JFactory::getDBO();
		$query = 'SELECT `id` FROM #__plugins WHERE `element` = "CometChat"';
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$query = 'SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin"';
		}
		$db->setQuery($query);
		$id = $db->loadResult();
		$query1 = "DROP table cometchat";
		$db->setQuery($query1);
		$result1 = $db->execute();
		$query2 = "DROP table cometchat_announcements";
		$db->setQuery($query2);
		$result2 = $db->execute();
		$query3 = "DROP table cometchat_block";
		$db->setQuery($query3);
		$result3 = $db->execute();
		$query4 = "DROP table cometchat_chatroommessages";
		$db->setQuery($query4);
		$result4 = $db->execute();
		$query5 = "DROP table cometchat_chatrooms";
		$db->setQuery($query5);
		$result5 = $db->execute();
		$query6 = "DROP table cometchat_chatrooms_users";
		$db->setQuery($query6);
		$result6 = $db->execute();
		$query7 = "DROP table cometchat_guests";
		$db->setQuery($query7);
		$result7 = $db->execute();
		$query8 = "DROP table cometchat_session";
		$db->setQuery($query8);
		$result8 = $db->execute();
		$query9 = "DROP table cometchat_status";
		$db->setQuery($query9);
		$result9 = $db->execute();
		if($id)
		{
			$installer = new JInstaller;
			$result = $installer->uninstall('plugin',$id,1);
			$status->plugins[] = array('name'=>'CometChat','group'=>'system', 'result'=>$result);
		}

		$cometchat_dir = dirname(JPATH_BASE).DS.'plugins'.DS.'cometchat';
		$cometchat_old_dir = $cometchat_dir.'_'.time();
		if(is_dir($cometchat_dir)) {
			JFolder::move($cometchat_dir, $cometchat_old_dir);
		}
		return true;
	}
}