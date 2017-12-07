<?php

/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JKHelperSwfuploader {

	function loadScripts($task)
	{
		$component_name = JRequest::getVar('option');
		//add the links to the external files into the head of the webpage (note the 'administrator' in the path, which is not nescessary if you are in the frontend)
		$document =& JFactory::getDocument();
		$document->addScript(JK_ADMIN_LIBRARIES . '/swfupload/swfupload.js');
		$document->addScript(JK_ADMIN_LIBRARIES . '/swfupload/swfupload.queue.js');
		$document->addScript(JK_ADMIN_LIBRARIES . '/swfupload/fileprogress.js');
		$document->addScript(JK_ADMIN_LIBRARIES . '/swfupload/handlers.js');
		$document->addStyleSheet(JK_ADMIN_LIBRARIES . '/swfupload/default.css');
		
		//when we send the files for upload, we have to tell Joomla our session, or we will get logged out 
		$session = & JFactory::getSession();
		$id = rand(1000,9999);
		$swfUploadHeadJs ='
		var swfu;

		window.onload = function()
		{

		var settings = 
		{
				//this is the path to the flash file, you need to put your components name into it
				flash_url : "'.JK_ADMIN_LIBRARIES.'/swfupload/swfupload.swf",

				//we can not put any vars into the url for complicated reasons, but we can put them into the post...
				upload_url: "index.php",
				post_params: 
				{
						"option" : "'.$component_name.'",
						"task" : "'.$task.'",
						"id" : "'.$id.'",
						"'.$session->getName().'" : "'.$session->getId().'",
						"format" : "raw"
				}, 
				//you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
				file_size_limit : "5 MB",
				//client side file chacking is for usability only, you need to check server side for security
				file_types : "*.json; *.txt; *.tpid",
				file_types_description : "All Files",
				file_upload_limit : 100,
				file_queue_limit : 100,
				custom_settings : 
				{
						progressTarget : "fsUploadProgress",
						cancelButtonId : "btnCancel"
				},
				debug: false,

				// Button settings
				button_image_url: "'.JK_ADMIN_LIBRARIES.'/swfupload/TestImageNoText_65x29.png",
				button_width: "85",
				button_height: "29",
				button_placeholder_id: "spanButtonPlaceHolder",
				button_text: \'<span class="theFont">Choose Files</span>\',
				button_text_style: ".theFont { font-size: 13; }",
				button_text_left_padding: 5,
				button_text_top_padding: 5,

				// The event handler functions are defined in handlers.js
				file_queued_handler : fileQueued,
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				queue_complete_handler : queueComplete  // Queue plugin event
		};
		swfu = new SWFUpload(settings);
		};

		';

		//add the javascript to the head of the html document
		$document->addScriptDeclaration($swfUploadHeadJs);
	}
	
	function generateForm() {
		$id = rand(1000,9999);
		$form = '
		<div id="swfuploader">
			<form id="form-'.$id.'" action="index.php" method="post" enctype="multipart/form-data">
				<fieldset class="adminform">
					<div class="fieldset flash" id="fsUploadProgress">
						<span class="legend">Upload Queue</span>
					</div>
					<div id="divStatus">0 Files Uploaded</div>
					<div>
						<span id="spanButtonPlaceHolder"></span>
						<input id="btnCancel" type="button" value="Cancel All Uploads" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" />
					</div>
				</fieldset>
			</form>
		</div>
		';
		
		return $form;
	}
}