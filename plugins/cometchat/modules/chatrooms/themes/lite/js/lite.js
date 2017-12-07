<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/


?>

if (typeof(jqcc) === 'undefined') {
	jqcc = jQuery;
}
(function($) {
    var settings = {};
    settings = jqcc.cometchat.getcrAllVariables();
    var calleeAPI = jqcc.cometchat.getChatroomVars('calleeAPI');

    $.crlite = (function() {
            return {
                playsound: function() {
                        try	{
                            document.getElementById('messageBeep').play();
                        } catch (error) {
                            jqcc.cometchat.setChatroomVars('messageBeep',0);
                        }
                },
                sendChatroomMessage: function(chatboxtextarea) {
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height','18px');
                    var height = $[calleeAPI].crgetWindowHeight();
                    var contentDivHeight = height-parseInt($('.topbar').css('height'));
                    $("div.content_div").css('height',contentDivHeight);
                    var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                    var prependHeight = parseInt($('.cometchat_prependMessages_container').outerHeight(true));
                    $("#currentroom_convo").css('height',contentDivHeight-textareaHeight-prependHeight);
                    $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                    $(chatboxtextarea).css('overflow-y','hidden');
                    $(chatboxtextarea).focus();
                },
                createChatroom: function() {
                    $[calleeAPI].hidetabs();
                    jqcc.cometchat.setChatroomVars('currentroom',0);
                    var controlparameters = {"name":"open", "val":"0"};
                    jqcc.cometchat.setCrSessionVariable(controlparameters);
                    $('#createtab').addClass('tab_selected');
                    $('#create').css('display','block');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[5];?>');
                },
                getTimeDisplay: function(ts,id) {
                    var time = getTimeDisplay(ts);
                            return "<span class=\"cometchat_ts\">"+time.hour+":"+time.minute+time.ap+"</span>";
                },
                addChatroomMessage: function(fromid,incomingmessage,incomingid,selfadded,sent,fromname,calledfromsend,chatroomid) {
                    var todaysdate = new Date();
                    var tdmonth  = todaysdate.getMonth();
                    var tddate  = todaysdate.getDate();
                    var tdyear = todaysdate.getFullYear();
                    var today_date_class = tdmonth+"_"+tddate+"_"+tdyear;
                    var ydaysdate = new Date((new Date()).getTime() - 3600000 * 24);
                    var ydmonth  = ydaysdate.getMonth();
                    var yddate  = ydaysdate.getDate();
                    var ydyear = ydaysdate.getFullYear();
                    var yday_date_class = ydmonth+"_"+yddate+"_"+ydyear;
                    var d = '';
                    var month = '';
                    var date  = '';
                    var year = '';
                    var msg_date_class = '';
                    var msg_date = '';
                    var date_class = '';
                    var msg_date_format = '';

                    if(typeof(fromname) === 'undefined' || fromname == 0 || fromid == settings.myid){
                        fromname = '<?php echo $chatrooms_language[6]; ?>';
                    }
                    var temp = '';
                    var crUnreadMessages = jqcc.cometchat.getChatroomVars('crUnreadMessages');
                    var chatroomreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                    var receivedcrunreadmessages = jqcc.cometchat.getFromStorage('crreceivedunreadmessages');
                    if (calledfromsend != '1') {
                        settings.timestamp=incomingid;
                    }
                    var controlparameters = {"id":incomingid, "from":fromname, "fromid":fromid, "message":incomingmessage, "sent":sent};
                    separator = '<?php echo $chatrooms_language[7]; ?>';
                    var message = jqcc.cometchat.processcontrolmessage(controlparameters);

                    var msg_time = controlparameters.sent;
                    msg_time = msg_time+'';
                    if (msg_time.length == 10){
                        msg_time = parseInt(msg_time * 1000);
                    }

                    var months_set = new Array();

                    <?php
                    $months_array = array($chatrooms_language[90],$chatrooms_language[91],$chatrooms_language[92],$chatrooms_language[93],$chatrooms_language[94],$chatrooms_language[95],$chatrooms_language[96],$chatrooms_language[97],$chatrooms_language[98],$chatrooms_language[99],$chatrooms_language[101],$chatrooms_language[102]);

                    foreach($months_array as $key => $val){
                        ?>
                        months_set.push('<?php echo $val; ?>');
                        <?php
                    }
                    ?>

                    d = new Date(parseInt(msg_time));
                    month  = d.getMonth();
                    date  = d.getDate();
                    year = d.getFullYear();
                    msg_date_class = month+"_"+date+"_"+year;
                    msg_date = months_set[month]+" "+date+", "+year;

                    var type = 'th';
                    if(date==1||date==21||date==31){
                        type = 'st';
                    }else if(date==2||date==22){
                        type = 'nd';
                    }else if(date==3||date==23){
                        type = 'rd';
                    }
                    msg_date_format = date+type+' '+months_set[month]+', '+year;

                    if(msg_date_class == today_date_class){
                        date_class = "today";
                        msg_date = '<?php echo $chatrooms_language[103]; ?>';
                    }else  if(msg_date_class == yday_date_class){
                        date_class = "yesterday";
                        msg_date = '<?php echo $chatrooms_language[104]; ?>';
                    }

                    if(message != '' && chatroomid == jqcc.cometchat.getChatroomVars('currentroom')) {
                        if ($("#cometchat_message_"+incomingid).length > 0) {
                                $("#cometchat_message_"+incomingid).find("span.cometchat_chatboxmessagecontent").html(message);
                        } else {
                            sentdata = '';
                            if (sent != null) {
                                var ts = new Date(parseInt(sent));
                                sentdata = $[calleeAPI].getTimeDisplay(ts,incomingid);
                            }
                            if (fromid != settings.myid) {
                                temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage" id="cometchat_message_'+incomingid+'"><div class="cometchat_chatboxmessagefrom"></div><div class="cometchat_messagearrow"></div>');

                                temp += ('<div class="cometchat_chatboxmessagecontent"><div><strong>');
                                if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && fromid != 0) {
                                    temp += ('<a id="fromname" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');">');
                                }
                                temp += fromname+separator;
                                if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && fromid != 0) {
                                    temp += ('</a>');
                                }
                                temp += ('</strong></div><span class="chatroom_msg">'+message+'</span>'+sentdata+'</div></div>');
                            } else {
                                var selfstyle = ' cometchat_self';
                                temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage '+selfstyle+'" id="cometchat_message_'+incomingid+'"><div class="cometchat_chatboxmessagefrom '+selfstyle+'"></div><div class="cometchat_messagearrow"></div><div class="cometchat_chatboxmessagecontent '+selfstyle+'"><div style="display:none;"><strong>'+fromname+separator+'</strong></div><span class="chatroom_msg">'+message+'</span> '+sentdata+'</div></div>');
                            }
                            $("#currentroom_convotext").append(temp);
                            if ($.cookie(jqcc.cometchat.getChatroomVars('cookiePrefix')+"sound") && $.cookie(jqcc.cometchat.getChatroomVars('cookiePrefix')+"sound") == 'true') { } else {
                                $[calleeAPI].playsound();
                            }
                        }
                    }

                    if(jqcc.cometchat.getChatroomVars('owner')|| jqcc.cometchat.getChatroomVars('isModerator') || (jqcc.cometchat.getChatroomVars('allowDelete') == 1 && fromid == settings.myid)) {
                        if ($("#cometchat_message_"+incomingid).find(".delete_msg").length < 1) {
                            jqcc('#cometchat_message_'+incomingid).find(".cometchat_ts").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incomingid+'\');">(<span class="hoverbraces"><?php echo $chatrooms_language[46]; ?></span>)</span>');
                        }
                        $(".cometchat_chatboxmessage").live("mouseover",function() {
                            $(this).find(".delete_msg").css('visibility','visible');
                        });
                        $(".cometchat_chatboxmessage").live("mouseout",function() {
                            $(this).find(".delete_msg").css('visibility','hidden');
                        });
                        $(".delete_msg").mouseover(function() {
                            $(this).css('visibility','visible');
                        });
                    }
                    var forced = (fromid == settings.myid) ? 1 : 0;
                    if((message).indexOf('<img')!=-1 && (message).indexOf('src')!=-1){
                        $( "#cometchat_message_"+incomingid+" img" ).load(function() {
                            $[calleeAPI].chatroomScrollDown(forced);
                        });
                    }else{
                        $[calleeAPI].chatroomScrollDown(forced);
                    }

                    if (message != '' && chatroomid != jqcc.cometchat.getChatroomVars('currentroom') && (typeof(receivedcrunreadmessages[chatroomid])=='undefined' || receivedcrunreadmessages[chatroomid] < incomingid)){
                        if(!crUnreadMessages.hasOwnProperty(chatroomid)){
                            crUnreadMessages[chatroomid] = 1;
                        } else {
                            var newUnreadMessages = parseInt(crUnreadMessages[chatroomid]) + 1;
                            crUnreadMessages[chatroomid] = newUnreadMessages;
                        }
                        $[calleeAPI].updateCRReceivedUnreadMessages(chatroomid,incomingid);
                    }
                    jqcc.cometchat.setChatroomVars('crUnreadMessages',crUnreadMessages);
                    receivedcrunreadmessages = jqcc.cometchat.getFromStorage('crreceivedunreadmessages');
                    $.each(crUnreadMessages, function(chatroomid,unreadMessageCount) {
                        var chatroomreadmessagesId = chatroomreadmessages[chatroomid];
                        var receivedcrunreadmessagesId = receivedcrunreadmessages[chatroomid];
                        if(receivedcrunreadmessagesId != 'undefined'){
                            if(receivedcrunreadmessagesId > chatroomreadmessagesId){
                                $[calleeAPI].chatroomUnreadMessages(jqcc.cometchat.getChatroomVars('crUnreadMessages'),chatroomid);
                            }
                        }
                    });
                    if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addMessageCounter) == "function" && fromid != settings.myid){
                        jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addMessageCounter();
                    }
                    if (settings.apiAccess == 1 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                        parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                    }
                    $[calleeAPI].updateCRReadMessages(jqcc.cometchat.getChatroomVars('currentroom'));
                    var crreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                    jqcc.cometchat.setChatroomVars('crreadmessages',crreadmessages);
                    $[calleeAPI].groupbyDate();
                },
                updateCRReadMessages: function(id){
                    if(typeof(id) == 'object'){
                        jqcc.each(id, function(chatroomId,lastmessage) {
                            chatroomId = chatroomId.replace('_','');
                            if(typeof (jqcc.cometchat.updateToStorage)!=='undefined'){
                                var alreadycrreadmessages = jqcc.cometchat.getFromStorage('crreadmessages');
                                if((typeof(alreadycrreadmessages[chatroomId])!='undefined' && parseInt(alreadycrreadmessages[chatroomId])<parseInt(lastmessage)) || typeof(alreadycrreadmessages[chatroomId])=='undefined'){
                                    var crreadmessages = {};
                                    crreadmessages[chatroomId] = parseInt(lastmessage);
                                    jqcc.cometchat.updateToStorage('crreadmessages',crreadmessages);
                                    jqcc.cometchat.setChatroomVars('crreadmessages',jqcc.cometchat.getFromStorage("crreadmessages"));
                                }
                            }
                        });
                    } else {
                        if($('#currentroom_convotext').find('.cometchat_chatboxmessage:last').length){
                            if(typeof (jqcc.cometchat.updateToStorage)!=='undefined'){
                                var alreadycrreadmessages = jqcc.cometchat.getFromStorage('crreadmessages');
                                var lastid = parseInt($('#currentroom_convotext').find('.cometchat_chatboxmessage:last').attr('id').replace('cometchat_message_',''));
                                if((typeof(alreadycrreadmessages[id])!='undefined' && parseInt(alreadycrreadmessages[id])<parseInt(lastid)) || typeof(alreadycrreadmessages[id])=='undefined'){
                                    var crreadmessages = {};
                                    crreadmessages[id] = parseInt(lastid);
                                    jqcc.cometchat.updateToStorage('crreadmessages',crreadmessages);
                                    jqcc.cometchat.setChatroomVars('crreadmessages',jqcc.cometchat.getFromStorage("crreadmessages"));
                                }
                            }
                        }
                    }
                },
                updateCRReceivedUnreadMessages: function(id,lastid){
                    if(typeof (jqcc.cometchat.updateToStorage)!=='undefined'){
                        var alreadycrreceivedmessages = jqcc.cometchat.getFromStorage('crreceivedunreadmessages');
                        if((typeof(alreadycrreceivedmessages[id])!='undefined' && parseInt(alreadycrreceivedmessages[id])<parseInt(lastid)) || typeof(alreadycrreceivedmessages[id])=='undefined'){
                            var crreceivedmessages = {};
                            crreceivedmessages[id] = parseInt(lastid);
                            jqcc.cometchat.updateToStorage('crreceivedunreadmessages',crreceivedmessages);
                        }
                    }
                },
                chatroomBoxKeyup: function(event,chatboxtextarea) {
                    var adjustedHeight = chatboxtextarea.clientHeight;
                    var maxHeight = 94;
                    var height = $[calleeAPI].crgetWindowHeight();

                    if (maxHeight > adjustedHeight) {
                        adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                        if (maxHeight)
                            adjustedHeight = Math.min(maxHeight, adjustedHeight);
                        if (adjustedHeight > chatboxtextarea.clientHeight) {
                            $(chatboxtextarea).css('height',adjustedHeight+6 +'px');
                            var contentDivHeight = height-parseInt($('.topbar').css('height'));
                            $("div.content_div").css('height',contentDivHeight);
                            var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                            var prependHeight = parseInt($('.cometchat_prependMessages_container').outerHeight(true));
                            $("#currentroom_convo").css('height',contentDivHeight-textareaHeight-prependHeight);
                            $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                            $[calleeAPI].chatroomScrollDown(1);
                        }
                    } else {
                        $(chatboxtextarea).css('overflow-y','auto');
                    }
                },
                hidetabs: function() {
                    $('#lobbytab').removeClass('tab_selected');
                    $('#createtab').removeClass('tab_selected');
                    $('#currentroomtab').find('span').removeClass('tab_selected');
                    $('#lobby').css('display','none');
                    $('#currentroom').css('display','none');
                    $('#create').css('display','none');
                    $('#plugins').css('display','none');
                },
                loadLobby: function(forced) {
                    $[calleeAPI].hidetabs();
                    jqcc.cometchat.setChatroomVars('currentroom',0);
                    $('#lobbytab').addClass('tab_selected');
                    $('#lobby').css('display','block');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[1];?>');
                    clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                    jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addMessageCounter();
                    jqcc.cometchat.chatroomHeartbeat(forced);
                },
                crcheckDropDown: function(dropdown) {
                    var id = dropdown.value;
                    if (id == 1) {
                        $('div.password_hide').css('display','block');
                    } else {
                        $('div.password_hide').css('display','none');
                    }
                },
                loadRoom: function() {
                    var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                    var roomno = jqcc.cometchat.getChatroomVars('currentroom');
                    var inviteLink = '';
                    var messageCounter = '0';
                    if(jqcc.cometchat.getChatroomVars('checkBarEnabled') == 1){
                        inviteLink = '<span> | </span><?php echo $chatrooms_language[48];?>';
                    }
                    $[calleeAPI].hidetabs();
                    $('#plugins').css('display','block');
                    $('#currentroom').css('display','block');
                    $('#currentroomtab').css('display','block');
                    $('#currentroomtab').find('.activeRoom_'+roomno).addClass('tab_selected');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[4];?>'+inviteLink+'<?php echo $chatrooms_language[39];?>');
                    if(jqcc('#createtab').length > 0){
                        var topbarWidth = jqcc('.topbar').outerWidth(true);
                        var lobbytabWidth = jqcc('#lobbytab').outerWidth(true);
                        var createtabWidth = jqcc('#createtab').outerWidth(true);
                        var currentroomtabWidth = parseInt(topbarWidth - lobbytabWidth - createtabWidth);
                        jqcc('#currentroomtab').css('width',currentroomtabWidth);
                    }

                    var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                    var room_no = '_'+roomno;
                    var chatroomdata = cc_chatroom.active;
                    if(!chatroomdata.hasOwnProperty(room_no)){
                        var chatroomData = {};
                        var controlparameters = {"name":"active", "val":chatroomData, "roomno":roomno, "messageCounter":messageCounter, "isOpen": "1"};
                        jqcc.cometchat.setCrSessionVariable(controlparameters);
                    }
                    var crUnreadMessages = jqcc.cometchat.getChatroomVars('crUnreadMessages');
                    crUnreadMessages[roomno] = 0;
                    jqcc.cometchat.setChatroomVars('crUnreadMessages',crUnreadMessages);

                    if ($('#currentroomtab').find("a").attr('show')==0) {
                        $('#unbanuser').remove();
                    }
                    $('.cometchat_prependMessages_container > .cometchat_prependMessages').attr('onclick','jqcc.lite.prependCrMessagesInit('+roomno+')');

                    $('#currentroom_convo').attr('onscroll','jqcc.lite.chatScroll('+roomno+')');

                    var pluginshtml = '';
                    var plugins = jqcc.cometchat.getChatroomVars('plugins');
                    if (plugins.length > 0) {
                        pluginshtml += '<div class="cometchat_plugins">';
                        for (var i = 0;i < plugins.length;i++) {
                            var name = 'cc'+plugins[i];
                            if (typeof($[name]) == 'object') {
                                pluginshtml += '<div class="cometchat_pluginsicon cometchat_'+ settings.plugins[i] + '" title="' + $[name].getTitle() + '" name="'+name+'" to="'+roomno+'" chatroommode="1"></div>';
                            }
                        }
                        pluginshtml += '</div>';
                    }
                    $('#plugins').html(pluginshtml);
                    $('.cometchat_pluginsicon').click(function(){
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
                        if(typeof(parent) != 'undefined' && parent != null && parent != self && name != 'ccsave' && name != 'ccclearconversation' && name != 'ccchattime'){
                            var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid, "caller":"cometchat_trayicon_chatrooms_iframe"}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                            jqcc[name].init(controlparameters);
                        }
                    });
                    $[calleeAPI].chatroomWindowResize();
                },
                chatroomWindowResize: function() {
                    var height = $[calleeAPI].crgetWindowHeight();
                    var contentDivHeight = height-parseInt($('.topbar').css('height'));
                    $("div.content_div").css('height',contentDivHeight);
                    var textareaHeight = parseInt($('textarea.cometchat_textarea').css('height')) + 4 + 4;//Padding + padding of container
                    var prependHeight = parseInt($('.cometchat_prependMessages_container').outerHeight(true));
                    $("#currentroom_convo").css('height',contentDivHeight-textareaHeight-prependHeight);

                    var width = $[calleeAPI].crgetWindowWidth();
                    $('#currentroom_left').css('width',width-144-48);
                    $('textarea.cometchat_textarea').css('width',width-174-48);

                    if (jqcc().slimScroll) {
                        $("#currentroom_left").find("div.slimScrollDiv").css('height',$("#currentroom_convo").css('height'));
                        $("#currentroom_right").find("div.slimScrollDiv").css('height',$("#currentroom_right").css('height'));
                    }
                },
                kickid: function(kickid) {
                    $("#chatroom_userlist_"+kickid).remove();
                },
                banid: function(banid) {
                    $("#chatroom_userlist_"+banid).remove();
                },
                chatroomScrollDown: function(forced) {
                    var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
                	if(settings.newMessageIndicator == 1 && ($('#currentroom_convotext').outerHeight(false)+$('#currentroom_convotext').offset().top-$('#currentroom_convo').height()-$('#currentroom_convo').offset().top-$('.cometchat_chatboxmessage').height()-$('.cometchat_chatboxmessage').height()>0)){
                        if(($('#currentroom_convo').height()-$('#currentroom_convotext').outerHeight(false)) < 0){
                        	if(forced) {
    	                        if (jqcc().slimScroll) {
                                    if(mobileDevice){
                                        $('#currentroom_convo').css('overflow','scroll !important');
                                        $('#currentroom_convo').scrollTop($('#currentroom_convo')[0].scrollHeight);
                                    }else{
                                        $('#currentroom_convo').slimScroll({scroll: '1'});
                                    }
    	                        } else {
    	                            setTimeout(function() {
    	                            $("#currentroom_convo").scrollTop(50000);
    	                            },100);
    	                        }
    	                        if($('.talkindicator').length != 0){
	                            $('.talkindicator').fadeOut();
                                }
                            }else{
                                if(!$('.talkindicator').length != 0){
                                    var indicator = "<a class='talkindicator' href='#'><?php echo $chatrooms_language[52];?></a>";
                                    $('#currentroom_convo').append(indicator);
                                    $('.talkindicator').click(function(e) {
                                        e.preventDefault();
                                        if (jqcc().slimScroll) {
                                            if(mobileDevice){
                                                $('#currentroom_convo').css('overflow','scroll !important');
                                                $('#currentroom_convo').scrollTop($('#currentroom_convo')[0].scrollHeight);
                                            }else{
                                                $('#currentroom_convo').slimScroll({scroll: '1'});
                                            }
                                        } else {
                                            setTimeout(function() {
                                                $("#currentroom_convo").scrollTop(50000);
                                            },100);
                                        }
                                        $('.talkindicator').fadeOut();
                                    });
                                    $('#currentroom_convo').scroll(function(){
                                        if($('#currentroom_convotext').outerHeight(false) + $('#currentroom_convotext').offset().top - $('#currentroom_convo').offset().top <= $('#currentroom_convo').height()){
                                            $('.talkindicator').fadeOut();
                                        }
                                    });
                                }
                        	}
                        }
                    }else{
                        if (jqcc().slimScroll) {
                            if(mobileDevice){
                                $('#currentroom_convo').css('overflow','scroll !important');
                                $('#currentroom_convo').scrollTop($('#currentroom_convo')[0].scrollHeight);
                            }else{
                                $('#currentroom_convo').slimScroll({scroll: '1'});
                            }
                        } else {
                            setTimeout(function() {
                                $("#currentroom_convo").scrollTop(50000);
                            },100);
                        }
                    }
                },
                createChatroomSubmitStruct: function() {
                    var string = $('input.create_input').val();
                    var room={};
                    if (($.trim( string )).length == 0) {
                        return false;
                    }
                    var name = document.getElementById('name').value;
                    name = (name).replace(/'/g, "%27");
                    var type = document.getElementById('type').value;
                    var password = document.getElementById('password').value;
                    if (name != '' && name != null && name != '<?php echo $chatrooms_language[63];?>') {
                        name = name.replace(/^\s+|\s+$/g,"");
                        if (type == 1 && password == '') {
                            alert ('<?php echo $chatrooms_language[26];?>');
                            return 'invalid password';
                        }
                        if (type == 0 || type == 2) {
                            password = '';
                        }
                        room['name'] = name;
                        room['password'] = password;
                        room['type'] = type;
                    }else{
                        alert('<?php echo $chatrooms_language[50];?>');
                        return false;
                    }
                    document.getElementById('name').value = '';
                    document.getElementById('password').value = '';
                    return room;
                },
                crgetWindowHeight: function() {
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
                },
                crgetWindowWidth: function() {
                    var windowWidth = 0;
                    if (typeof(window.innerWidth) == 'number') {
                        windowWidth = window.innerWidth;
                    } else {
                        if (document.documentElement && document.documentElement.clientWidth) {
                            windowWidth = document.documentElement.clientWidth;
                        } else {
                            if (document.body && document.body.clientWidth) {
                                windowWidth = document.body.clientWidth;
                            }
                        }
                    }
                    return windowWidth;
                },
                selectChatroom: function(currentroom,id) {
                    jqcc("#cometchat_chatroomlist_"+currentroom).removeClass("cometchat_chatroomselected");
                    jqcc("#cometchat_chatroomlist_"+id).addClass("cometchat_chatroomselected");
                },
                checkOwnership: function(owner,isModerator,name) {
                    name  = decodeURI(name);
                    var id = jqcc.cometchat.getChatroomVars('currentroom');
                    var switchroom = 'javascript:jqcc["'+calleeAPI+'"].switchChatroom('+id+',"1")';
                    var show = 0;
                    if (owner || isModerator) {
                        show = 1;
                    }
                    if(!jqcc('#currentroomtab').is(":visible")){
                        jqcc('#currentroomtab').html('<span class="activeRoom_'+id+' activeRooms tab_selected"><a href="javascript:void(0);" show="'+show+'" onclick='+switchroom+'>'+name+'</a></span>');
                    } else {
                        jqcc('#currentroomtab').html('<span class="activeRoom_'+id+' activeRooms tab_selected"><a href="javascript:void(0);" show="'+show+'" onclick='+switchroom+'>'+name+'</a></span>');
                    }
                    jqcc('#currentroom_convotext').html('');
                    jqcc("#currentroom_users").html('');
                },
                leaveRoomClass : function(currentroom) {
                    jqcc("#cometchat_chatroomlist_"+currentroom).removeClass("cometchat_chatroomselected");
                },
                removeCurrentRoomTab : function(id) {
                    jqcc("#currentroomtab").html('');
                },
                chatroomLogout : function() {
                    window.location.reload();
                },
                loadChatroomList : function(item) {
                    var chatroomitem = $[calleeAPI].getActiveChatrooms(item);
                    var activeChatroomIds = Object.keys(chatroomitem);
                    var activeChatroomhtml = jqcc.lite.activeChatrooms(item);
                    var temp = '';
                    if(Object.keys(item).length == activeChatroomIds.length){
                        temp = activeChatroomhtml;
                    } else {
                        temp = activeChatroomhtml+'<div class="cometchat_chatroomtitle"><hr class="hrleft"><?php echo $chatrooms_language[77];?><hr class="hrright"></div>';
                    }
                    var onlineNumber = 0;
                    var userCountCss = "style='display:none'";
                    if(settings.showChatroomUsers == 1){
                        userCountCss = '';
                    }
                    $.each(item, function(i,room) {
                        if(activeChatroomIds.indexOf(i) < 0){
                            longname = room.name;
                            shortname = room.name;

                            if (room.status == 'available') {
                                onlineNumber++;
                            }
                            var selected = '';

                            if (jqcc.cometchat.getChatroomVars('currentroom') == room.id) {
                                selected = ' cometchat_chatroomselected';
                            }
                            var roomtype = '';
                            var roomowner = '';
                            var deleteroom = '';
                            var renameChatroom = '';

                            if (room.type == 1) {
                                roomtype = '<?php echo $chatrooms_language[24];?>';
                            }

                            if (room.s == 1) {
                                roomowner = '<?php echo $chatrooms_language[25];?>';
                            }

                            if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                                deleteroom = '<img src="remove.png" />';
                                renameChatroom = '<img src="pencil.png" />';
                            }

                            if (room.s == 2) {
                                room.s = 1;
                            }

                            temp += '<div id="cometchat_chatroomlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="jQuery(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jQuery(this).removeClass(\'cometchat_chatroomlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\',\'0\',\'1\');" ><span class="lobby_room_1"><span class="currentroomname">'+longname+'</span></span><span class="lobby_room_2" '+userCountCss+'>'+room.online+' <?php echo $chatrooms_language[34];?></span><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><span class="lobby_room_6" title="<?php echo $chatrooms_language[80];?>" onclick="javascript:jqcc.'+[calleeAPI]+'.renameChatroom(event,\''+room.id+'\');">'+renameChatroom+'</span><div style="clear:both"></div></div>';
                        }
                    });
                    if (Object.keys(item).length != 0) {
                        jqcc('#lobby_rooms').html(temp);
                    }else{
                        jqcc('#lobby_rooms').html('<div class="lobby_noroom"><?php echo $chatrooms_language[53]; ?></div>');
                    }
                },
                displayChatroomMessage: function(item,fetchedUsers) {
                    var beepNewMessages = 0;
                    var crUnreadMessages = jqcc.cometchat.getChatroomVars('crUnreadMessages');
                    var chatroomreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                    var receivedcrunreadmessages = jqcc.cometchat.getFromStorage('crreceivedunreadmessages');
                    var todaysdate = new Date();
                    var tdmonth  = todaysdate.getMonth();
                    var tddate  = todaysdate.getDate();
                    var tdyear = todaysdate.getFullYear();
                    var today_date_class = tdmonth+"_"+tddate+"_"+tdyear;
                    var ydaysdate = new Date((new Date()).getTime() - 3600000 * 24);
                    var ydmonth  = ydaysdate.getMonth();
                    var yddate  = ydaysdate.getDate();
                    var ydyear = ydaysdate.getFullYear();
                    var yday_date_class = ydmonth+"_"+yddate+"_"+ydyear;
                    var d = '';
                    var month = '';
                    var date  = '';
                    var year = '';
                    var msg_date_class = '';
                    var msg_date = '';
                    var date_class = '';
                    var msg_date_format = '';

                    $.each(item, function(i,incoming) {
                        if(incoming.fromid == settings.myid){
                            incoming.from = '<?php echo $chatrooms_language[6];?>';
                        }
                        jqcc.cometchat.setChatroomVars('timestamp',incoming.id);
                        var message = jqcc.cometchat.processcontrolmessage(incoming);
                        var msg_time = incoming.sent;
                        msg_time = String(msg_time);

                        if (msg_time.length == 10){
                            msg_time = parseInt(msg_time * 1000);
                        }

                        var months_set = new Array();

                        <?php
                        $months_array = array($chatrooms_language[90],$chatrooms_language[91],$chatrooms_language[92],$chatrooms_language[93],$chatrooms_language[94],$chatrooms_language[95],$chatrooms_language[96],$chatrooms_language[97],$chatrooms_language[98],$chatrooms_language[99],$chatrooms_language[101],$chatrooms_language[102]);

                        foreach($months_array as $key => $val){
                            ?>
                            months_set.push('<?php echo $val; ?>');
                            <?php
                        }
                        ?>

                        d = new Date(parseInt(msg_time));
                        month  = d.getMonth();
                        date  = d.getDate();
                        year = d.getFullYear();

                        msg_date_class = month+"_"+date+"_"+year;
                        msg_date = months_set[month]+" "+date+", "+year;

                        var type = 'th';
                        if(date==1||date==21||date==31){
                            type = 'st';
                        }else if(date==2||date==22){
                            type = 'nd';
                        }else if(date==3||date==23){
                            type = 'rd';
                        }
                        msg_date_format = date+type+' '+months_set[month]+', '+year;

                        if(msg_date_class == today_date_class){
                            date_class = "today";
                            msg_date = '<?php echo $chatrooms_language[103]; ?>';
                        }else  if(msg_date_class == yday_date_class){
                            date_class = "yesterday";
                            msg_date = '<?php echo $chatrooms_language[104]; ?>';
                        }

                        if (message != '' && incoming.chatroomid == jqcc.cometchat.getChatroomVars('currentroom')) {
                                var temp = '';
                                var fromname = incoming.from;
                                var bannedKicked = incoming.message;
                                if ($("#cometchat_message_"+incoming.id).length > 0) {
                                    $("#cometchat_message_"+incoming.id).find("span.cometchat_chatboxmessagecontent").html(message);
                                } else {
                                    var ts = new Date(parseInt(incoming.sent)*1000);
                                    if (incoming.fromid != settings.myid) {
                                        temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom"></div><div class="cometchat_messagearrow"></div>');
                                        temp += ('<div class="cometchat_chatboxmessagecontent"><div><strong>');
                                        if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                            temp += ('<a id="fromname" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');">');
                                        }
                                        temp += fromname+':';
                                        if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                            temp += ('</a>');
                                        }
                                         temp += ('&nbsp;&nbsp;</strong></div><span class="chatroom_msg">'+message+'</span> '+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'</div></div>');
                                        jqcc.cometchat.setChatroomVars('newMessages',jqcc.cometchat.getChatroomVars('newMessages')+1);
                                        beepNewMessages++;
                                    } else {
                                        var selfstyle = ' cometchat_self';
                                        temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage '+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom '+selfstyle+'"></div><div class="cometchat_messagearrow"></div><div class="cometchat_chatboxmessagecontent '+selfstyle+'"><div style="display:none;"><strong>'+fromname+':&nbsp;&nbsp;</strong></div><span class="chatroom_msg">'+message+'</span> '+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'</div></div>');
                                    }
                                }
                                $('#currentroom_convotext').append(temp);
                                if (jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    if ($("#cometchat_message_"+incoming.id).find(".delete_msg").length < 1) {
                                        jqcc('#cometchat_message_'+incoming.id).find(".cometchat_ts").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');">(<span class="hoverbraces"><?php echo $chatrooms_language[46]; ?></span>)</span>');
                                    }
                                    $(".cometchat_chatboxmessage").live("mouseover",function() {
                                        $(this).find(".delete_msg").css('visibility','visible');
                                    });
                                    $(".cometchat_chatboxmessage").live("mouseout",function() {
                                        $(this).find(".delete_msg").css('visibility','hidden');
                                    });
                                    $(".delete_msg").mouseover(function() {
                                        $(this).css('visibility','visible');
                                        $(this).find(".hoverbraces").css('text-decoration','underline');
                                    });
                                    $(".delete_msg").mouseout(function() {
                                        $(this).find(".hoverbraces").css('text-decoration','none');
                                    });
                                }
                                var forced = (incoming.fromid == settings.myid) ? 1 : 0;
                               	if((message).indexOf('<img')!=-1 && (message).indexOf('src')!=-1){
                                    $( "#cometchat_message_"+incoming.id+" img" ).load(function() {
                                         $[calleeAPI].chatroomScrollDown(forced);
                                    });
                                }else{
                                    $[calleeAPI].chatroomScrollDown(forced);
                                }
                            }

                            if (message != '' && incoming.chatroomid != jqcc.cometchat.getChatroomVars('currentroom') && (typeof(receivedcrunreadmessages[incoming.chatroomid])=='undefined' || receivedcrunreadmessages[incoming.chatroomid] < incoming.id)){
                                if(!crUnreadMessages.hasOwnProperty(incoming.chatroomid)){
                                    crUnreadMessages[incoming.chatroomid] = 1;
                                } else {
                                    var newUnreadMessages = parseInt(crUnreadMessages[incoming.chatroomid]) + 1;
                                    crUnreadMessages[incoming.chatroomid] = newUnreadMessages;
                                }
                                $[calleeAPI].updateCRReceivedUnreadMessages(incoming.chatroomid,incoming.id);
                            }
                        });
                        jqcc.cometchat.setChatroomVars('crUnreadMessages',crUnreadMessages);
                        receivedcrunreadmessages = jqcc.cometchat.getFromStorage('crreceivedunreadmessages');
                        $.each(crUnreadMessages, function(chatroomid,unreadMessageCount) {
                            var chatroomreadmessagesId = chatroomreadmessages[chatroomid];
                            var receivedcrunreadmessagesId = receivedcrunreadmessages[chatroomid];
                            if(receivedcrunreadmessagesId != 'undefined'){
                                if(receivedcrunreadmessagesId > chatroomreadmessagesId || typeof(chatroomreadmessagesId) == 'undefined'){
                                    $[calleeAPI].chatroomUnreadMessages(jqcc.cometchat.getChatroomVars('crUnreadMessages'),chatroomid);
                                }
                            }
                        });

                        if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addMessageCounter) == "function"){
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].addMessageCounter();
                        }

                        jqcc.cometchat.setChatroomVars('heartbeatCount',1);
                        jqcc.cometchat.setChatroomVars('heartbeatTime',settings.minHeartbeat);
                        if (settings.apiAccess == 1 && fetchedUsers == 0 && typeof (parent.jqcc.cometchat.setAlert) != 'undefined') {
                            parent.jqcc.cometchat.setAlert('chatrooms',jqcc.cometchat.getChatroomVars('newMessages'));
                        }
                        if ($.cookie(settings.cookiePrefix+"sound") && $.cookie(settings.cookiePrefix+"sound") == 'true') { } else {
                            if (beepNewMessages > 0 && fetchedUsers == 0) {
                                $[calleeAPI].playsound();
                            }
                        }
                        if(($("#currentroom_convo")[0].scrollHeight) - ($("#currentroom_convo").scrollTop() + $("#currentroom_convo").innerHeight()) > 80) {
                            $('.talkindicator').fadeIn();
                        }
                        $[calleeAPI].updateCRReadMessages(jqcc.cometchat.getChatroomVars('currentroom'));
                        var crreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                        jqcc.cometchat.setChatroomVars('crreadmessages',crreadmessages);
                        $[calleeAPI].groupbyDate();
                    },
                    silentRoom: function(id, name, silent) {
                        if (settings.lightboxWindows == 1) {
                            var controlparameters = {"type":"modules", "name":"cometchat", "method":"loadCCPopup", "params":{"url":settings.baseUrl+"modules/chatrooms/chatrooms.php?id="+id+"&basedata="+settings.basedata+"&name="+name+"&silent="+silent+"&action=passwordBox", "action":"passwordBox", "properties":"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=320,height=110", "width":320, "height":110, "lang":name}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var temp = prompt('<?php echo $chatrooms_language[8];?>','');
                            if (temp) {
                                jqcc.cometchat.checkChatroomPass(id,name,silent,temp);
                            } else {
                                return;
                            }
                        }
                    },
                    switchChatroom: function(id, force) {
                        jqcc.cometchat.getChatroomDetails(id,1,force);
                        jqcc.cometchat.setChatroomVars('currentroom', id);
                        var controlparameters = {"name":"open", "val":id};
                        jqcc.cometchat.setCrSessionVariable(controlparameters);
                    },
                    renameChatroom: function(event,id){
                        event.stopPropagation();
                        jqcc('.cancel_edit').click();
                        jqcc('#cometchat_chatroomlist_'+id).append('<div class="cometchat_chatroom_overlay"><input class="chatroomName" id="chatroomName_'+id+'" type="textbox" value="0" style="display:none;" /><a title="<?php echo $chatrooms_language[51];?>" class="cancel_edit" href="javascript:void(0);" onclick="javascript:jqcc.'+jqcc.cometchat.getChatroomVars('calleeAPI')+'.canceledit(event,\''+id+'\');" style="display:none;"><?php echo $chatrooms_language[51];?></a></div>');
                        var currentroomname = jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').html();
                        var currentroomname = jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').html();
                        var baseUrl = settings.baseUrl;
                        var basedata = settings.basedata;
                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').val(currentroomname);
                        jqcc('.chatroomName').on('click', function(e) {
                            e.stopPropagation();
                        });
                        jqcc('.cometchat_chatroom_overlay').on('click', function(e) {
                            e.stopPropagation();
                            var cname = jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').val();
                            jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].renameChatroomsubmit(id,currentroomname,cname);
                        });
                        jqcc(".chatroomName").keydown(function(e) {
                            if (e.keyCode == 13) {
                                var cname = jqcc(this).val();
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].renameChatroomsubmit(id,currentroomname,cname);
                            }
                        });
                    },
                    renameChatroomsubmit: function(id, currentroomname, name) {
                        var baseUrl = settings.baseUrl;
                        var basedata = settings.basedata;
                        name = name.trim();
                        name = decodeURI(name);
                        if(currentroomname != name) {
                            name = escape(name);
                            jqcc.ajax({
                                url: baseUrl+"modules/chatrooms/chatrooms.php?action=renamechatroom",
                                data: {id: id, basedata: basedata, cname: name},
                                type: 'post',
                                cache: false,
                                timeout: 10000,
                                async: false,
                                success: function(data) {
                                    if (data == 0) {
                                        alert('<?php echo $chatrooms_language[38];?>');
                                    }else{
                                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').hide();
                                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').show();
                                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').hide();
                                        if(currentroomname == jqcc('.currentroomtab,.activeRooms a').clone().children().remove().end().text()){
                                            jqcc('.currentroomtab,.activeRooms a').text(name);
                                        }
                                        jqcc.cometchat.chatroomHeartbeat(1);
                                    }
                                }
                            });
                        } else {
                            jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').click();
                        }
                    },
                    canceledit: function(event,id) {
                        event.stopPropagation();
                        jqcc('#cometchat_chatroomlist_'+id).find('.cometchat_chatroom_overlay').remove();
                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').hide();
                    },
                    updateChatroomUsers: function(item,fetchedUsers) {
                        var temp = '';
                        var temp1 = '';
                        var moderatorhtml = '';
                        var userhtml = '';
                        var newUsers = {};
                        var newUsersName = {};
                        fetchedUsers = 1;
                        $.each(item, function(i,user) {
                            longname = user.n;
                            if (settings.users[user.id] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                var ts = new Date();
                                $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+user.n+'<?php echo $chatrooms_language[14]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                $[calleeAPI].chatroomScrollDown();
                            }
                            if (parseInt(user.b)!=1) {
                                var avatar = '';
                                if (user.a != '') {
                                    avatar = '<span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src='+user.a+'></span>';
                                }
                                newUsers[user.id] = 1;
                                newUsersName[user.id] = user.n;
                                userhtml='<div class="cometchat_subsubtitleusers"><hr class="hrleft"><?php echo $chatrooms_language[61];?><hr class="hrright"></div>';
                                moderatorhtml='<div class="cometchat_subsubtitle"><hr class="hrleft"><?php echo $chatrooms_language[62];?><hr class="hrright"></div>';
                                if (jQuery.inArray(user.id ,jqcc.cometchat.getChatroomVars('moderators') ) != -1 ) {
                                    if (user.id == settings.myid) {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid='+user.id+' owner='+settings.owner+' username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                } else {
                                    if (user.id == settings.myid) {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_userlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid='+user.id+' owner='+settings.owner+' username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                }
                            }
                        });
                        for (user in settings.users) {
                            if (settings.users.hasOwnProperty(user)) {
                                if (newUsers[user] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                    var ts = new Date();
                                    $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+settings.usersName[user]+'<?php echo $chatrooms_language[13]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                    $[calleeAPI].chatroomScrollDown();
                                }
                            }
                        }
                        if(temp1 != "" && temp !="")
                            jqcc('#currentroom_users').html(moderatorhtml+temp1+userhtml+temp);
                        else if(temp == "")
                            jqcc('#currentroom_users').html(moderatorhtml+temp1);
                        else
                            jqcc('#currentroom_users').html(userhtml+temp);
                        jqcc.cometchat.setChatroomVars('users',newUsers);
                        jqcc.cometchat.setChatroomVars('usersName',newUsersName);
                        jqcc.cometchat.setChatroomVars('initializeRoom',0);
                    },
                    loadCCPopup: function(url,name,properties,width,height,title,force,allowmaximize,allowresize,allowpopout){
                        if (jqcc.cometchat.getChatroomVars('lightboxWindows') == 1) {
                            var controlparameters = {"type":"modules", "name":"chatrooms", "method":"loadCCPopup", "params":{"url":url, "name":name, "properties":properties, "width":width, "height":height, "title":title, "force":force, "allowmaximize":allowmaximize, "allowresize":allowresize, "allowpopout":allowpopout}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var w = window.open(url,name,properties);
                            w.focus();
                        }
                    },
                    prependCrMessagesInit: function(id){
                        var messages = jqcc('#currentroom_convotext').find('.cometchat_chatboxmessage');
                        $('.cometchat_prependMessages').text('<?php echo $chatrooms_language[76];?>');
                        jqcc('.cometchat_prependMessages').attr('onclick','');
                        if(messages.length > 0){
                            jqcc('#scrolltop_'+id).remove();
                            prepend = messages[0].id.split('_')[2];
                        }else{
                            prepend = -1;
                        }
                        jqcc.cometchat.updateChatroomMessages(id,prepend);
                    },
                    prependCrMessages:function(id,data){
                        var oldMessages = '';
                        var count = 0;
                        var todaysdate = new Date();
                        var tdmonth  = todaysdate.getMonth();
                        var tddate  = todaysdate.getDate();
                        var tdyear = todaysdate.getFullYear();
                        var today_date_class = tdmonth+"_"+tddate+"_"+tdyear;
                        var ydaysdate = new Date((new Date()).getTime() - 3600000 * 24);
                        var ydmonth  = ydaysdate.getMonth();
                        var yddate  = ydaysdate.getDate();
                        var ydyear = ydaysdate.getFullYear();
                        var yday_date_class = ydmonth+"_"+yddate+"_"+ydyear;
                        var d = '';
                        var month = '';
                        var date  = '';
                        var year = '';
                        var msg_date_class = '';
                        var msg_date = '';
                        var date_class = '';
                        var msg_date_format = '';

                        $.each(data, function(i, incoming){
                            if(incoming.fromid == settings.myid){
                                incoming.from = '<?php echo $chatrooms_language[6];?>';
                            }
                            lastMessageId = incoming.id;
                            var deleteMessage = '';
                            var message = jqcc.cometchat.processcontrolmessage(incoming);
                            var msg_time = incoming.sent;
                            msg_time = msg_time+'';

                            if (msg_time.length == 10){
                                msg_time = parseInt(msg_time * 1000);
                            }

                            var months_set = new Array();

                            <?php

                            $months_array = array($chatrooms_language[90],$chatrooms_language[91],$chatrooms_language[92],$chatrooms_language[93],$chatrooms_language[94],$chatrooms_language[95],$chatrooms_language[96],$chatrooms_language[97],$chatrooms_language[98],$chatrooms_language[99],$chatrooms_language[101],$chatrooms_language[102]);

                            foreach($months_array as $key => $val){
                                ?>
                                months_set.push('<?php echo $val; ?>');
                                <?php
                            }
                            ?>

                            d = new Date(parseInt(msg_time));
                            month  = d.getMonth();
                            date  = d.getDate();
                            year = d.getFullYear();

                            msg_date_class = month+"_"+date+"_"+year;
                            msg_date = months_set[month]+" "+date+", "+year;

                            var type = 'th';
                            if(date==1||date==21||date==31){
                                type = 'st';
                            }else if(date==2||date==22){
                                type = 'nd';
                            }else if(date==3||date==23){
                                type = 'rd';
                            }
                            msg_date_format = date+type+' '+months_set[month]+', '+year;

                            if(msg_date_class == today_date_class){
                                date_class = "today";
                                msg_date = '<?php echo $chatrooms_language[103]; ?>';
                            }else  if(msg_date_class == yday_date_class){
                                date_class = "yesterday";
                                msg_date = '<?php echo $chatrooms_language[104]; ?>';
                            }
                            if (message != '') {
                                count = count+1;
                                var fromname = incoming.from;
                                var ts = new Date(parseInt(incoming.sent)*1000);
                                if (jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    deleteMessage = '<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');">(<span class="hoverbraces"><?php echo $chatrooms_language[46]; ?></span>)</span>';
                                }
                                if (incoming.fromid != settings.myid) {
                                     oldMessages += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom"></div><div class="cometchat_messagearrow"></div>');

                                     oldMessages += ('<div class="cometchat_chatboxmessagecontent"><div><strong>');
                                    if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                        oldMessages += ('<a id="fromname" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');">');
                                    }
                                    oldMessages += fromname+':';
                                    if (jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                        oldMessages += ('</a>');
                                    }
                                   oldMessages += ('</strong></div><span class="chatroom_msg">'+message+'</span> '+$[calleeAPI].getTimeDisplay(ts,incoming.from)+deleteMessage+'</div></div>');
                                    var msgcount = 0;
                                    jqcc.cometchat.setChatroomVars('newMessages',msgcount);
                                } else {
                                    var selfstyle = ' cometchat_self';
                                    oldMessages += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage '+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom '+selfstyle+'"></div><div class="cometchat_messagearrow"></div><div class="cometchat_chatboxmessagecontent '+selfstyle+'"><div style="display:none;"><strong>'+fromname+':&nbsp;&nbsp;</strong></div><span class="chatroom_msg">'+message+'</span>'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+deleteMessage+'</div></div>');
                                }
                            }
                        });
                        jqcc('#currentroom_convotext').prepend(oldMessages);

                        $(".cometchat_chatboxmessage").live("mouseover",function() {
                            $(this).find(".delete_msg").css('visibility','visible');
                        });
                        $(".cometchat_chatboxmessage").live("mouseout",function() {
                            $(this).find(".delete_msg").css('visibility','hidden');
                        });
                        $(".delete_msg").mouseover(function() {
                            $(this).css('visibility','visible');
                            $(this).find(".hoverbraces").css('text-decoration','underline');
                        });
                        $(".delete_msg").mouseout(function() {
                            $(this).find("span.hoverbraces").css('text-decoration','none');
                        });

                        $('.cometchat_prependMessages').text('<?php echo $chatrooms_language[74];?>');

                        if((count - parseInt(settings.prependLimit) < 0)){
                            $('.cometchat_prependMessages').text('<?php echo $chatrooms_language[75];?>');
                            jqcc('.cometchat_prependMessages').attr('onclick','');
                            jqcc('.cometchat_prependMessages').css('cursor','default');
                        }else{
                            jqcc('.cometchat_prependMessages').attr('onclick','jqcc.lite.prependCrMessagesInit('+id+')');
                        }
                        $[calleeAPI].groupbyDate();
                    },
                    groupbyDate: function(){
                        $('.cometchat_time').hide();
                        $.each($('.cometchat_time'),function (i,divele){
                            var classes = $(divele).attr('class').split(/\s+/);
                            for(var i in classes){
                                if(classes[i].indexOf('cometchat_time_') === 0){
                                    $('.'+classes[i]+':first').show();
                                }
                            }
                        });
                    },
                    getActiveChatrooms: function(item){
                        var chatroomitem = {};
                        var cc_chatroom = JSON.parse($.cookie(jqcc.cometchat.getChatroomVars('cookiePrefix')+'crstate'));
                        var chatroomData = cc_chatroom.active;
                        var Ids = new Array();
                        var temp = '';
                        var onlineNumber = 0;

                        for(chatroomId in chatroomData){
                            Ids.push(chatroomId);
                        }
                        for(var key in item) {
                            if(Ids.indexOf(key) >= 0){
                                chatroomitem[key] = item[key];
                            }
                        }
                        return chatroomitem;
                    },
                    activeChatrooms: function(item){
                        var chatroomitem = $[calleeAPI].getActiveChatrooms(item);
                        var temp = '';

                        if(Object.keys(chatroomitem).length > 0){
                            temp = '<div class="cometchat_chatroomtitle"><hr class="hrleft"><?php echo $chatrooms_language[78];?><hr class="hrright"></div>';
                        }
                        var userCountCss = "style='display:none'";
                        if(settings.showChatroomUsers == 1){
                            userCountCss = '';
                        }
                        $.each(chatroomitem, function(i,room) {
                            longname = room.name;
                            shortname = room.name;

                            if (room.status == 'available') {
                                onlineNumber++;
                            }
                            var selected = '';

                            if (jqcc.cometchat.getChatroomVars('currentroom') == room.id) {
                                selected = ' cometchat_chatroomselected';
                            }
                            var roomtype = '';
                            var roomowner = '';
                            var deleteroom = '';
                            var renameChatroom = '';

                            if (room.type == 1) {
                                roomtype = '<img src="lock.png" />';
                            }

                            if (room.s == 1) {
                                roomowner = '<img src="user.png" />';
                            }

                            if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                                deleteroom = '<img src="remove.png" />';
                                renameChatroom = '<img src="pencil.png" />';
                            }

                            if (room.s == 2) {
                                room.s = 1;
                            }

                            temp += '<div id="cometchat_chatroomlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="jQuery(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jQuery(this).removeClass(\'cometchat_chatroomlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\',\'0\',\'1\');" ><span class="lobby_room_1"><span class="currentroomname">'+longname+'</span></span><span class="lobby_room_2" '+userCountCss+'>'+room.online+' <?php echo $chatrooms_language[34];?></span><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><span class="lobby_room_6" title="<?php echo $chatrooms_language[80];?>" onclick="javascript:jqcc.'+[calleeAPI]+'.renameChatroom(event,\''+room.id+'\');">'+renameChatroom+'</span><div style="clear:both"></div></div>';
                        });
                        return temp;
                    },
                    chatroomUnreadMessages: function(crUnreadMessages,chatroomid){
                        if(typeof(chatroomid) == 'undefined') {
                            if(Object.keys(crUnreadMessages).length > 0){
                                $.each(crUnreadMessages, function(chatroomid,unreadMessageCount) {
                                    var chatroomData = {};
                                    var controlparameters = {"name":"active", "val":chatroomData, "roomno":chatroomid, "messageCounter":unreadMessageCount, "isOpen":"0"};
                                    jqcc.cometchat.setCrSessionVariable(controlparameters);
                                });
                            }
                        } else {
                            var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                            var chatroomData = cc_chatroom.active;
                            var unreadMessageCount = 0;
                            var isOpen = 0;
                            var chatroomId = '_'+chatroomid;
                            if(chatroomData.hasOwnProperty(chatroomId)){
                                isOpen = chatroomData[chatroomId].o;
                                unreadMessageCount = crUnreadMessages[chatroomid];
                            }
                            var chatroomData = {};
                            var controlparameters = {"name":"active", "val":chatroomData, "roomno":chatroomid, "messageCounter":unreadMessageCount, "isOpen":isOpen};
                            jqcc.cometchat.setCrSessionVariable(controlparameters);
                        }
                    },
                    addMessageCounter: function(add){
                        var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        var chatroomdata = cc_chatroom.active;
                        $.each(chatroomdata, function (chatroomid, data){
                            chatroomid = chatroomid.replace('_','');
                            var cometchat_chatroommsgcount = $("#currentroomtab").find('.activeRoom_'+chatroomid).find('.cometchat_chatroommsgcount');
                            var cometchat_lobbymsgcount = $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.cometchat_lobbychatroommsgcount');
                            if(chatroomid != jqcc.cometchat.getChatroomVars('currentroom') && data.c != 0){
                                if(cometchat_chatroommsgcount.length > 0) {
                                    $("#currentroomtab").find('.activeRoom_'+chatroomid).find('.cometchat_chatroommsgcounttext').text(data.c);
                                } else {
                                    $("#currentroomtab").find('.activeRoom_'+chatroomid+' a').prepend("<span class='cometchat_chatroommsgcount'><div class='cometchat_chatroommsgcounttext'>"+data.c+"</div></span>");
                                }
                                if(cometchat_lobbymsgcount.length > 0) {
                                    $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.cometchat_chatroommsgcounttext').text(data.c);
                                } else {
                                    $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.lobby_room_3').after("<span class='cometchat_lobbychatroommsgcount'><div class='cometchat_chatroommsgcounttext'>"+data.c+"</div></span>");
                                }
                            }
                        });
                    },
                    chatScroll: function(id){
                        var baseUrl = settings.baseUrl;
                        if($('#scrolltop_'+id).length == 0){
                            $("#currentroom_convo").prepend('<div id="scrolltop_'+id+'" class="cometchat_scrollup"><img src="'+baseUrl+'images/arrowtop.png" class="cometchat_scrollimg" /></div>');
                        }
                        if($('#scrolldown_'+id).length == 0){
                            $("#currentroom_convo").append('<div id="scrolldown_'+id+'" class="cometchat_scrolldown"><img src="'+baseUrl+'images/arrowbottom.png" class="cometchat_scrollimg" /></div>');
                        }
                        $('#currentroom_convo').unbind('wheel');
                        $('#currentroom_convo').on('wheel',function(event){
                            var scrollTop = $(this).scrollTop();
                            if(event.originalEvent.deltaY != 0){
                                clearTimeout($.data(this, 'scrollTimer'));
                                if(event.originalEvent.deltaY > 0){
                                    $('#scrolltop_'+id).hide();
                                    var down = jqcc("#currentroom_convo")[0].scrollHeight-250-50;
                                    if(scrollTop < down){
                                        $('#scrolldown_'+id).fadeIn('slow');
                                    }else{
                                        $('#scrolldown_'+id).fadeOut();
                                    }
                                    $.data(this, 'scrollTimer', setTimeout(function() {
                                        $('#scrolldown_'+id).fadeOut('slow');
                                    },2000));

                                }else{
                                    $('#scrolldown_'+id).hide();
                                    var top = 45+50;
                                    if(scrollTop > top){
                                        $('#scrolltop_'+id).fadeIn('slow');
                                    }else{
                                        $('#scrolltop_'+id).fadeOut();
                                    }
                                    $.data(this, 'scrollTimer', setTimeout(function() {
                                        $('#scrolltop_'+id).fadeOut('slow');
                                    },2000));
                                }
                            }
                        });

                        $('#scrolltop_'+id).click(function(){
                            $('#scrolltop_'+id).hide();
                            $('#currentroom_convo').slimScroll({scroll: 0});
                        });

                        $('#scrolldown_'+id).click(function(){
                            $('#scrolldown_'+id).hide();
                            $('#currentroom_convo').slimScroll({scroll: 1});
                        });
                    }
                };
        })();
})(jqcc);

if(typeof(jqcc.lite) === "undefined"){
    jqcc.lite=function(){};
}

jqcc.extend(jqcc.lite, jqcc.crlite);

jqcc(document).ready(function(){

    var lang = '<?php echo $chatrooms_language[21];?>';

    jqcc('.inviteChatroomUsers').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=invite&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"inviteChatroomUser", "params":{"url":url, "action":"invite", "lang":lang}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.inviteChatroomUser();
        }
    });

    jqcc('.unbanChatroomUser').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=unban&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&time='+Math.random();

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"invite", "lang":lang}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.unbanChatroomUser();
        }
    });

    jqcc('.loadChatroomPro').live('click',function(){
        var owner = jqcc(this).attr('owner');
        var uid = jqcc(this).attr('userid');
        username = jqcc(this).attr('username');
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=loadChatroomPro&apiAccess='+jqcc.cometchat.getChatroomVars('apiAccess')+'&owner='+owner+'&roomid='+roomid+'&basedata='+basedata+'&inviteid='+uid+'&roomname='+roomname;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"loadChatroomPro", "lang":username}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.loadChatroomPro(uid,owner,username,popoutmode);
        }
    });
});
