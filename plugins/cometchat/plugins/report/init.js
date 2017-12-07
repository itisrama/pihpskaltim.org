<?php

		include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
			include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
		}

		foreach ($report_language as $i => $l) {
			$report_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){

	$.ccreport = (function () {

		var title = '<?php echo $report_language[0];?>';
		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);

        return {

			getTitle: function() {
				return title;
			},

			init: function (params) {
				var id = params.to;
				var chatroommode = params.chatroommode;
				var mode = params.mode;
				if(typeof(mode) !== "undefined" && mode !== 'mobilewebapp') {
					chatroommode = mode;
				}
				var caller = '';
				if(typeof(params.caller) != "undefined") {
					caller = params.caller;
				}
				if ($("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").html() != '') {
					baseData = $.cometchat.getBaseData();
					baseUrl = $.cometchat.getBaseUrl();
					if (mobileDevice != null) {
						window.open(baseUrl+'plugins/report/index.php?id='+id+'&basedata='+baseData+'&callback=mobilewebapp');
					} else {
						if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'){
							loadCCPopup(baseUrl+'plugins/report/index.php?id='+id+'&basedata='+baseData, 'report',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $report_language[1];?>',0,0,0,0,1);
						}else{
							loadCCPopup(baseUrl+'plugins/report/index.php?id='+id+'&caller='+caller+'&basedata='+baseData, 'report',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $report_language[1];?>');
						}
					}
				} else {
					alert('<?php echo $report_language[5];?>');
				}

			}

        };
    })();

})(jqcc);