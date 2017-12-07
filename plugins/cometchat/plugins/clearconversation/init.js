<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($clearconversation_language as $i => $l) {
	$clearconversation_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */

 (function($){

 	$.ccclearconversation = (function () {

 		var title = '<?php echo $clearconversation_language[0];?>';
 		var type = '<?php echo $isDelete;?>';

 		return {

 			getTitle: function() {
 				return title;
 			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;

 				if(chatroommode == 1) {
 					if ($("#currentroom_convotext").html() != '') {
 						baseUrl = $.cometchat.getBaseUrl();
 						basedata = $.cometchat.getBaseData();
						var currentroom = params.roomid;
						var lastid = parseInt($('#currentroom_convotext').find('.cometchat_chatboxmessage:last').attr('id').replace('cometchat_message_',''));
 						$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&basedata='+basedata+'&chatroommode=1&callback=?', {clearid: currentroom, lastid: lastid});
 						$("#currentroom_convotext").html('');
 					}
 				} else {
 					var settings = jqcc.cometchat.getSettings();
 					if ($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").html() != '') {
 						baseUrl = $.cometchat.getBaseUrl();
 						baseData = $.cometchat.getBaseData();
 						if(type == 0 && settings.theme!='tapatalk'){
 							$.getJSON(baseUrl+'plugins/clearconversation/index.php?action=clear&callback=?', {clearid: id, basedata: baseData});
 						}else{
 							$.ajax({
                                    url: baseUrl+'plugins/clearconversation/index.php?action=delete&callback=?',
                                    data: {deleteid: id, basedata: baseData},
                                    dataType: 'jsonp',
                                    type: 'get',
                                    timeout: '10000'
                            });
 						}
 						if($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").length == 0){
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_messagebox").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_time").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_time").remove();
 						}else{
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_chatboxmessage").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > table.cometchat_iphone").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_time").remove();
 							$("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext > div.cometchat_message_container > div.cometchat_time").remove();
 						}
 					}
 				}
 			}

 		};
 	})();

 })(jqcc);