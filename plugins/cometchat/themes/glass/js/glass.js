(function($){
    $.ccglass = (function(){
        var settings = {};
        var baseUrl;
        var language;
        var trayicon;
        var typingSenderTimer;
        var typingRecieverTimer;
        var typingSenderFlag = 1;
        var typingReceiverFlag = {};
        var resynchTimer;
        var notificationTimer;
        var chatboxOpened = {};
        var undeliveredmessages = [];
        var unreadmessages = [];
        var trayWidth = 0;
        var siteOnlineNumber = 0;
        var olddata = {};
        var tooltipPriority = 0;
        var desktopNotifications = {};
        var webkitRequest = 0;
        var lastmessagetime = Math.floor(new Date().getTime());
        var favicon;
        var msg_beep = '';
        var option_button = '';
        var user_tab = '';
        var chat_boxes = '';
        var chat_left = '';
        var chat_right = '';
        var usertab2 = '';
        var checkfirstmessage;
        var chatboxHeight = parseInt('<?php echo $chatboxHeight; ?>');
        var chatboxWidth = parseInt('<?php echo $chatboxWidth; ?>');
        var bannedMessage = '<?php echo $bannedMessage;?>';
        var lastseen = 0;
        var lastseenflag = false;
        var messagereceiptflag = 0;
        return {
            playSound: function(){
                var flag = 0;
                try{
                    if(settings.messageBeep==1&&(settings.beepOnAllMessages==1||(settings.beepOnAllMessages==0&&checkfirstmessage==1))){
                        document.getElementById('messageBeep').play();
                    }
                }catch(error){
                }
            },
            initialize: function(){
                settings = jqcc.cometchat.getSettings();
                baseUrl = jqcc.cometchat.getBaseUrl();
                language = jqcc.cometchat.getLanguage();
                trayicon = jqcc.cometchat.getTrayicon();
                var trayData = '';
                var trayDataBody = '';
                if(settings.windowFavicon==1){
                    favicon = new Favico({
                        animation: 'pop'
                    });
                }
                $("body").append('<div id="cometchat"></div><div id="cometchat_hidden"><div id="cometchat_hidden_content"></div></div><div id="cometchat_tooltip"><div class="cometchat_tooltip_content"></div></div>');
                for(x in trayicon){
                    if(trayicon.hasOwnProperty(x)){
                        var icon = trayicon[x];
                        var onmouseoverURL = baseUrl+'themes/'+settings.theme+'/images/modules/dark/'+icon[0]+'.png';
                        var onmouseoutURL = baseUrl+'themes/'+settings.theme+'/images/modules/'+icon[0]+'.png';
                        trayData += ('<div id="cometchat_trayicon_'+x+'" class="cometchat_trayicon" onmouseover="javascript:jqcc(this).find(\'img\').attr(\'src\',\''+onmouseoverURL+'\');" onmouseout="javascript:if(!jqcc(this).hasClass(\'cometchat_trayclick\'))jqcc(this).find(\'img\').attr(\'src\',\''+onmouseoutURL+'\');"  ><div class="cometchat_trayicon_bordertop"></div><div class="cometchat_trayiconimage"><img class="cometchat_trayiconimage_content " src="'+onmouseoutURL+'"  width="16px" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\');"></div>');
                        if(trayicon[x][8]){
                            trayData += '<div class="cometchat_trayicontext">'+trayicon[x][1]+'</div>';
                        }
                        trayData += '</div>';
                        cometchat_popout = "";
                        if(x=="chatrooms"||x=="games"||x=="broadcastmessage"){
                            cometchat_popout = '<div class="cometchat_popout cometchat_pop'+x+'"></div>';
                        }
                        if(icon[3]=='_popup'){
                            trayDataBody += '<div id="cometchat_trayicon_'+x+'_popup" class="cometchat_traypopup" style="display:none"><div class="cometchat_traytitle"><div class="cometchat_name">'+icon[1]+'</div><div class="cometchat_minimizebox"></div>'+cometchat_popout+'<br clear="all"/></div><div class="cometchat_traycontent"><div class="cometchat_traycontenttext" style="height:'+icon[5]+'px"><div class="cometchat_loading"></div><iframe class="cometchat_iframe" allowtransparency="true" frameborder=0 width="'+icon[4]+'" height="'+icon[5]+'" id="cometchat_trayicon_'+x+'_iframe"></iframe></div></div></div>';
                        }
                        if(!isNaN(icon[6])&&Number(icon[6])>0){
                            trayWidth += Number(icon[6]);
                        }else{
                            trayWidth += 16;
                        }
                        trayWidth += 18;
                    }
                }
                var cc_state = $.cookie(settings.cookiePrefix+'state');
                var number = 0;
                if(cc_state!=null){
                    var cc_states = cc_state.split(/:/);
                    number = cc_states[3];
                }
                var ccauthpopup = '';
                var ccauthlogout = '';
                if(settings.ccauth.enabled=="1"){
                    ccauthlogout = '<div class="cometchat_tooltip" id="cometchat_authlogout" title="'+language[80]+'"></div>';
                }
                if(settings.ccauth.enabled=="1"){
                    ccauthpopup = '<div id="cometchat_auth_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[77]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_auth_popup" title="'+language[78]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[79]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="social_login">';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\',\'location=0,status=0,scrollbars=0,width=1000,height=600\')" src="'+baseUrl+'themes/'+settings.theme+'/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                }
                var count = 140;
                var lastseenoption = '';
                var messagereceiptoption = '';
                if(settings.lastseen == 1){
                    lastseenoption = '<div style="clear:both"></div><div><input type="checkbox" id="cometchat_lastseen" style="vertical-align: -2px;">'+language[108]+'</div>';
                } else{
                    lastseenflag = true;
                }
                if(settings.messagereceiptEnabled == 1 && settings.cometserviceEnabled == 1){
                    messagereceiptoption = '<div style="clear:both"></div><div><input type="checkbox" id="cometchat_messagereceipt" style="vertical-align: -2px;">'+language['disable_message_receipt']+'</div>';
                }
                var baseCode = '<div id="cometchat_trayicons">'+trayDataBody+'</div><div id="cometchat_base"><div id="cometchat_hide" onclick="jqcc.cometchat.hideBar();"></div><div id="cometchat_optionsbutton" class="cometchat_tab"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div></div>'+ccauthpopup+'<div id="cometchat_trayicons">'+trayData+'</div><span id="cometchat_userstab" class="cometchat_tab">'+'<div class="cometchat_userstab_bottomborder"></div>'+'<span id="cometchat_userstab_icon"></span><span id="cometchat_userstab_text">'+language[9]+' ('+number+')</span></span><div id="cometchat_chatbox_right"><span class="cometchat_tabtext"></span><span style="top:-5px;display:none" class="cometchat_tabalertlr"></span></div><div id="cometchat_chatboxes"><div id="cometchat_chatboxes_wide"></div></div><div id="cometchat_chatbox_left"><span class="cometchat_tabtext"></span><span class="cometchat_tabalertlr" style="top:-5px;display:none;"></span></div></div><div id="cometchat_optionsbutton_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_optionspopup_bottomborder"></div><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[0]+'</div>'+ccauthlogout+'<div class="cometchat_minimizebox"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea" maxlength="140"></textarea><div style="overflow:hidden;"><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusmessagecount">'+count+'</div></div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div>'+lastseenoption+messagereceiptoption+'</div></div></div><div id="cometchat_userstab_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[12]+'</div><div class="cometchat_minimizebox"></div><br clear="all"/></div><div class="cometchat_tabsubtitle" id="cometchat_searchbar"><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light" placeholder="'+language[18]+'"></div><div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div>';
                $('#cometchat').html(baseCode);
                $('div.cometchat_trayicontext').each(function(){
                    trayWidth += this.clientWidth+1;
                    id = $(this).parent().attr('id');
                    $('#'+id+'_popup').find('div.cometchat_traycontent').css('background-position', (this.clientWidth)+'px bottom');
                });
                if(jqcc().slimScroll){
                    $('#cometchat_userscontent').slimScroll({
                        height: '278px',
                        distance : '3px'
                    });
                }
                jqcc[settings.theme].optionsButton();
                jqcc[settings.theme].chatTab();
                $('.cometchat_statustextarea').keyup(function(){
                    $('.cometchat_statusmessagecount').show();
                    count = $(this).attr('maxlength')-$(this).val().length;
                    $('.cometchat_statusmessagecount').html(count);
                });
                $('.cometchat_statustextarea').mouseup(function(){
                    $('.cometchat_statusmessagecount').show();
                    count = $(this).attr('maxlength')-$(this).val().length;
                    $('.cometchat_statusmessagecount').html(count);
                });
                $('.cometchat_statustextarea').mousedown(function(){
                    $('.cometchat_statusmessagecount').show();
                    count = $(this).attr('maxlength')-$(this).val().length;
                    $('.cometchat_statusmessagecount').html(count);
                });
                $('.cometchat_statustextarea').blur(function() {
                    $('.cometchat_statusmessagecount').hide();
                });
                $('#cometchat_chatboxes').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_userscontent').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('div.cometchat_trayicon').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_hidden').click(function(){
                    $('#cometchat').css('display', 'block');
                    $('#cometchat_hidden').css('display', 'none');
                    $.cookie(settings.cookiePrefix+"hidebar", '0', {path: '/', expires: 365});
                    if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                        clearTimeout(jqcc.cometchat.getThemeVariable('heartbeatTimer'));
                        jqcc.cometchat.chatHeartbeat();
                    }
                });
                $('#cometchat_hidden').mouseover(function(){
                    if(tooltipPriority==0){
                        jqcc[settings.theme].tooltip('cometchat_hidden_content', language[26], 0);
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat_hidden').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat_hide').mouseover(function(){
                    if(tooltipPriority==0){
                        jqcc[settings.theme].tooltip('cometchat_hide', language[27], 0);
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat_hide').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat').find('div.cometchat_trayicon').mouseover(function(){
                    var id = $(this).attr('id').substr(19);
                    if(!trayicon[id][8]){
                        if(tooltipPriority==0){
                            jqcc[settings.theme].tooltip('cometchat_trayicon_'+id, trayicon[id][1], 1);
                        }
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat').find('div.cometchat_trayicon').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $("#cometchat").find("div.cometchat_traytitle").click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 200001+cc_zindex-1);
                    $(this).parent().css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 300001+cc_zindex);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                });
                $("#cometchat").find("div.cometchat_minimizebox").click(function(){
                    var id = $(this).parent().parent().attr('id');
                    id = id.substring(19, id.length-6);
                    $("#cometchat_trayicon_"+id).click();
                    $("#cometchat_trayicon_"+id).mouseout();
                });
                $("#cometchat").find("div.cometchat_popchatrooms").click(function(){
                    chatroom_location = jqcc("#cometchat").children().find('#cometchat_trayicon_chatrooms_iframe').attr('src');
                    myRef = window.open(chatroom_location+'&popoutmode=1', 'cc_module_chatrooms', 'left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width=800,height=600');
                    jqcc.cometchat.closeModule('chatrooms');
                    $("#cometchat_trayicon_chatrooms").mouseout();
                });
                $("#cometchat").find("div.cometchat_popgames").click(function(){
                    games_location = jqcc("#cometchat").children().find('#cometchat_trayicon_games_iframe').attr('src');
                    myRef = window.open(games_location,'cc_module_games','left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width='+trayicon['games'][4]+',height='+trayicon['games'][5]+'');
                    jqcc.cometchat.closeModule('games');
                    $("#cometchat_trayicon_games").mouseout();
                });
                $("#cometchat").find("div.cometchat_popbroadcastmessage").click(function(){
                    broadcastmessage_location = jqcc("#cometchat").children().find('#cometchat_trayicon_broadcastmessage_iframe').attr('src');
                    myRef = window.open(broadcastmessage_location+'&popoutmode=1','cc_module_broadcastmessage','left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width='+trayicon['broadcastmessage'][4]+',height='+trayicon['broadcastmessage'][5]+'');
                    jqcc.cometchat.closeModule('broadcastmessage');
                    $("#cometchat_trayicon_broadcastmessage").mouseout();
                });
                $('#cometchat').find('div.cometchat_trayicon').click(function(){
                    var id = $(this).attr('id').substr(19);
                    jqcc.cometchat.setAlert(id, 0);
                    if(id != 'scrolltotop') {
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').removeClass("cometchat_closebox_bottom_click");
                            jqcc.cometchat.setSessionVariable('openChatboxId', '');
                        }
                        $('#cometchat_auth_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                        $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                    }
                    var target = "_self";
                    if(trayicon[id][3]){
                        target = trayicon[id][3];
                    }
                    if(target=='_popup'){
                        if(jqcc.cometchat.getThemeVariable('trayOpen')!=id){
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                            jqcc[settings.theme].removeClass_cometchat_trayclick();
                            if(jqcc.cometchat.getThemeVariable('currentStatus')=='offline'){
                                jqcc.cometchat.setThemeVariable('offline', 0);
                                $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                                jqcc.cometchat.sendStatus('available');
                                jqcc.cometchat.chatHeartbeat(1);
                                jqcc[settings.theme].removeUnderline();
                                jqcc[settings.theme].updateStatus('available');
                            }
                            jqcc.cometchat.setThemeVariable('trayOpen', '');
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                        }
                        if(jqcc.cometchat.getThemeVariable('trayOpen')==''){
                            $('#cometchat_trayicon_'+id+'_popup').css('left', $('#cometchat_trayicon_'+id).offset().left-jqcc(window).scrollLeft()).css('width', trayicon[id][4]);
                            var iframesrc = $("#cometchat_trayicon_"+id+"_iframe").attr('src');
                            if(iframesrc==undefined||iframesrc=='blank.html'||iframesrc==''){
                                $("#cometchat_trayicon_"+id+'_iframe').attr('src', trayicon[id][2]+'?basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                            }
                            $("#cometchat_trayicon_"+id).addClass("cometchat_trayclick");
                            $("#cometchat_trayicon_"+id+'_popup').addClass("cometchat_tabopen");
                            if($("#cometchat_trayicon_"+id).hasClass("cometchat_trayclick")){
                                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                            }
                            cc_zindex += 2;
                            $('#cometchat_base').css('z-index', 200001+cc_zindex-1);
                            $("#cometchat_trayicon_"+id+'_popup').css('z-index', 100001+cc_zindex);
                            $('#cometchat_optionsbutton_popup').css('z-index', 300001+cc_zindex);
                            $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                            $("#cometchat_user_"+id+'_popup').css('z-index', 300001+cc_zindex);
                            jqcc.cometchat.setThemeVariable('trayOpen', id);
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                            if(id=='chatrooms'){
                                if($.cookie(settings.cookiePrefix+'crstate')!=null){
                                    var cc_crstate = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                                    var activeChatroom = cc_crstate.open;
                                    if(activeChatroom != ''){
                                        var controlparameters = {"type":"modules", "name":"cometchat", "method":"setCrSessionVariable", "params":{"name":"active", "val":{}, "roomno":activeChatroom, "messageCounter":0, "isOpen":1}};
                                        controlparameters = JSON.stringify(controlparameters);
                                        jqcc('#cometchat_trayicon_chatrooms_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                                    }
                                }
                            }
                            if(id=='announcements') {
                                jqcc("#cometchat_trayicon_announcements_popup").find("#cometchat_trayicon_announcements_iframe").attr('src',jqcc("#cometchat_trayicon_announcements_popup").find("#cometchat_trayicon_announcements_iframe").attr('src'));
                            }
                        }else{
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                            jqcc[settings.theme].removeClass_cometchat_trayclick();
                            jqcc.cometchat.setThemeVariable('trayOpen', '');
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                            if(id=='chatrooms'){
                                var cc_crstate = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                                var chatroomdata = cc_crstate.active;
                                var messageCount = 0;
                                if(Object.keys(chatroomdata).length > 0){
                                    $.each(chatroomdata, function(chatroomid,data) {
                                        messageCount = messageCount + parseInt(data.c);
                                    });
                                }
                                jqcc.cometchat.setAlert('chatrooms',messageCount);
                            }
                        }
                    }else if(target=='_lightbox'){
                        if(jqcc.cometchat.getThemeVariable('currentStatus')=='offline'){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.sendStatus('available');
                            jqcc.cometchat.chatHeartbeat(1);
                            jqcc[settings.theme].removeUnderline();
                            jqcc[settings.theme].updateStatus('available');
                        }
                        jqcc.cometchat.lightbox(id);
                    }else{
                        window.open(trayicon[id][2], target);
                    }
                    var onmouseoverURL = baseUrl+'themes/'+settings.theme+'/images/modules/dark/'+id+'.png';
                            var onmouseoutURL = baseUrl+'themes/'+settings.theme+'/images/modules/'+id+'.png';
                            $("#cometchat_trayicon_"+id).find('img').attr('src',onmouseoverURL);
                });
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                cometchat_chatbox_right.bind('click', function(){
                    jqcc[settings.theme].moveRight();
                });
                cometchat_chatbox_left.bind('click', function(){
                    jqcc[settings.theme].moveLeft();
                });
                jqcc[settings.theme].windowResize();
                jqcc[settings.theme].scrollBars();
                cometchat_chatbox_right.mouseover(function(){
                    $(this).addClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_right.mouseout(function(){
                    $(this).removeClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_left.mouseover(function(){
                    $(this).addClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_left.mouseout(function(){
                    $(this).removeClass("cometchat_chatbox_lr_mouseover");
                });
                $(window).bind('resize', function(){
                    jqcc[settings.theme].windowResize();
                });
                if(typeof document.body.style.maxHeight==="undefined"){
                    jqcc[settings.theme].scrollFix();
                    $(window).bind('scroll', function(){
                        jqcc[settings.theme].scrollFix();
                    });
                }else if(jqcc.cometchat.getThemeVariable('mobileDevice')){
                    if(settings.disableForMobileDevices){
                        $('#cometchat').css('display', 'none');
                        jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                    }
                }
                document.onmousemove = function(){
                    var nowTime = new Date();
                    jqcc.cometchat.setThemeVariable('idleTime', Math.floor(nowTime.getTime()/1000));
                };
                if($.cookie(settings.cookiePrefix+'hidebar')=='1'){
                    $('#cometchat').css('display', 'none');
                    $('#cometchat_hidden').css('display', 'block');
                    jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                }
                var extlength = settings.extensions.length;
                if(extlength>0){
                    for(var i = 0; i<extlength; i++){
                        var name = 'cc'+settings.extensions[i];
                        if(typeof ($[name])=='object'){
                            $[name].init();
                        }
                    }
                }
                if($.inArray('block', settings.plugins)>-1){
                    $.ccblock.addCode();
                }

                if($.cookie(settings.cookiePrefix+"disablemessagereceipt")){
                    if($.cookie(settings.cookiePrefix+"disablemessagereceipt")==1){
                        jqcc.cometchat.setExternalVariable('messagereceiptsetting', 1);
                    }
                }

                attachPlaceholder('#cometchat_searchbar');
                if(typeof(jqcc.cookie(settings.cookiePrefix+'crstate')) != 'undefined' && jqcc.cookie(settings.cookiePrefix+'crstate') != null && typeof(trayicon['chatrooms']) != 'undefined'){
                    var cc_crstate = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                    var chatroomData = cc_crstate.active;
                    if(Object.keys(chatroomData).length > 0){
                        var iframesrc = $("#cometchat_trayicon_chatrooms_iframe").attr('src');
                        if(iframesrc==undefined||iframesrc=='blank.html'||iframesrc==''){
                            $("#cometchat_trayicon_chatrooms_iframe").attr('src', trayicon['chatrooms'][2]+'?basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                        }
                    }
                }
            },
            newAnnouncement: function(item){
                if($.cookie(settings.cookiePrefix+"popup")&&$.cookie(settings.cookiePrefix+"popup")=='true'){
                }else{
                    tooltipPriority = 100;
                    message = '<div class="cometchat_announcement">'+item.m+'</div>';
                    if(item.o){
                        var notifications = (item.o-1);
                        if(notifications>0){
                            message += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.launchModule(\'announcements\')"><div class="cometchat_notification_message cometchat_notification_message_and">'+language[36]+notifications+language[37]+'</div><div style="clear:both" /></div>';
                        }
                    }else{
                        $.cookie(settings.cookiePrefix+"an", item.id, {path: '/', expires: 365});
                    }
                    jqcc[settings.theme].tooltip("cometchat_userstab", message, 0);
                    clearTimeout(notificationTimer);
                    notificationTimer = setTimeout(function(){
                        $('#cometchat_tooltip').css('display', 'none');
                        tooltipPriority = 0;
                    }, settings.announcementTime);
                }
            },
            buddyList: function(item){
                var onlineNumber = 0;
                var totalFriendsNumber = 0;
                var lastGroup = '';
                var groupNumber = 0;
                var tooltipMessage = '';
                var buddylisttemp = '';
                var buddylisttempavatar = '';
                $.each(item, function(i, buddy){
                    if(buddy.n == null || buddy.n == 'null' || buddy.n == '' || jqcc.cometchat.getThemeVariable('banned', '1')) {
                        return;
                    }
                    longname = buddy.n;
					if(lastseenflag){
                        jqcc[settings.theme].hideLastseen(buddy.id);
                    } else if(!lastseenflag){
                        if((buddy.s == 'available')||(buddy.s == 'offline' && buddy.lstn == 1)){
                            jqcc[settings.theme].hideLastseen(buddy.id);
                        }
                        else if(buddy.s == 'offline' && buddy.lstn == 0){
                            jqcc[settings.theme].showLastseen(buddy.id, buddy.ls);
                        }
                    }
                    var usercontentstatus = buddy.s;
                    var icon = '';
                    if(buddy.d==1){
                        mobilestatus = 'mobile';
                        usercontentstatus = 'mobile cometchat_mobile_'+buddy.s;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    if(chatboxOpened[buddy.id]!=null){
                        if(buddy.d == 1 && $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status.cometchat_mobile").length < 1){
                            $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status").addClass('cometchat_mobile').html('<div class="cometchat_dot"></div>');
                        }else if(buddy.d == 0){
                            $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status").removeClass('cometchat_mobile').remove('.cometchat_dot');
                        }
                        $("#cometchat_user_"+buddy.id).find("div.cometchat_closebox_bottom_status")
                        .removeClass("cometchat_available")
                        .removeClass("cometchat_busy")
                        .removeClass("cometchat_offline")
                        .removeClass("cometchat_away")
                        .removeClass("cometchat_blocked")
                        .addClass("cometchat_"+usercontentstatus);
                        $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status.cometchat_mobile")
                        .removeClass("cometchat_mobile_available")
                        .removeClass("cometchat_mobile_busy")
                        .removeClass("cometchat_mobile_offline")
                        .removeClass("cometchat_mobile_away")
                        .removeClass("cometchat_available")
                        .removeClass("cometchat_busy")
                        .removeClass("cometchat_offline")
                        .removeClass("cometchat_away")
                        .removeClass("cometchat_blocked")
                        .addClass('cometchat_mobile_'+usercontentstatus);
                        if(buddy.s!='blocked'){
                             $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_blocked_overlay").remove();
                        }
                        if($("#cometchat_user_"+buddy.id+"_popup").length>0){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_message").html(buddy.m);
                        }
                    }
                    if(buddy.s!='offline'){
                        onlineNumber++;
                    }
                    totalFriendsNumber++;
                    var group = '';
                    var icon = '';
                    if(buddy.g!=lastGroup&&typeof buddy.g!="undefined"){
                        if(buddy.g==''){
                            groupName = language[40];
                        }else{
                            groupName = buddy.g;
                        }
                        if(groupNumber==0){
                            group = '<div class="cometchat_subsubtitle cometchat_subsubtitle_top"><hr class="hrleft">'+groupName+'<hr class="hrright"></div>';
                        }else{
                            group = '<div class="cometchat_subsubtitle"><hr class="hrleft">'+groupName+'<hr class="hrright"></div>';
                        }
                        groupNumber++;
                        lastGroup = buddy.g;
                    }
                    var overlay_div = '';
                    if(buddy.s=="blocked"){
                        overlay_div = '<div class="cometchat_blocked_overlay"></div>';
                    }
                    var usercontentstatus = buddy.s;
                    if(buddy.d==1){
                       usercontentstatus = 'mobile cometchat_mobile_'+buddy.s;
                       icon = '<div class="cometchat_dot"></div>';
                    }
                    if((buddy.s != 'offline' && settings.hideOffline == 1) || settings.hideOffline == 0){
                        buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" ><span class="cometchat_userscontentavatar">'+overlay_div+'<img class="cometchat_userscontentavatarimage" original="'+baseUrl+'themes/'+settings.theme+'/images/cometchat_'+buddy.s+'.png"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><span id="cometchat_buddylist_typing_'+buddy.id+'" class="cometchat_buddylist_typing"></span><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
                        buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist"><span class="cometchat_userscontentavatar">'+overlay_div+'<img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><span id="cometchat_buddylist_typing_'+buddy.id+'" class="cometchat_buddylist_typing"></span><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
                    }
                    var message = '';
                    if(settings.displayOnlineNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!=buddy.s&&buddy.s=='available'){
                        message = language[19];
                    }
                    if(settings.displayBusyNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!=buddy.s&&buddy.s=='busy'){
                        message = language[21];
                    }
                    if(settings.displayOfflineNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!='offline'&&buddy.s=='offline'){
                        message = language[20];
                    }
                    if(message!=''){
                        tooltipMessage += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.chatWith(\''+buddy.id+'\')"><div class="cometchat_notification_avatar"><img class="cometchat_notification_avatar_image" src="'+buddy.a+'"></div><div class="cometchat_notification_message">'+buddy.n+' '+message+'<br/><span class="cometchat_notification_status">'+buddy.m+'</span></div><div style="clear:both" /></div>';
                    }
                    jqcc.cometchat.setThemeArray('buddylistStatus', buddy.id, buddy.s);
                    jqcc.cometchat.setThemeArray('buddylistMessage', buddy.id, buddy.m);
                    jqcc.cometchat.setThemeArray('buddylistName', buddy.id, buddy.n);
                    jqcc.cometchat.setThemeArray('buddylistAvatar', buddy.id, buddy.a);
                    jqcc.cometchat.setThemeArray('buddylistLink', buddy.id, buddy.l);
                    jqcc.cometchat.setThemeArray('buddylistIsDevice', buddy.id, buddy.d);
                    jqcc.cometchat.setThemeArray('buddylistChannelHash', buddy.id, buddy.ch);
					jqcc.cometchat.setThemeArray('buddylistLastseen', buddy.id, buddy.ls);
                    jqcc.cometchat.setThemeArray('buddylistLastseensetting', buddy.id, buddy.lstn);

                });
                if(groupNumber>0){
                    $('.cometchat_subsubtitle_siteusers').css('display', 'none');
                }
                var bltemp = buddylisttempavatar;
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    bltemp = buddylisttemp;
                }
                if(bltemp!=''){
                    document.getElementById('cometchat_userslist').style.display = 'block';
                    jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                }else{
                    $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                }
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    jqcc('#cometchat_userslist').find('.cometchat_blocked_overlay').remove();
                    jqcc('#cometchat_userslist').find('.cometchat_blocked').remove();
                }
                if(jqcc.cometchat.getSessionVariable('buddylist')==1){
                    $("span.cometchat_userscontentavatar").find("img").each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                }
                $(".cometchat_search").keyup();
                $('div.cometchat_userlist').unbind('click');
                $('div.cometchat_userlist').bind('click', function(e){
                    jqcc.cometchat.userClick(e.target);
                });
                $('#cometchat_userstab_text').html(language[9]+' ('+(onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'))+')');
                siteOnlineNumber = onlineNumber;
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'));
                if(totalFriendsNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber')>settings.searchDisplayNumber){
                    $('#cometchat_searchbar').css('display', 'block');
                }else{
                    $('#cometchat_searchbar').css('display', 'none');
                }
                if(tooltipMessage!=''&&!$('#cometchat_userstab_popup').hasClass('cometchat_tabopen')&&!$('#cometchat_optionsbutton_popup').hasClass('cometchat_tabopen')){
                    if(tooltipPriority<10){
                        if($.cookie(settings.cookiePrefix+"popup")&&$.cookie(settings.cookiePrefix+"popup")=='true'){
                        }else{
                            tooltipPriority = 108;
                            jqcc[settings.theme].tooltip("cometchat_userstab", tooltipMessage, 0);
                            clearTimeout(notificationTimer);
                            notificationTimer = setTimeout(function(){
                                $('#cometchat_tooltip').css('display', 'none');
                                tooltipPriority = 0;
                            }, settings.notificationTime);
                        }
                    }
                }
            },
            loggedOut: function(){
                document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                if(settings.ccauth.enabled=="1"){
                    $("#cometchat_optionsbutton_icon").addClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").attr("title",language[77]);
                }else{
                    $("#cometchat_optionsbutton").addClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton_icon").css('display', 'none');
                    $("#cometchat_optionsbutton").attr("title",language[8]);
                }
                $("#cometchat_userstab").hide();
                $("#cometchat_chatboxes").hide();
                $("#cometchat_chatbox_left").hide();
                $("#cometchat_chatbox_right").hide();
                msg_beep = $("#messageBeep").detach();
                option_button = $("#cometchat_optionsbutton_popup").detach();
                user_tab = $("#cometchat_userstab_popup").detach();
                chat_boxes = $("#cometchat_chatboxes").detach();
                chat_left = $("#cometchat_chatbox_left").detach();
                chat_right = $("#cometchat_chatbox_right").detach();
                usertab2 = $("#cometchat_userstab").detach();
                $("#cometchat_optionsbutton_popup").removeClass("cometchat_tabopen");
                $("#cometchat_userstab_popup").removeClass("cometchat_tabopen");
                $("#cometchat_optionsbutton").removeClass("cometchat_tabclick");
                $("#cometchat_userstab").removeClass("cometchat_tabclick");
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+"_popup").removeClass("cometchat_tabopen");
                    jqcc.cometchat.setThemeVariable('openChatboxId', '');
                    jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                }
            },
            userStatus: function(item){
                var cometchat_optionsbutton_popup = $('#cometchat_optionsbutton_popup');
                var count = 140-item.m.length;
                cometchat_optionsbutton_popup.find('textarea.cometchat_statustextarea').val(item.m);
                cometchat_optionsbutton_popup.find('.cometchat_statusmessagecount').html(count);
                if(item.s != 'away'){
                    jqcc.cometchat.setThemeVariable('currentStatus', item.s);
                }
                if(item.lstn==1){
                    lastseenflag = true;
                }
                if(item.s=='offline'){
                    jqcc[settings.theme].goOffline(1);
                }else{
                    jqcc[settings.theme].removeUnderline();
                    jqcc[settings.theme].updateStatus(item.s);
                }
                if(item.id>10000000){
                    $("#guestsname").show();
                    $("#guestsname").find("input.cometchat_guestnametextbox").val((item.n).replace("<?php echo $guestnamePrefix;?>-", ""));
                    cometchat_optionsbutton_popup.find(".cometchat_tabsubtitle").html(language[45]);
                }
                if(typeof item.b != 'undefined' && item.b == '1') {
                    jqcc[settings.theme].loggedOut();
                    jqcc.cometchat.setThemeVariable('banned', '1');
                    jqcc("#cometchat_optionsbutton").attr("title",bannedMessage);
                    jqcc[settings.theme].tooltip('cometchat_optionsbutton', bannedMessage);
                }
                jqcc.cometchat.setThemeVariable('userid', item.id);
                jqcc.cometchat.setThemeArray('buddylistStatus', item.id, item.s);
                jqcc.cometchat.setThemeArray('buddylistMessage', item.id, item.m);
                jqcc.cometchat.setThemeArray('buddylistName', item.id, item.n);
                jqcc.cometchat.setThemeArray('buddylistAvatar', item.id, item.a);
                jqcc.cometchat.setThemeArray('buddylistLink', item.id, item.l);
                jqcc.cometchat.setThemeArray('buddylistChannelHash', item.id, item.ch);
                jqcc.cometchat.setThemeArray('buddylistLastseen', item.id, item.ls);
                jqcc.cometchat.setThemeArray('buddylistLastseensetting', item.id, item.lstn);
            },
            typingTo: function(item){
                if(typeof item['fromid'] != 'undefined'){

                    var id = item['fromid'];

                    $("#cometchat_typing_"+id).css('display', 'block');
                    $("#cometchat_buddylist_typing_"+id).css('display', 'block');

                    typingReceiverFlag[id] = item['typingtime'];
                }

               if(typeof typingRecieverTimer == 'undefined' || typingRecieverTimer == null || typingRecieverTimer == ''){
                    typingRecieverTimer = setTimeout(function(){
                        typingRecieverTimer = '';
                        var counter = 0;
                        $.each(typingReceiverFlag, function(typingid,typingtime){
                            if(((parseInt(new Date().getTime()))+jqcc.cometchat.getThemeVariable('timedifference')) - typingtime > 5000){
                                $("#cometchat_typing_"+typingid).css('display', 'none');
                                $("#cometchat_buddylist_typing_"+typingid).css('display', 'none');
                                delete typingReceiverFlag[typingid];
                            }else{
                                counter++;
                            }

                        });
                        if(counter > 0){
                            jqcc[settings.theme].typingTo(1);
                        }

                    }, 4000);
                }

            },
			typingStop: function(item){
               $("#cometchat_typing_"+item['fromid']).css('display', 'none');
               $("#cometchat_buddylist_typing_"+item['fromid']).css('display', 'none');

            },
            sentMessageNotify: function(item){
                var size = 0, key;
                for (key in item) {
                    if (typeof item[key] == 'object'){
                        jqcc[settings.theme].sentMessageNotify(item[key]);
                    }
                }
                if(typeof item['id'] != 'undefined' && $("#cometchat_chatboxseen_"+item['id']).parent().hasClass('cometchat_self')){
                    $("#cometchat_chatboxseen_"+item['id']).addClass('cometchat_sentnotification');
                }
            },
            deliveredMessageNotify: function(item){
                if($("#cometchat_message_"+item['message']).length == 0){
                    undeliveredmessages.push(item['message']);
                } else if(typeof item['fromid'] != 'undefined' && $("#cometchat_chatboxseen_"+item['message']).parent().hasClass('cometchat_self')){
                    $("#cometchat_chatboxseen_"+item['message']).addClass('cometchat_deliverednotification');
                }
            },
            readMessageNotify: function(item){
                if($("#cometchat_message_"+item['fromid']).length == 0 && jqcc.cometchat.getExternalVariable('messagereceiptsetting') == 0){
                    unreadmessages.push(item['fromid']);
                }
                if(jqcc.cometchat.getExternalVariable('messagereceiptsetting') == 0) {
                    jqcc("#cometchat_user_"+item['fromid']+"_popup span.cometchat_deliverednotification").addClass('cometchat_readnotification');
                }
            },
            deliveredReadMessageNotify: function(item){
               if($("#cometchat_message_"+item['message']).length == 0){
                    undeliveredmessages.push(item['message']);
                    unreadmessages.push(item['message']);
                } else if(typeof item['fromid'] != 'undefined' && $("#cometchat_chatboxseen_"+item['message']).parent().hasClass('cometchat_self') && jqcc.cometchat.getExternalVariable('messagereceiptsetting') == 0){
                    $("#cometchat_chatboxseen_"+item['message']).addClass('cometchat_readnotification');
                }
            },
            removeClass_cometchat_trayclick: function(){
                $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                var onmouseoutURL = baseUrl+'themes/'+settings.theme+'/images/modules/'+jqcc.cometchat.getThemeVariable('trayOpen')+'.png';
                $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).find('img').attr('src',onmouseoutURL);
            },
            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                if(chatboxOpened[id]!=null){
                    if(!$("#cometchat_user_"+id).hasClass('cometchat_tabclick')&&silent!=1){
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            jqcc.cometchat.setThemeVariable('openChatboxId', '');
                            jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                        }
                        if(($("#cometchat_user_"+id).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+id).offset().left-cometchat_chatboxes.offset().left)>=0){
                            jqcc[settings.theme].positionChatbox(id);
                        }else{
                            $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").css('display', 'none');
                            var ms = settings.scrollTime;
                            if(jqcc.cometchat.getExternalVariable('initialize')==1){
                                ms = 0;
                            }
                            cometchat_chatboxes.scrollToCC("#cometchat_user_"+id, ms, function(){
                                jqcc[settings.theme].positionChatbox(id);
                                jqcc[settings.theme].scrollBars();
                                jqcc[settings.theme].checkPopups();
                            });
                        }
                    }
                    jqcc[settings.theme].scrollBars();
                    return;
                }
                $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()+152);
                jqcc[settings.theme].windowResize(1);
                shortname = name;
                longname = name;
                $("<span/>").attr("id", "cometchat_user_"+id).addClass("cometchat_tab").html('<div class="cometchat_user_shortname">'+shortname+'</div>').appendTo($("#cometchat_chatboxes_wide"));
                var icon = '';
                var usercontentstatus = status;
                if(isdevice==1){
                   usercontentstatus = 'mobile cometchat_mobile_'+status;
                   icon = '<div class="cometchat_dot"></div>';
                }
                var cometchat_user_id = $("#cometchat_user_"+id);
                cometchat_user_id.append('<div class="cometchat_closebox_bottom_status cometchat_'+usercontentstatus+'">'+icon+'</div>');
                cometchat_user_id.append('<div class="cometchat_closebox_bottom">x</div>');
                var cometchat_closebox_bottom = cometchat_user_id.find(".cometchat_closebox_bottom");
                cometchat_closebox_bottom.mouseenter(function(){
                    $(this).addClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.mouseleave(function(){
                    $(this).removeClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.click(function(){
                    $("#cometchat_user_"+id+"_popup").remove();
                    cometchat_user_id.remove();
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')==id){
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-152);
                    cometchat_chatboxes.scrollToCC("-=152px");
                    jqcc[settings.theme].windowResize();
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                    chatboxOpened[id] = null;
                    olddata[id] = 0;
                    jqcc.cometchat.orderChatboxes();
                });
                var pluginshtml = '';
                var smilieshtml = '<div style="margin-right:28px;">';
                var filetransferhtml = '';
                var cometchat_tabsubtitlehtml = '';
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    var pluginslength = settings.plugins.length;
                    if(pluginslength>0){
                        pluginshtml += '<div class="cometchat_plugins">';
                        for(var i = 0; i<pluginslength; i++){
                            var name = 'cc'+settings.plugins[i];
                            if(settings.plugins[i]=='smilies'){
                                smilieshtml='<div class="ccplugins cometchat_smilies" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0" >&#9786;</div><div style="margin:0px 28px;">';
                            }else if(typeof ($[name])=='object'){
                                pluginshtml += '<div class="cometchat_pluginsicon cometchat_'+settings.plugins[i]+'" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"></div>';
                            }
                        }
                        pluginshtml += '</div>';
                    }

                    cometchat_tabsubtitlehtml = '<div class="cometchat_tabsubtitle"><div class="cometchat_message" title="'+message+'">'+message+'</div><div style="clear:both"></div></div>';

                }
                var startlink = '';
                var endlink = '';
                if(link!=''){
                    startlink = '<a href="'+link+'">';
                    endlink = '</a>';
                }
                var avatarsrc = '';
                var overlay_div = '';
                if(status=="blocked"){
                    overlay_div = '<div class="cometchat_blocked_overlay"></div>';
                }

                if(avatar!=''){
                    avatarsrc = '<div class="cometchat_avatarbox">'+startlink+overlay_div+'<img src="'+avatar+'" class="cometchat_avatar" />'+endlink+'</div>';
                }
                var prepend = '';
                var jabber = jqcc.cometchat.getThemeArray('isJabber', id);

                if(jqcc.cometchat.getThemeVariable('prependLimit') != '0' && jabber != 1){
                    prepend = '<div class=\"cometchat_prependMessages_container\"><div class=\"cometchat_prependMessages\" onclick\="jqcc.glass.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div></div>';
                }

                $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_tabtitle"><span id="cometchat_typing_'+id+'" class="cometchat_typing"></span><div class="cometchat_name" title="'+longname+'" >'+startlink+longname+endlink+'</div></div>'+pluginshtml+cometchat_tabsubtitlehtml+prepend+'<div class="cometchat_tabcontent"><div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'" onscroll="jqcc.'+settings.theme+'.chatScroll(\''+id+'\');">'+'</div><div class="cometchat_tabinputcontainer"><div class="cometchat_tabcontentinput"><div class="cometchat_tabcontentsubmit cometchat_sendicon" title="Send"></div>'+smilieshtml+'<textarea class="cometchat_textarea" ></textarea></div></div></div><div class="cometchat_chatbox_border_bottom"></div><div style="clear:both"></div></div>').appendTo($("#cometchat"));

                if(lastseenflag){
                    jqcc[settings.theme].hideLastseen(id);
                } else if(!lastseenflag){
                    if((jqcc.cometchat.getThemeArray('buddylistStatus', id) == 'available')||(jqcc.cometchat.getThemeArray('buddylistStatus', id) == 'offline' && jqcc.cometchat.getThemeArray('buddylistLastseensetting', id) == 1)){
                        jqcc[settings.theme].hideLastseen(id);
                    }
                    else if(jqcc.cometchat.getThemeArray('buddylistStatus', id) == 'offline' && jqcc.cometchat.getThemeArray('buddylistLastseensetting', id) == 0){
                        jqcc[settings.theme].showLastseen(id, jqcc.cometchat.getThemeArray('buddylistLastseen', id));
                    }
                }

                var cometchat_user_popup = $("#cometchat_user_"+id+'_popup');
                if(jqcc().slimScroll){
                    cometchat_user_popup.find("div.cometchat_tabcontenttext").slimScroll({
                        height: (chatboxHeight+11)+'px',
                        color: '#CCCCCC'
                    });
                }
                cometchat_user_popup.find('.cometchat_pluginsicon,.ccplugins').click(function(){
                    var name = $(this).attr('name');
                    var to = $(this).attr('to');
                    var chatroommode = $(this).attr('chatroommode');
                    var controlparameters = {"to":to, "chatroommode":chatroommode};
                    jqcc[name].init(controlparameters);
                });
                cometchat_user_popup.find("textarea.cometchat_textarea").keydown(function(event){
                    if(typingSenderFlag != 0 && settings.cometserviceEnabled == 1 && settings.istypingEnabled == 1 && settings.transport == 'cometservice'){
                        var fid = jqcc.cometchat.getThemeVariable('userid');
                        var senttime = parseInt(new Date().getTime())+jqcc.cometchat.getThemeVariable('timedifference');
                        if(settings.transport == 'cometservice-selfhosted'){
                            var jsondata = {channel:"/"+jqcc.cometchat.getThemeArray('buddylistChannelHash', id),data:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingTo\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        } else if(settings.transport == 'cometservice'){
                            var jsondata = {channel:jqcc.cometchat.getThemeArray('buddylistChannelHash', id),message:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingTo\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        }
                        COMET.publish(jsondata);

                        typingSenderFlag = 0;
                        clearTimeout(typingSenderTimer);
                        typingSenderTimer = setTimeout(function(){
                            typingSenderFlag = 1;
                        },4000);

                    }
                    return jqcc[settings.theme].chatboxKeydown(event, this, id);
                });
                cometchat_user_popup.find("textarea.cometchat_textarea").blur(function(event){
                    if(settings.cometserviceEnabled == 1 && settings.istypingEnabled == 1 && settings.transport == 'cometservice'){
                        var fid = jqcc.cometchat.getThemeVariable('userid');
                        var senttime = parseInt(new Date().getTime())+jqcc.cometchat.getThemeVariable('timedifference');
                        if(settings.transport == 'cometservice-selfhosted'){
                            var jsondata = {channel:"/"+jqcc.cometchat.getThemeArray('buddylistChannelHash', id),data:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingStop\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        } else if(settings.transport == 'cometservice'){
                            var jsondata = {channel:jqcc.cometchat.getThemeArray('buddylistChannelHash', id),message:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingStop\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        }
                        COMET.publish(jsondata);
                    }
                });
                cometchat_user_popup.find("div.cometchat_tabcontentsubmit").click(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, cometchat_user_popup.find(".cometchat_textarea"), id, 1);
                });
                cometchat_user_popup.find("textarea.cometchat_textarea").keyup(function(event){
                    return jqcc[settings.theme].chatboxKeyup(event, this, id);
                });
                var cometchat_plugins_button = '';
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    cometchat_plugins_button = '<div class="cometchat_plugins_button"></div>';
                }
                cometchat_user_popup.find("div.cometchat_tabtitle").append('<div class="cometchat_closebox"></div><div class="cometchat_minimizebox"></div>'+cometchat_plugins_button+'<br clear="all"/>');
                var cometchat_closebox = cometchat_user_popup.find("div.cometchat_closebox");
                var cometchat_plugins_button = cometchat_user_popup.find("div.cometchat_plugins_button");
                var cometchat_minimizebox =  cometchat_user_popup.find("div.cometchat_minimizebox");
                cometchat_closebox.mouseenter(function(){
                    $(this).addClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.mouseleave(function(){
                    $(this).removeClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.click(function(){
                    cometchat_user_popup.remove();
                    cometchat_user_id.remove();
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')==id){
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-152);
                    cometchat_chatboxes.scrollToCC("-=152px");
                    jqcc[settings.theme].windowResize();
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                    chatboxOpened[id] = null;
                    olddata[id] = 0;
                    jqcc.cometchat.orderChatboxes();
                });
                cometchat_plugins_button.mouseenter(function(){
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_plugins_button.mouseleave(function(){
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_plugins_button.click(function(e){
                    e.stopPropagation();
                    cometchat_user_popup.find("div.cometchat_plugins").slideToggle('fast');
                });
                var cometchat_tabtitle = cometchat_user_popup.find("div.cometchat_tabtitle");
                cometchat_tabtitle.click(function(){
                    cometchat_user_id.click();
                });
                cometchat_tabtitle.mouseenter(function(){
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_tabtitle.mouseleave(function(){
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_user_id.mouseenter(function(){
                    $(this).addClass("cometchat_tabmouseover");
                    cometchat_user_id.find(".cometchat_user_shortname").addClass("cometchat_tabmouseovertext");
                });
                cometchat_user_id.mouseleave(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    cometchat_user_id.find("div.cometchat_user_shortname").removeClass("cometchat_tabmouseovertext");
                });
                cometchat_user_popup.click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 200001+cc_zindex-1);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 300001+cc_zindex);
                    cometchat_user_popup.css('z-index', 300001+cc_zindex);
                });
                cometchat_user_id.click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 200001+cc_zindex-1);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 300001+cc_zindex);
                    cometchat_user_popup.css('z-index', 300001+cc_zindex);
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        jqcc[settings.theme].removeClass_cometchat_trayclick();
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(cometchat_user_id.find("span.cometchat_tabalert").length>0){
                        cometchat_user_id.find("span.cometchat_tabalert").remove();
                        jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                        chatboxOpened[id] = 0;
                        jqcc.cometchat.orderChatboxes();
                    }
                    if($(this).hasClass('cometchat_tabclick')){
                        $(this).removeClass("cometchat_tabclick").removeClass("cometchat_usertabclick");
                        cometchat_user_popup.removeClass("cometchat_tabopen");
                        cometchat_closebox_bottom.removeClass("cometchat_closebox_bottom_click");
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }else{
                        var baseLeft = $('#cometchat_base').position().left;
                        if((cometchat_user_id.offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&(cometchat_user_id.offset().left-cometchat_chatboxes.offset().left)>=0){
                            if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''&&jqcc.cometchat.getThemeVariable('openChatboxId')!=id){
                                $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                                $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find("div.cometchat_closebox_bottom").removeClass("cometchat_closebox_bottom_click");
                                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                                jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                            }
                            var isfirst = 0;
                            if(cometchat_user_id.is(':first-child')){
                                isfirst = 1;
                            }
                            var popupLeft = cometchat_user_id.offset().left- jqcc(window).scrollLeft()+152-chatboxWidth+1-isfirst;/*152 cometchat_tab and 1 its border*/
                            cometchat_user_popup.css('left',popupLeft);
                            $(this).addClass("cometchat_tabclick").addClass("cometchat_usertabclick");
                            cometchat_user_popup.addClass("cometchat_tabopen");
                            cometchat_closebox_bottom.addClass("cometchat_closebox_bottom_click");
                            jqcc.cometchat.setThemeVariable('openChatboxId', [id+'']);
                            jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                            jqcc('#cometchat_prependMessages_'+id).text(language[83]);
                            jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.glass.prependMessagesInit('+id+')');
                            if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                                if(typeof $("#cometchat_user_"+id+"_popup").find("div.cometchat_chatboxmessage:last-child").attr('id') != 'undefined'){
                                    var messageid = $("#cometchat_user_"+id+"_popup").find("div.cometchat_chatboxmessage:last-child").attr('id').split('_')[2];
                                }
                                var message = {"id": messageid, "from": id, "self": 0};
                                if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[id] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[id]==0 && jqcc.cometchat.getExternalVariable('messagereceiptsetting') == 0){
                                    jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                                }
                            }
                            if(olddata[id]!=1&&(jqcc.cometchat.getExternalVariable('initialize')!=1||isNaN(id))){
                                jqcc[settings.theme].updateChatbox(id);
                                olddata[id] = 1;
                            }
                        }else{
                            cometchat_user_popup.removeClass('cometchat_tabopen');
                            cometchat_user_id.removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            var newPosition = ((cometchat_user_id.offset().left-$("#cometchat_chatboxes_wide").offset().left))-((Math.floor((cometchat_chatboxes.width()/152))-1)*152);
                            cometchat_chatboxes.scrollToCC(newPosition+'px', 0, function(){
                                jqcc[settings.theme].positionChatbox(id);
                                jqcc[settings.theme].checkPopups();
                                jqcc[settings.theme].scrollBars();
                            });
                        }
                        jqcc[settings.theme].scrollDown(id);
                    }
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        cometchat_user_popup.find(".cometchat_textarea").focus();
                    }
                    $('.cometchat_chatbox_border_bottom').css('width',Math.round(cometchat_user_id.innerWidth()));
                });
                if(silent!=1){
                    cometchat_user_id.click();
                }
                attachPlaceholder("#cometchat_user_"+id+'_popup');
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
                jqcc[settings.theme].updateReadMessages(id);
            },
            addMessages: function(item){
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
                var msg_time = '';
                var jabber = '';
                $.each(item, function(i, incoming){
                    if(typeof(incoming.self) ==='undefined' && typeof(incoming.old) ==='undefined' && typeof(incoming.sent) ==='undefined'){
                        incoming.sent = Math.floor(new Date().getTime()/1000);
                        incoming.old = 0;
                        incoming.self = 1;
                    }
                    if(typeof(incoming.m)!== 'undefined'){
                        incoming.message = incoming.m;
                    }

                    var message = jqcc.cometchat.processcontrolmessage(incoming);

                    if(message == null || message == ""){
                        return;
                    }

                    if(typeof(incoming.nopopup) === "undefined" || incoming.nopopup =="") {
                        incoming.nopopup = 0;
                    }
                    if(incoming.self ==1 ){
                         incoming.nopopup = 1;
                    }

                    if(incoming.jabber == 1 && typeof(incoming.selfadded) != "undefined" && incoming.selfadded != null) {
                       msg_time = incoming.id;
                       jabber = 1;
                    }else{
                      msg_time = incoming.sent;
                      jabber = 0;
                    }

                    msg_time = msg_time+'';

                    if (msg_time.length == 10){
                        msg_time = parseInt(msg_time * 1000);
                    }

                    months_set = new Array(language['jan'],language['feb'],language['mar'],language['apr'],language['may'],language['jun'],language['jul'],language['aug'],language['sep'],language['oct'],language['nov'],language['dec']);

                    d = new Date(parseInt(msg_time));
                    month  = d.getMonth();
                    date  = d.getDate();
                    year = d.getFullYear();
                    msg_date_class = month+"_"+date+"_"+year;

                    var type = 'th';
                    if(date==1||date==21||date==31){
                        type = 'st';
                    }else if(date==2||date==22){
                        type = 'nd';
                    }else if(date==3||date==23){
                        type = 'rd';
                    }
                    msg_date_format = date+type+' '+months_set[month]+', '+year;

                    msg_date = months_set[month]+" "+date+", "+year;
                    date_class = "";

                    if(msg_date_class == today_date_class){
                        date_class = "today";
                        msg_date = language['today'];
                    }else  if(msg_date_class == yday_date_class){
                        date_class = "yesterday";
                        msg_date = language['yesterday'];
                    }

                    checkfirstmessage = ($("#cometchat_tabcontenttext_"+incoming.from+" .cometchat_chatboxmessage").length) ? 0 : 1;
                    var shouldPop = 0;
                    if($('#cometchat_user_'+incoming.from).length == 0){
                            shouldPop = 1;
                    }
                    if(jqcc.cometchat.getThemeArray('trying', incoming.from)===undefined){
                        if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && incoming.nopopup == 0){
                            jqcc[settings.theme].createChatbox(incoming.from, jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistStatus', incoming.from), jqcc.cometchat.getThemeArray('buddylistMessage', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), jqcc.cometchat.getThemeArray('buddylistLink', incoming.from), jqcc.cometchat.getThemeArray('buddylistIsDevice', incoming.from), 1, 1);
                        }
                    }
                    if(jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==null||jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==''){
                        if(jqcc.cometchat.getThemeArray('trying', incoming.from)<5){
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'){
                                    jqcc[settings.theme].addMessages([{"from": incoming.from, "message": message, "self": incoming.self, "old": incoming.old, "id": incoming.id, "sent": incoming.sent, "direction":incoming.direction}]);
                                }
                            }, 2000);
                        }
                    }else{
                        jqcc.cometchat.sendReceipt(incoming);
                        var selfstyle = '';
                        var fromavatar = '';
                        if(parseInt(incoming.self)==1){
                            fromname = language[10];
                            selfstyle = ' cometchat_self';
                        }else{
                            fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            fromavatar = '<img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)+'">';
                        }
                        if(incoming.old!=1 && incoming.self!=1){
                            if((typeof $.cookie(settings.cookiePrefix+"sound") == 'undefined' || $.cookie(settings.cookiePrefix+"sound") == null) || $.cookie(settings.cookiePrefix+"sound")!='true'){
                                jqcc[settings.theme].playSound();
                            }
                        }
                        separator = ':&nbsp;&nbsp;';
                        sentdata = '';
                        if(incoming.sent!=null){
                            var ts = incoming.sent;
                            sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                        }
                        if($("#message_"+incoming.id).length>0){
                            $('#message_'+incoming.id).html(message);
                        }else{
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage '+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom'+selfstyle+'">'+fromavatar+'</div><div class="cometchat_messagearrow"></div><div class="cometchat_chatboxmessagecontent'+selfstyle+'"><span id="message_'+incoming.id+'" class="cometchat_msg">'+message+'</span><span id="cometchat_chatboxseen_'+incoming.id+'"></span><div class="cometchat_ts_continer">'+sentdata+'</div></div></div>', selfstyle);

                            $("#cometchat_user_"+incoming.from+"_popup").find("div.cometchat_tabcontenttext").append(msg);
                            $("#cometchat_typing_"+incoming.from).css('display', 'none');
                            if(message.indexOf('<img')!=-1 && message.indexOf('src')!=-1){
                                $( "#cometchat_message_"+incoming.id+" img" ).load(function() {
                                     jqcc[settings.theme].scrollDown(incoming.from);
                                });
                            }else{
                                jqcc[settings.theme].scrollDown(incoming.from);
                            }
                            if(undeliveredmessages.indexOf(incoming.id) >= 0){
                                $("#cometchat_chatboxseen_"+incoming.id).addClass('cometchat_deliverednotification');
                                undeliveredmessages.pop(incoming.id);
                            }
                            if(unreadmessages.indexOf(incoming.id) >= 0){
                                $("#cometchat_chatboxseen_"+incoming.id).addClass('cometchat_readnotification');
                                unreadmessages.pop(incoming.id);
                            }
                            var nowTime = new Date();
                            var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                            if(idleDifference>5){
                                if(settings.windowTitleNotify==1){
                                    document.title = language[15];
                                }
                            }
                        }
                        var alreadyreceivedunreadmessages = jqcc.cometchat.getFromStorage('receivedunreadmessages');

                        if(jqcc.cometchat.getThemeVariable('openChatboxId') != incoming.from && incoming.old!=1 && ((typeof(alreadyreceivedunreadmessages[incoming.from])!='undefined'&& alreadyreceivedunreadmessages[incoming.from]<incoming.id) || typeof(alreadyreceivedunreadmessages[incoming.from])=='undefined')){
                            if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                                jqcc[settings.theme].addPopup(incoming.from, 1, 1);
                            }
                        }

                        if(typeof(incoming.calledfromsend) === 'undefined'){
                            jqcc[settings.theme].updateReceivedUnreadMessages(incoming.from,incoming.id);
                        }
                    }
                    jqcc[settings.theme].groupbyDate(incoming.from,jabber);
                    var newMessage = 0;
                    if((jqcc.cometchat.getThemeVariable('isMini')==1||(jqcc.cometchat.getThemeVariable('openChatboxId')!=incoming.from))&&incoming.self!=1&&settings.desktopNotifications==1&&incoming.old==0){
                        var callChatboxEvent = function(){
                            if(typeof incoming.from!='undefined'){
                                for(x in desktopNotifications){
                                    for(y in desktopNotifications[x]){
                                        desktopNotifications[x][y].close();
                                    }
                                }
                                desktopNotifications = {};
                                if(jqcc.cometchat.getThemeVariable('isMini')==1){
                                    window.focus();
                                }
                                jqcc.cometchat.chatWith(incoming.from);
                            }
                        };
                        if(typeof desktopNotifications[incoming.from]!='undefined'){
                            var newMessageCount = 0;
                            for(x in desktopNotifications[incoming.from]){
                                ++newMessageCount;
                                desktopNotifications[incoming.from][x].close();
                            }
                            jqcc.cometchat.notify((++newMessageCount)+' '+language[46]+' '+jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistName', incoming.from), language[47], callChatboxEvent, incoming.from, incoming.id);
                        }else{
                            jqcc.cometchat.notify(language[48]+' '+jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), message, callChatboxEvent, incoming.from, incoming.id);
                        }
                    }
                    var chatBoxArray = jqcc.cometchat.getThemeVariable('openChatboxId');
                    if($.inArray(incoming.from + '',chatBoxArray)==-1&&settings.autoPopupChatbox==1&&shouldPop==1&&incoming.self==0&&jqcc.cometchat.getInternalVariable('allowchatboxpopup')==1){
                        jqcc.cometchat.tryClick(incoming.from);
                        jqcc.cometchat.setInternalVariable('allowchatboxpopup', '0');
                    }
                    jqcc[settings.theme].updateReadMessages(incoming.from);
                    if(settings.cometserviceEnabled == 1 && settings.messagereceiptEnabled == 1 && jqcc.cometchat.getCcvariable().callbackfn != "mobilewebapp" && settings.tapatalk == 0 && (settings.transport == 'cometservice' || settings.transport == 'cometservice-selfhosted')  && incoming.old == 0 && incoming.self == 1 && incoming.direction == 0){
                        jqcc[settings.theme].sentMessageNotify(incoming);
                    }
                });

            },
            updateReadMessages: function(id){
                if($('#cometchat_user_'+id+'_popup:visible').find('.cometchat_chatboxmessage:not(.cometchat_self):last').length){
                    if(typeof (jqcc.cometchat.updateToStorage)!=='undefined'){
                        var alreadyreadmessages = jqcc.cometchat.getFromStorage('readmessages');
                        var lastid = parseInt($('#cometchat_user_'+id+'_popup').find('.cometchat_chatboxmessage:not(.cometchat_self):last').attr('id').replace('cometchat_message_',''));
                        if((typeof(alreadyreadmessages[id])!='undefined' && parseInt(alreadyreadmessages[id])<parseInt(lastid)) || typeof(alreadyreadmessages[id])=='undefined'){
                            var readmessages={};
                            readmessages[id]= parseInt(lastid);
                            jqcc.cometchat.updateToStorage('readmessages',readmessages);
                        }
                    }
                }
            },
            updateReceivedUnreadMessages: function(id,lastid){
                if(typeof (jqcc.cometchat.updateToStorage)!=='undefined'){
                    var alreadyreceivedmessages = jqcc.cometchat.getFromStorage('receivedunreadmessages');
                    if((typeof(alreadyreceivedmessages[id])!='undefined' && parseInt(alreadyreceivedmessages[id])<parseInt(lastid)) || typeof(alreadyreceivedmessages[id])=='undefined'){
                        var receivedmessages={};
                        receivedmessages[id]= parseInt(lastid);
                        jqcc.cometchat.updateToStorage('receivedunreadmessages',receivedmessages);
                    }
                }
            },
            statusSendMessage: function(statustextarea){
                var message = $("#cometchat_optionsbutton_popup").find("textarea.cometchat_statustextarea").val();
                var oldMessage = jqcc.cometchat.getThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'));
                if(message!=''&&oldMessage!=message){
                    $('div.cometchat_statusbutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'), message);
                    jqcc.cometchat.statusSendMessageSet(message, statustextarea);
                }else{
                    $('div.cometchat_statusbutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    }, 1500);
                }
            },
            statusSendMessageSuccess: function(statustextarea){
                $(statustextarea).blur();
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                }, 2500);
            },
            statusSendMessageError: function(){
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[50]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                }, 2500);
            },
            setGuestName: function(guestnametextarea){
                var guestname = $("#cometchat_optionsbutton_popup").find("input.cometchat_guestnametextbox").val();
                var oldguestname = jqcc.cometchat.getThemeArray('buddylistName', jqcc.cometchat.getThemeVariable('userid'));
                if(guestname!=''&&oldguestname!=guestname){
                    $('div.cometchat_guestnamebutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistName', jqcc.cometchat.getThemeVariable('userid'), guestname);
                    jqcc.cometchat.setGuestNameSet(guestname, guestnametextarea);
                }else{
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                    }, 1500);
                }
            },
            setGuestNameSuccess: function(guestnametextarea){
                $(guestnametextarea).blur();
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                }, 2500);
            },
            setGuestNameError: function(){
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[50]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                }, 2500);
            },
            removeUnderline: function(){
                $("#cometchat_optionsbutton_popup").find("span.busy").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.invisible").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.offline").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.available").css('text-decoration', 'none');
                jqcc[settings.theme].removeUnderline2();
            },
            removeUnderline2: function(){
                $("#cometchat_userstab_icon").removeClass('cometchat_user_available2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_busy2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_invisible2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_offline2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_away2');
            },
            updateStatus: function(status){
                $("#cometchat_userstab_icon").addClass('cometchat_user_'+status+'2');
                $('span.cometchat_optionsstatus.'+status).css('text-decoration', 'underline');
            },
            goOffline: function(silent){
                jqcc.cometchat.setThemeVariable('offline', 1);
                jqcc[settings.theme].removeUnderline();
                if(silent!=1){
                    jqcc.cometchat.sendStatus('offline');
                }else{
                    jqcc[settings.theme].updateStatus('offline');
                }
                $('#cometchat_auth_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                jqcc.cometchat.setSessionVariable('buddylist', '0');
                $('#cometchat_userstab_text').html(language[17]);
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+" .cometchat_closebox_bottom").click();
                    jqcc.cometchat.setThemeVariable('openChatboxId', '');
                    jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                }
                for(chatbox in jqcc.cometchat.getThemeVariable('chatBoxesOrder')){
                    if(jqcc.cometchat.getThemeVariable('chatBoxesOrder').hasOwnProperty(chatbox)){
                        if(jqcc.cometchat.getThemeVariable('chatBoxesOrder')[chatbox]!=null){
                            $("#cometchat_user_"+chatbox).find("div.cometchat_closebox_bottom").click();
                        }
                    }
                }
                $('.cometchat_container').remove();
                if(typeof window.cometuncall_function=='function'){
                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                }
            },
            tryAddMessages: function(id, atleastOneNewMessage){
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            if(typeof (jqcc[settings.theme].tryAddMessages)!=='undefined'){
                                jqcc[settings.theme].tryAddMessages(id, atleastOneNewMessage);
                            }
                        }, 1000);
                    }
                }else{
                    jqcc[settings.theme].scrollDown(id);
                    chatboxOpened[id] = 1;
                    if(atleastOneNewMessage==1){
                        var nowTime = new Date();
                        var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                        if(idleDifference>5){
                            document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                        }
                    }
                    if($.cookie(settings.cookiePrefix+"sound")&&$.cookie(settings.cookiePrefix+"sound")=='true'){
                    }else{
                        if(atleastOneNewMessage==1){
                            jqcc[settings.theme].playSound();
                        }
                    }
                }
            },
            countMessage: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    var cc_state = $.cookie(settings.cookiePrefix+'state');
                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                    if(cc_state!=null){
                        var cc_states = cc_state.split(/:/);
                        if(jqcc.cometchat.getThemeVariable('offline')==0){
                            var value = 0;
                            if(cc_states[0]!=' '&&cc_states[0]!=''){
                                value = cc_states[0];
                            }
                            if((value==0&&$('#cometchat_userstab').hasClass("cometchat_tabclick"))||(value==1&&!($('#cometchat_userstab').hasClass("cometchat_tabclick")))){
                                $('#cometchat_userstab').click();
                            }
                            value = '';
                            if(cc_states[1]!=' '&&cc_states[1]!=''){
                                value = cc_states[1];
                            }
                            if(value==jqcc.cometchat.getSessionVariable('activeChatboxes')){
                                var newActiveChatboxes = {};
                                if(value!=''){
                                    var badge = 0;
                                    var chatboxData = value.split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        newActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
                                        badge += parseInt(chatboxIds[1]);
                                    }
                                    favicon.badge(badge);
                                }
                            }
                        }
                    }
                }
            },
            resynch: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    var cc_state = $.cookie(settings.cookiePrefix+'state');
                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                    if(cc_state!=null){
                        var cc_states = cc_state.split(/:/);
                        if(jqcc.cometchat.getThemeVariable('offline')==0){
                            var value = 0;
                            if(cc_states[0]!=' '&&cc_states[0]!=''){
                                value = cc_states[0];
                            }
                            if((value==0&&$('#cometchat_userstab').hasClass("cometchat_tabclick"))||(value==1&&!($('#cometchat_userstab').hasClass("cometchat_tabclick")))){
                                $('#cometchat_userstab').click();
                            }
                            value = '';
                            if(cc_states[1]!=' '&&cc_states[1]!=''){
                                value = cc_states[1];
                            }
                            if(value!=jqcc.cometchat.getSessionVariable('activeChatboxes')){
                                var newActiveChatboxes = {};
                                var oldActiveChatboxes = {};
                                if(value!=''){
                                    var count = 0;
                                    var chatboxData = value.split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        newActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
                                        count += parseInt(chatboxIds[1]);
                                    }
                                    if(settings.windowFavicon==1){
                                        favicon.badge(count);
                                    }
                                }
                                if(jqcc.cometchat.getSessionVariable('activeChatboxes')!=''){
                                    var chatboxData = jqcc.cometchat.getSessionVariable('activeChatboxes').split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        oldActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
                                    }
                                }
                                for(r in newActiveChatboxes){
                                    if(newActiveChatboxes.hasOwnProperty(r)){
                                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                                            jqcc[settings.theme].addPopup(r, parseInt(newActiveChatboxes[r]), 0);
                                        }
                                        if(parseInt(newActiveChatboxes[r])>0){
                                            jqcc.cometchat.setThemeVariable('newMessages', 1);
                                        }
                                    }
                                }
                                for(y in oldActiveChatboxes){
                                    if(oldActiveChatboxes.hasOwnProperty(y)){
                                        if(newActiveChatboxes[y]==null){
                                            $("#cometchat_user_"+y+"_popup").find("div.cometchat_closebox").click();
                                        }
                                    }
                                }
                            }
                            if(jqcc.cometchat.getThemeVariable('newMessages')>0){
                                if(settings.windowFavicon==1){
                                    jqcc[settings.theme].countMessage();
                                }
                                if(document.title==language[15]){
                                    document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                                }else{
                                    if(settings.windowTitleNotify==1){
                                        document.title = language[15];
                                    }
                                }
                            }else{
                                var nowTime = new Date();
                                var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                                if(idleDifference<5){
                                    document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                                    if(settings.windowFavicon==1){
                                        favicon.badge(0);
                                    }
                                }
                            }
                            value = 0;
                            if(cc_states[2]!=' '&&cc_states[2]!=''){
                                value = cc_states[2];
                            }
                            if(value!=jqcc.cometchat.getThemeVariable('openChatboxId')){
                                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                                    if($('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).length>0){
                                        jqcc[settings.theme].positionChatbox(jqcc.cometchat.getThemeVariable('openChatboxId'));
                                    }
                                    jqcc.cometchat.tryClickSync(jqcc.cometchat.getThemeVariable('openChatboxId'));
                                }
                                if(value!=''){
                                    jqcc.cometchat.tryClickSync(value);
                                }
                            }
                            if(cc_states[4]==1){
                                jqcc[settings.theme].goOffline(1);
                            }
                        }
                        if(cc_states[4]==0&&jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            jqcc[settings.theme].removeUnderline();
                            jqcc[settings.theme].updateStatus('available');
                        }
                        if(cc_states[5]!=' '&&cc_states[5]!=''&&cc_states[5]!=jqcc.cometchat.getThemeVariable('trayOpen')&&settings.autoLoadModules==1){
                            if($('#cometchat_container_'+cc_states[5]).length == 0){
                                $('#cometchat_trayicon_'+cc_states[5]).click();
                            }
                        } else if(cc_states[5]==''){
                            jqcc[settings.theme].closeAllModule(0);
                        }
                    }
                    jqcc.cometchat.setInternalVariable('updatingsession', '0');
                    clearTimeout(resynchTimer);
                    resynchTimer = setTimeout(function(){
                        jqcc[settings.theme].resynch();
                    }, 5000);
                }
            },
            setModuleAlert: function(id, number){
                if((!$('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')) && (jqcc('#cometchat_trayicon_'+id+'_popup').length>0)){
                    if($("#cometchat_trayicon_"+id).find("span.cometchat_tabalert").length > 0){
                        $("#cometchat_trayicon_"+id).find("span.cometchat_tabalert").remove();
                    }
                    if(number!=0){
                        $("<span>").css('top', '-10px').addClass("cometchat_tabalert").html(number).appendTo($("#cometchat_trayicon_"+id));
                    }
                }
            },
            addPopup: function(id, amount, add){
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined){
                        jqcc[settings.theme].createChatbox(id, null, null, null, null, null, null, 1, null);
                    }
                    if(jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            jqcc[settings.theme].addPopup(id, amount, add);
                        }, 5000);
                    }
                }else{
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    var cometchat_tabalert = cometchat_user_id.find("span.cometchat_tabalert");
                    jqcc.cometchat.userDoubleClick(id);
                    if(add==1){
                        if(cometchat_tabalert.length>0){
                            amount = parseInt(cometchat_user_id.find("span.cometchat_tabalert").html())+parseInt(amount);
                        }
                    }
                    if(amount==0){
                        cometchat_tabalert.remove();
                    }else{
                        if(cometchat_tabalert.length>0){
                            cometchat_tabalert.html(amount);
                        }else{
                            $("<span/>").css('top', '-5px').addClass("cometchat_tabalert").html(amount).appendTo($("#cometchat_user_"+id).find(".cometchat_closebox_bottom_status"));
                        }
                    }
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, amount);
                    jqcc.cometchat.orderChatboxes();
                    jqcc[settings.theme].checkPopups();
                }
            },
            getTimeDisplay: function(ts, id){
                ts = parseInt(ts);

                var time = getTimeDisplay(ts);
                if((ts+"").length == 10){
                    ts = ts*1000;
                }
                var timeDataStart = "<span class=\"cometchat_ts\">"+time.hour+":"+time.minute+time.ap;
                var timeDataEnd = "</span>";
                if(ts<jqcc.cometchat.getThemeVariable('todays12am')){
                    return timeDataStart+" "+time.date+time.type+" "+time.month+timeDataEnd;
                }else{
                    return timeDataStart+timeDataEnd;
                }
            },
            createChatbox: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                if(id==null||id==''){
                    return;
                }
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined){
                        jqcc.cometchat.setThemeArray('trying', id, 1);
                        if(!isNaN(id)){
                            jqcc.cometchat.createChatboxSet(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages);
                        }else{
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                                }
                            }, 5000);
                        }
                    }else{
                        if(jqcc.cometchat.getThemeArray('trying', id)<5){
                            jqcc.cometchat.incrementThemeVariable('trying['+id+']');
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                                }
                            }, 5000);
                        }
                    }
                }else{
                    if(typeof (jqcc[settings.theme].createChatboxData)!=='undefined'){
                        jqcc[settings.theme].createChatboxData(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                    }
                }
            },
            createChatboxSuccess: function(data, silent, tryOldMessages){
                var id = data.id;
                var name = data.n;
                var status = data.s;
                var message = data.m;
                var avatar = data.a;
                var link = data.l;
                var isdevice = data.d;
                var lastseensetting = data.lstn;
                jqcc.cometchat.setThemeArray('buddylistStatus', id, status);
                jqcc.cometchat.setThemeArray('buddylistMessage', id, message);
                jqcc.cometchat.setThemeArray('buddylistAvatar', id, avatar);
                jqcc.cometchat.setThemeArray('buddylistName', id, name);
                jqcc.cometchat.setThemeArray('buddylistLink', id, link);
                jqcc.cometchat.setThemeArray('buddylistIsDevice', id, isdevice);
                jqcc.cometchat.setThemeArray('buddylistLastseensetting', id, lastseensetting);
                if(chatboxOpened[id]!=null){
                    $("#cometchat_user_"+id).find("div.cometchat_closebox_bottom_status")
                            .removeClass("cometchat_available")
                            .removeClass("cometchat_busy")
                            .removeClass("cometchat_offline")
                            .removeClass("cometchat_away")
                            .addClass("cometchat_"+status);
                    if($("#cometchat_user_"+id+"_popup").length>0){
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_message").html(message);
                    }
                }
                jqcc.cometchat.setThemeVariable('trying', id, 5);
                if(id!=null&&id!=''&&name!=null&&name!=''){
                    if(typeof (jqcc[settings.theme].createChatboxData)!=='undefined'){
                        jqcc[settings.theme].createChatboxData(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages);
                    }
                }
            },
            tooltip: function(id, message, orientation){
                var cometchat_tooltip = $('#cometchat_tooltip');
                cometchat_tooltip.css('display', 'none').removeClass("cometchat_tooltip_left").css('left', '-100000px').find(".cometchat_tooltip_content").html(message);
                var pos = $('#'+id).offset();
                var width = $('#'+id).width();
                var tooltipWidth = cometchat_tooltip.width();
                if(orientation==1){
                    cometchat_tooltip.css('left', (pos.left+width)-16).addClass("cometchat_tooltip_left");
                }else{
                    var leftposition = (pos.left+width)-tooltipWidth;
                    leftposition += 16;
                    cometchat_tooltip.removeClass("cometchat_tooltip_left").css('left', leftposition);
                }

                cometchat_tooltip.css('display', 'block');
            },
            moveBar: function(relativePixels){
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                }
                $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").css('display', 'none');
                var ms = settings.scrollTime;
                if(jqcc.cometchat.getExternalVariable('initialize')==1){
                    ms = 0;
                }
                $("#cometchat_chatboxes").scrollToCC(relativePixels, ms, function(){
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                        if(($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left<($("#cometchat_chatboxes").offset().left+$("#cometchat_chatboxes").width()))&&($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                            jqcc[settings.theme].positionChatbox(jqcc.cometchat.getThemeVariable('openChatboxId'));
                        }else{
                            jqcc.cometchat.setSessionVariable('openChatboxId', '');
                        }
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    jqcc[settings.theme].checkPopups();
                    jqcc[settings.theme].scrollBars();
                });
            },
            chatTab: function(){
                var cometchat_search = $(".cometchat_search");
                var cometchat_userscontent = $('#cometchat_userscontent');
                cometchat_search.click(function(){
                    var searchString = $(this).val();
                    if(searchString==language[18]){
                        cometchat_search.val('');
                        cometchat_search.addClass('cometchat_search_light');
                    }
                });
                cometchat_search.blur(function(){
                    var searchString = $(this).val();
                    if(searchString==''){
                        cometchat_search.addClass('cometchat_search_light');
                        cometchat_search.val(language[18]);
                    }
                });
                cometchat_search.keyup(function(){
                    var searchString = $(this).val();
                    if(searchString.length>0&&searchString!=language[18]){
                        cometchat_userscontent.find('div.cometchat_userlist').hide();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        var searchcount = cometchat_userscontent.find('div.cometchat_userdisplayname:icontains('+searchString+')').length + cometchat_userscontent.find('span.cometchat_userscontentname:icontains('+searchString+')').length;
                        if(searchcount >= 1 ){
                            $('div.cometchat_userdisplayname:icontains('+searchString+')').parents('div.cometchat_userlist').show();
                            $('span.cometchat_userscontentname:icontains('+searchString+')').parents('div.cometchat_userlist').show();
                            $(document).find('#cometchat_userscontent').find('.cc_nousers').remove();
                        } else {
                            if($(document).find('.cc_nousers').length == 0){
                                $(document).find('#cometchat_userscontent').append('<div class="cc_nousers" style= "padding-top:6px;padding-left:6px;">'+language[58]+'</div>');
                            }
                        }
                        cometchat_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
                        cometchat_userscontent.find('.cc_nousers').hide();
                    }
                });
                var cometchat_userstabtitle = $("#cometchat_userstab_popup").find("div.cometchat_userstabtitle");
                var cometchat_userstab = $('#cometchat_userstab');
                cometchat_userstabtitle.click(function(){
                    cometchat_userstab.click();
                });
                cometchat_userstabtitle.mouseenter(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstabtitle.mouseleave(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstab.mouseover(function(){
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_userstab.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                });
                cometchat_userstab.click(function(){
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        jqcc[settings.theme].removeClass_cometchat_trayclick();
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        jqcc[settings.theme].removeUnderline();
                        jqcc[settings.theme].updateStatus('available');
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                        $("#cometchat_optionsbutton_popup").find("span.available").click();
                    }
                    $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                    if($(this).hasClass("cometchat_tabclick")){
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                    }else{
                        jqcc.cometchat.setSessionVariable('buddylist', '1');
                        $("#cometchat_tooltip").css('display', 'none');
                        $(".cometchat_userscontentavatar").find("img").each(function(){
                            if($(this).attr('original')){
                                $(this).attr("src", $(this).attr('original'));
                                $(this).removeAttr('original');
                            }
                        });
                    }
                    var cometchat_userstab_popup_left = jqcc('#cometchat_userstab').offset().left-jqcc(window).scrollLeft();
                    $('#cometchat_userstab_popup').css('left', cometchat_userstab_popup_left);
                    $(this).toggleClass("cometchat_tabclick").toggleClass("cometchat_userstabclick");
                    $('#cometchat_userstab_popup').toggleClass("cometchat_tabopen");
                });
            },
            optionsButton: function(){
                var cometchat_optionsbutton_popup = $("#cometchat_optionsbutton_popup");
                var cometchat_auth_popup = $("#cometchat_auth_popup");
                cometchat_optionsbutton_popup.find("span.cometchat_gooffline").click(function(){
                    jqcc[settings.theme].goOffline();
                });
                $("#cometchat_soundnotifications").click(function(event){
                    var notification = 'false';
                    if($("#cometchat_soundnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"sound", notification, {path: '/', expires: 365});
                });
                $("#cometchat_popupnotifications").click(function(event){
                    var notification = 'false';
                    if($("#cometchat_popupnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"popup", notification, {path: '/', expires: 365});
                });
                $("#cometchat_lastseen").click(function(event){
                    lastseenflag = false;
                    if(lastseenflag){
                        jqcc[settings.theme].hideLastseen(jqcc.cometchat.getThemeVariable('openChatboxId'));
                    } else if(!lastseenflag){
                        if((jqcc.cometchat.getThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('openChatboxId')) == 'available')||(jqcc.cometchat.getThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('openChatboxId')) == 'offline' && jqcc.cometchat.getThemeArray('buddylistLastseensetting', jqcc.cometchat.getThemeVariable('openChatboxId')) == 1)){
                            jqcc[settings.theme].hideLastseen(jqcc.cometchat.getThemeVariable('openChatboxId'));
                        }
                        else if(jqcc.cometchat.getThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('openChatboxId')) == 'offline' && jqcc.cometchat.getThemeArray('buddylistLastseensetting', jqcc.cometchat.getThemeVariable('openChatboxId')) == 0){
                            jqcc[settings.theme].showLastseen(jqcc.cometchat.getThemeVariable('openChatboxId'), jqcc.cometchat.getThemeArray('buddylistLastseen', jqcc.cometchat.getThemeVariable('openChatboxId')));
                        }
                    }
                    jqcc.cometchat.setExternalVariable('lastseensetting', 'false');
                    if($("#cometchat_lastseen").is(":checked")){
                        lastseenflag = true;
                        if($("#cometchat_lastseen_"+jqcc.cometchat.getThemeVariable('openChatboxId')).length == 1){
                            jqcc(".cometchat_lastseenmessage").remove();
                        }
                        jqcc.cometchat.setExternalVariable('lastseensetting', 'true');
                    }
                    $.ajax({
                        url: baseUrl+"cometchat_send.php",
                        data: {lastseenSettingsFlag: lastseenflag},
                        dataType: 'jsonp',
                        success: function(data){
                        }
                    });

                    $.cookie(settings.cookiePrefix+"disablelastseen", lastseenflag, {path: '/', expires: 365});
                });
                $("#cometchat_messagereceipt").click(function(event){
                    messagereceiptflag = 0;
                    jqcc.cometchat.setExternalVariable('messagereceiptsetting', messagereceiptflag);
                    if($("#cometchat_messagereceipt").is(":checked")){
                        messagereceiptflag = 1;
                    }
                    jqcc.cometchat.setExternalVariable('messagereceiptsetting', messagereceiptflag);

                    $.cookie(settings.cookiePrefix+"disablemessagereceipt", messagereceiptflag, {path: '/', expires: 365});
                });
                cometchat_optionsbutton_popup.find("span.available").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='available'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('available');
                    }
                });
                cometchat_optionsbutton_popup.find("div.cometchat_statusbutton").click(function(event){
                    jqcc[settings.theme].statusSendMessage();
                });
                $("#guestsname").find("div.cometchat_guestnamebutton").click(function(event){
                    jqcc[settings.theme].setGuestName();
                });
                cometchat_optionsbutton_popup.find("span.busy").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='busy'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'busy');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('busy');
                    }
                });
                cometchat_optionsbutton_popup.find("span.invisible").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'invisible');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('invisible');
                    }
                });
                cometchat_optionsbutton_popup.find("textarea.cometchat_statustextarea").keydown(function(event){
                    return jqcc.cometchat.statusKeydown(event, this);
                });
                cometchat_optionsbutton_popup.find("input.cometchat_guestnametextbox").keydown(function(event){
                    return jqcc.cometchat.guestnameKeydown(event, this);
                });
                var cometchat_optionsbutton = $('#cometchat_optionsbutton');
                cometchat_optionsbutton.mouseover(function(){
                    if(!cometchat_optionsbutton_popup.hasClass("cometchat_tabopen") && !cometchat_auth_popup.hasClass("cometchat_tabopen")){
                        if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', language[0]);
                            }
                        }else{
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', jqcc(this).attr("title"));
                            }
                        }
                        if(jqcc.cometchat.getThemeVariable('banned', '1')) {
                            jqcc[settings.theme].tooltip('cometchat_optionsbutton', bannedMessage);
                        }
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_optionsbutton.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                cometchat_optionsbutton.click(function(){
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        jqcc[settings.theme].removeClass_cometchat_trayclick();
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                        if(jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            cometchat_optionsbutton_popup.find("span.available").click();
                        }
                        $("#cometchat_tooltip").css('display', 'none');
                        if(jqcc('#cometchat_userstab').offset() != null) {
                            var cometchat_userstab_popup_left = jqcc('#cometchat_userstab').offset().left-jqcc(window).scrollLeft();
                        }
                        $('#cometchat_optionsbutton_popup').css('left', cometchat_userstab_popup_left-2);
                        $(this).toggleClass("cometchat_tabclick");
                        cometchat_optionsbutton_popup.toggleClass("cometchat_tabopen");
                        $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                        if($.cookie(settings.cookiePrefix+"sound")){
                            if($.cookie(settings.cookiePrefix+"sound")=='true'){
                                $("#cometchat_soundnotifications").attr("checked", true);
                            }else{
                                $("#cometchat_soundnotifications").attr("checked", false);
                            }
                        }
                        if($.cookie(settings.cookiePrefix+"popup")){
                            if($.cookie(settings.cookiePrefix+"popup")=='true'){
                                $("#cometchat_popupnotifications").attr("checked", true);
                            }else{
                                $("#cometchat_popupnotifications").attr("checked", false);
                            }
                        }
                        if($.cookie(settings.cookiePrefix+"disablelastseen")){
                            if($.cookie(settings.cookiePrefix+"disablelastseen")=='true'){
                                lastseenflag = true;
                                $("#cometchat_lastseen").attr("checked", true);
                            }else{
                                lastseenflag = false;
                                $("#cometchat_lastseen").attr("checked", false);
                            }
                        }
                        if($.cookie(settings.cookiePrefix+"disablemessagereceipt")){
                            if($.cookie(settings.cookiePrefix+"disablemessagereceipt")==1){
                                messagereceiptflag = 1;
                                $("#cometchat_messagereceipt").attr("checked", true);
                            }else{
                                messagereceiptflag = 0;
                                $("#cometchat_messagereceipt").attr("checked", false);
                            }
                        }
                    }else{
                        if(settings.ccauth.enabled == "1"){
                            $("#cometchat_tooltip").css('display', 'none');
                            var baseLeft = $('#cometchat_base').position().left;
                            var cometchat_hide = $('#cometchat_hide').innerWidth();
                            cometchat_auth_popup.css('right', baseLeft+cometchat_hide);
                            $(this).toggleClass("cometchat_tabclick");
                            cometchat_auth_popup.toggleClass("cometchat_tabopen");
                        }
                    }
                });
                var auth_cometchat_userstabtitle = cometchat_auth_popup.find("div.cometchat_userstabtitle");
                var auth_cometchat_minimize = auth_cometchat_userstabtitle.find("div.cometchat_minimizebox");

                auth_cometchat_userstabtitle.click(function(){
                    cometchat_optionsbutton.click();
                });
                auth_cometchat_userstabtitle.mouseenter(function(){
                    auth_cometchat_minimize.addClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_cometchat_userstabtitle.mouseleave(function(){
                    auth_cometchat_minimize.removeClass("cometchat_chatboxtraytitlemouseover");
                });

                var cometchat_userstabtitle = cometchat_optionsbutton_popup.find(".cometchat_userstabtitle");
                var auth_logout = cometchat_userstabtitle.find("div#cometchat_authlogout");
                cometchat_userstabtitle.click(function(){
                    $('#cometchat_optionsbutton').click();
                });
                cometchat_userstabtitle.mouseenter(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstabtitle.mouseleave(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_logout.mouseenter(function(){
                    auth_logout.css('opacity','1');
                    cometchat_optionsbutton_popup.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_logout.mouseleave(function(){
                    auth_logout.css('opacity','0.5');
                    cometchat_optionsbutton_popup.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                logout_click();
                function logout_click(){
                    auth_logout.click(function(event){
                        auth_logout.unbind('click');
                        event.stopPropagation();
                        auth_logout.css('background','url('+baseUrl+'themes/glass/images/loading.gif) no-repeat top left');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                            success: function(){
                                if(typeof(cometuncall_function)==="function"){
                                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                                    jqcc.cometchat.setThemeVariable('cometid','');
                                }
                                auth_logout.css('background','url('+baseUrl+'themes/glass/images/logout.png) no-repeat top left');
                                logout_click();
                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').click();
                                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                                $.cookie(settings.cookiePrefix+"loggedin", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"state", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber_type", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"hidebar", null, {path: '/'});
                                jqcc[settings.theme].loggedOut();
                                jqcc.cometchat.setThemeVariable('loggedout', 1);
                                clearTimeout(jqcc.cometchat.getCcvariable().heartbeatTimer);
                            },
                            error: function(){
                                logout_click();
                                alert(language[81]);
                            }
                        });
                    });
                }
            },
            chatboxKeyup: function(event, chatboxtextarea, id){

                var adjustedHeight = chatboxtextarea.clientHeight;

                var maxHeight = 94;
                if(maxHeight>adjustedHeight){
                    adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                    if(maxHeight)
                        adjustedHeight = Math.min(maxHeight, adjustedHeight);

                    if(adjustedHeight>chatboxtextarea.clientHeight){
                        $(chatboxtextarea).css('height', adjustedHeight+4+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', (chatboxHeight-(adjustedHeight-25))+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', (chatboxHeight-(adjustedHeight-25))+'px');
                    }
                }else{
                    $(chatboxtextarea).css('overflow-y', 'auto');
                }
            },
            chatboxKeydown: function(event, chatboxtextarea, id, force){
                var condition = 1;
                if((event.keyCode==13&&event.shiftKey==0)||force==1 && !$(chatboxtextarea).hasClass('placeholder')){
                    var message = $(chatboxtextarea).val();
                    message = message.replace(/^\s+|\s+$/g, "");
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height', '18px');

                    $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', ((chatboxHeight)+11)+'px');
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', (chatboxHeight)+'px');

                    /*$("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', ((chatboxHeight)+30)+'px');
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', ((chatboxHeight)+30)+'px');*/

                    $(chatboxtextarea).css('overflow-y', 'hidden');
                    $(chatboxtextarea).focus();
                    if(settings.floodControl){
                        condition = ((Math.floor(new Date().getTime()))-lastmessagetime>2000);
                    }
                    if(settings.cometserviceEnabled == 1 && settings.istypingEnabled == 1 && settings.transport == 'cometservice'){
                        var fid = jqcc.cometchat.getThemeVariable('userid');
                        var senttime = parseInt(new Date().getTime());
                        if(settings.transport == 'cometservice-selfhosted'){
                            var jsondata = {channel:"/"+jqcc.cometchat.getThemeArray('buddylistChannelHash', id),data:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingStop\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        } else if(settings.transport == 'cometservice'){
                            var jsondata = {channel:jqcc.cometchat.getThemeArray('buddylistChannelHash', id),message:{"from":fid,"message":"CC^CONTROL_{\"type\":\"core\",\"name\":\"textchat\",\"method\":\"typingStop\",\"params\":{\"fromid\":"+fid+",\"typingtime\":"+senttime+"}}","sent":senttime,"self":0},callback:""}
                        }
                        COMET.publish(jsondata);
                    }
                    if(message!=''){
                        if(condition){
                            lastmessagetime = Math.floor(new Date().getTime());
                            if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                                jqcc.cometchat.chatboxKeydownSet(id, message);
                            }else{
                                jqcc.ccjabber.sendMessage(id, message);
                            }
                        }else{
                            alert(language[53]);
                        }
                    }
                    return false;
                }
            },
            scrollBars: function(silent){
                var hidden = 0;
                var change = 0;
                var change2 = 0;
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_right_last')){
                    change = 1;
                }
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')){
                    change2 = 1;
                }
                if(cometchat_chatboxes.scrollLeft()==0){
                    cometchat_chatbox_left.addClass('cometchat_chatbox_left_last').find('span.cometchat_tabtext').html('0');
                    hidden++;
                }else{
                    var number = Math.round(cometchat_chatboxes.scrollLeft()/152);
                    cometchat_chatbox_left.find('span.cometchat_tabtext').html(number);
                    cometchat_chatbox_left.removeClass('cometchat_chatbox_left_last');
                }
                if((cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width())==$("#cometchat_chatboxes_wide").width()){
                    cometchat_chatbox_right.addClass('cometchat_chatbox_right_last').find('span.cometchat_tabtext').html('0');
                    hidden++;
                }else{
                    var number = Math.round(($("#cometchat_chatboxes_wide").width()-(cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width()))/152);
                    cometchat_chatbox_right.removeClass('cometchat_chatbox_right_last').find('span.cometchat_tabtext').html(number);
                }
                if(hidden==2){
                    cometchat_chatbox_right.addClass('cometchat_chatbox_lr');
                    cometchat_chatbox_left.addClass('cometchat_chatbox_lr');
                }else{
                    cometchat_chatbox_right.removeClass('cometchat_chatbox_lr');
                    cometchat_chatbox_left.removeClass('cometchat_chatbox_lr');
                }
                if((!cometchat_chatbox_right.hasClass('cometchat_chatbox_right_last')&&change==1)||(cometchat_chatbox_right.hasClass('cometcha t_chatbox_right_last')&&change==0)||(!cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')&&change2==1)||(cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')&&change2==0)){
                    jqcc[settings.theme].windowResize(silent);
                }
            },
            scrollDown: function(id,scrollForce){
                if(jqcc().slimScroll){
                    $('#cometchat_tabcontenttext_'+id).slimScroll({scroll: '1'});
                }else{
                    setTimeout(function(){
                        $("#cometchat_tabcontenttext_"+id).scrollTop(50000);
                    }, 100);
                }
            },
            updateChatbox: function(id){
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    jqcc.cometchat.updateChatboxSet(id);
                }else{
                    jqcc.ccjabber.getRecentData(id);
                }
            },
            updateChatboxSuccess: function(id, data){
                var name = jqcc.cometchat.getThemeArray('buddylistName', id);
                $("#cometchat_tabcontenttext_"+id).html('');
                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'&&data.hasOwnProperty('messages')){
                    jqcc[settings.theme].addMessages(data['messages']);
                }
                jqcc[settings.theme].scrollDown(id);
            },
            windowResize: function(silent){
                var baseWidth = $(window).width();
                var extraWidth = trayWidth+32;
                if(extraWidth<80){
                    extraWidth = 80;
                }
                var cometchat_base = $('#cometchat_base');
                var cometchat_chatboxes = $('#cometchat_chatboxes');
                if(settings.barType=='fixed'){
                    cometchat_base.css('width', settings.barWidth);
                    if(settings.barAlign=='center'){
                        var distance = (baseWidth-settings.barWidth)/2;
                        cometchat_base.css('left', distance);
                    }
                    if(settings.barAlign=='right'){
                        var distance = (baseWidth-settings.barWidth);
                        cometchat_base.css('left', distance-settings.barPadding);
                    }
                    if(settings.barAlign=='left'){
                        cometchat_base.css('left', settings.barPadding);
                    }
                }else{
                    if(baseWidth<400+extraWidth+settings.barPadding+20){
                        baseWidth = 400+extraWidth+settings.barPadding+20;
                    }
                    cometchat_base.css('left', settings.barPadding);
                    cometchat_base.css('width', baseWidth-(settings.barPadding*2));
                }
                if(cometchat_base.length && jqcc('#cometchat_userstab').length){
                    var cometchat_userstab_popup_left = jqcc('#cometchat_userstab').offset().left-jqcc(window).scrollLeft();
                    $('#cometchat_userstab_popup').css('left', cometchat_userstab_popup_left);
                    $('#cometchat_optionsbutton_popup').css('left', cometchat_userstab_popup_left-2);
                }
                if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                    $('#cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').css('left', $('#cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')).offset().left-jqcc(window).scrollLeft()).css('width', trayicon[jqcc.cometchat.getThemeVariable('trayOpen')][4]);
                }
                if($('#cometchat_chatboxes_wide').width()<=(cometchat_base.width()-26-178-44-extraWidth)){
                    cometchat_chatboxes.css('width', $('#cometchat_chatboxes_wide').width());
                    cometchat_chatboxes.scrollToCC("0px", 0);
                }else{
                    var change = cometchat_chatboxes.width();
                    cometchat_chatboxes.css('width', Math.floor((cometchat_base.width()-26-178-44-extraWidth)/152)*152);
                    var newChange = cometchat_chatboxes.width();
                    if(change!=newChange){
                        cometchat_chatboxes.scrollToCC("-=152px", 0);
                    }
                }
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''&&silent!=1){
                    if(($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-cometchat_chatboxes.offset().left)>=0){
                        var cometchat_user_id = $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId'));
                        var isfirst = 0;
                        if(cometchat_user_id.is(':first-child')){
                            isfirst = 1;
                        }
                        var calc_position = cometchat_user_id.offset().left-jqcc(window).scrollLeft()+152-chatboxWidth+1-isfirst;/*152 cometchat_tab and 1 its border*/
                        $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').css('left', calc_position);
                    }else{
                        $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                        var newPosition = (($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-$("#cometchat_chatboxes_wide").offset().left))-((Math.floor((cometchat_chatboxes.width()/152))-1)*152);
                        cometchat_chatboxes.scrollToCC(newPosition+'px', 0, function(){
                            jqcc[settings.theme].positionChatbox(jqcc.cometchat.getThemeVariable('openChatboxId'));
                        });
                    }
                }
                jqcc[settings.theme].checkPopups(silent);
                jqcc[settings.theme].scrollBars(silent);
            },
            positionChatbox: function(id){
                if($("#cometchat_user_"+id).length==1 && $("#cometchat_user_"+id+"_popup").length==1){
                    $("#cometchat_user_"+id).click();
                    $("#cometchat_user_"+id+"_popup").offset({
                        left:(jqcc("#cometchat_user_"+id).offset().left+$("#cometchat_user_"+id).outerWidth()-$("#cometchat_user_"+id+"_popup").width()+1)
                    });
                }
            },
            chatWith: function(id){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        $("#cometchat_optionsbutton_popup").find("span.available").click();
                    }
                    if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && jqcc.cometchat.getUserID() != id){
                        jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                    }
                }
            },
            scrollFix: function(){
                var elements = ['cometchat_base', 'cometchat_userstab_popup', 'cometchat_optionsbutton_popup', 'cometchat_tooltip', 'cometchat_hidden'];
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    elements.push('cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup');
                }
                if(jqcc.cometchat.getThemeVariable('trayOpen')!=''&&jqcc.cometchat.getThemeVariable('trayOpen')!=0){
                    elements.push('cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup');
                }
                for(x in elements){
                    $('#'+elements[x]).css('position', 'absolute');
                    var bottom = parseInt($('#'+elements[x]).css('bottom'));
                    if(x==0){
                        bottom = 0;
                    }
                    var height = parseInt($('#'+elements[x]).height());
                    if(windowHeights[elements[x]]&&x!=3){
                        height = windowHeights[elements[x]];
                    }else{
                        windowHeights[elements[x]] = height;
                    }
                    $('#'+elements[x]).css('top', (parseInt($(window).height())-bottom-height+parseInt($(window).scrollTop()))+'px');
                }
            },
            checkPopups: function(silent){
                var cometchat_tabalertlr_left = $("#cometchat_chatbox_left").find("span.cometchat_tabalertlr");
                var cometchat_tabalertlr_right = $("#cometchat_chatbox_right").find("span.cometchat_tabalertlr");
                cometchat_tabalertlr_left.html('0').css('display', 'none');
                cometchat_tabalertlr_right.html('0').css('display', 'none');
                $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").each(function(){
                    if(($(this).parent().offset().left<($("#cometchat_chatboxes").offset().left+$("#cometchat_chatboxes").width()))&&($(this).parent().offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                        $(this).css('display', 'block');
                    }else{
                        $(this).css('display', 'none');
                        if(($(this).parent().offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                            cometchat_tabalertlr_right.html(parseInt($("#cometchat_chatbox_right").find("span.cometchat_tabalertlr").html())+parseInt($(this).html())).css('display', 'block');
                        }else{
                            cometchat_tabalertlr_right.html(parseInt($("#cometchat_chatbox_left").find("span.cometchat_tabalertlr").html())+parseInt($(this).html())).css('display', 'block');
                        }
                    }
                });
            },
            launchModule: function(id){
                if(!$('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            toggleModule: function(id){
                $("#cometchat_trayicon_"+id).click();
            },
            closeModule: function(id){
                if($('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            closeAllModule: function(lightboxClose){
                trayicon = jqcc.cometchat.getTrayicon();
                jqcc('#cometchat_trayicons').find('.cometchat_traypopup').each(function(){
                    jqcc(this).removeClass('cometchat_tabopen');
                });
                jqcc[settings.theme].removeClass_cometchat_trayclick();
                if(typeof(lightboxClose) == 'undefined' || lightboxClose == 1){
                    for(x in trayicon){
                        if(x!='home' && x!='scrolltotop' && x!='chatrooms'){
                            if(jqcc('#cometchat_container_'+x).length > 0){
                                jqcc('#cometchat_container_'+x).detach();
                            }
                        }
                    }
                }
                jqcc.cometchat.setThemeVariable('trayOpen','');
                jqcc.cometchat.setSessionVariable(jqcc.cometchat.getThemeVariable('trayOpen'), '');
            },
            joinChatroom: function(roomid, inviteid, roomname){
                $("#cometchat_trayicon_chatrooms").click();
                jqcc('#cometchat_trayicon_chatrooms_iframe,.cometchat_embed_chatrooms').attr('src', baseUrl+'modules/chatrooms/index.php?roomid='+roomid+'&inviteid='+inviteid+'&roomname='+roomname+'&basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                jqcc.cometchat.setThemeVariable('openChatboxId', '');
            },
            hideBar: function(){
                jqcc.cometchat.closeAllModule();
                $('#cometchat').css('display', 'none');
                $('#cometchat_hidden').css('display', 'block');
                $.cookie(settings.cookiePrefix+"hidebar", '1', {path: '/', expires: 365});
            },
            closeTooltip: function(){
                $("#cometchat_tooltip").css('display', 'none');
            },
            scrollToTop: function(){
                $("html,body").animate({scrollTop: 0}, {"duration": "slow"});
            },
            reinitialize: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==1){
                    $('#cometchat_auth_popup').removeClass("cometchat_tabopen");
                    $("#cometchat_optionsbutton_icon").removeClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").removeClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton_icon").css('display', 'block');
                    $("body").append(msg_beep);
                    $("#cometchat").append(option_button);
                    $("#cometchat").append(user_tab);
                    $("#cometchat_base").append(usertab2);
                    $("#cometchat_base").append(chat_right);
                    $("#cometchat_base").append(chat_boxes);
                    $("#cometchat_base").append(chat_left);
                    $("#cometchat_userstab").show();
                    $("#cometchat_chatboxes").show();
                    $("#cometchat_chatbox_left").show();
                    $("#cometchat_chatbox_right").show();
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                    $("#cometchat_userstab").click();
                }
            },
            updateHtml: function(id, temp){
                if($("#cometchat_user_"+id+"_popup").length>0){
                    document.getElementById("cometchat_tabcontenttext_"+id).innerHTML = '<div>'+temp+'</div>';
                    jqcc[settings.theme].scrollDown(id);
                }else{
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined||jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            $.cometchat.updateHtml(id, temp);
                        }, 1000);
                    }
                }
            },
            updateJabberOnlineNumber: function(number){
                jqcc.cometchat.setThemeVariable('jabberOnlineNumber', number);
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', jqcc.cometchat.getThemeVariable('jabberOnlineNumber')+siteOnlineNumber);
                if(siteOnlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber')>settings.searchDisplayNumber){
                    $('#cometchat_searchbar').css('display', 'block');
                }else{
                    $('#cometchat_searchbar').css('display', 'none');
                }
                if(jqcc.cometchat.getThemeVariable('offline')==0){
                    $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                }
            },
            userClick: function(listing){
                var id = $(listing).attr('id');
                if(typeof id==="undefined"||$(listing).attr('id')==''){
                    id = $(listing).parents('.cometchat_userlist').attr('id');
                }
                id = id.substr(19);
                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                }
            },
            messageBeep: function(baseUrl){
                $('<audio id="messageBeep" style="display:none;"><source src="'+baseUrl+'sounds/beep.mp3" type="audio/mpeg"><source src="'+baseUrl+'sounds/beep.ogg" type="audio/ogg"><source src="'+baseUrl+'sounds/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
            },
            ccClicked: function(id){
                $(id).click();
            },
            moveLeft: function(){
                jqcc[settings.theme].moveBar("-=152px");
            },
            moveRight: function(){
                jqcc[settings.theme].moveBar("+=152px");
            },
            processMessage: function(message, self){
                if(settings.iPhoneView){
                    if(self==null||self==''){
                        return '<table class="cometchat_iphone" cellpadding=0 cellspacing=0 style="float:right"><tr><td class="cometchat_tl"></td><td class="cometchat_tc"></td><td class="cometchat_tr"></td></tr><tr><td class="cometchat_cl"></td><td class="cometchat_cc">'+message+'</td><td class="cometchat_cr"></td></tr><tr><td class="cometchat_bl"></td><td class="cometchat_bc"></td><td class="cometchat_br"></td></tr></table><div style="clear:both"></div>';
                    }else{
                        return '<table class="cometchat_iphone" cellpadding=0 cellspacing=0><tr><td class="cometchat_xtl"></td><td class="cometchat_xtc"></td><td class="cometchat_xtr"></td></tr><tr><td class="cometchat_xcl"></td><td class="cometchat_xcc">'+message+'</td><td class="cometchat_xcr"></td></tr><tr><td class="cometchat_xbl"></td><td class="cometchat_xbc"></td><td class="cometchat_xbr"></td></tr></table><div style="clear:both"></div>';
                    }
                }
                return message;
            },
            minimizeAll: function(){
                $(".cometchat_tabpopup").each(function(index){
                    if($(this).hasClass('cometchat_tabopen')){
                        $('#'+$(this).attr('id')).find('div.cometchat_minimizebox').click();
                    }
                });
            },
            iconNotFound: function(image, name){
                $(image).attr({'src': baseUrl+'modules/'+name+'/icon.png', 'width': '16px'});
            },
            prependMessagesInit: function(id){
                var messages = jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_chatboxmessage');
                $('#cometchat_prependMessages_'+id).text(language[41]);
                jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                if(messages.length > 0){
                    jqcc('#scrolltop_'+id).remove();
                    prepend = messages[0].id.split('_')[2];
                }else{
                    prepend = -1;
                }
                jqcc.cometchat.updateChatboxSet(id,prepend);
            },
            prependMessages:function(id,data){
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
                var msg_time = '';
                var jabber = '';
                $.each(data, function(type, item){
                    if(type=="messages"){
                        $.each(item, function(i, incoming){
                            count = count+1;
                            var selfstyle = '';
                            var fromavatar = '';
                            if(parseInt(incoming.self)==1){
                                fromname = language[10];
                                selfstyle = ' cometchat_self';
                            }else{
                                fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                                fromavatar = '<img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)+'">';
                            }

                            var message = jqcc.cometchat.processcontrolmessage(incoming);

                            if(message == null){
                                return;
                            }

                            if(incoming.jabber == 1 && typeof(incoming.selfadded) != "undefined" && incoming.selfadded != null) {
                                msg_time = incoming.id;
                                jabber = 1;
                            }else{
                                msg_time = incoming.sent;
                                jabber = 0;
                            }

                            msg_time = msg_time+'';

                            if (msg_time.length == 10){
                                msg_time = parseInt(msg_time * 1000);
                            }

                            months_set = new Array(language['jan'],language['feb'],language['mar'],language['apr'],language['may'],language['jun'],language['jul'],language['aug'],language['sep'],language['oct'],language['nov'],language['dec']);

                            d = new Date(parseInt(msg_time));
                            month  = d.getMonth();
                            date  = d.getDate();
                            year = d.getFullYear();
                            msg_date_class = month+"_"+date+"_"+year;
                            msg_date = months_set[month]+" "+date+", "+year;
                            date_class = "";

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
                                msg_date = language['today'];
                            }else  if(msg_date_class == yday_date_class){
                                date_class = "yesterday";
                                msg_date = language['yesterday'];
                            }

                           if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            var separator = ':&nbsp;&nbsp;';
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_chatboxmessage '+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagefrom'+selfstyle+'">'+fromavatar+'</div><div class="cometchat_messagearrow"></div><div class="cometchat_chatboxmessagecontent'+selfstyle+'"><span class="cometchat_msg">'+message+'</span><span id="cometchat_chatboxseen_'+incoming.id+'"></span><div class="cometchat_ts_continer">'+sentdata+'</div></div></div>', selfstyle);
                            oldMessages+=msg;
                        });
                    }
                });
                jqcc('#cometchat_tabcontenttext_'+id).prepend(oldMessages);
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }else{
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.glass.prependMessagesInit('+id+')');
                }
                jqcc[settings.theme].groupbyDate(id,jabber);
            },
            groupbyDate: function(id,j){
                     if(j == '0' ){
                         $('#cometchat_user_'+id+'_popup .cometchat_time').hide();
                         $.each($('#cometchat_user_'+id+'_popup .cometchat_time'),function (i,divele){
                            var classes = $(divele).attr('class').split(/\s+/);
                            for ( var i = 0, l = classes.length; i < l; i++ ) {
                                if(classes[i].indexOf('cometchat_time_') === 0){
                                    $('#cometchat_user_'+id+'_popup .'+classes[i]+':first').show();
                                }
                            }
                        });
                     }else{
                       $('#cometchat_tabcontenttext_'+id+' .cometchat_time').hide();
                       $.each($('#cometchat_tabcontenttext_'+id+' .cometchat_time'),function (i,divele){
                        var classes = $(divele).attr('class').split(/\s+/);
                        for ( var i = 0, l = classes.length; i < l; i++ ) {
                            if(classes[i].indexOf('cometchat_time_') === 0){
                                $('#cometchat_tabcontenttext_'+id+' .'+classes[i]+':first').show();
                            }
                        }
                    });
                   }
           },
            getLastseenTime: function(lastseen) {
                var ap = "";
                var dt=eval(lastseen*1000);
                var ts = new Date(dt);
                var hour = ts.getHours();
                var minute = ts.getMinutes();
                var todaysDate = new Date();
                var todays12am = todaysDate.getTime() - (todaysDate.getTime()%(24*60*60*1000));
                var date = ts.getDate();
                var month = ts.getMonth();
                var armyTime = settings.armyTime;
                if(!armyTime){
                        ap = hour>11 ? "PM" : "AM";
                        hour = hour==0 ? 12 : hour>12 ? hour-12 : hour;
                }else{
                        hour = hour<10 ? "0"+hour : hour;
                    }
                minute = minute<10 ? "0"+minute : minute;
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                var type = 'th';
                if (date == 1 || date == 21 || date == 31) { type = 'st'; }
                else if (date == 2 || date == 22) { type = 'nd'; }
                else if (date == 3 || date == 23) { type = 'rd'; }

                if (ts < todays12am) {
                    return hour+":"+minute+ap+' '+date+type+' '+months[month];
                } else {
                    return hour+":"+minute+ap;
                }
            },
            showLastseen:function(id,lastactivity){
                var lastseen = lastactivity;
                var timest = jqcc[settings.theme].getLastseenTime(lastseen);
                if($('#cometchat_user_'+id+'_popup').find('#cometchat_lastseen_'+id).length == 0){
                    $("#cometchat_user_"+id+"_popup div.cometchat_message").after('<div id="cometchat_lastseen_'+id+'" class="cometchat_lastseenmessage"> '+language[109]+': '+timest+'</div>');
                }
            },
            hideLastseen:function(id){
                $('#cometchat_lastseen_'+id).remove();
            },
            chatScroll: function(id){
                if($('#scrolltop_'+id).length == 0){
                    $("#cometchat_tabcontenttext_"+id).prepend('<div id="scrolltop_'+id+'" class="cometchat_scrollup"><img src="'+baseUrl+'images/arrowtop.png" class="cometchat_scrollimg" /></div>');
                }
                if($('#scrolldown_'+id).length == 0){
                    $("#cometchat_tabcontenttext_"+id).append('<div id="scrolldown_'+id+'" class="cometchat_scrolldown"><img src="'+baseUrl+'images/arrowbottom.png" class="cometchat_scrollimg" /></div>');
                }
                $('#cometchat_tabcontenttext_'+id).unbind('wheel');
                $('#cometchat_tabcontenttext_'+id).on('wheel',function(event){
                    var scrollTop = $(this).scrollTop();
                    if(event.originalEvent.deltaY != 0){
                        clearTimeout($.data(this, 'scrollTimer'));
                        if(event.originalEvent.deltaY > 0){
                            $('#scrolltop_'+id).hide();
                            var down = jqcc("#cometchat_tabcontenttext_"+id)[0].scrollHeight-250-50;
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

                $('#scrolltop_'+id).on("click",function(){
                    $('#scrolltop_'+id).hide();
                    $('#cometchat_tabcontenttext_'+id).slimScroll({scroll: 0});
                });

                $('#scrolldown_'+id).click(function(){
                    $('#scrolldown_'+id).hide();
                    $('#cometchat_tabcontenttext_'+id).slimScroll({scroll: 1});
                });
            }
        };
    })();
})(jqcc);

if(typeof(jqcc.glass) === "undefined"){
    jqcc.glass=function(){};
}

jqcc.extend(jqcc.glass, jqcc.ccglass);

/* code for Cloud Mobileapp compatibilty to Hide CometChat bar.*/
jqcc(document).ready(function() {
    var platform = jqcc.cookie('cc_platform_cod');
    if((platform == 'android' || platform == 'ios') && jqcc.cometchat.getSettings().enableMobileTab == 1) {
        var hideInterval = setInterval(function(){
            if(jqcc('.cometchat_ccmobiletab_redirect').length>0||jqcc('#cometchat').length>0){
                jqcc('#cometchat').hide();
                jqcc('.cometchat_ccmobiletab_redirect').hide();
                clearTimeout(hideInterval);
            }
        },500);
    }
});
