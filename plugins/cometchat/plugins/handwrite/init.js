<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($handwrite_language as $i => $l) {
			$handwrite_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.cchandwrite = (function () {

		var title = '<?php echo $handwrite_language[0];?>';

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var roomname  = params.roomname;
				var windowMode = 0;
				var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(chatroommode == 1 && mobileDevice == null) {
					baseUrl = $.cometchat.getBaseUrl();
					basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/handwrite/index.php?chatroommode=1&id='+id+'&basedata='+basedata+'&sendername='+roomname, 'handwrite',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=330,height=250",330,250,'<?php echo $handwrite_language[0];?>',0,1,1,1,windowMode);
				} else if(chatroommode == 1 && mobileDevice != null) {
					baseUrl = $.cometchat.getBaseUrl();
					basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/handwrite/index.php?chatroommode=1&id='+id+'&basedata='+basedata+'&sendername='+roomname, 'handwrite',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=330,height=250",330,250,'<?php echo $handwrite_language[0];?>',0,1,1,1,1);
				} else if(chatroommode != 1 && mobileDevice != null){
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/handwrite/index.php?id='+id+'&basedata='+baseData+'&sendername='+jqcc.cometchat.getName(jqcc.cometchat.getThemeVariable('userid')), 'handwrite',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=330,height=250",330,250,'<?php echo $handwrite_language[0];?>',0,1,1,1,1);
				} else {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/handwrite/index.php?id='+id+'&basedata='+baseData+'&sendername='+jqcc.cometchat.getName(jqcc.cometchat.getThemeVariable('userid')), 'handwrite',"status=0,toolbar=0,menubar=0,directories=0,resizable=1,location=0,status=0,scrollbars=0, width=330,height=250",330,250,'<?php echo $handwrite_language[0];?>',0,1,1,1,windowMode);
				}
			}

        };
    })();

})(jqcc);