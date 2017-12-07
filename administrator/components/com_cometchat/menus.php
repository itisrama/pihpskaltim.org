<?php
	defined('_JEXEC') or die('Restricted access');
	$params = JPluginHelper::getPlugin('system', 'cometchat');
	$params = json_decode($params->params,true);
	$db = JFactory::getDbo();
	if(empty($params)){
		$db = JFactory::getDbo();
		$db->setQuery("SELECT params FROM #__extensions where type='plugin' && element='cometchat'");
		$params = $db->loadResult();
		$params = json_decode($params,true);
	}
	$checked = 'checked="checked"';
	$administratorChecked = (empty($params['usergroups']['administrator'])) ? '' : $checked;
	$superuserChecked = (empty($params['usergroups']['superuser'])) ? '' : $checked;
	$authorChecked = (empty($params['usergroups']['author'])) ? '' : $checked;
	$db->setQuery("SELECT title FROM #__usergroups");
	$result = $db->loadAssocList();
	$hideCheckedYes = '';
	$hideCheckedNo = $checked;
	if(!empty($params['hide_bar'])) {
		$hideCheckedYes = $checked;
		$hideCheckedNo = '';
	}
	$usergroups = '';
	foreach( $result as $index => $row ) {
		$tempTitle = preg_replace('/\s+/', '', strtolower($row['title']));
		$isChecked = (empty($params['usergroups'][$tempTitle])) ? '' : $checked;
		$usergroups .= '<input type="checkbox" value="'.$tempTitle.'" name="usergroups" id="'.$tempTitle.'" class="usergroups" '.$isChecked.' /><label class="checkbox-inline" for="'.$tempTitle.'">'.$row['title'].'</label>';
	}
?>
<style type="text/css">
	#add_additional_settings .checkbox-inline {
		display: inline-block;
		padding-left: 5px;
		margin-bottom: 0;
		margin-right: 10px;
		font-weight: 400;
		cursor: pointer;
		vertical-align: text-top;
	}
	#add_additional_settings .checkbox-inline input {
		margin: 0;
	}
	#add_additional_settings .sub {
		display: none;
	}
	#add_additional_settings input[type="checkbox"] {
		margin: 0;
	}
</style>

</head>
<script type="text/javascript">
	jQuery(function() {
		jQuery("#submenu").find("li").click(function(){
			jQuery(".menus").removeClass("active");
			jQuery(this).addClass("active");
			var rel=jQuery(this).data("rel");
			jQuery(".sub").hide();
			jQuery("#"+rel).show();
		});
		jQuery('#save_settings').click(function(){
			var sync_inbox = jQuery('input:radio[name=sync_inbox]:checked').val();
			var hide_bar = jQuery('input:radio[name=hide_bar]:checked').val();
			var usergroups = {};
			jQuery('input[name="usergroups"]').each(function() {
				usergroups[this.value] = (this.checked) ? 1 : 0;
			});
			jQuery.ajax({
				url: '',
				data: {updateParam:1,sync_inbox:sync_inbox,hide_bar:hide_bar,usergroups:usergroups},
				dataType: 'json',
				method: 'post',
				success: function(res){
					if(res) {
						jQuery('#system-message-container').html('<button type="button" class="closeCustom close">x</button><div class="alert alert-success"><h4 class="alert-heading">Message</h4><p>Settings successfully saved.</p></div>');
					} else {
						jQuery('#system-message-container').html('<button type="button" class="closeCustom close">x</button><div class="alert"><h4 class="alert-heading">Warning</h4><p>Unable to save settings. Please try again.</p></div>');
					}
				}
			});
		});

		jQuery('#system-message-container').on('click','',function(e){
			e.stopImmediatePropagation();
			jQuery('#system-message-container').html('');
		});

		var urlHash = window.location.hash.substr(1);
		jQuery("#submenu").find("li[data-rel='"+urlHash+"']").click();

	});
</script>

<div id="navbar">
	<div id="holder">
		<ul class="nav nav-tabs nav-justified" id="submenu">
			<li data-rel="cc_admin_panel" class="active menus"><a href="#cc_admin_panel">CometChat Administration Panel</a></li>
			<li data-rel="add_additional_settings" class="menus"><a href="#add_additional_settings">Additional Settings</a></li>
			<li data-rel="upgrade_cometchat" class="menus"><a href="#upgrade_cometchat">Upgrade</a></li>
		</ul>
		<div class="clr"></div>
	</div>
	<div class="sub" id="cc_admin_panel" style="display: block;">
		<iframe  src="<?php echo JURI::root();?>plugins/cometchat/admin" style=" width: 80%; height: 720px; border: 0; position: relative; left: 10%" scrolling="yes"></iframe>
	</div>
	<div class="sub" id="add_additional_settings">
		<table cellspacing="1" style="margin-top:20px;">
			<tr style="margin-top:20px;">
				<td width="550" style="padding-top: 20px;">
					Hide CometChat for which usergroups?
				</td>
				<td valign="top" style="padding-top: 20px;">
					<?php echo $usergroups; ?>
				</td>
			</tr>
			<tr style="margin-top:20px;">
				<td width="550" style="padding-top: 20px;">
					Hide CometChat bar?
				</td>
				<td valign="top" style="padding-top: 20px;">
					<div class="controls">
						<fieldset id="hide_bar" class="radio btn-group btn-group-yesno" >
							<input type="radio" id="hide_bar0" name="hide_bar" value="1" <?php echo $hideCheckedYes; ?> />
							<label for="hide_bar0" >Yes</label>
							<input type="radio" id="hide_bar1" name="hide_bar" value="0" <?php echo $hideCheckedNo; ?> />
							<label for="hide_bar1" >No</label>
						</fieldset>
					</div>
				</td>
			</tr>
			<tr>
				<td style="padding-top: 20px;">
					<button type="button" id="save_settings" class="btn btn-primary">Save Settings</button>
				</td>
			</tr>
		</table>
	</div>
	<div class="sub" id="upgrade_cometchat">
		<form name="uploadForm" method="post" action="?option=com_cometchat" enctype="multipart/form-data" class="form-horizontal">
			<fieldset class="uploadform">
				<legend>Please select "cometchat.zip" which you have downloaded from our site and click "Install" to proceed.</legend>
				<div class="control-group">
					<label for="install_cometchat" class="control-label">CometChat package file</label>
					<div class="controls">
						<input class="input_box" id="install_cometchat" name="install_cometchat" type="file" size="57" onchange="checkFileType(this.value);">
					</div>
				</div>
				<div class="form-actions">
					<input class="btn btn-primary" type="submit" value="Install" onclick="if(document.getElementById('install_cometchat').value==''){alert('Upload a file with .zip extensions first.');return false;}">
				</div>
			</fieldset>
			<div>You can download the latest version of <i>cometchat.zip</i> from <a href="http://www.cometchat.com" target="_blank">our site</a>. You will need to purchase a CometChat license if you haven't already. Feel free to email us at <a href="mailto:sales@cometchat.com" target="_blank">sales@cometchat.com</a> if you have any questions.</div>
			<input type="hidden" name="upload" value="1">
			<input type="hidden" name="upgrade_process" value="1">
		</form>
	</div>
</div>
<div class="clr"></div>
