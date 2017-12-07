<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($chathistory_language as $i => $l) {
			$chathistory_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccchathistory = (function () {

		var title = '<?php echo $chathistory_language[0];?>';
		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var callbackfn='';
				if(typeof(jqcc.cometchat)!="undefined" && typeof(jqcc.cometchat.getCcvariable) != "undefined"){
					if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'){
						callbackfn='&callbackfn=desktop';
					}
				}
				var id = params.to;
				var chatroommode = params.chatroommode;
				var windowMode = 0;
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
				if(chatroommode == 1 && mobileDevice == null) {
					baseUrl = $.cometchat.getBaseUrl();
					basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/chathistory/index.php?embed=web&chatroommode=1&logs=1&history='+id+'&basedata='+basedata+callbackfn, 'chathistory',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0,width=650,height=480",650,480,'<?php echo $chathistory_language[6];?>',null,null,null,null,windowMode);
				} else if(chatroommode == 1 && mobileDevice != null) {
					baseUrl = $.cometchat.getBaseUrl();
					basedata = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/chathistory/index.php?embed=web&chatroommode=1&logs=1&history='+id+'&basedata='+basedata+callbackfn, 'chathistory',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0,width=650,height=480",650,480,'<?php echo $chathistory_language[6];?>',null,null,null,null,1);
				} else if(mobileDevice != null) {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/chathistory/index.php?embed=web&history='+id+'&logs=1&basedata='+baseData+callbackfn, 'chathistory',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=650,height=480",650,480,'<?php echo $chathistory_language[6];?>',null,null,null,null,1);
				} else {
					baseUrl = $.cometchat.getBaseUrl();
					baseData = $.cometchat.getBaseData();
					loadCCPopup(baseUrl+'plugins/chathistory/index.php?embed=web&history='+id+'&logs=1&basedata='+baseData+callbackfn, 'chathistory',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=650,height=480",650,480,'<?php echo $chathistory_language[6];?>',null,null,null,null,windowMode);
				}
			}
        };
    })();

})(jqcc);