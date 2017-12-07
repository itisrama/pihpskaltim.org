<?php
		include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."config.php");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($stickers_language as $i => $l) {
			$stickers_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccstickers = (function () {

		var title = '<?php echo $stickers_language[0];?>';
		var height = <?php echo $stiHeight;?>;
		var width = <?php echo $stiWidth;?>;
		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var caller = '';
				var windowMode = 0;
				var baseUrl = $.cometchat.getBaseUrl();
				var baseData = $.cometchat.getBaseData();
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(typeof(params.caller) != "undefined") {
					caller = params.caller;
				}
				if(chatroommode == 1){
					loadCCPopup(baseUrl+'plugins/stickers/index.php?chatroommode=1&id='+id+'&basedata='+baseData+'&caller='+caller, 'stickers',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0,width="+width+",height="+height,width,height,'<?php echo $stickers_language[1];?>',null,null,null,null,windowMode);
				} else {
					loadCCPopup(baseUrl+'plugins/stickers/index.php?id='+id+'&basedata='+baseData+'&caller='+caller, 'stickers',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+width+",height="+height,width,height,'<?php echo $stickers_language[1];?>',null,null,null,null,windowMode);
				}
			},
			sendStickerMessage: function(params) {
				var baseUrl = $.cometchat.getBaseUrl();
				var baseData = $.cometchat.getBaseData();
				var to = params.to;
				var key = params.key;
				var chatroommode = params.chatroommode;
				var category = params.category;
				var caller = params.caller;
				var calleeAPI = '<?php echo $theme;?>';
				$.ajax({
					url: baseUrl+'plugins/stickers/index.php?action=sendSticker&callback=?',
					type: 'GET',
					data: {to: to, key: key, basedata: baseData, chatroommode: chatroommode, category: category, caller: caller},
					dataType: 'jsonp',
					success: function(data) {
						if(data != null && typeof(data) != 'undefined'){
							if(chatroommode == 1){
								if(typeof (jqcc[calleeAPI].addChatroomMessage)!=='undefined'){
									jqcc[calleeAPI].addChatroomMessage(jqcc.cometchat.getChatroomVars('myid'), data.m,data.id,1,Math.floor(new Date().getTime()),'0','1');
								}
								if(mobileDevice){
									jqcc[calleeAPI].closeModule('stickers');
									$('#currentroom').find('.cometchat_userchatarea').css('display','block');
									setTimeout(function(){
										$('#currentroom_convo').css('height',$(window).height()-($('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+$('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+$('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
									}, 10);
								}
							} else {
								jqcc.cometchat.chatWith(to);
								var controlparameters = {"type":"plugins", "name":"ccstickers", "method":"appendStickerMessage", "params":{"to":to, "data":data, "chatroommode": chatroommode, "caller": caller}};
								controlparameters = JSON.stringify(controlparameters);
								if(typeof(window.opener) == 'undefined' || window.opener == null){
									parent.postMessage('CC^CONTROL_'+controlparameters,'*');
								}else{
									window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
								}
								if(mobileDevice){
									jqcc[calleeAPI].closeModule('stickers');
				                    $('#cometchat_user_'+to+'_popup').find('.cometchat_userchatarea').css('display','block');
				                    setTimeout(function(){
				                        $('#cometchat_tabcontenttext_'+to).css('height',$(window).height()-(jqcc('#cometchat_user_'+to+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+to+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+to+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
				                    }, 10);
								}

							}

						}
					},
					error: function(data) {

					}
				});
			},
			appendStickerMessage: function(controlparameters) {
				var to = controlparameters.to;
				var data = controlparameters.data;
				var chatroommode = controlparameters.chatroommode;
				var calleeAPI = '<?php echo $theme;?>';
				if(chatroommode == 1){
					if(typeof (jqcc[calleeAPI].addChatroomMessage)!=='undefined'){
						jqcc[calleeAPI].addChatroomMessage(jqcc.cometchat.getChatroomVars('myid'), data.m,data.id,1,Math.floor(new Date().getTime()),'0','0');
					}
				} else {
					if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                        jqcc[calleeAPI].addMessages([{"from": to, "message": data.m, "id": data.id, "broadcast": 0}]);
                        if(mobileDevice){
                        	var tabcontenttext_height = ($(window).height()*30)/100;
                        	jqcc('#cometchat_tabcontenttext_'+to).css('height',tabcontenttext_height);
                        }
                    }
				}
			},
			processControlMessage: function(controlparameters) {
				var baseUrl = $.cometchat.getBaseUrl();
				var category = controlparameters.category;
				var imageName = controlparameters.key+'.png';
				var message = "<img class=\"cometchat_stickerImage\" type=\"image\" src=\""+baseUrl+"plugins/stickers/images/"+category+"/"+imageName+"\" />";
				return message;
			}
        };
    })();

})(jqcc);
