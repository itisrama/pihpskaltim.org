<?php

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($transliterate_language as $i => $l) {
	$transliterate_language[$i] = str_replace("'", "\'", $l);
}
?>

/*
	* CometChat
	* Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

 (function($){
 	$.cctransliterate = (function () {
 		var title = '<?php echo $transliterate_language[0];?>';
 		var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);
 		var transliterationControl;
        var options = {
            sourceLanguage: 'en',
            destinationLanguage: ['hi','ar','kn','ml','ta','te'],
            transliterationEnabled: true,
            shortcutKey: 'ctrl+g'
        };
 		return {

 			getTitle: function() {
 				return title;
 			},

 			init: function (params) {
 				var id = params.to;
				var chatroommode = params.chatroommode;
				var windowMode = 0;
				var callerUrl = "";
				if(typeof(params.caller) != "undefined") {
					callerUrl = "caller="+params.caller+"&";
				}
				if(typeof(params.windowMode) == "undefined") {
					windowMode = 0;
				} else {
					windowMode = 1;
				}
 				if(chatroommode == 1 && mobileDevice == null) {
	 				baseUrl = $.cometchat.getBaseUrl();
	 				basedata = $.cometchat.getBaseData();
	 				loadCCPopup(baseUrl+'plugins/transliterate/index.php?'+callerUrl+'chatroommode=1&id='+id+'&basedata='+basedata, 'transliterate',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $transliterate_language[0];?>',null,null,null,null,windowMode);
 				}else if(chatroommode == 1 && mobileDevice != null) {
	 				jqcc.cctransliterate.onLoad({"to":id, "chatroommode": "1", "caller": jqcc.cometchat.getUserID()});
 				} else if(chatroommode == 0 && mobileDevice == null) {
	 				baseUrl = $.cometchat.getBaseUrl();
	 				baseData = $.cometchat.getBaseData();
	 				loadCCPopup(baseUrl+'plugins/transliterate/index.php?'+callerUrl+'id='+id+'&basedata='+baseData, 'transliterate',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=430,height=220",430,175,'<?php echo $transliterate_language[0];?>',null,null,null,null,windowMode);
 				} else if(chatroommode == 0 && mobileDevice != null){
 					jqcc.cctransliterate.onLoad({"to":id, "chatroommode": "0", "caller": jqcc.cometchat.getUserID()});
 				}
 			},

 			setTitle: function(params) {
 				var lang = params.lang;
 				if(typeof(params.formatLang) == 'undefined'){
					$(document.getElementById("cometchat_container_transliterate")).find( ".cometchat_container_title span" ).html(lang);
 				} else {
 					$(document.getElementById("cometchat_container_transliterate")).find( ".cometchat_container_title span" ).html(lang + ' : ' + params.formatLang);
 				}
 			},

 			appendMessage: function(params) {
 				var to = params.to;
 				var data = params.data;
 				var chatroommode = params.chatroommode;
 				var e = {'keyCode':13, 'shiftKey':0};
 				if(chatroommode == 1){
 					if(jqcc('#currentroom').length != 0) {
 						jqcc('#currentroom .cometchat_textarea').focus();
	 					jqcc('#currentroom .cometchat_textarea').val(data);
						jqcc('#currentroom .cometchat_tabcontentsubmit').click();
					}
 				} else {
 					if(jqcc('#cometchat_user_'+to+'_popup').length != 0) {
 						var theme = jqcc.cometchat.getSettings().theme;
 						jqcc('#cometchat_user_'+to+'_popup .cometchat_textarea').focus();
 						jqcc('#cometchat_user_'+to+'_popup .cometchat_textarea').val(data);
 						jqcc[theme].chatboxKeydown(e,'#cometchat_user_'+to+'_popup .cometchat_textarea',to);
					}
 				}
 			},

	      onLoad: function(params) {
	      	if(params.chatroommode == 0){
		        var ids = [ "cometchat_textarea_"+params.to ];
		        var languageSelect = document.getElementById('selectlanguage_'+params.to);
		    } else{
		    	var ids = [ "cometchat_textarea"];
		    	var languageSelect = document.getElementById('selectlanguage');
		    }
		    if(languageSelect.length <= 1){
		    	google.load("elements", "1", {
		            packages: "transliteration"
		          });
		      	transliterationControl = new google.elements.transliteration.TransliterationControl(options);
		        transliterationControl.makeTransliteratable(ids);

		        var destinationLanguage =
		          transliterationControl.getLanguagePair().destinationLanguage;

		        var supportedDestinationLanguages =
		        google.elements.transliteration.getDestinationLanguages(google.elements.transliteration.LanguageCode.ENGLISH);
		        for (var lang in supportedDestinationLanguages) {
		          var opt = document.createElement('option');
		          opt.text = lang;
		          opt.value = supportedDestinationLanguages[lang];
		          if (destinationLanguage == opt.value) {
		            opt.selected = true;
		          }
		          try {
		            languageSelect.add(opt, null);
		          } catch (ex) {
		            languageSelect.add(opt);
		          }
		        }
		    }

	        if (document.createEvent) {
                var e = document.createEvent("MouseEvents");
                e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                if(params.chatroommode == 0){
                	jqcc('#selectlanguage_'+params.to)[0].dispatchEvent(e);
                } else{
                	jqcc('#selectlanguage')[0].dispatchEvent(e)
                }
            } else if (element.fireEvent) {
            	if(params.chatroommode == 0){
                	jqcc('#selectlanguage_'+params.to)[0].fireEvent("onmousedown");
                } else{
                	jqcc('#selectlanguage')[0].fireEvent("onmousedown");
                }
            }


	      },

	      languageChangeHandler: function(language){
	      	if(language == 'no'){
	      		transliterationControl.disableTransliteration();
	      		jqcc('.cometchat_textarea').removeAttr('dir');
	      	} else{
	      		transliterationControl.enableTransliteration();
		      	languageSelect = language;
		        transliterationControl.setLanguagePair(google.elements.transliteration.LanguageCode.ENGLISH,
		            languageSelect);
		    }
	      }

 		};
 	})();
 })(jqcc);

 function chatboxKeydown(event) {
	if(event.keyCode == 13 && event.shiftKey == 0)  {
		pushcontents();
	}
}

function pushcontents() {
	var data = document.getElementById('cometchat_textarea').value;
	document.getElementById('cometchat_textarea').value = '';
	var controlparameters = {"type":"plugins", "name":"cctransliterate", "method":"appendMessage", "params":{"to":"{$toId}", "data":data}};
	controlparameters = JSON.stringify(controlparameters);
	if(typeof(window.opener) == 'undefined' || window.opener == null){
		parent.postMessage('CC^CONTROL_'+controlparameters,'*');
	}else{
		window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
	}
	setTimeout('document.getElementById(\'cometchat_textarea\').focus()',100);
	setTimeout('document.getElementById(\'cometchat_textarea\').focus()',1000);
}

jqcc(document).ready(function() {
	jqcc('.selectlanguage').live('change',function(){
		id = jqcc.cometchat.getThemeVariable('openChatboxId');
		jqcc.cctransliterate.languageChangeHandler($(this).val());
	});
});
