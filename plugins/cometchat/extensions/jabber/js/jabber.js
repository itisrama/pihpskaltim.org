<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

?>

function login_gtalk(session,username,caller) {
	var currenttime = new Date();
	currenttime = parseInt(currenttime.getTime()/1000);

	jqcc.getJSON("<?php echo $cometchatServer;?>j?json_callback=?", {action:'login', username: username, password: session, session_key: session, server: '<?php echo $jabberServer;?>', port: '<?php echo $jabberPort;?>', id: '<?php echo $gtalkAppId;?>', key: '<?php echo $gtalkSecretKey;?>'} , function(data){

		if (data[0].error == '0') {
			$.cookie('cc_jabber','true',{ path: '/' });
			$.cookie('cc_jabber_id',data[0].msg,{ path: '/' });
			$.cookie('cc_jabber_type','gtalk',{ path: '/' });
			$('.container_body_2').remove();
			$('#gtalk_box').html('<span><?php echo $jabber_language[7];?></span>');

			setTimeout(function() {
				try {
					if(before == "parent") {
						parent.jqcc.ccjabber.process(caller);
						parent.closeCCPopup('jabber');
					} else if(before == "window.opener") {
						window.opener.jqcc.ccjabber.process(caller);
						window.close();
					} else {
						parentSandboxBridge.jqcc.ccjabber.process();
						parentSandboxBridge.closeCCPopup('jabber');
					}
				} catch (e) {
					crossDomain();
				}
			}, 4000);
			var controlparameters = {"type":"extensions", "name":"jabber", "method":"login_gtalk", "params":{}};
            controlparameters = JSON.stringify(controlparameters);
			if((typeof(parent) != 'undefined' && parent != null && parent != self)) {
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
            	window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
		} else {
			alert('<?php echo $jabber_language[9];?>');
			$('#gtalk').css('display','block');
			$('#loader').css('display','none');
		}
	});
	return false;
}

/*	$(function() {
//	$.cookie('cc_jabber','false',{ path: '/' });
//	$.getJSON("<?php echo $cometchatServer;?>j?json_callback=?", {'action':'logout'});
});*/

function crossDomain() {
	var ts = Math.round((new Date()).getTime() / 1000);
	var baseUrl = '<?php echo BASE_URL; ?>';
	baseUrl = (typeof(baseUrl)=='undefined' || baseUrl.indexOf('http://') >= 0 || baseUrl.indexOf('https://') >= 0)? '':baseUrl+'/extensions/jabber';
	location.href= '//'+domain+baseUrl+'/chat.htm?ts='+ts+'&jabber='+$.cookie('cc_jabber')+'&jabber_type='+$.cookie('cc_jabber_type')+'&jabber_id='+$.cookie('cc_jabber_id');
}

// Copyright (c) 2006 Klaus Hartl (stilbuero.de)
// http://www.opensource.org/licenses/mit-license.php

jqcc.cookie=function(a,b,c){if(typeof b!='undefined'){c=c||{};if(b===null){b='';c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=$.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};
