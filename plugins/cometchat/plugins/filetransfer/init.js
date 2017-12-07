<?php
include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}
foreach ($filetransfer_language as $i => $l) {
	$filetransfer_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){
	$.ccfiletransfer = (function() {
		var title = '<?php echo $filetransfer_language[0];?>';
		var aws_storage = '<?php echo AWS_STORAGE;?>';
		var aws_bucket_url = '<?php echo $aws_bucket_url;?>';
		var bucket_path = '<?php echo $bucket_path;?>';

		return {
			getTitle: function() {
				return title;
			},
			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var roomname = params.roomname;
				var caller = '';
				var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
				if(typeof(params.caller) != "undefined") {
					caller = params.caller;
				}
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(chatroommode == 1 && mobileDevice == null) {
					var baseUrl = $.cometchat.getBaseUrl();
					var basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/filetransfer/index.php?chatroommode=1&caller='+caller+'&id='+id+'&basedata='+basedata+'&sendername='+roomname, 'filetransfer',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=400,height=140",400,130,'<?php echo $filetransfer_language[1];?>',null,null,null,null,windowMode);
				} else if(chatroommode == 0 &&mobileDevice == null){
					var baseUrl = $.cometchat.getBaseUrl();
					var baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/filetransfer/index.php?id='+id+'&caller='+caller+'&basedata='+baseData+'&sendername='+jqcc.cometchat.getName(jqcc.cometchat.getThemeVariable('userid')), 'filetransfer',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=400,height=140",400,130,'<?php echo $filetransfer_language[1];?>',null,null,null,null,windowMode);
				} else if(chatroommode == 0 && mobileDevice != null){
					var baseUrl = $.cometchat.getBaseUrl();
					var baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/filetransfer/index.php?id='+id+'&caller='+caller+'&basedata='+baseData+'&sendername='+jqcc.cometchat.getName(jqcc.cometchat.getThemeVariable('userid')), 'filetransfer',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=400,height=140",400,130,'<?php echo $filetransfer_language[1];?>',null,null,null,null,1);
				} else if(chatroommode == 1 && mobileDevice != null){
					var baseUrl = $.cometchat.getBaseUrl();
					var basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/filetransfer/index.php?chatroommode=1&caller='+caller+'&id='+id+'&basedata='+basedata+'&sendername='+roomname, 'filetransfer',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=400,height=140",400,130,'<?php echo $filetransfer_language[1];?>',null,null,null,null,1);
				}
			},
			viewMedia: function (params) {
				var baseUrl = $.cometchat.getBaseUrl(),
					mediaContentData = '',
					url = '';
				if(aws_storage == '1') {
					url = '//'+aws_bucket_url+'/'+bucket_path+'filetransfer/';
				}else {
					url = baseUrl+'writable/filetransfer/uploads/';
				}
				if(params.mediatype == 1){
					mediaContentData = '<img class="cometchat_filetransfer_data cometchat_filetransfer_image" md5fileName="'+params.md5file+'" fileName="'+params.file+'" src= "'+url+params.md5file+'">';
				} else if(params.mediatype == 2){
					mediaContentData = '<video class="cometchat_filetransfer_data" width="360" height="260" md5fileName="'+params.md5file+'" fileName="'+params.file+'" controls autoplay><source src="'+url+params.md5file+'" ></video>';
				} else if(params.mediatype == 3){
					mediaContentData = '<audio class="cometchat_filetransfer_data" md5fileName="'+params.md5file+'" fileName="'+params.file+'" controls><source src="'+url+params.md5file+'"></audio>';
				}
				if($('.cometchat_filetransfer_data').length == 0){
					jqcc('body').find('.cometchat_media_container').append(mediaContentData);
				}
				$('.cometchat_filetransfer_modal div').css('visibility','visible');
  				$('.cometchat_filetransfer_overlay').addClass('cometchat_filetransfer_overlay_show');
			}
		};
	})();
})(jqcc);

jqcc(function(){
	var baseUrl = '';
	var intervalCount = 0;
	var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);
	if(mobileDevice==null){
		var fileTransferinterval = setInterval(function () {
			if(typeof(jqcc.cometchat.getBaseUrl)=='function') {
				baseUrl = jqcc.cometchat.getBaseUrl();
			var overlay = '<div class="cometchat_filetransfer_overlay"><div class="cometchat_filetransfer_modal"><div><div class="cometchat_filetransfer_content"><div class="cometchat_media_container"></div></div><img class="cometchat_filetransfer_download" src="'+baseUrl+'images/download.png" title="<?php echo $filetransfer_language[13];?>"><img class="close_dialog" src="'+baseUrl+'images/close.png" title="<?php echo $filetransfer_language[12];?>"></div></div></div>';
				if(jqcc('#cometchat').length >= 1 && jqcc('#cometchat').find('.cometchat_filetransfer_overlay').length <= 0) {
					jqcc('#cometchat').append(overlay);
				} else if(jqcc('#cometchat').length == 0 && jqcc('body').find('.cometchat_filetransfer_overlay').length <= 0) {
					jqcc('body').append(overlay);
				}
			}
			if (++intervalCount === 5 || typeof(jqcc.cometchat.getBaseUrl)=='function') {
			   window.clearInterval(fileTransferinterval);
			}
		}, 1000);
	}
	jqcc('.cometchat_filetransfer_download').live('click',function(){
		var file = jqcc('.cometchat_filetransfer_data').attr('fileName');
		var md5file = jqcc('.cometchat_filetransfer_data').attr('md5fileName');
		location.href = baseUrl+"plugins/filetransfer/download.php?file="+md5file+"&unencryptedfilename="+file+"";
	});
	jqcc('.close_dialog').live('click',function(){
		jqcc('.cometchat_filetransfer_modal div').css('visibility','hidden');
		jqcc('.cometchat_filetransfer_overlay').removeClass('cometchat_filetransfer_overlay_show');
		jqcc('body').find('.cometchat_media_container').html('');

	});
	jqcc('.mediamessage').live('click',function(e){
		if(mobileDevice==null){
			e.preventDefault();
			var file = jqcc(this).attr('filename');
			var md5file = jqcc(this).attr('encfilename');
			var mediatype = jqcc(this).attr('mediatype');
			if((typeof(parent) != 'undefined' && parent != null && parent != self) || window.top != window.self){
				var controlparameters = {"type":"plugins", "name":"ccfiletransfer", "method":"viewMedia", "params":{"file":file, "md5file":md5file, "mediatype":mediatype}};
				controlparameters = JSON.stringify(controlparameters);
				parent.postMessage('CC^CONTROL_'+controlparameters,'*');
			} else {
				var controlparameters = {"file":file, "md5file":md5file, "mediatype":mediatype};
		        jqcc.ccfiletransfer.viewMedia(controlparameters);
			}
		} else {
			var downloadLink = jqcc(this).attr('href');
			window.open(downloadLink);
		}
	});
});
