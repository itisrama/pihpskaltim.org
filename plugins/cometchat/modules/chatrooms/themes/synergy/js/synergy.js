<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/
?>

if(typeof(jqcc) === 'undefined') {
	jqcc = jQuery;
}

if(typeof($) === 'undefined') {
    $ = jqcc;
}

var enableType = 0;

(function($) {
    var settings = {};
    settings = jqcc.cometchat.getcrAllVariables();
    var calleeAPI = jqcc.cometchat.getChatroomVars('calleeAPI');
    var baseUrl = jqcc.cometchat.getBaseUrl();
    var tabWidth = 'width: 50%;right: 0;';
    var chromeReorderFix = '_';
    var newmess;
    var newmesscr;
    var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
    var iOSmobileDevice = navigator.userAgent.match(/ipad|ipod|iphone/i);
    $.crsynergy = (function() {
        return {
                chatroomInit: function(){
                    var createChatroom='';
                    if(settings.allowUsers == 1){
                        createChatroom='<div id="createChatroomOption" class="cometchat_tabsubtitle"><?php echo $chatrooms_language[2];?> &#9658;</div><div class="content_div" id="create" style="display:none"><div id="create_chatroom" class="content_div"><form class="create" onsubmit="javascript:jqcc.cometchat.createChatroomSubmit(); return false;"><div style="clear:both;padding-top:10px"></div><div class="create_value"><input type="text" id="name" class="create_input" placeholder="<?php echo $chatrooms_language[27];?>" /></div><div style="clear:both;padding-top:10px"></div><div class="create_value" ><select id="type" onchange="jqcc[\''+calleeAPI+'\'].crcheckDropDown(this)" class="create_input"><option value="0"><?php echo $chatrooms_language[29];?></option><option value="1"><?php echo $chatrooms_language[30];?></option><option value="2"><?php echo $chatrooms_language[31];?></option></select></div><div class="password_hide" style="clear:both;padding-top:10px"></div><div class="create_value password_hide"><input id="password" type="password" autocomplete="off" class="create_input" placeholder="<?php echo $chatrooms_language[32];?>" /></div><div class="create_value"><input type="submit" class="createroombutton" value="<?php echo $chatrooms_language[33];?>" /></div></form></div></div>';
                    }
                    var chatroomsTab = '';
                    var chatroomstabpopup = '';
                    if (typeof jqcc.cometchat.getSettings != "undefined") {
                        enableType = jqcc.cometchat.getSettings().enableType;
                    }
                    if(enableType==1){
                        $('#cometchat_righttab').find('.cometchat_noactivity').find('h3').text('<?php echo $chatrooms_language[81];?>');
                    }

                    if (enableType!=1) {
                        chatroomsTab = '<span id="cometchat_chatroomstab" class="cometchat_tab" style="'+tabWidth+'"><span id="cometchat_chatroomstab_text" class="cometchat_tabstext"><?php echo $chatrooms_language[100];?></span></span>';
                    }
                    if (enableType!=2) {
                        chatroomstabpopup = '<div id="cometchat_chatroomstab_popup">'+createChatroom+'<div id="lobby"><div class="cometchat_tabsubtitle" id="cometchat_chatroom_searchbar"><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light" id="cometchat_chatroom_search" value="<?php echo $chatrooms_language[60];?>"></div><div class="lobby_rooms content_div cometchat_tabpopup" id="lobby_rooms"></div></div></div>';
                    }

            	    selectlang = '<select id="selectlanguage" class="selectlanguage"></select>';
                    var currentroom = '<div class="content_div" id="currentroom" style="display:none"><div id="currentroom_left" class="content_div cometchat_tabpopup"><div class="cometchat_userchatarea"><div class="cometchat_tabsubtitle"><div class="cometchat_chatboxMenuOptions"><div class="cometchat_menuOption cometchat_chatroomUsersOption"><img title="<?php echo $chatrooms_language[71];?>" class="cometchat_chatroomUsersIcon cometchat_menuOptionIcon" src="'+baseUrl+'modules/chatrooms/chatroomusers.png"/><div id="chatroomusers_popup" class="menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="chatroomuser_container"></div></div></div><div class="cometchat_menuOption cometchat_pluginsOption"><img title="<?php echo $chatrooms_language[69];?>" class="cometchat_pluginsIcon cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+calleeAPI+'/images/pluginsicon.png"/><div id="cometchat_plugins" class="cometchat_plugins menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="plugin_container"></div></div></div><div class="cometchat_menuOption cometchat_chatroomModOption"><img title="<?php echo $chatrooms_language[70];?>" class="cometchat_chatroomUserOptionsIcon cometchat_menuOptionIcon" src="'+baseUrl+'modules/chatrooms/chatroommod.png"/><div id="cometchat_moderator_opt" class="cometchat_moderator_opt menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="moderator_container"></div></div></div></div><div class="cometchat_chatboxLeftDetails"><div class="cometchat_userscontentavatar"><img src="'+baseUrl+'modules/chatrooms/group.png" class="cometchat_chatroomavatarimage" /></div><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_chatroomdisplayname"></div></div></div><div title="<?php echo $chatrooms_language[79];?>" class="cometchat_user_closebox">X</div></div><div class="cometchat_prependMessages_container"><div class="cometchat_prependMessages"><?php echo $chatrooms_language[74];?></div></div><div id="currentroom_convo"><div id="currentroom_convotext" class="cometchat_message_container"></div></div><div style="clear:both"></div>'+selectlang+'</div><div class="cometchat_tabinputcontainer"><div title="Send" class="cometchat_tabcontentsubmit cometchat_sendicon"></div><div class="cometchat_tabcontentinput"><div style="margin-right:28px;"><textarea class="cometchat_textarea"></textarea></div></div></div></div></div>';

                    if($('#cometchat_userstab').length > 0) {
                        $('#cometchat_userstab').after(chatroomsTab);
                    }

                    if($('#cometchat_userstab_popup').length > 0) {
                        $('#cometchat_userstab_popup').after(chatroomstabpopup);
                    } else {
                        $('#cometchat_popup_container').html(chatroomstabpopup);
                    }

                    if(enableType==1) {
                        $('#cometchat_tabcontainer').remove();
                        $('#cometchat_chatroomstab_popup').addClass("cometchat_tabopen");
                    }

                    if(enableType!=2){
                        $('#cometchat_righttab').append(currentroom);
                    }

                    if(jqcc.cometchat.getSettings().extensions.indexOf('ads') > -1){
                        jqcc.ccads.init();
                    }
                    if(jqcc().slimScroll && !mobileDevice){
                        $('#lobby_rooms').slimScroll({height: 'auto'});
                        $("#plugin_container").slimScroll({width: 'auto'});
                        $("#moderator_container").slimScroll({width: 'auto'});
                        $("#chatroomuser_container").slimScroll({width: 'auto'});
                        $('#lobby_rooms').attr('style','overflow: hidden !important');
                    }

                    $('#createChatroomOption').click(function(){
                        var lobbyroomsHeight = $('#lobby_rooms').height();
                        var lefttab = $('#cometchat_lefttab');
                        var winHt = $(window).height();
                        var winWidth = $(window).width();
                        if($('#create').is(':visible')){
                            $(this).html('<?php echo $chatrooms_language[2];?>  &#9658;');
                            if(mobileDevice){
                                lefttab.find('#cometchat_tabcontainer').css('display','block');
                                lefttab.find('#lobby').css('display','block');
                            }
                            $('#create').hide('slow',function(){
                                $[calleeAPI].chatroomWindowResize();
                            });
                        }else{
                            $(this).html("<?php echo $chatrooms_language[2];?>  &#9660;");
                            if(jqcc().slimScroll && !mobileDevice){
                                $('#lobby_rooms').parent('.slimScrollDiv').css('height',lobbyroomsHeight-$('#create').outerHeight(true)+'px');
                                $('#lobby_rooms').attr('style','overflow: hidden !important');
                            }
                            if(mobileDevice && (winWidth > winHt)){
                                lefttab.find('#lobby').css('display','none');
                            }
                            $('#lobby_rooms').css('height',lobbyroomsHeight-$('#create').outerHeight(true)+'px');
                            $('#create').show('slow');
                        }
                    });

                    $('.create_value').find('#name').on('focus', function() {
                        document.body.scrollTop = $(this).offset().top;
                    });
                    $('.cometchat_textarea').click(function() {
                        if($('#cometchat_container_smilies').length == 1 && mobileDevice != null){
                            jqcc.synergy.closeModule('smilies');
                            $('#currentroom').find('.cometchat_userchatarea').css('display','block');
                            setTimeout(function(){
                                $('#currentroom_convo').css('height',$(window).height()-($('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+$('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+$('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                            }, 10);
                        }

                        if($('#cometchat_container_stickers').length == 1 && mobileDevice != null){
                            jqcc.synergy.closeModule('stickers');
                            $('#currentroom').find('.cometchat_userchatarea').css('display','block');
                            setTimeout(function(){
                                $('#currentroom_convo').css('height',$(window).height()-($('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+$('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+$('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                            }, 10);
                        }
                    });
                    $('#currentroom').click(function() {
                        if($('#cometchat_container_stickers').length == 1 && mobileDevice != null){
                            jqcc.synergy.closeModule('stickers');
                            $('#currentroom_convo').css('height',$(window).height()-(jqcc('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                        }
                        if($('#cometchat_container_smilies').length == 1 && mobileDevice != null){
                            jqcc.synergy.closeModule('smilies');
                            $('#currentroom').find('.cometchat_userchatarea').css('display','block');
                            $('#currentroom_convo').css('height',$(window).height()-(jqcc('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                        }
                        if($('#cometchat_container_loadChatroomPro').length == 1 && mobileDevice != null){
                            jqcc.synergy.closeModule('loadChatroomPro');
                        }
                    });
                    var currentroom = $('#currentroom');
                    currentroom.find('.cometchat_chatroomUsersOption').click(function(){
                        if($('#chatroomusers_popup').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#chatroomusers_popup').addClass('cometchat_tabopen');
                            var winHt = $(window).innerHeight();
                            var winWidth = $(window).innerWidth();
                            var tabsubtitleHt = $(".cometchat_userchatarea").find('.cometchat_tabsubtitle').outerHeight(true);
                            if((winWidth > winHt) && mobileDevice){
                                $('#chatroomusers_popup').css('max-height',(winHt-tabsubtitleHt-5));
                                $('#chatroomuser_container').css('max-height',(winHt-tabsubtitleHt-5));
                            } else{
                                $('#chatroomusers_popup').css('max-height','');
                                $('#chatroomuser_container').css('max-height','');
                            }
                        }
                    });
                    currentroom.find('.cometchat_pluginsOption').click(function(){
                        if($('#cometchat_plugins').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#cometchat_plugins').addClass('cometchat_tabopen');
                            var winHt = $(window).innerHeight();
                            var winWidth = $(window).innerWidth();
                            var tabsubtitleHt = $(".cometchat_userchatarea").find('.cometchat_tabsubtitle').outerHeight(true);
                            if((winWidth > winHt) && mobileDevice){
                                currentroom.find('#plugin_container').css('max-height',(winHt-tabsubtitleHt-5));
                            } else{
                                currentroom.find('#plugin_container').css('max-height','');
                            }
                            $(this).find('.cometchat_menuOptionIcon').toggleClass('cometchat_menuOptionIconClick');
                        }
                    });
                    currentroom.find('.cometchat_chatroomModOption').click(function(){
                        if($('#cometchat_moderator_opt').hasClass('cometchat_tabopen')){
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                        }else{
                            jqcc[calleeAPI].hideMenuPopup();
                            $(this).find('.cometchat_menuOptionIcon').addClass('cometchat_menuOptionIconClick');
                            $('#cometchat_moderator_opt').addClass('cometchat_tabopen');
                        }
                    });
                    currentroom.find('div.cometchat_user_closebox').click(function(){
                        jqcc.cometchat.setThemeVariable('trayOpen','');
                        jqcc.cometchat.setSessionVariable('trayOpen', '');
                        currentroom.hide();
                        jqcc[calleeAPI].closeChatroom(jqcc.cometchat.getChatroomVars('currentroom'));
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        var nextChatBox;
                        for(chatBoxId in chatBoxesOrder){
                            nextChatBox = chatBoxId.replace('_','');
                        }
                        if($('#cometchat_container_smilies').length == 1){
                            jqcc.synergy.closeModule('smilies');
                        }
                        if($('#cometchat_container_stickers').length == 1){
                            jqcc.synergy.closeModule('stickers');
                        }
                        $("#cometchat_user_"+nextChatBox+"_popup").addClass('cometchat_tabopen');
                        if(jqcc.cometchat.getSettings().extensions.indexOf('ads') > -1){
                            jqcc.ccads.init();
                        }
                        jqcc.cometchat.setThemeVariable('openChatboxId', [nextChatBox+'']);
                        jqcc[calleeAPI].addPopup(nextChatBox,0,0);
                        if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                           if(typeof $("#cometchat_user_"+nextChatBox+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id') != 'undefined'){
                                var messageid = $("#cometchat_user_"+nextChatBox+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id').split('_')[2];
                            }
                            var message = {"id": messageid, "from": nextChatBox, "self": 0};
                            if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[nextChatBox] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[nextChatBox]==0){
                                    jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                            }
                        }
                        jqcc.cometchat.setSessionVariable('openChatboxId', nextChatBox);
                        jqcc.cometchat.orderChatboxes();
                        jqcc[calleeAPI].windowResize();
                        $('.cometchat_noactivity').css('display','block');
                    });
                    setTimeout(function(){
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        for (var key in chatBoxesOrder)
                        {
                            if(chatBoxesOrder.hasOwnProperty(key))
                            {
                                if(typeof (jqcc.synergy.addPopup)!=='undefined'){
                                    jqcc.synergy.addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                                }
                            }
                        }
                    },500);
                    $('.cometchat_noactivity').css('display','none');
                },
                chatroomTab: function(){
                    var cometchat_chatroom_search = $("#cometchat_chatroom_search");
                    var lobby_rooms = $('#lobby_rooms');
                    cometchat_chatroom_search.click(function(){
                        var searchString = $(this).val();
                        if(searchString=='<?php echo $chatrooms_language[60];?>'){
                            cometchat_chatroom_search.val('');
                            cometchat_chatroom_search.addClass('cometchat_search_light');
                        }
                    });
                    cometchat_chatroom_search.blur(function(){
                        var searchString = $(this).val();
                        if(searchString==''){
                            cometchat_chatroom_search.addClass('cometchat_search_light');
                            cometchat_chatroom_search.val('<?php echo $chatrooms_language[60];?>');
                        }
                    });
                    cometchat_chatroom_search.keyup(function(){
                        var searchString = $(this).val();
                        if(searchString.length>0&&searchString!='<?php echo $chatrooms_language[60];?>'){
                            lobby_rooms.find('div.lobby_room').hide();
                            lobby_rooms.find('span.lobby_room_1:icontains('+searchString+')').parents('div.lobby_room').show();
                            cometchat_chatroom_search.removeClass('cometchat_search_light');
                        }else{
                            lobby_rooms.find('div.lobby_room').show();
                        }
                    });
                    var cometchat_userstab = $('#cometchat_userstab');
                    var cometchat_chatroomstab = $('#cometchat_chatroomstab');
                    cometchat_chatroomstab.click(function(){
                        jqcc[calleeAPI].hideMenuPopup();
                        $('#cometchat_chatroomstab_text').text('<?php echo $chatrooms_language[100];?>');
                        if(typeof(newmess)!="undefined"){
                            clearInterval(newmess);
                        }
                        newmess = setInterval(function(){
                            if($("#cometchat_chatroomstab.cometchat_tabclick").length>0){
                                var newOneonOneMessages = 0;
                                jqcc('#cometchat_activechatboxes_popup .cometchat_msgcount').each(function(){
                                    newOneonOneMessages += parseInt(jqcc(this).children('.cometchat_msgcounttext').text());
                                });
                                if(newOneonOneMessages>0){
                                    $('#cometchat_userstab_text').text('<?php echo $language[88]?> ('+newOneonOneMessages+')');
                                }
                                setTimeout(function(){
                                    $('#cometchat_userstab_text').text('<?php echo $language[9];?> ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                                },2000);
                            }else{
                                if(typeof(newmess)!='undefined'){
                                    clearInterval(newmess);
                                }
                            }
                        },4000);
                        if(jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                            jqcc[calleeAPI].removeUnderline();
                            $("#cometchat_self .cometchat_userscontentdot").addClass('cometchat_available');
                            $('.cometchat_optionsstatus.available').css('text-decoration', 'underline');
                            $('#cometchat_userstab_text').html('<?php echo $language[9];?> ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            $("#cometchat_optionsbutton_popup").find("span.available").click();
                        }

                        if (typeof(jqcc[calleeAPI].addMessageCounter) == "function"){
                            jqcc[calleeAPI].addMessageCounter();
                        }
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                        jqcc.cometchat.chatroomHeartbeat();
                        $(this).addClass("cometchat_tabclick");
                        cometchat_userstab.removeClass("cometchat_tabclick");
                        $('#cometchat_userstab_popup').removeClass("cometchat_tabopen");
                        $('#cometchat_chatroomstab_popup').addClass("cometchat_tabopen");
                        $[calleeAPI].chatroomWindowResize();
                    });
                },
                chatroomOffline: function(){
                    $('#cometchat_chatroomstab_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_chatroomstab').removeClass('cometchat_tabclick');
                    jqcc.cometchat.leaveChatroom();
                },
                playsound: function() {
                    try	{
                        document.getElementById('messageBeep').play();
                    } catch (error) {
                        jqcc.cometchat.setChatroomVars('messageBeep',0);
                    }
                },
                sendChatroomMessage: function(chatboxtextarea) {
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height','25px');
                    $(chatboxtextarea).css('overflow-y','hidden');
                    if($('#cometchat_container_smilies').length != 1 && mobileDevice != null && !iOSmobileDevice) {
                        $[calleeAPI].chatroomWindowResize();
                    }
                    $(chatboxtextarea).focus();
                },
                createChatroom: function() {
                    $('#createtab').addClass('tab_selected');
                    $('#create').css('display','block');
                    $('div.welcomemessage').html('<?php echo $chatrooms_language[5];?>');
                },
                getTimeDisplay: function(ts,id) {
                    var time = getTimeDisplay(ts);
                    if(ts < jqcc.cometchat.getChatroomVars('todays12am')) {
							return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+" "+time.date+time.type+" "+time.month+")</span>";
                    } else {
                            return "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap+")</span>";
                    }
                },
                deletemessage: function(delid) {
                    $("#cometchat_message_"+delid).prev(".cometchat_ts").remove();
                    $("#cometchat_message_"+delid).remove();
                    $("#cometchat_usersavatar_"+delid).remove();
                },
                addChatroomMessage: function(fromid,incomingmessage,incomingid,selfadded,sent,fromname,calledfromsend,chatroomid){
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
                    var controlparameters = {"id":incomingid, "from":fromname, "fromid":fromid, "message":incomingmessage, "sent":sent};
                    if (calledfromsend != '1') {
                        settings.timestamp=incomingid;
                    }
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
                        if($("#cometchat_message_"+incomingid).length > 0) {
                                $("#cometchat_message_"+incomingid).find("span.cometchat_chatboxmessagecontent").html(message);
                        } else {
                            sentdata = '';
                            if(sent != null) {
                                var ts = parseInt(sent);
                                sentdata = $[calleeAPI].getTimeDisplay(ts,incomingid);
                            }
                            if(fromid != settings.myid) {
                                if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', fromid))=='undefined'){
                                    jqcc.cometchat.getUserDetails(fromid);
                                }
                                var fromavatar = '<a id="cometchat_usersavatar_'+incomingid+'" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');"><img class="cometchat_userscontentavatarsmall" title="'+fromname+'" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', fromid)+'"></a>';
                                temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage" id="cometchat_message_'+incomingid+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>');
                                if(jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && fromid != 0) {
                                    temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+fromid+'\');">');
                                }
                                temp += fromname+separator;
                                if(jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && fromid != 0) {
                                    temp += ('</a>');
                                }
                                temp += ('</strong></span><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                        } else {
                            temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+sentdata+'<div class="cometchat_chatboxmessage cometchat_self" id="cometchat_message_'+incomingid+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+separator+'</strong></span><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                            }
                            $("#currentroom_convotext").append(temp);

                            if($.cookie(jqcc.cometchat.getChatroomVars('cookie_prefix')+"sound") && $.cookie(jqcc.cometchat.getChatroomVars('cookie_prefix')+"sound") == 'true'){ }
                            else {
                                $[calleeAPI].playsound();
                            }
                        }
                    }

                    if(jqcc.cometchat.getChatroomVars('owner')|| jqcc.cometchat.getChatroomVars('isModerator') || (jqcc.cometchat.getChatroomVars('allowDelete') == 1 && fromid == settings.myid)) {
                        if($("#cometchat_message_"+incomingid).find(".delete_msg").length < 1) {
                            jqcc('#cometchat_message_'+incomingid).find(".cometchat_chatboxmessagefrom").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incomingid+'\');"><img class="hoverbraces" src="'+baseUrl+'modules/chatrooms/bin.png"></span>');
                        }
                        $(".cometchat_chatboxmessage").live("mouseover",function() {
                            $(this).find(".delete_msg").css('display','inline-block');
                        });
                        $(".cometchat_chatboxmessage").live("mouseout",function() {
                            $(this).find(".delete_msg").css('display','none');
                        });
                        $(".delete_msg").mouseover(function() {
                            $(this).css('display','inline-block');
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

                    if($.cookie(settings.cookiePrefix+'crstate') !== 'undefined' && $.cookie(settings.cookiePrefix+'crstate')!=null) {
                        var cc_crstate = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        var chatroomData = cc_crstate.active;
                        var messageCount = 0;
                        if(Object.keys(chatroomData).length > 0){
                            $.each(chatroomData, function(chatroomid,data) {
                                messageCount = messageCount + parseInt(data.c);
                            });
                        }
                        jqcc.cometchat.setChatroomVars('newMessages',messageCount);
                    }

                    if(jqcc('#currentroom:visible').length<1){
                        var newMessagesCount = jqcc.cometchat.getChatroomVars('newMessages');
                        $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_chatroommsgcounttext').text(newMessagesCount);
                        if(newMessagesCount > 0){
                            $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_chatroommsgcount').show();
                        }
                    }

                    $[calleeAPI].updateCRReadMessages(jqcc.cometchat.getChatroomVars('currentroom'));
                    var crreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                    jqcc.cometchat.setChatroomVars('crreadmessages',crreadmessages);
                    jqcc.crsynergy.groupbyDate();
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
                            if(id == 0){ return; }
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
                    if(event.keyCode==8&&$(chatboxtextarea).val()==''){
                        $(chatboxtextarea).css('height', '25px');
                        if(!iOSmobileDevice){
                            $[calleeAPI].chatroomWindowResize();
                        }
                    }
                    var chatboxtextareaheight  = $(chatboxtextarea).height();
                    var maxHeight = 94;
                    chatboxtextareaheight = Math.max(chatboxtextarea.scrollHeight, chatboxtextareaheight);
                    chatboxtextareaheight = Math.min(maxHeight, chatboxtextareaheight);
                    if(chatboxtextareaheight>chatboxtextarea.clientHeight && chatboxtextareaheight<maxHeight){
                        $(chatboxtextarea).css('height', chatboxtextareaheight+'px');
                    }else if(chatboxtextareaheight>chatboxtextarea.clientHeight){
                        $(chatboxtextarea).css('height', maxHeight+'px');
                        $(chatboxtextarea).css('overflow-y', 'auto');
                    }
                    if(!iOSmobileDevice){
                        $[calleeAPI].chatroomWindowResize();
                    }

                },
                hidetabs: function() {

                },
                loadLobby: function() {
                    $[calleeAPI].hidetabs();
                    $('#lobbytab').addClass('tab_selected');
                    $('#lobby').css('display','block');
                    $('div.moderator_container').html('<?php echo $chatrooms_language[1];?>');
                    clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                    if(typeof(jqcc.cometchat.getThemeVariable) == 'undefined' || jqcc.cometchat.getThemeVariable('currentStatus') != 'offline'){
                        jqcc.cometchat.chatroomHeartbeat(1);
                    }
                },
                crcheckDropDown: function(dropdown) {
                    var id = dropdown.selectedIndex;
                    if(id == 1) {
                        $('div.password_hide').css('display','block');
                    } else {
                        $('div.password_hide').css('display','none');
                    }
                    $[calleeAPI].chatroomWindowResize();
                },
                loadRoom: function(clicked) {
                    jqcc[calleeAPI].hideMenuPopup();
                    var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                    var roomno = jqcc.cometchat.getChatroomVars('currentroom');
                    var messageCounter = '0';
                    if(embeddedchatroomid==0 || (embeddedchatroomid>0 && embeddedchatroomid==roomno)){
                        if(clicked==1){
                            jqcc.cometchat.setThemeVariable('trayOpen','chatrooms');
                            jqcc.cometchat.setSessionVariable('trayOpen', 'chatrooms');
                            $('.cometchat_userchatbox').removeClass('cometchat_tabopen');
                        }
                        if($('#create').is(':visible')){
                            $(this).html('<?php echo $chatrooms_language[2];?>  &#9658;');
                            $('#create').hide('slow',function(){
                                $[calleeAPI].chatroomWindowResize();
                            });
                        }
                        $('#currentroom').css('display','block');
                        $('#currentroom').find('.cometchat_chatroomdisplayname').text(roomname);
                        $('div.welcomemessage').html('<?php echo $chatrooms_language[4];?>'+'<span> | </span>'+'<?php echo $chatrooms_language[48];?>'+'<?php echo $chatrooms_language[39];?>');

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

                        var moderatorcontainer = '<div class="mod_list_item inviteChatroomUsers"><img class="mod_option_icons" src="'+baseUrl+'themes/'+calleeAPI+'/images/inviteuser.png"/><a href="javascript:void(0);" ><?php echo $chatrooms_language[67];?></a></div><div class="mod_list_item unbanChatroomUser" id="unbanuser"><img class="mod_option_icons" src="'+baseUrl+'themes/'+calleeAPI+'/images/unbanuser.png"/><a  href="javascript:void(0);" ><?php echo $chatrooms_language[68];?></a></div>';

                        if(typeof embeddedchatroomid != "undefined" && embeddedchatroomid == 0){
                            moderatorcontainer += '<div class="mod_list_item leaveRoom" id="leaveroom"><img class="mod_option_icons" src="'+baseUrl+'themes/'+calleeAPI+'/images/leave.png"/><a  href="javascript:void(0);" ><?php echo $chatrooms_language[72];?></a></div>';
                        }

                        $('#moderator_container').html(moderatorcontainer);
                        if(jqcc.cometchat.getChatroomVars('isModerator')==undefined||jqcc.cometchat.getChatroomVars('isModerator')==0){
                           jqcc('#unbanuser').remove();
                        }
                        if(mobileDevice != null){
                            var index = settings.plugins.indexOf('screenshare');
                            if(index != -1){
                                settings.plugins.splice(index, 1);
                            }
                        }
                        $('.cometchat_prependMessages_container > .cometchat_prependMessages').text('<?php echo $chatrooms_language[74];?>');
                        $('.cometchat_prependMessages_container > .cometchat_prependMessages').attr('onclick','jqcc.synergy.prependCrMessagesInit('+roomno+')');
                        $('#currentroom_convo').attr('onscroll','jqcc.crsynergy.chatScroll('+roomno+')');

                        var pluginshtml = '';
                        var plugins = jqcc.cometchat.getChatroomVars('plugins');
                        var avchathtml = '';
                        var smilieshtml = '';
                        var filetransferhtml = '';

                        if(jqcc.cometchat.getCcvariable().callbackfn!=""&&jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                           var ccpluginindex=(plugins).indexOf('screenshare');
                           plugins.splice(ccpluginindex,1);
                        }

                        if(plugins.length > 0) {
                            for (var i=0;i<plugins.length;i++) {
                                var name = 'cc'+plugins[i];
                                if(settings.plugins[i]=='avchat'){
                                    avchathtml='<div class="cometchat_menuOption cometchat_avchatOption"><img class="ccplugins  cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+calleeAPI+'/images/avchaticon.png" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1" /></div>';
                                }else if(settings.plugins[i]=='smilies'){
                                    smilieshtml='<div class="ccplugins cometchat_smilies" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1"><img src="'+baseUrl+'/images/smiley.png" class="cometchat_smiley"/></div>';
                                }else if(settings.plugins[i]=='filetransfer'){
                                    filetransferhtml='<img src="'+baseUrl+'themes/'+calleeAPI+'/images/attachment.png" class="ccplugins cometchat_transfericon cometchat_filetransfer" title="'+$[name].getTitle()+'" name="'+name+'" to="'+roomno+'" chatroommode="1"/>';
                                }else if(typeof($[name]) == 'object') {
                                    if(name != 'ccchattime'){
                                        pluginshtml += '<div class="ccplugins cometchat_pluginsicon cometchat_'+ settings.plugins[i] + '" title="' + $[name].getTitle() + '" name="'+name+'" to="'+roomno+'" chatroommode="1"><span>'+$[name].getTitle()+'</span></div>';
                                    }
                                }
                            }
                        }
                        if($('#currentroom_left .cometchat_avchatOption').length > 0){
                            $('#currentroom_left .cometchat_avchatOption').remove();
                        }
                        $('#currentroom_left .cometchat_chatboxMenuOptions').prepend(avchathtml);
                        if($('#currentroom_left .cometchat_smilies').length > 0){
                            $('#currentroom_left .cometchat_smilies').remove();
                        }
                        $('#currentroom_left .cometchat_tabcontentinput').prepend(smilieshtml);
                        if($('#currentroom_left .cometchat_filetransfer').length > 0){
                            $('#currentroom_left .cometchat_filetransfer').remove();
                        }
                        $('#currentroom_left .cometchat_tabinputcontainer').prepend(filetransferhtml);

                        $('#plugin_container').html(pluginshtml);

                        if($('#currentroom_left #plugin_container .cometchat_pluginsicon').length==0){
                            $('#currentroom_left').find('.cometchat_pluginsOption').remove();
                        }
                    }
                    $('.ccplugins').click(function(event){
                        event.stopImmediatePropagation();
                        jqcc[calleeAPI].hideMenuPopup();
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        var roomname = jqcc.cometchat.getChatroomVars('currentroomname');
                        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
                        var tabcontenttext_height = ($(window).innerHeight()*30)/100;
                        var tabcontenttext_width = $(window).innerWidth();
                        var winHt = $(window).innerHeight();
                        var winWidth = $(window).innerWidth();
                        var caller = 'cometchat_synergy_iframe';
                        if(jqcc.cometchat.getCcvariable().callbackfn != "desktop" && window.top != window.self){
                            caller = window.frameElement.id;
                        }
                        if((window.top == window.self && !mobileDevice)|| name == 'ccclearconversation' || name == 'ccsave') {
                            var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                            jqcc[name].init(controlparameters);
                        } else if(name=='ccstickers' && mobileDevice != null){
                            if($('#cometchat_container_smilies').length == 1){
                                jqcc.synergy.closeModule('smilies');
                            }
                            if($('#cometchat_container_stickers').length == 0){
                                var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                                jqcc[name].init(controlparameters);
                                $('#currentroom_convo').css('height',tabcontenttext_height); /* 42px is added to the tabcontenttext_height to fill the height of load earlier message.*/
                                $('.cometchat_container_title').css('display','none');
                                $('#cometchat_container_stickers').css('bottom',0);
                                $('.cometchat_container_body').css('border',0);
                                jqcc.synergy.stickersKeyboard(winWidth,winHt);
                            }
                        } else if(name=='ccsmilies' && mobileDevice != null){
                            if($('#cometchat_container_stickers').length == 1){
                                jqcc.synergy.closeModule('stickers');
                            }
                            if($('#cometchat_container_smilies').length == 0){
                                var controlparameters = {"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid};
                                jqcc[name].init(controlparameters);
                                $('.cometchat_container_title').css('display','none');
                                $('#cometchat_container_smilies').css('bottom',0);
                                $('.cometchat_container_body').css('border',0);
                                jqcc.synergy.smiliesKeyboard(winWidth,winHt);
                            } else{
                                jqcc.synergy.closeModule('smilies');
                                 $('#currentroom').find('.cometchat_userchatarea').css('display','block');
                                setTimeout(function(){
                                    $('#currentroom_convo').css('height',$(window).height()-($('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+$('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+$('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                                }, 10);
                            }
                        }else {
                            var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "roomname":roomname, "roomid":roomid, "caller":caller}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        }
                    });
                    $[calleeAPI].chatroomWindowResize();
                },
                chatroomWindowResize: function() {
                    var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],winWidth=w.innerWidth||e.clientWidth||g.clientWidth,winHt=w.innerHeight||e.clientHeight||g.clientHeight;
                    var searchbar_Height = $('#cometchat_chatroom_searchbar').is(':visible') ? $('#cometchat_chatroom_searchbar').outerHeight(true) : 0;
                    var createChatroomHeight = $('#create').is(':visible') ? $('#create').outerHeight(true) : 0;
                    var lobbyroomsHeight = $('#cometchat_tabcontainer').is(':visible') ? (winHt-$('#cometchat_self_container').outerHeight(true)-$('#cometchat_tabcontainer').outerHeight(true)-$('#cometchat_trayicons').outerHeight(true)-$('#createChatroomOption').outerHeight(true)-searchbar_Height-createChatroomHeight+'px') : (winHt-$('#cometchat_self_container').outerHeight(true)-$('#cometchat_trayicons').outerHeight(true)-$('#createChatroomOption').outerHeight(true)-searchbar_Height-createChatroomHeight+'px');
                    if($('#create').is(':visible') && mobileDevice ){
                        if(winWidth<winHt){
                            $('#cometchat_lefttab').find('#lobby').css('display','block');
                        } else{
                            $('#cometchat_lefttab').find('#lobby').css('display','none');
                        }
                    }
                    if($('#chatroomusers_popup').hasClass('cometchat_tabopen')){
                        var winHt = $(window).innerHeight();
                        var winWidth = $(window).innerWidth();
                        var tabsubtitleHt = $(".cometchat_userchatarea").find('.cometchat_tabsubtitle').outerHeight(true);
                        if((winWidth > winHt) && mobileDevice){
                            $('#chatroomusers_popup').css('max-height',(winHt-tabsubtitleHt-5));
                            $('#chatroomuser_container').css('max-height',(winHt-tabsubtitleHt-5));
                        } else{
                            $('#chatroomusers_popup').css('max-height','');
                            $('#chatroomuser_container').css('max-height','');
                        }
                    }
                    if(jqcc().slimScroll && !mobileDevice){
                        $('#lobby_rooms').parent('.slimScrollDiv').css('height',lobbyroomsHeight);
                    }
                    $('#lobby_rooms').css('height',lobbyroomsHeight);
                    var prependHeight = parseInt($('.cometchat_prependMessages_container').outerHeight(true));
                    var roomConvoHeight = winHt-$('#currentroom').find('.cometchat_ad').outerHeight(true)-$('.cometchat_tabinputcontainer').outerHeight(true)-($('#currentroom_left').find('.cometchat_tabsubtitle').outerHeight(true))-prependHeight;
                    if($('#cometchat_container_stickers').length != 1 && $('#cometchat_container_smilies').length != 1 && mobileDevice != null){
                        $("#currentroom_convo").css('height',roomConvoHeight+'px');
                    }
                    if(iOSmobileDevice && $('#cometchat_container_stickers').length != 1 && $('#cometchat_container_smilies').length != 1){
                        $('#currentroom').find('.cometchat_userchatarea').css('display','block');
                        $('#currentroom_convo').css('height',$(window).height()-(jqcc('#currentroom').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#currentroom').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#currentroom').find('.cometchat_prependMessages').outerHeight(true)));
                    }
                    if(jqcc().slimScroll && !mobileDevice){
                        $("#currentroom_convo").css('height',roomConvoHeight+'px');
                        $("#currentroom_convo").parent("div.slimScrollDiv").css('height',roomConvoHeight+'px');
                    } else {
                        $("#currentroom_convo").css('overflow','auto');
                    }
                },
                kickid: function(kickid) {
                    $("#chatroom_userlist_"+kickid).remove();
                },
                banid: function(banid) {
                    $("#chatroom_userlist_"+banid).remove();
                },
                chatroomScrollDown: function(forced) {
                	if(enableType != 2 && settings.newMessageIndicator == 1 && ($('#currentroom_convotext').length > 0) && ($('#currentroom_convotext').outerHeight()+$('#currentroom_convotext').offset().top-$('#currentroom_convo').height()-$('#currentroom_convo').offset().top-(2*$('.cometchat_chatboxmessage').outerHeight(true))>0)){
                        if(($('#currentroom_convo').height()-$('#currentroom_convotext').outerHeight()) < 0){
                        	if(forced) {
    	                        if(jqcc().slimScroll && !mobileDevice){
    	                            $('#currentroom_convo').slimScroll({scroll: '1'});
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
                                        if(jqcc().slimScroll && !mobileDevice){
                                            $('#currentroom_convo').slimScroll({scroll: '1'});
                                        } else {
                                            setTimeout(function() {
                                                $("#currentroom_convo").scrollTop(50000);
                                            },100);
                                        }
                                        $('.talkindicator').fadeOut();
                                    });
                                    $('#currentroom_convo').scroll(function(){
                                        if($('#currentroom_convotext').outerHeight() + $('#currentroom_convotext').offset().top - $('#currentroom_convo').offset().top <= $('#currentroom_convo').height()){
                                            $('.talkindicator').fadeOut();
                                        }
                                    });
                                }
                        	}
                        }
                    }else{
                        if(jqcc().slimScroll && !mobileDevice){
                            $('#currentroom_convo').slimScroll({scroll: '1'});
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
                    if(($.trim( string )).length == 0) {
                        return false;
                    }
                    var name = document.getElementById('name').value;
                    name = (name).replace(/'/g, "%27");
                    var type = document.getElementById('type').value;
                    var password = document.getElementById('password').value;
                    if(name != '' && name != null && name != '<?php echo $chatrooms_language[27];?>') {
                        name = name.replace(/^\s+|\s+$/g,"");
                        if(type == 1 && password == '') {
                            alert ('<?php echo $chatrooms_language[26];?>');
                            return 'invalid password';
                        }
                        if(type == 0 || type == 2) {
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
                    if(typeof(window.innerHeight) == 'number') {
                        windowHeight = window.innerHeight;
                    } else {
                        if(document.documentElement && document.documentElement.clientHeight) {
                            windowHeight = document.documentElement.clientHeight;
                        } else {
                            if(document.body && document.body.clientHeight) {
                                windowHeight = document.body.clientHeight;
                            }
                        }
                    }
                    return windowHeight;
                },
                crgetWindowWidth: function() {
                    var windowWidth = 0;
                    if(typeof(window.innerWidth) == 'number') {
                        windowWidth = window.innerWidth;
                    } else {
                        if(document.documentElement && document.documentElement.clientWidth) {
                            windowWidth = document.documentElement.clientWidth;
                        } else {
                            if(document.body && document.body.clientWidth) {
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
                    if(owner || isModerator) {
                    	jqcc.cometchat.setChatroomVars('isModerator',1);
                    } else {
                        jqcc('#currentroomtab').html('<a href="javascript:void(0);" show=0 onclick='+switchroom+'>'+name+'</a>');
                        jqcc.cometchat.setChatroomVars('isModerator',0);
                    }
                    jqcc.cometchat.chatroomHeartbeat(1);
                    jqcc('#currentroom_convotext').html('');
                    jqcc("#chatroomuser_container").html('');
                },
                leaveRoomClass : function(currentroom) {
                    jqcc("#cometchat_chatroomlist_"+currentroom).removeClass("cometchat_chatroomselected");
                },
                removeCurrentRoomTab : function(id) {
                    jqcc('.lobby_rooms').find('#cometchat_chatroomlist_'+id).first().css('display','none');
                    var cc_chatroom = JSON.parse($.cookie('<?php echo $cookiePrefix;?>crstate'));
                    var chatroomdata = cc_chatroom.active;
                    if(Object.keys(chatroomdata).length == 0){
                        $('#currentroom').hide();
                    }
                },
                chatroomLogout : function() {
                },
                loadChatroomList : function(item) {
                    var createChatroom='';
                    var chatroomitem = $[calleeAPI].getActiveChatrooms(item);
                    var activeChatroomIds = Object.keys(chatroomitem);
                    var activeChatroomhtml = jqcc.synergy.activeChatrooms(item);
                    var temp = '';
                    if(Object.keys(item).length == activeChatroomIds.length){
                        temp = activeChatroomhtml;
                    } else {
                        temp = activeChatroomhtml+'<div style="font-weight:bold;" class="cometchat_chatroomtitle"><hr style="height:3px;" class="hrleft"><?php echo $chatrooms_language[77];?><hr style="height:3px;" class="hrright"></div>';
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

                            if(room.status == 'available') {
                                onlineNumber++;
                            }
                            var selected = '';

                            if(jqcc.cometchat.getChatroomVars('currentroom') == room.id) {
                                selected = ' cometchat_chatroomselected';
                            }
                            var roomtype = '';
                            var roomowner = '';
                            var deleteroom = '';
                            var renameChatroom = '';

                            if(room.type == 1) {
                                roomtype = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/lock.png" />';
                            }

                            if(room.s == 1) {
                                roomowner = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/user.png" />';
                            }

                            if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                                deleteroom = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/remove.png" />';
                                renameChatroom = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/pencil_buddylist.png" />';
                            }

                            if(room.s == 2) {
                                room.s = 1;
                            }
                            temp += '<div id="cometchat_chatroomlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="$(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="$(this).removeClass(\'cometchat_chatroomlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\',\'1\',\'1\');" ><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><span class="lobby_room_6" title="<?php echo $chatrooms_language[80];?>" onclick="javascript:jqcc.'+[calleeAPI]+'.renameChatroom(event,\''+room.id+'\');">'+renameChatroom+'</span><div><span class="lobby_room_2" '+userCountCss+' title="'+room.online+' <?php echo $chatrooms_language[34];?>">('+room.online+')</span><span class="lobby_room_1" title="'+longname+'"><span class="currentroomname">'+longname+'</span></span></div></span><div style="clear:both"></div></div>';
                        }
                    });
                    if(Object.keys(item).length != 0) {
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
                                    if((incoming.message).indexOf('has shared a file')!=-1){
                                        if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                                            if(incoming.message.indexOf('target')>=-1){
                                                incoming.message=incoming.message.replace(/target="_blank"/g,'');
                                            }
                                        }
                                    }
                                    if((incoming.message).indexOf('has shared a handwritten message')!=-1){
                                        if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                                            if(incoming.message.indexOf('href')>=-1){
                                                var start = (incoming.message).indexOf('href');
                                                var end = (incoming.message).indexOf('target');
                                                var HtmlString=(incoming.message).slice(start,end);
                                                incoming.message=(incoming.message).replace(HtmlString,'');
                                            }
                                        }
                                    }
                                if($("#cometchat_message_"+incoming.id).length > 0) {
                                    $("#cometchat_message_"+incoming.id).find("span.cometchat_chatboxmessagecontent").html(message);
                                } else {
                                    var ts = parseInt(incoming.sent)*1000;
                                    if(incoming.fromid != settings.myid) {
                                        if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid))=='undefined'){
                                            jqcc.cometchat.getUserDetails(incoming.fromid);
                                        }
                                        var fromavatar = '<a id="cometchat_usersavatar_'+incoming.id+'" href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');"><img class="cometchat_userscontentavatarsmall" title="'+fromname+'" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid)+'"></a>';
                                        temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+fromavatar+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>');
                                        if(jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                            temp += ('<a href="javascript:void(0)" onclick="javascript:parent.jqcc.cometchat.chatWith(\''+incoming.fromid+'\');">');
                                        }
                                        temp += fromname+':';
                                        if(jqcc.cometchat.getChatroomVars('checkBarEnabled')==1 && incoming.fromid != 0) {
                                            temp += ('</a>');
                                        }
                                        temp += ('&nbsp;&nbsp;</strong></span><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                                        beepNewMessages++;
                                    } else {
                                        temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'"  msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage cometchat_self" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+':&nbsp;&nbsp;</strong></span><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                                    }
                                }
                                $('#currentroom_convotext').append(temp);
                                if(jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    if($("#cometchat_message_"+incoming.id).find(".delete_msg").length < 1) {
                                        jqcc('#cometchat_message_'+incoming.id).find(".cometchat_chatboxmessagefrom").after('<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');"><img class="hoverbraces" src="'+baseUrl+'modules/chatrooms/bin.png"></span>');
                                    }
                                    $(".cometchat_chatboxmessage").live("mouseover",function() {
                                        $(this).find(".delete_msg").css('display','inline-block');
                                    });
                                    $(".cometchat_chatboxmessage").live("mouseout",function() {
                                        $(this).find(".delete_msg").css('display','none');
                                    });
                                    $(".delete_msg").mouseover(function() {
                                        $(this).css('display','inline-block');
                                    });
                                    $(".delete_msg").mouseout(function() {
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

                        var cc_crstate = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        var chatroomData = cc_crstate.active;
                        var messageCount = 0;
                        if(Object.keys(chatroomData).length > 0){
                            $.each(chatroomData, function(chatroomid,data) {
                                messageCount = messageCount + parseInt(data.c);
                            });
                        }
                        jqcc.cometchat.setChatroomVars('newMessages',messageCount);

                        if(jqcc('#currentroom:visible').length<1){
                            var newMessagesCount = jqcc.cometchat.getChatroomVars('newMessages');
                            $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_chatroommsgcounttext').text(newMessagesCount);
                            if(newMessagesCount > 0){
                                $('#cometchat_chatroomlist_'+jqcc.cometchat.getChatroomVars('currentroom')).find('.cometchat_chatroommsgcount').show();
                            }
                        }
                        if($.cookie(settings.cookie_prefix+"sound") && $.cookie(settings.cookie_prefix+"sound") == 'true') { } else {
                            if(beepNewMessages > 0 && fetchedUsers == 0) {
                                $[calleeAPI].playsound();
                            }
                        }
                        if(($("#currentroom_convo")[0].scrollHeight) - ($("#currentroom_convo").scrollTop() + $("#currentroom_convo").innerHeight()) > 80) {
                            $('.talkindicator').fadeIn();
                        }
                        $[calleeAPI].updateCRReadMessages(jqcc.cometchat.getChatroomVars('currentroom'));
                        var crreadmessages = jqcc.cometchat.getFromStorage("crreadmessages");
                        jqcc.cometchat.setChatroomVars('crreadmessages',crreadmessages);
                        jqcc.crsynergy.groupbyDate();
                    },
                    silentRoom: function(id, name, silent) {
                        if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'  || mobileDevice){
                            loadCCPopup(settings.baseUrl+'modules/chatrooms/chatrooms.php?id='+id+'&basedata='+settings.basedata+'&name='+name+'&silent='+silent+'&action=passwordBox','passwordBox','status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=320,height=130',320, 110, name, null, null,null,null,1);
                        }else if(settings.lightboxWindows == 1) {
                            var controlparameters = {"type":"modules", "name":"core", "method":"loadCCPopup", "params":{"url": settings.baseUrl+'modules/chatrooms/chatrooms.php?id='+id+'&basedata='+settings.basedata+'&name='+name+'&silent='+silent+'&action=passwordBox', "name":"passwordBox", "properties":"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=320,height=130", "width":"320", "height":"110", "title":name, "force":null, "allowmaximize":null, "allowresize":null, "allowpopout":null, "windowMode":null}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var temp = prompt('<?php echo $chatrooms_language[8];?>','');
                            if(temp) {
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
                        var chatroomDivHeight = jqcc('#cometchat_chatroomlist_'+id).outerHeight();
                        jqcc('#cometchat_chatroomlist_'+id).append('<div class="cometchat_chatroom_overlay"><input class="chatroomName" type="textbox" value="0" style="display:none;" /><a title="<?php echo $chatrooms_language[51];?>" class="cancel_edit" href="javascript:void(0);" onclick="javascript:jqcc.'+jqcc.cometchat.getChatroomVars('calleeAPI')+'.canceledit(event,\''+id+'\');" style="display:none;"><?php echo $chatrooms_language[51];?></a></div>');

                        jqcc('.cometchat_chatroom_overlay').css('height',chatroomDivHeight);
                        var currentroomname = jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').html();
                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').css('visibility','hidden');
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').val(currentroomname);
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_3').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_4').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_5').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_6').hide();

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
                                        jqcc.cometchat.chatroomHeartbeat(1);
                                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').hide();
                                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').css('visibility','visible');
                                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').hide();
                                        if(currentroomname == jqcc('.cometchat_chatroomdisplayname').text()){
                                            jqcc('.cometchat_chatroomdisplayname').text(name);
                                        }
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
                        jqcc('#cometchat_chatroomlist_'+id).find('.currentroomname').css('visibility','visible');
                        jqcc('#cometchat_chatroomlist_'+id).find('.chatroomName').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.cancel_edit').hide();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_3').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_4').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_5').show();
                        jqcc('#cometchat_chatroomlist_'+id).find('.lobby_room_6').show();
                    },
                    updateChatroomsTabtext: function(){
                        $('#cometchat_chatroomstab_text').text('<?php echo $chatrooms_language[100];?>');
                    },
                    minimizeChatrooms: function(){
                        if(jqcc('#create').is(':visible')){
                            jqcc('#createChatroomOption').html('<?php echo $chatrooms_language[2];?>  &#9658;');
                            jqcc('#create').hide('slow',function(){
                                jqcc[settings.theme].chatroomWindowResize();
                            });
                        }
                        jqcc.cometchat.leaveChatroom();
                        jqcc.cometchat.setChatroomVars('newMessages',0);
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
                            if(settings.users[user.id] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                var nowTime = new Date();
                                var ts = Math.floor(nowTime.getTime()/1000);
                                $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+user.n+'<?php echo $chatrooms_language[14]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                $[calleeAPI].chatroomScrollDown();
                            }
                            if(parseInt(user.b)!=1) {
                                var avatar = '';
                                if(user.a != '') {
                                    avatar = '<span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src='+user.a+'></span>';
                                }
                                newUsers[user.id] = 1;
                                newUsersName[user.id] = user.n;
                                userhtml='<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft"><?php echo $chatrooms_language[61]?><hr style="height:3px;" class="hrright"></div>';
                                moderatorhtml='<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft"><?php echo $chatrooms_language[62]?><hr style="height:3px;" class="hrright"></div>';
                                if($.inArray(user.id ,jqcc.cometchat.getChatroomVars('moderators') ) != -1 ) {
                                    if(user.id == settings.myid || (typeof embeddedchatroomid != "undefined" && embeddedchatroomid > 0 && settings.apiAccess == 0)) {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp1 += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid="'+user.id+'" owner="'+settings.owner+'" username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                } else {
                                    if(user.id == settings.myid || (typeof embeddedchatroomid != "undefined" && embeddedchatroomid > 0 && settings.apiAccess == 0)) {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist" style="cursor:default !important;">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    } else {
                                        temp += '<div id="chatroom_userlist_'+user.id+'" class="cometchat_chatroomlist loadChatroomPro" onmouseover="jqcc(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_chatroomlist_hover\');" userid="'+user.id+'" owner="'+settings.owner+'" username="'+user.n+'">'+avatar+'<span class="cometchat_userscontentname">'+longname+'</span></div>';
                                    }
                                }
                            }
                        });
                        for (user in settings.users) {
                            if(settings.users.hasOwnProperty(user)) {
                                if(newUsers[user] != 1 && settings.initializeRoom == 0 && settings.hideEnterExit == 0) {
                                    var nowTime = new Date();
                                    var ts = Math.floor(nowTime.getTime()/1000);
                                    $("#currentroom_convotext").append('<div class="cometchat_chatboxalert" id="cometchat_message_0">'+settings.usersName[user]+'<?php echo $chatrooms_language[13]?>'+$[calleeAPI].getTimeDisplay(ts,user.id)+'</div>');
                                    $[calleeAPI].chatroomScrollDown();
                                }
                            }
                        }
                        if(temp1 != "" && temp !="")
                            jqcc('#chatroomuser_container').html(moderatorhtml+temp1+userhtml+temp);
                        else if(temp == "")
                            jqcc('#chatroomuser_container').html(moderatorhtml+temp1);
                        else
                            jqcc('#chatroomuser_container').html(userhtml+temp);

                        jqcc.cometchat.setChatroomVars('users',newUsers);
                        jqcc.cometchat.setChatroomVars('usersName',newUsersName);
                        jqcc.cometchat.setChatroomVars('initializeRoom',0);
                    },
                    loadCCPopup: function(url,name,properties,width,height,title,force,allowmaximize,allowresize,allowpopout){
                        if(jqcc.cometchat.getChatroomVars('lightboxWindows') == 1) {
                            var controlparameters = {"type":"modules", "name":"chatrooms", "method":"loadCCPopup", "params":{"url":url, "name":name, "properties":properties, "width":width, "height":height, "title":title, "force":force, "allowmaximize":allowmaximize, "allowresize":allowresize, "allowpopout":allowpopout}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        } else {
                            var w = window.open(url,name,properties);
                            w.focus();
                        }
                    },
                    inviteUsertab: function(){
                       var chatroomPro = $('#cometchat_trayicon_loadChatroomPro_iframe');
                       $('.cometchat_container_title').css('display','none');
                       chatroomPro.css({'border':'solid grey 3px','border-radius':'5px','height':(chatroomPro.height()-5),'width':(chatroomPro.width()-17)});
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
                        var temp = '';
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
                                count = count + 1;
                                var fromname = incoming.from;
                                if((incoming.message).indexOf('has shared a file')!=-1){
                                    if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                                        if(incoming.message.indexOf('target')>=-1){
                                            incoming.message=incoming.message.replace(/target="_blank"/g,'');
                                        }
                                    }
                                }
                                if((incoming.message).indexOf('has shared a handwritten message')!=-1){
                                    if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                                        if(incoming.message.indexOf('href')>=-1){
                                            var start = (incoming.message).indexOf('href');
                                            var end = (incoming.message).indexOf('target');
                                            var HtmlString=(incoming.message).slice(start,end);
                                            incoming.message=(incoming.message).replace(HtmlString,'');
                                        }
                                    }
                                }
                                var ts = parseInt(incoming.sent)*1000;
                                if (jqcc.cometchat.getChatroomVars('owner') || jqcc.cometchat.getChatroomVars('isModerator') || (incoming.fromid == settings.myid && jqcc.cometchat.getChatroomVars('allowDelete') == 1)) {
                                    deleteMessage = '<span class="delete_msg" onclick="javascript:jqcc.cometchat.confirmDelete(\''+incoming.id+'\');"><img class="hoverbraces" src="'+baseUrl+'modules/chatrooms/bin.png"></span>';
                                }
                                if (incoming.fromid != settings.myid) {
                                    if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid))=='undefined'){
                                        jqcc.cometchat.getUserDetails(incoming.fromid);
                                    }
                                    var fromavatar = '<a id="cometchat_usersavatar_'+incoming.id+'" href="javascript:void(0)"><img class="cometchat_userscontentavatarsmall" title="'+fromname+'" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.fromid)+'"></a>';
                                    temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+fromavatar+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>');
                                    if (settings.apiAccess && incoming.fromid != 0) {
                                        temp += '';
                                    }
                                    temp += fromname;
                                    if (settings.apiAccess && incoming.fromid != 0) {
                                        temp += ('&nbsp;:');
                                    }
                                    temp += ('&nbsp;&nbsp;</strong></span><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                                } else {
                                    temp += ('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+$[calleeAPI].getTimeDisplay(ts,incoming.from)+'<div class="cometchat_chatboxmessage cometchat_self" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagefrom"><strong>'+fromname+':&nbsp;&nbsp;</strong></span>'+deleteMessage+'<span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>');
                                }
                            }
                        });
                        jqcc('#currentroom_convotext').prepend(temp);

                        $(".cometchat_chatboxmessage").live("mouseover",function() {
                            $(this).find(".delete_msg").css('display','inline-block');
                        });
                        $(".cometchat_chatboxmessage").live("mouseout",function() {
                            $(this).find(".delete_msg").css('display','none');
                        });
                        $(".delete_msg").mouseover(function() {
                            $(this).css('display','inline-block');
                        });
                        $(".delete_msg").mouseout(function() {
                        });

                        $('.cometchat_prependMessages').text('<?php echo $chatrooms_language[74];?>');
                        if((count - parseInt(settings.prependLimit) < 0)){
                            $('.cometchat_prependMessages').text('<?php echo $chatrooms_language[75];?>');
                            jqcc('.cometchat_prependMessages').attr('onclick','');
                            jqcc('.cometchat_prependMessages').css('cursor','default');
                        }else{
                            jqcc('.cometchat_prependMessages').attr('onclick','jqcc.synergy.prependCrMessagesInit('+id+')');
                        }
                        jqcc.crsynergy.groupbyDate();
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
                        var userCountCss = "style='display:none'";
                        if(settings.showChatroomUsers == 1){
                            userCountCss = '';
                        }

                        if(Object.keys(chatroomitem).length > 0){
                            temp = '<div class="cometchat_chatroomtitle" style="font-weight:bold;"><hr class="hrleft" style="height:3px;"><?php echo $chatrooms_language[78];?><hr class="hrright" style="height:3px;"></div>';
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
                                roomtype = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/lock.png" />';
                            }

                            if (room.s == 1) {
                                roomowner = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/user.png" />';
                            }

                            if((room.s == 1 || jqcc.cometchat.checkModerator() == 1) && room.createdby != 0){
                                deleteroom = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/remove.png" />';
                                renameChatroom = '<img src="'+baseUrl+'themes/'+calleeAPI+'/images/pencil_buddylist.png" />';
                            }

                            if (room.s == 2) {
                                room.s = 1;
                            }
                            temp += '<div id="cometchat_chatroomlist_'+room.id+'" class="lobby_room'+selected+'" onmouseover="$(this).addClass(\'cometchat_chatroomlist_hover\');" onmouseout="$(this).removeClass(\'cometchat_chatroomlist_hover\');" onclick="javascript:jqcc.cometchat.chatroom(\''+room.id+'\',\''+urlencode(shortname)+'\',\''+room.type+'\',\''+room.i+'\',\''+room.s+'\',\'1\',\'1\');" ><span class="lobby_room_3">'+roomtype+'</span><span class="lobby_room_4" title="<?php echo $chatrooms_language[58];?>" onclick="javascript:jqcc.cometchat.deleteChatroom(event,\''+room.id+'\');">'+deleteroom+'</span><span class="lobby_room_5">'+roomowner+'</span><span class="lobby_room_6" title="<?php echo $chatrooms_language[80];?>" onclick="javascript:jqcc.'+[calleeAPI]+'.renameChatroom(event,\''+room.id+'\');">'+renameChatroom+'</span><div><span class="lobby_room_2" '+userCountCss+' title="'+room.online+' <?php echo $chatrooms_language[34];?>">('+room.online+')</span><span class="lobby_room_1" title="'+longname+'"><span class="currentroomname">'+longname+'</span></span></div></span><div style="clear:both"></div></div>';

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
                        if($.cookie(settings.cookiePrefix+'crstate') != null){
                            var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                            var chatroomdata = cc_chatroom.active;
                            $.each(chatroomdata, function (chatroomid, data){
                                chatroomid = chatroomid.replace('_','');
                                var cometchat_chatroommsgcount = $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.cometchat_chatroommsgcounttext');
                                if(chatroomid != jqcc.cometchat.getChatroomVars('currentroom') && data.c != 0){
                                    if(cometchat_chatroommsgcount.length > 0) {
                                        $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.cometchat_chatroommsgcounttext').text(data.c);
                                    } else {
                                        $('.lobby_rooms').find('#cometchat_chatroomlist_'+chatroomid).find('.lobby_room_3').after("<span class='cometchat_chatroommsgcount'><div class='cometchat_chatroommsgcounttext'>"+data.c+"</div></span>");
                                    }
                                }
                            });
                        } else {
                            var cc_chatroom = {"active":{}, "open":""};
                            cc_chatroom = JSON.stringify(cc_chatroom);
                            $.cookie(settings.cookiePrefix+'crstate', cc_chatroom, {path: '/'});
                        }
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
                    closeChatroom: function(id){
                        var chatroomId = '_'+id;
                        var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        var chatroomData = cc_chatroom.active;
                        var previousChatroomId = 0;

                        if(Object.keys(chatroomData).length > 0) {
                            var controlparameters = {"name":"active", "val":chatroomData, "roomno":id, "messageCounter":chatroomData[chatroomId].c, "isOpen":"0"};
                            jqcc.cometchat.setCrSessionVariable(controlparameters);
                        }

                        var cc_chatroom_new = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        chatroomData = cc_chatroom_new.active;
                        $.each(chatroomData, function(chatroomid,data) {
                            if(chatroomData[chatroomid].o == '0'){
                                delete chatroomData[chatroomid];
                            }
                        });
                        if(Object.keys(chatroomData).length > 0){
                            previousChatroomId = Object.keys(chatroomData).reverse()[0].replace('_','');
                        } else {
                            previousChatroomId = '';
                        }
                            jqcc.cometchat.setChatroomVars('currentroom', previousChatroomId);
                        var controlparameters = {"name":"open", "val":previousChatroomId};
                        jqcc.cometchat.setCrSessionVariable(controlparameters);

                        if(Object.keys(chatroomData).length > 0){
                            if (typeof(jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].switchChatroom) == "function"){
                                jqcc[jqcc.cometchat.getChatroomVars('calleeAPI')].switchChatroom(previousChatroomId,1);
                            }
                            $('#currentroom').css('display','block');
                        }
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
    jqcc.synergy=function(){};
}

jqcc.extend(jqcc.synergy, jqcc.crsynergy);

jqcc(document).ready(function(){
    jqcc('.leaveRoom').live('click',function(){
        jqcc.cometchat.leaveChatroom();
    });

    jqcc( "#password" ).keyup(function() {
        if(jqcc("#password").val() == ' '){
            alert("<?php echo $chatrooms_language[82]; ?>");
            jqcc("#password").val('');
        }
    });
    jqcc('.inviteChatroomUsers').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var lang = '<?php echo $chatrooms_language[21];?>';
        var caller = 'cometchat_synergy_iframe';
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=invite&caller='+caller+'&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&popoutmode='+popoutmode;
        var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);

        if(jqcc.cometchat.getCcvariable().callbackfn=='desktop' || mobileDevice){
            jqcc.cometchat.inviteChatroomUser(1);
        }else{
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
        }
    });

    jqcc('.unbanChatroomUser').live('click',function(){
        var baseurl = jqcc.cometchat.getBaseUrl();
        var basedata = jqcc.cometchat.getBaseData();
        var roomid = jqcc.cometchat.getChatroomVars('currentroom');
        var roompass = jqcc.cometchat.getChatroomVars('currentp');
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var popoutmode = jqcc.cometchat.getChatroomVars('popoutmode');
        var caller = 'cometchat_synergy_iframe';
        var lang = '<?php echo $chatrooms_language[21];?>';
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=unban&caller='+caller+'&roomid='+roomid+'&inviteid='+roompass+'&basedata='+basedata+'&roomname='+roomname+'&popoutmode='+popoutmode+'&time='+Math.random();
        var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);

        if(jqcc.cometchat.getCcvariable().callbackfn=='desktop' || mobileDevice){
            jqcc.cometchat.unbanChatroomUser(1);
        }else{
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
        jqcc.cometchat.setChatroomVars('checkBarEnabled',1);
        var lang = '<?php echo $chatrooms_language[21];?>';
        var caller = 'cometchat_synergy_iframe';
        var roomname = urlencode(jqcc.cometchat.getChatroomVars('currentroomname'));
        var cbfn_desktop='';
        var apiAccess = (typeof (jqcc.cometchat.chatWith) == "function");
        if(enableType == 1 && embeddedchatroomid == 0 && chatroomsonly == 0) {
            apiAccess = 0;
        }
        if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'){
            cbfn_desktop='&callbackfn=desktop';
        }
        var url = baseurl+'modules/chatrooms/chatrooms.php?action=loadChatroomPro&caller='+caller+'&apiAccess='+apiAccess+'&owner='+owner+'&roomid='+roomid+'&basedata='+basedata+'&inviteid='+uid+'&roomname='+roomname+cbfn_desktop;

        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            var controlparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":url, "action":"loadChatroomPro", "lang":username, "synergy":1}};
            controlparameters = JSON.stringify(controlparameters);
            if(typeof(parent) != 'undefined' && parent != null && parent != self){
                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
            } else {
                window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
            }
        } else {
            var controlparameters = {};
            jqcc.cometchat.loadChatroomPro(uid,owner,username);
        }
    });
});
