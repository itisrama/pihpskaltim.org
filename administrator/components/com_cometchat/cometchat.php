<?php
	defined( '_JEXEC' ) or die( 'Restricted access' );
	if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
	if(!empty($_POST['updateParam'])){
		$tempParams = $_POST;
		$params = JPluginHelper::getPlugin('system', 'cometchat');
		unset($tempParams['updateParam']);
		$tempParams = json_encode($tempParams);
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__extensions ');
		$query->set('params = \''.$tempParams.'\' ');
		$query->where('element = "cometchat" AND type = "plugin"');
		$db->setQuery($query);
		$db->query();
		$result = $db->query();
		setcookie('sync_inbox', null, -1, '/');
		echo json_encode($result);
		exit();
	}
	$cometchat_dir = dirname(JPATH_BASE).DS.'plugins'.DS.'cometchat';
	if((isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] != 0) && empty($_FILES)) {
		echo "<script>alert('The uploaded file exceeds the upload_max_filesize');</script>";
	}
	if(is_dir($cometchat_dir) && file_exists($cometchat_dir. DS .'license.php') && is_dir($cometchat_dir. DS .'admin')&& empty($_POST['upgrade_process']) && empty($_REQUEST['cc_upgrade'])){
		JToolBarHelper::title( 'CometChat', '' );
		require_once( JPATH_ROOT . '/administrator/components/com_cometchat/menus.php' );

	} else {
		if(isset($_POST['upload'])){
			if(fileUpload()) {
				JToolBarHelper::title( 'Installed CometChat Successfullly!', '' );
				$JROOT = JURI::root();
				echo <<<EOD
				<div class="alert alert-success">
					<h4 class="alert-heading">CometChat has been successfully installed!</h4>
				</div>
				<table id="successinfo" class="adminform" style="width:100%;background-color: #F2F2F2;border: 1px solid #ddd;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">
					<tbody>
						<tr>
							<td style="padding: 10px;">You can now access the admin panel directly from the navigation menu. Your default login is:</td>
						</tr>
						<tr>
							<td>
								<label style="margin-bottom: 0px !important;cursor:default;padding: 0 10px;"><b>Username:</b> cometchat</label>
							</td>
						</tr>
						<tr>
							<td>
								<label style="margin-bottom: 0px !important;cursor:default;padding: 0 10px;"><b>Password :</b> cometchat</label>
							</td>
						</tr>
						<tr>
							<td style="padding: 10px;">
								<a class="btn btn-primary" href="{$JROOT}" target="_blank">View Website</a>
								<a class="btn btn-primary" href="" target="_blank" style="margin-left: 10px;" >Access CometChat Admin Panel</a>
							</td>
						</tr>
						<tr>
							<td style="padding: 0 10px 10px;">The CometChat bar is now active on your site and integrated with your inbox as well!</td>
						</tr>
					</tbody>
				</table>
				<iframe src="{$JROOT}plugins/cometchat/install.php" style="width:1px;height:1px;border:0;"></iframe>
EOD;
			} else {
				JToolBarHelper::title( 'Invalid CometChat zip', '' );
				$upgradeProcess = (!empty($_POST['upgrade_process'])) ? '&cc_upgrade=1' : '';
				echo '<div class="alert alert-error"><h4 class="alert-heading">Uploaded zip was not a valid CometChat zip. <a href="#" onclick="window.location.href=\'index.php?option=com_cometchat'.$upgradeProcess.'\';">Click here </a>to try again to upload valid CometChat zip.</h4></div>';
			}
		} else {
			JToolBarHelper::title( 'CometChat Installation', '' );
			$upgradeProcess = (!empty($_REQUEST['cc_upgrade'])) ? '<input type="hidden" name="upgrade_process" value="1">' : '';
			echo <<<EOD
				<form name="uploadForm" method="post" action="?option=com_cometchat" enctype="multipart/form-data" class="form-horizontal">
					<fieldset class="uploadform">
						<legend>You are one step away from having CometChat on your site! Please select "cometchat.zip" which you have downloaded from our site and click "Install" to proceed.</legend>
						<div class="control-group">
							<label for="install_cometchat" class="control-label">CometChat package file</label>
							<div class="controls">
								<input class="input_box" id="install_cometchat" name="install_cometchat" type="file" size="57" onchange="checkFileType(this.value);" />
							</div>
						</div>
						<div class="form-actions">
							<input class="btn btn-primary" type="submit" value="Install" accept="*.zip"" onclick="if(document.getElementById('install_cometchat').value==''){alert('Upload a file with .zip extensions first.');return false;}" />
						</div>
					</fieldset>
					<div>You can download the latest version of <i>cometchat.zip</i> from <a href="http://www.cometchat.com" target="_blank">our site</a>. You will need to purchase a CometChat license if you haven't already. Feel free to email us at <a href="mailto:sales@cometchat.com" target="_blank">sales@cometchat.com</a> if you have any questions.</div>
					<input type="hidden" name="upload" value="1">
					{$upgradeProcess}
				</form>
				<script>
					function checkFileType(fileName) {
						if (fileName == "") {
							alert("Browse to upload a valid File with zip extension");
							return false;
						}
						else if (fileName.split(".")[1].toUpperCase() == "ZIP")
							return true;
						else {
							alert("File with ." + fileName.split(".")[1] + " extensions is invalid. Upload a valid file with .zip extensions");
							return false;
						}
						return true;
					}
				</script>
EOD;
		}
	}

	function fileUpload(){
		jimport('joomla.filesystem.file');
		jimport( 'joomla.filesystem.archive');
		jimport('joomla.filesystem.folder');
		$jroot_dir = dirname(JPATH_BASE);
		$cometchat_dir = dirname(JPATH_BASE).DS.'plugins'.DS.'cometchat';
		$file = JRequest::getVar('install_cometchat', null, 'files', 'array');
		if(isset($file)){
			$filename = JFile::makeSafe($file['name']);
			$src = $file['tmp_name'];
			$dest = dirname(JPATH_BASE). DS . 'plugins' . DS . $filename;
			$file_ext = end((explode(".", $file['name'])));
			if ($file_ext == 'zip') {
				$cometchat_old_dir = $cometchat_dir.'_'.time();
				if (move_uploaded_file($src,$dest)) {
					$adapter = JArchive::getAdapter('zip');
					if(!empty($_POST['upgrade_process'])){
						JFolder::move($cometchat_dir, $cometchat_old_dir);
					}
					if ($adapter && $adapter->extract($dest, $jroot_dir.DS.'plugins')) {
						$files = array('config.php','cache/','temp/','lang/','plugins/handwrite/uploads/','plugins/filetransfer/uploads/');
                        JPath::setPermissions($cometchat_dir,'0644','0755');
                        foreach($files as $filepath){
                            JPath::setPermissions($cometchat_dir. DS .$filepath,'0777','0777');
                        }
                        if (file_exists($cometchat_dir. DS .'install.php') && file_exists($cometchat_dir. DS .'license.php')) {
							return true;
						} else if(is_dir($cometchat_dir)) {
							JFolder::delete($cometchat_dir);
						}
						JFolder::move($cometchat_old_dir, $cometchat_dir);
					}
				}
			}
		}
		return false;
	}
