<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

foreach ($broadcastmessage_language as $i => $l) {
	$broadcastmessage_language[$i] = str_replace("'", "\'", $l);
}
?>
if (typeof($) === 'undefined') {
	$ = jqcc;
}
if (typeof(jQuery) === 'undefined') {
	jQuery = jqcc;
}
$(document).ready(function() {
	jqcc("#inviteuserboxes").slimScroll({scroll: '1', height: jqcc("#inviteuserboxes").css('height')});
	$(document).find("textarea.cometchat_textarea").keypress(function(event){
		broadcastBoxKeydown(event, this);
	});

	$(document).find("textarea.cometchat_textarea").keydown(function(event){
		if (event.keyCode == 8 || event.keyCode == 46) {
			if($(document).find("textarea.cometchat_textarea").val()==""){
				$(document).find("textarea.cometchat_textarea").css('height','16px');
				broadcastWindowResize();
			}
		}
	});

	$('#cometchat_broadcastMessage_submit').live("click",function(e1){
		e1.stopPropagation();
		addbroadcastmsg();
	});

	$('.cometchat_tabcontentsubmit').live("click",function(e1){
		if(!$('.cometchat_textarea').hasClass('placeholder')){
			e1.stopPropagation();
			addbroadcastmsg();
		}
	});

	var userSelectionrunning = false;
	$('#cc_refreshbroadcastusers').live("click",function(e1){
		if(!userSelectionrunning){
			userSelectionrunning = true;
			e1.stopPropagation();
			cc_deselectallusers();
			$.ajax({
				url: "index.php?action=userSelection",
				dataType: 'jsonp',
				success: function (data) {
					userSelectionrunning = false;
					var buddyList = data.buddyList;
					var status = data.status;

					var s = [];
					s['available'] = '';
					s['away'] = '';
					s['busy'] = '';
					s['offline'] = '';

					$.each( buddyList, function( key, value ) {
						s[value.s] += '<div class="invite_1"><div class="invite_2" onclick="javascript:document.getElementById(\'check_'+value.id+'\').checked = document.getElementById(\'check_'+value.id+'\').checked?false:true;"><img height=30 width=30 src="'+value.a+'" /></div><div class="invite_3" onclick="javascript:document.getElementById(\'check_'+value.id+'\').checked = document.getElementById(\'check_'+value.id+'\').checked?false:true;"><span class="invite_name">'+value.n+'</span><br/><span class="invite_5">'+status[value.s]+'</span></div><input type="checkbox" name="to[]" value="'+value.id+'" id="check_'+value.id+'" class="invite_4" /></div>';
					});

					var inviteContent = s['available']+""+s['away']+""+s['offline'];
					inviteContent = inviteContent.trim();
					if(inviteContent == '' || inviteContent ==null){
						inviteContent = '<div style= "padding-top:6px">'+'<?php echo $broadcastmessage_language[2]?>'+'</div>';
					}

					$(document).find('#inviteuserboxes').html('');
					$(document).find('#inviteuserboxes').html(inviteContent);

					searchbroadcastusers();

					if(!($(document).find("#ccbroadcastuserrel").is(':animated'))) {
						$(document).find("#ccbroadcastuserrel").fadeIn().delay( 500 ).fadeOut('slow');
					}else{
						$(document).find("#ccbroadcastuserrel").clearQueue();
					}
				},
				error: function(data){
					userSelectionrunning = false;
				}
			});
		}
	});

	$('[placeholder]').focus(function() {
		var input = $(this);
		if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.removeClass('placeholder');
		}
	}).blur(function() {
		var input = $(this);
		if (input.val() == '') {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		}
	}).blur();
	$('[placeholder]').parents('form').submit(function() {
		$(this).find('[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
				input.val('');
			}
		});
	});

	$('#cometchat_broadcastsearchbar').find('#cometchat_broadcastsearch').keyup(function(){
		searchbroadcastusers();
	});

	broadcastWindowResize();

});

function broadcastBoxKeydown(event,chatboxtextarea) {
	if (event.keyCode == 13 && event.shiftKey == 0 && !$(chatboxtextarea).hasClass('placeholder'))  {
		event.preventDefault();
		event.stopPropagation();
		addbroadcastmsg(event);
	}
	var adjustedHeight = chatboxtextarea.clientHeight;
	var maxHeight = 94;
	var height = $(document).find("div.inviteuserboxes").css('height');
	var heightbody = $(document).find("body").css('height');
	if (maxHeight > adjustedHeight) {
		adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
		if (maxHeight){
			adjustedHeight = Math.min(maxHeight, adjustedHeight);
		}
		if (adjustedHeight > chatboxtextarea.clientHeight) {
			$(chatboxtextarea).css('height', (adjustedHeight)+'px');
			var newheight = parseInt(heightbody) - (parseInt(adjustedHeight) +26 +11 +30);/*26 topbar, 11 textareapadding & 30 searchbar*/
			$(document).find("div.inviteuserboxes").css('height', newheight+'px');
			$(document).find("div.slimScrollDiv").css('height', newheight+'px');
		}
	} else {
		$(chatboxtextarea).css('overflow-y','auto');
	}
}

function confirmBroadcast() {
	if (confirm("<?php echo $broadcastmessage_language[9]?>") == true) {
		return true;
	} else {
		return false;
	}
}

function addbroadcastmsg(event) {
	var message = $('#cometchat_broadcastMessage_textarea').val().trim();
	var addmsg = $('#cometchat_broadcastMessage_textarea').attr('addmsg');
	var caller = $('#cometchat_broadcastMessage_textarea').attr('caller');
	var inviteids = Array();
	$(document).find('input[type="checkbox"]:checked').each(function(index){
		inviteids[index] = $(this).val();
	});
	if(inviteids.length < 1){
		alert("<?php echo $broadcastmessage_language[3]?>");
		return;
	}

	var toids = inviteids.join();
	if(message!=""&&message!=null && inviteids.length > 0){
		if(!confirmBroadcast()){
			return;
		}
		$(document).find("textarea.cometchat_textarea").val('');
		var basedata = "<?php echo $_REQUEST['basedata']?>";
		$.ajax({
			url: "index.php?action=sendbroadcast",
			data: {broadcastmessage: 1,to: toids,message: message,basedata:basedata},
			dataType:'jsonp',
			success: function (data) {
				if(data != null && data != 'undefined'){
					$.each( data, function( key, value ) {
						var controlparameters = {"type":"modules", "name":"cometchat", "method":"addMessage", "params":{"from":parseInt(value.from), "message":value.m, "messageId":value.id, "nopopup":"1", "caller":caller}};
						controlparameters = JSON.stringify(controlparameters);

						if(addmsg==1){
							if(typeof(parent) != 'undefined' && parent != null && parent != self){
								parent.postMessage('CC^CONTROL_'+controlparameters,'*');
							}else{
								window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
							}
						}
					});
				}
				$(document).find("textarea.cometchat_textarea").css('height','16px');
				broadcastWindowResize();
				$(document).find("#ccbroadcastsucc").fadeIn().delay( 500 ).fadeOut('slow');
				return;
			}
		});
	}

}

function cc_selectallusers() {
	$('input::not(:checked)').each(function(index){
		if($(this).parent().css('display') == 'block') {
	      this.click();
	    }
		$('#cc_selectallusers').hide();
		$('#cc_deselectallusers').css('display','inline-block');
	});
}
function cc_deselectallusers() {
	$('input:checked').each(function(index){
		if($(this).parent().css('display') == 'block') {
	      this.click();
	    }
		$('#cc_selectallusers').css('display','inline-block');
		$('#cc_deselectallusers').hide();
	});
}

function searchbroadcastusers(){
	var searchString = $('#cometchat_broadcastsearchbar').find('#cometchat_broadcastsearch').val();
	var inviteuserboxes = $('.cometchat_broadcastMessage').find('#inviteuserboxes');
	var uncheckedusers = 0;
	var availableusers = 0;
	searchString = searchString.trim();
	if(searchString.length>=1&&searchString!="<?php echo $broadcastmessage_language[4]?>"){
		availableusers = inviteuserboxes.find('div.invite_1').hide().parent().find('.invite_name:icontains("'+searchString+'")').parent().parent().show().length;

		$('input::not(:checked)').each(function(index){
		    if($(this).parent().css('display') == 'block') {
		      uncheckedusers++;
		    }
		});
	}else{
		inviteuserboxes.find('div.invite_1').show();
		$('input::not(:checked)').each(function(index){
		    uncheckedusers++;
		});
	}
	if(uncheckedusers > 1) {
		$('#cc_selectallusers').css('display','inline-block');
		$('#cc_deselectallusers').hide();
	} else {
		$('#cc_deselectallusers').css('display','inline-block');
		$('#cc_selectallusers').hide();
	}

	if(availableusers < 1 && searchString.length>=1 && searchString!="<?php echo $broadcastmessage_language[4]?>") {
		$('#cc_selectallusers').hide();
		$('#cc_deselectallusers').hide();
		$('.cc_separator').hide();
		if($(document).find('.cc_nousers').length == 0){
			$(document).find('#inviteuserboxes').append('<div class="cc_nousers" style= "padding-top:6px">'+'<?php echo $broadcastmessage_language[12]?>'+'</div>');
		}
	} else {
		$('.cc_separator').show();
		$(document).find('#inviteuserboxes').find('.cc_nousers').remove();
	}
}

function broadcastWindowResize() {
	var chatboxtextarea = $(document).find("#cometchat_broadcastMessage_textarea");
	var heightbody = bmgetWindowHeight();
	var adjustedHeight = chatboxtextarea.outerHeight(false);
	var maxHeight = 94;
	if (maxHeight){
		adjustedHeight = Math.min(maxHeight, adjustedHeight);
	}
	var newheight = parseInt(heightbody) - (parseInt(adjustedHeight) +26 +8 +30);/*26 topbar, 8 textareapadding & 30 searchbar*/
	$(document).find("div.inviteuserboxes").css('height', newheight+'px');
	$(document).find("div.slimScrollDiv").css('height', newheight+'px');
}

function bmgetWindowHeight() {
	var windowHeight = 0;
	if (typeof(window.innerHeight) == 'number') {
		windowHeight = window.innerHeight;
	} else {
		if (document.documentElement && document.documentElement.clientHeight) {
			windowHeight = document.documentElement.clientHeight;
		} else {
			if (document.body && document.body.clientHeight) {
				windowHeight = document.body.clientHeight;
			}
		}
	}
	return windowHeight;
}
window.onresize = function(event) {
	broadcastWindowResize();
};