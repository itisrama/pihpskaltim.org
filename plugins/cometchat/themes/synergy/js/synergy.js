<?php
    foreach ($trayicon as $value){
        if($value[0]=='chatrooms'){
            if(file_exists(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js")){
            include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js");
            }
        }
    }
?>

(function($){
    $.ccsynergy = (function(){
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
        var tooltipPriority = 0;
        var desktopNotifications = {};
        var webkitRequest = 0;
        var lastmessagetime = Math.floor(new Date().getTime());
        var favicon;
        var checkfirstmessage;
        var cometchat_lefttab;
        var cometchat_righttab;
        var chromeReorderFix = '_';
        var hasChatroom = 0;
        var newmesscr;
        var cookiePrefix = '<?php echo $cookiePrefix; ?>';
        var lastseen = 0;
        var lastseenflag = false;
        var iOSmobileDevice = navigator.userAgent.match(/ipad|ipod|iphone/i);
        var mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|blackberry|palm|symbian/i);
        var messagereceiptflag = 0;
        return {
            playSound: function(){
                var flag = 0;
                try{
                    if(settings.messageBeep==1&&(settings.beepOnAllMessages==1||(settings.beepOnAllMessages == 0 && checkfirstmessage == 1))){
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
                var tabWidth = 'width: 100%;';

                if(embeddedchatroomid != 0 || chatroomsonly == 1){
                    settings.enableType = 1;
                }
                if(settings.enableType == 0 || settings.enableType == 1) {
                    hasChatroom = 1;
                    if(settings.enableType==0) {
                        tabWidth = 'width: 50%;left: 0;';
                    }
                }

                if(settings.windowFavicon==1){
                    favicon = new Favico({
                        animation: 'pop'
                    });
                }
                $("body").append('<div id="cometchat"></div><div id="cometchat_tooltip"><div class="cometchat_tooltip_content"></div></div>');
                var optionsbutton = '';
                var optionsbuttonpop = '';
                var ccauthlogout = '';
                var usertab = '';
                var usertabpop = '';
                var optionsbuttonpadding = '';
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
                if(settings.ccauth.enabled=="1" || jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                    ccauthlogout = '<div id="cometchat_authlogout" title="'+language[80]+'"></div>';
                    optionsbuttonpadding = 'style="margin-right: 0px;"';
                }
                if(settings.showSettingsTab==1){
                    optionsbuttonpop = '<div id="cometchat_optionsbutton_popup" class="cometchat_dropdownpopup cometchat_tabpopup" style="display:none"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_optionstyle"><div class="cometchat_optionstyle_container"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea" maxlength="140"></textarea><div style="overflow:hidden;"><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusmessagecount">'+count+'</div></div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div>'+lastseenoption+messagereceiptoption+'</div></div></div>';
                    optionsbutton = '<div id="cometchat_optionsbutton" '+optionsbuttonpadding+' title = "'+language[0]+'"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div>'+optionsbuttonpop+'</div>'+ccauthlogout;
                }
                var selfDetails = '<div id="cometchat_self_container"><div id="cometchat_self_right">'+optionsbutton+'</div><div id="cometchat_self_left"></div></div>';
                if(settings.showModules==1){
                    trayData += '<div id="cometchat_trayicons" class="cometchat_tabsubtitle">';
                    for(x in trayicon){
                        if(trayicon.hasOwnProperty(x)){
                            if(x!='chatrooms'){
                                var icon = trayicon[x];
                                if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'){
                                    if(x != 'home' && x != 'scrolltotop' && x != 'themechanger' && x != 'share' && x !='translate'){
                                        trayData += '<span id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage" title="'+trayicon[x][1]+'" name="'+x+'" ><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\')" width="16px"></span>';
                                    }
                                }else if(mobileDevice){
                                    if(x != 'scrolltotop' && x != 'themechanger' && x != 'share' && x !='translate'){
                                        trayData += '<span id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage" title="'+trayicon[x][1]+'" name="'+x+'" ><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\')" width="16px"></span>';
                                    }
                                }else{
                                    if(x != 'home' && x != 'scrolltotop' && x != 'themechanger'){
                                        trayData += '<span id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage" title="'+trayicon[x][1]+'" name="'+x+'" ><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\')" width="16px"></span>';
                                    }
                                }
                            }
                        }
                    }
                    trayData += '</div>';
                }
                var cc_state = $.cookie(settings.cookiePrefix+'state');
                var number = 0;
                if(cc_state!=null){
                    var cc_states = cc_state.split(/:/);
                    number = cc_states[3];
                }

                usertabpop = '<div id="cometchat_popup_container"></div>';
                if(settings.showOnlineTab==1 && settings.enableType!=1){
                    usertab = '<span id="cometchat_userstab" class="cometchat_tab" style="'+tabWidth+'"><span id="cometchat_userstab_text" class="cometchat_tabstext">'+language[9]+' ('+number+')</span></span>';
                    usertabpop = '<div id="cometchat_popup_container"><div id="cometchat_userstab_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_tabsubtitle" id="cometchat_user_searchbar"><input type="text" name="cometchat_user_search" class="cometchat_search cometchat_search_light" id="cometchat_user_search" value="'+language[18]+'"></div><div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cc_gotoPrevNoti"></div><div id="cc_gotoNextNoti"></div><div id="cometchat_activechatboxes_popup"></div><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div></div>';
                }
                var tabscontainer = '<div id="cometchat_tabcontainer">'+usertab+'</div>';
                if(hasChatroom != 1  || settings.enableType != 0){
                    tabscontainer = '';
                    if(jqcc.cometchat.getSessionVariable('buddylist')!=1){
                        jqcc.cometchat.setThemeArray('sessionVars','buddylist', '1');
                    }
                }
                var baseCode = '<div class="cometchat_offline_overlay"><h3>'+language[92]+'</h3></div><div id="cometchat_lefttab">'+''+selfDetails+''+trayData+tabscontainer+usertabpop+'</div><div id="cometchat_righttab"><div class="cometchat_noactivity"><h1>'+language[89]+' <span id="cometchat_welcome_username"></span>'+language[91]+'</h1><h3>'+language[90]+'</h3></div></div>';

                document.getElementById('cometchat').innerHTML = baseCode;
                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomInit();
                }
                if(settings.enableType == 2) {
                    $('#cometchat_userstab_popup').addClass("cometchat_tabopen");
                }
                if(settings.showSettingsTab==0){
                    $('#cometchat_userstab').addClass('cometchat_extra_width');
                    $('#cometchat_userstab_popup').find('div.cometchat_tabstyle').addClass('cometchat_border_bottom');
                }
                if(jqcc().slimScroll && mobileDevice == null){
                    $('#cometchat_userscontent').slimScroll({height: 'auto'});
                    $('#cometchat_userscontent').attr('style','overflow: hidden !important');
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
				$('#cometchat_userscontent').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_trayicon').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $(window).bind('resize', function(){
                    if(mobileDevice){
                        $('#cometchat').css('overflow','scroll');
                    }
                    jqcc[settings.theme].windowResize();
                });
                if(typeof document.body.style.maxHeight==="undefined"){
                    jqcc[settings.theme].scrollFix();
                    $(window).bind('scroll', function(){
                        jqcc[settings.theme].scrollFix();
                    });
                }else if(mobileDevice){
					var mobileOverlay = '';
					if('<?php echo $p_; ?>'<3){
                        mobileOverlay = '<div class="cometchat_mobile_overlay"><p>'+language[94]+'</p></div>';
                        $('#cometchat').html(mobileOverlay);
                        jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                    }
                }
                $('.cometchat_openmobiletab').click(function(event){
                    var url = jqcc.cometchat.getBaseUrl()+'cometchat_popout.php?cookiePrefix='+cookiePrefix+'&basedata='+jqcc.cometchat.getBaseData()+'&ccmobileauth='+jqcc.cometchat.getThemeVariable('ccmobileauth');
                    jqcc.ccmobiletab.openWebapp(url);
                });
                $('.cometchat_trayiconimage').click(function(event){
                    event.stopImmediatePropagation();
                    var moduleName = $(this).attr('name');
                    var windowMode = 0;
                    if(jqcc.cometchat.getCcvariable().callbackfn=='desktop' || mobileDevice){
                        windowMode = 1;
                    }
                    if(moduleName == 'home') {
                        if(typeof settings.ccsiteurl != "undefined" && settings.ccsiteurl != "") {
                            window.location = settings.ccsiteurl;
                        } else {
                            window.location = "/";
                        }
                    } else if(window.top == window.self || jqcc.cometchat.getCcvariable().callbackfn=='desktop') {
                        jqcc.cometchat.lightbox(moduleName,'',windowMode);
                    } else {
                        var controlparameters = {"type":"modules", "name":"cometchat", "method":"lightbox", "params":{"moduleName":moduleName, "caller":"cometchat_synergy_iframe"}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                    }

                });
                document.onmousemove = function(){
                    var nowTime = new Date();
                    jqcc.cometchat.setThemeVariable('idleTime', Math.floor(nowTime.getTime()/1000));
                };
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

                $('#cometchat_userscontent').on('DOMMouseScroll mousewheel', function(event){
                    clearTimeout($.data(this, 'timer'));
                    $.data(this, 'timer', setTimeout(function() {
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                    }, 250));
                });
                $('#cometchat_userstab_popup').find('.cometchat_tabcontent').on('mouseup', function(event){
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                });
                $('#cometchat_userstab_popup').find('.cometchat_tabcontent').on('mousedown', function(event){
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                });
                $('#cc_gotoPrevNoti').click(function(event){
                    var mindiff = 0;
                    var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent');
                    var cometchat_userslist = $('#cometchat_userslist');
                    var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                    var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                    var cometchat_userscontent = $('#cometchat_userscontent');
                    var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                    var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                    var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                    var heightScrolled = parseFloat(percentScroll*fullheight)-(cometchat_userscontent_ht*percentScroll);
                    var userHeight = $('.cometchat_userlist').outerHeight();
                    var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                    var scrolltomsg;
                    $('.cometchat_userlist').each(function(){
                        var diff = 0;
                        if($(this).find('.cometchat_msgcount').length>0){
                            var userHeightFromTop = 0;
                            activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                            if(typeof(activeChatboxesHeight) != "number"){
                                activeChatboxesHeight =0;
                            }
                            userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                            diff = Math.round(heightScrolled - userHeightFromTop);
                            if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                                mindiff = Math.round(diff) ;
                                scrolltomsg = userHeightFromTop;
                            }
                        }
                    });
                    if(mindiff > 0){
                        scrolltomsg = (scrolltomsg  < 0)?0:scrolltomsg;
                        cometchat_userscontent.scrollTop(scrolltomsg);
                        var newpercentScroll = scrolltomsg/fullheight ;
                        var bartop = newpercentScroll*cometchat_userscontent_ht;
                        bartop = (bartop > railMinusBarHt)?railMinusBarHt:bartop;
                        bar.css('top',bartop+'px');
                        jqcc[settings.theme].calcPrevNoti();
                        jqcc[settings.theme].calcNextNoti();
                    }
                    jqcc[settings.theme].calcPrevNoti();
                    jqcc[settings.theme].calcNextNoti();
                });
                $('#cc_gotoNextNoti').click(function(event){
                    var mindiff = 0;
                    var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent');
                    var cometchat_userslist = $('#cometchat_userslist');
                    var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                    var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                    var cometchat_userscontent = $('#cometchat_userscontent');
                    var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                    var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                    var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                    var heightScrolled = parseFloat(percentScroll*fullheight)+(cometchat_userscontent_ht*(1-percentScroll));
                    var userHeight = $('.cometchat_userlist').outerHeight();
                    var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                    var scrolltomsg = 0 ;
                    $('.cometchat_userlist').each(function(){
                        var diff = 0;
                        if($(this).find('.cometchat_msgcount').length>0){
                            var userHeightFromTop = 0;
                            activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                            if(typeof(activeChatboxesHeight) != "number"){
                                activeChatboxesHeight =0;
                            }
                            userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                            diff = Math.round(userHeightFromTop - heightScrolled + userHeight);
                            if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                                mindiff = diff;
                                scrolltomsg = userHeightFromTop;
                            }
                        }
                    });
                    if(mindiff >0){
                        scrolltomsg = (scrolltomsg  > fullheight)?fullheight:scrolltomsg;
                        cometchat_userscontent.scrollTop(scrolltomsg);
                        var newpercentScroll = scrolltomsg/fullheight ;
                        var bartop = newpercentScroll*cometchat_userscontent_ht;
                        bartop = (bartop > railMinusBarHt)?railMinusBarHt:bartop;
                        bar.css('top',bartop+'px');
                        jqcc[settings.theme].calcPrevNoti();
                        jqcc[settings.theme].calcNextNoti();
                    }
                });
                $('.cometchat_offline_overlay').click(function(){
                    $('.cometchat_offline_overlay').css('display','none');
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setSessionVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                        $('.cometchat_noactivity').css('display','block');
                        if(chatroomsonly == 1 && !($('#cometchat_chatroomstab_popup').hasClass("cometchat_tabopen"))){
                            $('#cometchat_chatroomstab_popup').addClass('cometchat_tabopen');
                        } else {
                            $('#cometchat_userstab').click();
                        }
                    }
                });
                if($.cookie(settings.cookiePrefix+"disablemessagereceipt")){
                    if($.cookie(settings.cookiePrefix+"disablemessagereceipt")==1){
                        jqcc.cometchat.setExternalVariable('messagereceiptsetting', 1);
                    }
                }
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
            },
            calcNextNoti: function(){
                var mindiff = 0;
                var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent');
                var cometchat_userslist = $('#cometchat_userslist');
                var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                var cometchat_userscontent = $('#cometchat_userscontent');
                var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                var heightScrolled = parseFloat(percentScroll*fullheight)+(cometchat_userscontent_ht*(1-percentScroll));
                var userHeight = $('.cometchat_userlist').outerHeight();
                var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                $('.cometchat_userlist').each(function(){
                    var diff = 0;
                    if($(this).find('.cometchat_msgcount').length>0){
                        var userHeightFromTop = 0;
                        activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                        if(typeof(activeChatboxesHeight) != "number"){
                            activeChatboxesHeight =0;
                        }
                        userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                        diff = Math.round(userHeightFromTop - heightScrolled);
                        if((diff > 0 && diff < mindiff && userHeightFromTop > cometchat_userscontent_ht)||(diff > 0 && mindiff == 0 && userHeightFromTop > cometchat_userscontent_ht)){
                            mindiff = diff;
                        }
                    }
                });
                if(mindiff<=0){
                    $("#cc_gotoNextNoti").hide();
                }else{
                    $("#cc_gotoNextNoti").show();
                }
            },
            calcPrevNoti: function(){
                var mindiff = 0;
                var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent');
                var cometchat_userslist = $('#cometchat_userslist');
                var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                var cometchat_userscontent = $('#cometchat_userscontent');
                var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                var heightScrolled = parseFloat(percentScroll*fullheight)-(cometchat_userscontent_ht*percentScroll);
                var userHeight = $('.cometchat_userlist').outerHeight();
                var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                $('.cometchat_userlist').each(function(){
                    var diff = 0;
                    if($(this).find('.cometchat_msgcount').length>0){

                        var userHeightFromTop = 0;
                        activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                        if(typeof(activeChatboxesHeight) != "number"){
                            activeChatboxesHeight =0;
                        }
                        userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                        diff = Math.round(heightScrolled - userHeightFromTop);
                        if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                            mindiff = Math.round(diff) ;
                        }
                    }
                });
                if(mindiff<=0){
                    $("#cc_gotoPrevNoti").hide();
                }else{
                    $("#cc_gotoPrevNoti").show();
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
                    if(buddy.n == null || buddy.n == 'null' || buddy.n == '') {
                        return;
                    }
                    longname = buddy.n;
                    if(lastseenflag){
                        jqcc[settings.theme].hideLastseen(buddy.id);
                    } else if(!lastseenflag){
                        if((buddy.s == 'available') || (buddy.s == 'offline' && buddy.lstn == 1)){
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
                        $("#cometchat_user_"+buddy.id+"_popup").find("span.cometchat_userscontentdot")
                            .removeClass("cometchat_available")
                            .removeClass("cometchat_busy")
                            .removeClass("cometchat_offline")
                            .removeClass("cometchat_away")
                            .removeClass("cometchat_blocked")
                            .removeClass("cometchat_mobile")
                            .removeClass("cometchat_mobile_available")
                            .removeClass("cometchat_mobile_busy")
                            .removeClass("cometchat_mobile_offline")
                            .removeClass("cometchat_mobile_away")
                            .addClass("cometchat_"+usercontentstatus);
                        if(icon == ''){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_dot").remove();
                        }else if($("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_dot").length<1){
                            $("#cometchat_user_"+buddy.id+"_popup").find("span.cometchat_userscontentdot").append(icon);
                        }
                        if(buddy.s!='blocked'){
                             $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_blocked_overlay").remove();
                        }
                        if($("#cometchat_user_"+buddy.id+"_popup").length>0){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_userdisplaystatus").html(buddy.m);
                        }
                    }
                    if(buddy.s!='offline'){
                        onlineNumber++;
                    }
                    totalFriendsNumber++;
                    var group = '';
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
                    if((buddy.s != 'offline' && settings.hideOffline == 1) || settings.hideOffline == 0){
                        buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar">'+overlay_div+'<img class="cometchat_userscontentavatarimage" original="themes/'+settings.theme+'/images/cometchat_'+buddy.s+'.png"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><span id="cometchat_buddylist_typing_'+buddy.id+'" class="cometchat_buddylist_typing"></span><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
                        buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar">'+overlay_div+'<img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><span id="cometchat_buddylist_typing_'+buddy.id+'" class="cometchat_buddylist_typing"></span><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
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
                        tooltipMessage += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.chatWith(\''+buddy.id+'\')"><div class="cometchat_notification_avatar"><img class="cometchat_notification_avatar_image" src="'+buddy.a+'"></div><div class="cometchat_notification_message">'+buddy.n+' '+message+'</div><div style="clear:both" /></div>';
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
                jqcc.cometchat.setThemeVariable('showAvatar','1');
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    bltemp = buddylisttemp;
                    jqcc.cometchat.setThemeVariable('showAvatar','0');
                }
                if(document.getElementById('cometchat_userslist')){
                    if(bltemp!=''){
                        document.getElementById('cometchat_userslist').style.display = 'block';
                        jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                    }else{
                        $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                    }
                }
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    jqcc('#cometchat_userslist').find('.cometchat_blocked_overlay').remove();
                    jqcc('#cometchat_userslist').find('.cometchat_blocked').remove();
                }
                if(jqcc.cometchat.getSessionVariable('buddylist')==1){
                    $(".cometchat_userscontentavatar").find("img").each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                }
                jqcc[settings.theme].activeChatBoxes();
                $("#cometchat_user_search").keyup();
                $('div.cometchat_userlist').die('click');
                $('div.cometchat_userlist').live('click', function(e){
                    jqcc.cometchat.userClick(e.target);
                });
                $('#cometchat_userstab_text').html(language[9]+' ('+(onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'))+')');
                siteOnlineNumber = onlineNumber;
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'));
                if(totalFriendsNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber')>settings.searchDisplayNumber){
                    $('#cometchat_user_searchbar').css('display', 'block');
                }else{
                    $('#cometchat_user_searchbar').css('display', 'none');
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
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for (var key in chatBoxesOrder)
                {
                    if(chatBoxesOrder.hasOwnProperty(key) && parseInt(chatBoxesOrder[key])!=0)
                    {
                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                            jqcc[settings.theme].addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                        }
                    }
                }
            },
            loggedOut: function(){
                document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                cometchat_lefttab = $('#cometchat_lefttab').detach();
                cometchat_righttab = $('#cometchat_righttab').detach();
                if(settings.ccauth.enabled=="1" && !mobileDevice){
                    var ccauthpopup = '<div class="cc_overlay" onclick=""></div><div id="cometchat_social_login"><div class="login_container"><div class="login_image_container"><p>'+language[93]+'</p>';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\')" src="'+baseUrl+'themes/mobile/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                    $('#cometchat').html(ccauthpopup);
                }else if(settings.ccauth.enabled=="1" && mobileDevice){
                    var mobileOverlay = '<div class="cometchat_mobile_overlay"><p>'+language[94]+'<a target = "_blank" href = "'+jqcc.cometchat.getBaseUrl()+'cometchat_popout.php?cookiePrefix='+cookiePrefix+'&basedata='+jqcc.cometchat.getBaseData()+'&ccmobileauth='+jqcc.cometchat.getThemeVariable('ccmobileauth')+'" style="color:#1C97D0">Click Here</a> to access mobile compatible chat.</p></div>';
                    $('#cometchat').html(mobileOverlay);
                }else{
                    $('#cometchat').html('<div id="cometchat_loggedout_container"><div id="cometchat_loggedout"><div><img class="cometchat_loggedout_icon" src="'+baseUrl+'themes/'+settings.theme+'/images/exclamation.png" /></div><div>'+language[8]+'</div></div></div>');
                }
            },
            userStatus: function(item){
                var usercontentstatus = item.s;
                var icon = '';
                var count = 140-item.m.length;
                if(usercontentstatus=='invisible'){
                    usercontentstatus = 'offline';
                }
                if(item.d==1){
                    usercontentstatus = 'mobile cometchat_mobile_'+usercontentstatus;
                    icon = '<div class="cometchat_dot"></div>';
                }
                var userDetails = '<div id="cometchat_self"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src="'+item.a+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div id="cometchat_selfDetails"><div class="cometchat_userdisplayname">'+item.n+'</div><div class="cometchat_userdisplaystatus">'+item.m+'</div></div></div>';
                var cometchat_optionsbutton_popup = $('#cometchat_optionsbutton_popup');
                cometchat_optionsbutton_popup.find('textarea.cometchat_statustextarea').val(item.m);
                cometchat_optionsbutton_popup.find('.cometchat_statusmessagecount').html(count);
                if(item.lastseensetting==1){
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
                    cometchat_optionsbutton_popup.find("div.cometchat_tabsubtitle").html(language[45]);
                }
                jqcc.cometchat.setThemeVariable('userid', item.id);
                if(item.s != 'away'){
                    jqcc.cometchat.setThemeVariable('currentStatus', item.s);
                }
                jqcc.cometchat.setThemeArray('buddylistStatus', item.id, item.s);
                jqcc.cometchat.setThemeArray('buddylistMessage', item.id, item.m);
                jqcc.cometchat.setThemeArray('buddylistName', item.id, item.n);
                jqcc.cometchat.setThemeArray('buddylistAvatar', item.id, item.a);
                jqcc.cometchat.setThemeArray('buddylistLink', item.id, item.l);
                jqcc.cometchat.setThemeArray('buddylistChannelHash', item.id, item.ch);
                jqcc.cometchat.setThemeArray('buddylistLastseen', item.id, item.ls);
                jqcc.cometchat.setThemeArray('buddylistLastseensetting', item.id, item.lastseensetting);
                $('#cometchat_self_left').html(userDetails);
                $('#cometchat_welcome_username').text(item.n);
            },
            typingTo: function(item){
                if(typeof item['fromid'] != 'undefined'){

                    var id = item['fromid'];

                    $("#cometchat_typing_"+id).css('display', 'inline-block');
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

                    }, 5000);
                }

            },
            typingStop: function(item){
               $("#cometchat_typing_"+item['fromid']).css('display', 'none');
               $("#cometchat_buddylist_typing_"+item['fromid']).css('display', 'none');

            },
            sentMessageNotify: function(item){
                var size = 0, key;
                for (key in item) {
                    if(typeof item[key] == 'object'){
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
            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){

            if(settings.enableType!=1 && embeddedchatroomid==0){
                jqcc[settings.theme].hideMenuPopup();
                if(hasChatroom == 1 && jqcc.cometchat.getThemeVariable('trayOpen')!='chatrooms'){
                    $('#currentroom').hide();
                    jqcc.cometchat.setChatroomVars('currentroom',0);
                }
                var cometchat_user_popup = $("#cometchat_user_"+id+"_popup");

                if(typeof(cometchat_user_popup)=='undefined' || cometchat_user_popup.length<1){
                    shortname = name;
                    longname = name;
                    var usercontentstatus = status;
                    var icon = '';
                    if(jqcc.cometchat.getThemeArray('buddylistIsDevice', id) == '1'){
                        usercontentstatus = 'mobile cometchat_mobile_'+status;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    var hasFlash = false;
                    try {
                        hasFlash = Boolean(new ActiveXObject('ShockwaveFlash.ShockwaveFlash'));
                    } catch(exception) {
                        hasFlash = ('undefined' != typeof navigator.mimeTypes['application/x-shockwave-flash']);
                    }
                    if(hasFlash == false && mobileDevice != null){
                        var index = settings.plugins.indexOf('games');
                        if(settings.plugins.indexOf('games') != -1){
                            settings.plugins.splice(index, 1);
                        }
                    }
                    if(mobileDevice != null){
                        var index = settings.plugins.indexOf('screenshare');
                        if(settings.plugins.indexOf('screenshare') != -1){
                            settings.plugins.splice(index, 1);
                        }
                    }
                    var pluginshtml = '';
                    var avchathtml = '';
                    var smilieshtml = '';
                    var filetransferhtml = '';
                    if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                        var pluginslength = settings.plugins.length;
                        if(pluginslength>0){
                            for(var i = 0; i<pluginslength; i++){
                                var name = 'cc'+settings.plugins[i];
                                if(settings.plugins[i]=='avchat'){
                                    avchathtml='<div class="cometchat_menuOption cometchat_avchatOption"><img class="ccplugins  cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+settings.theme+'/images/avchaticon.png" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0" /></div>';
                                }else if(settings.plugins[i]=='smilies'){
                                    smilieshtml='<div class="ccplugins cometchat_smilies" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0" ><img src="'+baseUrl+'/images/smiley.png" class="cometchat_smiley"/></div>';
                                }else if(settings.plugins[i]=='filetransfer'){
                                    filetransferhtml='<img src="'+baseUrl+'themes/'+settings.theme+'/images/attachment.png" class="ccplugins cometchat_transfericon cometchat_filetransfer" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"/>';
                                }else if(typeof ($[name])=='object'){
                                    if(pluginshtml == ""){
                                        pluginshtml = '<div class="cometchat_menuOption cometchat_pluginsOption" title="'+language[95]+'"><img class="cometchat_pluginsIcon cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+settings.theme+'/images/pluginsicon.png"/><div class="cometchat_plugins menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="plugin_container">';
                                    }
                                    if(name!='ccchattime'){
                                        pluginshtml += '<div class="ccplugins cometchat_pluginsicon cometchat_'+settings.plugins[i]+'" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"><span>'+$[name].getTitle()+'</span></div>';
                                    }
                                }
                            }
                            pluginshtml += '</div></div></div>';
                        }
                    }

                    var startlink = '';
                    var endlink = '';
                    if(link!=''){
                        if(jqcc.cometchat.getCcvariable().callbackfn=='desktop'){
                            startlink='';
                            endlink
                        }else{
                            startlink = '<a href="'+link+'" target="_blank">';
                            endlink = '</a>';
                        }
                    }
                    var prepend = '';
                    var jabber = jqcc.cometchat.getThemeArray('isJabber', id);

                    if(jqcc.cometchat.getThemeVariable('prependLimit') != '0' && jabber != 1){
                        prepend = '<div class=\"cometchat_prependMessages\" onclick\="jqcc.synergy.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div>';
                    }
                    var avatarsrc = '';
                    var overlay_div = '';
                    if(status=="blocked"){
                        overlay_div = '<div class="cometchat_blocked_overlay"></div>';
                    }

                    if(avatar!=''){
                        avatarsrc = '<div class="cometchat_userscontentavatar">'+startlink+overlay_div+'<img src="'+avatar+'" class="cometchat_userscontentavatarimage" />'+endlink+'<span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></div>';
                    }

                    selectlang = '<select id="selectlanguage_'+id+'" class="selectlanguage"><option value="no">None</option></select>'
                    $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_userchatbox").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_userchatarea"><div class="cometchat_tabsubtitle"><div class="cometchat_chatboxLeftDetails">'+avatarsrc+'<div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname" title="'+longname+'">'+startlink+longname+endlink+'<span id="cometchat_typing_'+id+'" class="cometchat_typing"></span></div><div class="cometchat_userdisplaystatus" title="'+message+'">'+message+'</div></div></div><div class="cometchat_user_closebox" title="Close Chat Box">&#x2715;</div><div class="cometchat_chatboxMenuOptions">'+avchathtml+pluginshtml+'</div></div>'+prepend+'<div class="cometchat_tabcontent"><div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'" onscroll="jqcc.'+settings.theme+'.chatScroll(\''+id+'\');"><div class="cometchat_message_container"></div></div></div></div>'+selectlang+'<div class="cometchat_tabinputcontainer">'+filetransferhtml+'<div class="cometchat_tabcontentsubmit cometchat_sendicon" title="Send"></div><div class="cometchat_tabcontentinput">'+smilieshtml+'<div style="margin-right:28px;"><textarea class="cometchat_textarea" id="cometchat_textarea_'+id+'"></textarea><div style="clear:both"></div></div></div></div>').appendTo($("#cometchat_righttab"));


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

                    cometchat_user_popup = $("#cometchat_user_"+id+"_popup");
                    if(jqcc().slimScroll && mobileDevice == null){
                        cometchat_user_popup.find(".cometchat_tabcontenttext").slimScroll({height: 'auto',width: 'auto'});
                        cometchat_user_popup.find("#plugin_container").slimScroll({width: 'auto'});
                    }
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
                    $('.cometchat_textarea').click(function() {
                       var winWidth = $(window).innerWidth();
                       var winHt = $(window).innerHeight();
                       if((winWidth > winHt)&&(mobileDevice != null)){
                            $("html, body").scrollTop($(document).height());
                        }
                        if(mobileDevice != null){
                            if($('#cometchat_container_smilies').length == 1){
                                jqcc[settings.theme].closeModule('smilies');
                            }
                            if($('#cometchat_container_stickers').length == 1){
                                jqcc[settings.theme].closeModule('stickers');
                            }
                            $('#cometchat_user_'+id+'_popup').find('.cometchat_userchatarea').css('display','block');
                            setTimeout(function(){
                                $('#cometchat_tabcontenttext_'+id).css('height',$(window).height()-(jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
                            }, 10);
                        }
                    });

                    $('.cometchat_tabcontent').click(function() {
                        if($('#cometchat_container_stickers').length == 1 && mobileDevice != null){
                            jqcc[settings.theme].closeModule('stickers');
                        } else if($('#cometchat_container_smilies').length == 1 && mobileDevice != null){
                            jqcc[settings.theme].closeModule('smilies');
                        }
                        $('#cometchat_user_'+id+'_popup').find('.cometchat_userchatarea').css('display','block');
                        $('#cometchat_tabcontenttext_'+id).css('height',$(window).height()-(jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
                    });
                    cometchat_user_popup.find("div.cometchat_tabcontentsubmit").click(function(event){
                        jqcc[settings.theme].chatboxKeydown(event, cometchat_user_popup.find("textarea.cometchat_textarea"), id, 1);
                        jqcc[settings.theme].chatboxKeyup(event, cometchat_user_popup.find("textarea.cometchat_textarea"), id);
                    });
                    cometchat_user_popup.find("textarea.cometchat_textarea").keyup(function(event){
                        return jqcc[settings.theme].chatboxKeyup(event, this, id);
                    });
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    cometchat_user_popup.find('.ccplugins').click(function(event){
                        event.stopImmediatePropagation();
                        jqcc[settings.theme].hideMenuPopup();
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        var winHt = $(window).innerHeight();
                        var winWidth = $(window).innerWidth();
                        if(!mobileDevice){
                            if(window.top == window.self || name == 'ccclearconversation' || name == 'ccsave'){
                                var controlparameters = {"to":to, "chatroommode":chatroommode};
                                jqcc[name].init(controlparameters);
                            }
                            else {
                                var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "caller":"cometchat_synergy_iframe"}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                            }
                        }
                        else{
                            if((window.top == window.self && (name != 'ccstickers' && name != 'ccsmilies'))|| name == 'ccclearconversation' || name == 'ccsave'){
                                var controlparameters = {"to":to, "chatroommode":chatroommode};
                                jqcc[name].init(controlparameters);
                            } else if(name=='ccstickers' && mobileDevice){
                                if($('#cometchat_container_smilies').length == 1){
                                    jqcc[settings.theme].closeModule('smilies');
                                }
                                var controlparameters = {"to":to, "chatroommode":chatroommode};
                                jqcc[name].init(controlparameters);
                                $('#cometchat_container_stickers').css('bottom',0);
                                $('.cometchat_container_title').css('display','none');
                                $('#cometchat_container_stickers .cometchat_container_body').css('border','0px');
                                jqcc[settings.theme].stickersKeyboard(winWidth,winHt,id);
                            } else if(name=='ccsmilies' && mobileDevice){
                                if($('#cometchat_container_stickers').length == 1){
                                    jqcc[settings.theme].closeModule('stickers');
                                }
                                if($('#cometchat_container_smilies').length == 0){
                                    var controlparameters = {"to":to, "chatroommode":chatroommode};
                                    jqcc[name].init(controlparameters);
                                    $('#cometchat_container_smilies').css('bottom',0);
                                    $('.cometchat_container_title').css('display','none');
                                    $('#cometchat_container_smilies .cometchat_container_body').css('border','0px');
                                    jqcc[settings.theme].smiliesKeyboard(winWidth,winHt,id);
                                } else{
                                    jqcc[settings.theme].closeModule('smilies');
                                    $('#cometchat_user_'+id+'_popup').find('.cometchat_userchatarea').css('display','block');
                                    setTimeout(function(){
                                        $('#cometchat_tabcontenttext_'+id).css('height',$(window).height()-(jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
                                    }, 10);
                                }
                            } else {
                                var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode, "caller":"cometchat_synergy_iframe"}};
                                controlparameters = JSON.stringify(controlparameters);
                                parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                            }
                        }
                    });

                    cometchat_user_popup.find('div.cometchat_user_closebox').mouseenter(function(){
                        $(this).addClass("cometchat_user_closebox_hover");
                    });
                    cometchat_user_popup.find('div.cometchat_user_closebox').mouseleave(function(){
                        $(this).removeClass("cometchat_user_closebox_hover");
                    });
                    cometchat_user_popup.find('div.cometchat_user_closebox').click(function(){
                        var chatboxid = cometchat_user_popup.attr('id').split('_')[2];
                        $('#cometchat_userlist_'+chatboxid).show();
                        cometchat_user_popup.remove();
                        if($('#cometchat_container_smilies').length == 1){
                            jqcc[settings.theme].closeModule('smilies');
                        }
                        if($('#cometchat_container_stickers').length == 1){
                            jqcc[settings.theme].closeModule('stickers');
                        }
                        jqcc.cometchat.unsetThemeArray('chatBoxesOrder', chromeReorderFix+id);
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        if(jqcc.isEmptyObject(chatBoxesOrder)&&$.cookie(settings.cookiePrefix+'crstate')!=null&&$.cookie(settings.cookiePrefix+'crstate')!='' && hasChatroom== 1){
                            var chatroomData = cc_chatroom.active;
							if(Object.keys(chatroomData).length > 0) {
								var activeChatroom = cc_chatroom.open;
	                            jqcc.cometchat.setChatroomVars('activeChatroom', activeChatroom);
	                            for(var data in chatroomData) {
		                            var chatroomId = data.replace('_','');
		                            if(chatroomData[data].o == "1") {
		                            	jqcc.cometchat.getChatroomDetails(chatroomId,1);
		                            }
		                        }
							}
		                }
                        var nextChatBox;
                        for(chatBoxId in chatBoxesOrder){
                            nextChatBox = chatBoxId.replace('_','');
                        }
                        chatboxOpened[id] = null;
                        $("#cometchat_user_"+nextChatBox+"_popup").addClass('cometchat_tabopen');
                        jqcc[settings.theme].addPopup(nextChatBox,0,0);
                        jqcc.cometchat.setThemeVariable('openChatboxId', [nextChatBox+'']);
                        if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                           if(typeof $("#cometchat_user_"+nextChatBox+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id') != 'undefined'){
                                var messageid = $("#cometchat_user_"+nextChatBox+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id').split('_')[2];
                            }
                            var message = {"id": messageid, "from": nextChatBox, "self": 0};
                            if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[nextChatBox] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[nextChatBox]==0 && jqcc.cometchat.getExternalVariable('messagereceiptsetting') == 0){
                                    jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                            }
                        }
                        jqcc.cometchat.setSessionVariable('openChatboxId', nextChatBox);

                        if(settings.extensions.indexOf('ads') > -1){
                            jqcc.ccads.init();
                        }
                        $('.cometchat_noactivity').css('display','block');
                        jqcc[settings.theme].activeChatBoxes();
                        jqcc.cometchat.orderChatboxes();
                    });
                    cometchat_user_popup.find('.cometchat_pluginsOption').click(function(){
                        var winHt = $(window).innerHeight();
                        var winWidth = $(window).innerWidth();
                        var tabsubtitleHt = $(".cometchat_userchatarea").find('.cometchat_tabsubtitle').outerHeight(true);
                        if((winWidth > winHt) && mobileDevice){
                            cometchat_user_popup.find('#plugin_container').css('max-height',(winHt-tabsubtitleHt-5));
                        } else{
                            cometchat_user_popup.find('#plugin_container').css('max-height','');
                        }
                        $(this).find('.cometchat_menuOptionIcon').toggleClass('cometchat_menuOptionIconClick');
                        $('.cometchat_plugins').toggleClass('cometchat_tabopen');
                    });
                    jqcc[settings.theme].scrollDown(id);
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        cometchat_user_popup.find("textarea.cometchat_textarea").focus();
                    }
                    if(jqcc.cometchat.getExternalVariable('initialize')!=1||isNaN(id)){
                        jqcc[settings.theme].updateChatbox(id);
                    }
                    cometchat_user_popup.css('left', 0);
                    $('.cometchat_noactivity').css('display','block');
                }
                if(jqcc.cometchat.getThemeVariable('openChatboxId')==id&&jqcc.cometchat.getThemeVariable('trayOpen')!='chatrooms'){
                    jqcc.cometchat.unsetThemeArray('chatBoxesOrder', chromeReorderFix+id);
                    $('#cometchat_user_'+jqcc.cometchat.getSessionVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                    jqcc.cometchat.setSessionVariable('openChatboxId', id);
                    cometchat_user_popup.addClass('cometchat_tabopen');
                    $('.cometchat_noactivity').css('display','none');
                }
                if(settings.extensions.indexOf('ads') > -1){
                    jqcc.ccads.init();
                }

                jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
                jqcc[settings.theme].activeChatBoxes();
                jqcc.cometchat.setThemeArray('trying', id, 5);
                jqcc[settings.theme].scrollDown(id);
                if($('#cometchat_container_smilies').length != 1) {
                    jqcc[settings.theme].windowResize();
                }
                jqcc[settings.theme].updateReadMessages(id);
            }
            },
            activeChatBoxes: function(){
                $('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                var openChatboxId = jqcc.cometchat.getThemeVariable('openChatboxId');
                var oneononeflag = '0';
                var cometchat_activechatboxes = '';
                for(chatBoxId in chatBoxesOrder){
                    chatBoxId = chatBoxId.replace('_','');
                    oneononeflag = '1';
                    var userstatus = jqcc.cometchat.getThemeArray('buddylistStatus', chatBoxId);
                    var usercontentstatus = userstatus;
                    var icon = '';
                    if(jqcc.cometchat.getThemeArray('buddylistIsDevice', chatBoxId)==1){
                        mobilestatus = 'mobile';
                        usercontentstatus = 'mobile cometchat_mobile_'+userstatus;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    var overlay_div = '';
                    if(userstatus=="blocked"){
                        overlay_div = '<div class="cometchat_blocked_overlay"></div>';
                    }
                    if(jqcc.cometchat.getThemeVariable('showAvatar')==0){
                        cometchat_activechatboxes = '<div id="cometchat_activech_'+chatBoxId+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src="themes/'+settings.theme+'/images/cometchat_'+userstatus+'.png"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+jqcc.cometchat.getThemeArray('buddylistName', chatBoxId)+'</div><div class="cometchat_userdisplaystatus">'+jqcc.cometchat.getThemeArray('buddylistMessage', chatBoxId)+'</div></div></div>'+cometchat_activechatboxes;
                    }else{
                        if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', chatBoxId)) != 'undefined') {
                            cometchat_activechatboxes = '<div id="cometchat_activech_'+chatBoxId+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar">'+overlay_div+'<img class="cometchat_userscontentavatarimage" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', chatBoxId)+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+jqcc.cometchat.getThemeArray('buddylistName', chatBoxId)+'</div><div class="cometchat_userdisplaystatus">'+jqcc.cometchat.getThemeArray('buddylistMessage', chatBoxId)+'</div></div></div>'+cometchat_activechatboxes;
                        }
                    }
                }
                if(oneononeflag=='1'){
                    cometchat_activechatboxes = '<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft">'+language[86]+'<hr style="height:3px;" class="hrright"></div>'+cometchat_activechatboxes;
                    if($('#cometchat_allusers').length<1){
                        $('#cometchat_userslist').prepend('<div class="cometchat_subsubtitle" style="font-weight:bold;" id="cometchat_allusers"><hr style="height:3px;" class="hrleft">'+language[87]+'<hr style="height:3px;" class="hrright"></div>');
                    }
                }else{
                    $('#cometchat_allusers').remove();
                }
                $('#cometchat_activechatboxes_popup').html(cometchat_activechatboxes);

                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for (var key in chatBoxesOrder)
                {
                    if(chatBoxesOrder.hasOwnProperty(key) && parseInt(chatBoxesOrder[key])!=0)
                    {
                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                            jqcc[settings.theme].addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                        }
                    }
                }
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
                    if(typeof(incoming.broadcast) == "undefined" || incoming.broadcast != 0){
                        if(incoming.self ==1 ){
                             incoming.nopopup = 1;
                        }
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

                    checkfirstmessage = ($("#cometchat_tabcontenttext_"+incoming.from+" .cometchat_chatboxmessage").length) ? 0 : 1;
                    var shouldPop = 0;
                    if($('#cometchat_user_'+incoming.from).length == 0){
                            shouldPop = 1;
                    }
                    if(message.indexOf('CC^CONTROL_PLUGIN_AUDIOCHAT_ENDCALL')!=-1 || message.indexOf('CC^CONTROL_PLUGIN_AVCHAT_ENDCALL')!=-1){
                        message ='This call has been ended';
                    }
                    if(jqcc.cometchat.getThemeArray('trying', incoming.from)==undefined){
                        if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && incoming.nopopup == 0){
                            jqcc[settings.theme].createChatbox(incoming.from, jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistStatus', incoming.from), jqcc.cometchat.getThemeArray('buddylistMessage', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), jqcc.cometchat.getThemeArray('buddylistLink', incoming.from), jqcc.cometchat.getThemeArray('buddylistIsDevice', incoming.from), 1, 1);
                        }
                    }
                    if(jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==null||jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==''){
                        if(jqcc.cometchat.getThemeArray('trying', incoming.from)<5){
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'){
                                    jqcc[settings.theme].addMessages([{"from": incoming.from, "message": message, "self": incoming.self, "old": incoming.old, "id": incoming.id, "sent": incoming.sent}]);
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
                        if($("#cometchat_message_"+incoming.id).length>0){
                            $("#cometchat_message_"+incoming.id).find(".cometchat_chatboxmessagecontent").html(message);
                        }else{
                            sentdata = '';
                            if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage'+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagecontent">'+message+'</span><span id="cometchat_chatboxseen_'+incoming.id+'"></span></div></div>', selfstyle);

                            $("#cometchat_user_"+incoming.from+"_popup").find("div.cometchat_tabcontenttext").find('.cometchat_message_container').append(msg);
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
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=incoming.from&&incoming.old!=1&& ((typeof(alreadyreceivedunreadmessages[incoming.from])!='undefined'&& alreadyreceivedunreadmessages[incoming.from]<incoming.id) || typeof(alreadyreceivedunreadmessages[incoming.from])=='undefined')){
                            jqcc[settings.theme].addPopup(incoming.from, 1, 1);
                        }
                        jqcc[settings.theme].updateReceivedUnreadMessages(incoming.from,incoming.id);
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
                                    window.top.focus();
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
            statusSendMessage: function(){
                var message = $("#cometchat_optionsbutton_popup").find("textarea.cometchat_statustextarea").val();
                var oldMessage = jqcc.cometchat.getThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'));
                if(message!=''&&oldMessage!=message){
                    $('div.cometchat_statusbutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'), message);
                    jqcc.cometchat.statusSendMessageSet(message);
                }else{
                    $('div.cometchat_statusbutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    }, 1500);
                }
            },
            statusSendMessageSuccess: function(){
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    $('#cometchat_selfDetails .cometchat_userdisplaystatus').text($('.cometchat_statustextarea').val());
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
                    $('div.cometchat_guestnamebutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">').css('padding','3px 6px 2px 6px');
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
                var guestname = $("#cometchat_optionsbutton_popup").find("input.cometchat_guestnametextbox").val();
                $('#cometchat_selfDetails .cometchat_userdisplayname').text("<?php echo $guestnamePrefix;?>-"+guestname);
                $('#cometchat_welcome_username').text("<?php echo $guestnamePrefix;?>-"+guestname);
                $(guestnametextarea).blur();
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[49]; ?>').css('padding','6px');
                    var guestname = $("#cometchat_optionsbutton_popup").find("input.cometchat_guestnametextbox").val();
                    $('#cometchat_selfDetails .cometchat_userdisplayname').text("<?php echo $guestnamePrefix;?>-"+guestname);
                    $('#cometchat_welcome_username').text("<?php echo $guestnamePrefix;?>-"+guestname);
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                }, 2500);
            },
            setGuestNameError: function(){
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[50]; ?>').css('padding','1px 6px');
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
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_available');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_busy');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_invisible');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_offline');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_away');
            },
            updateStatus: function(status){
                $("#cometchat_self .cometchat_userscontentdot").addClass('cometchat_'+status);
                $('span.cometchat_optionsstatus.'+status).css('text-decoration', 'underline');
                var userid = jqcc.cometchat.getUserID();
                jqcc.cometchat.getUserDetails(userid);
                $('#cometchat_selfDetails .cometchat_userdisplaystatus').text(jqcc.cometchat.getThemeArray('buddylistMessage', userid));
            },
            goOffline: function(silent){
                jqcc.cometchat.setThemeVariable('offline', 1);
                if(silent!=1){
                    jqcc.cometchat.sendStatus('offline');
                }else{
                    jqcc[settings.theme].updateStatus('offline');
                }
                if(hasChatroom== 1){
                    jqcc[settings.theme].chatroomOffline();
                }
                $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab').removeClass('cometchat_tabclick');
                $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for(chatBoxId in chatBoxesOrder){
                    $("#cometchat_user"+chatBoxId+"_popup").remove();
                    jqcc.cometchat.unsetThemeArray('chatBoxesOrder',chatBoxId);
                }
                $('#currentroom').find('div.cometchat_user_closebox').click();
                jqcc.cometchat.orderChatboxes();
                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                $('.cometchat_offline_overlay').css('display','table');
                if(typeof window.cometuncall_function=='function'){
                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                }
                $('.cometchat_noactivity').css('display','none');
                if(typeof jqcc.cometchat.setChatroomVars=='function'){
                    jqcc.cometchat.setChatroomVars('newMessages',0);
                }
                jqcc.synergy.activeChatBoxes();
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
                    $("#cometchat_typing_"+id).css('display', 'none');
                    jqcc[settings.theme].scrollDown(id);
                    chatboxOpened[id] = 1;
                    if(atleastOneNewMessage==1){
                        var nowTime = new Date();
                        var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                        if(idleDifference>5){
                            document.title = language[15];
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
                if(jqcc.cometchat.getThemeVariable('loggedout')==0 && embeddedchatroomid == 0){
                    var cc_state = $.cookie(settings.cookiePrefix+'state');
                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                    if(cc_state!=null){
                        var cc_states = cc_state.split(/:/);
                        if(jqcc.cometchat.getThemeVariable('offline')==0){
                            $('.cometchat_offline_overlay').css('display','none');
                            var value = 0;
                            if(cc_states[0]!=' '&&cc_states[0]!=''){
                                value = cc_states[0];
                            }
                            if((value==0&&$('#cometchat_userstab').hasClass("cometchat_tabclick"))||(value==1&&!($('#cometchat_userstab').hasClass("cometchat_tabclick")))){
                                $('#cometchat_userstab').click();
                            }else if(hasChatroom==1&&((value==1&&$('#cometchat_chatroomstab').hasClass("cometchat_tabclick"))||(value==0&&!($('#cometchat_chatroomstab').hasClass("cometchat_tabclick"))))) {
                                $('#cometchat_chatroomstab').click();
                            }
                            if(chatroomsonly == 1 && !($('#cometchat_chatroomstab_popup').hasClass("cometchat_tabopen"))){
                                $('#cometchat_chatroomstab_popup').addClass('cometchat_tabopen');
                            }
                            var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                            if(typeof($.cookie(settings.cookiePrefix+'crstate'))!=='undefined' && hasChatroom==1 && $.cookie(settings.cookiePrefix+'crstate')!=null && $.cookie(settings.cookiePrefix+'crstate')!=''){
                                var chatroomData = cc_chatroom.active;
                                if(Object.keys(chatroomData).length > 0){
                                    if(cc_states[5]=='chatrooms'&&jqcc.cometchat.getThemeVariable('trayOpen')!=cc_states[5]){
                                        jqcc.cometchat.setThemeVariable('trayOpen',cc_states[5]);
                                        var activeChatroom = cc_chatroom.open;
                                        jqcc.cometchat.setChatroomVars('activeChatroom', activeChatroom);
                                        for(var data in chatroomData) {
                                            var chatroomId = data.replace('_','');
                                            if(chatroomData[data].o == "1") {
                                                var chatroomDetails = jqcc.cometchat.getChatroomDetails(chatroomId);
                                                if(chatroomDetails != ''){
                                                    chatroomDetails = JSON.parse(chatroomDetails);
                                                    jqcc.cometchat.silentroom(chatroomDetails.id, chatroomDetails.password, urlencode(chatroomDetails.name));
                                                }
                                                jqcc.cometchat.setThemeVariable('chatroomOpen',chatroomDetails.id);
                                            }
                                        }
                                    }
                                }
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
                                        newActiveChatboxes[chromeReorderFix+chatboxIds[0]] = chatboxIds[1];
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
                                        oldActiveChatboxes[chromeReorderFix+chatboxIds[0]] = chatboxIds[1];
                                    }
                                }
                                for(r in newActiveChatboxes){
                                    if(newActiveChatboxes.hasOwnProperty(r)){
                                        var id = r.replace('_','');
                                        if($('#cometchat_user_'+id+'_popup').length<1){
                                            jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), 0, null);
                                        }
                                        jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id,parseInt(newActiveChatboxes[r]));
                                        if(parseInt(newActiveChatboxes[r])>0){
                                            jqcc.cometchat.setThemeVariable('newMessages', 1);
                                        }
                                    }
                                }
                                for(y in oldActiveChatboxes){
                                    if(oldActiveChatboxes.hasOwnProperty(y)){
                                        if(newActiveChatboxes[y]==null){
                                            y = y.replace('_','');
                                            $("#cometchat_user_"+y+"_popup").find("div.cometchat_user_closebox").click();
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
                            if(value!=jqcc.cometchat.getSessionVariable('openChatboxId')&&cc_states[5]!='chatrooms'){
                                if(value!=''){
                                    jqcc.cometchat.tryClickSync(value);
                                }
                            }
                            if(cc_states[4]==1){
                                jqcc[settings.theme].goOffline(1);
                            }
                        }else{
                            $('.cometchat_offline_overlay').css('display','table');
                        }
                    }else if(jqcc.cometchat.getThemeVariable('offline')!=1){
                        $('#cometchat_userstab').click();
                    }

                    var chatroom_activeFlag = 0;
                    if(typeof($.cookie(settings.cookiePrefix+'crstate'))!=='undefined' && hasChatroom==1 && $.cookie(settings.cookiePrefix+'crstate')!=null && $.cookie(settings.cookiePrefix+'crstate')!=''){
                        var cc_chatroom = JSON.parse($.cookie(settings.cookiePrefix+'crstate'));
                        var chatroomData = cc_chatroom.active;
                        if(Object.keys(chatroomData).length > 0){
                            chatroom_activeFlag = 1;
                        }
                    }

                    if(jqcc.cometchat.getSessionVariable('activeChatboxes') == '' && (chatroom_activeFlag == 1) && (jqcc.cometchat.getThemeVariable('offline') != '1')){
                        $('.cometchat_noactivity').css('display','block');
                    }
                    if(hasChatroom!=1){
                        $('#cometchat_userstab_popup').css('display','block');
                    }
                    jqcc.cometchat.setInternalVariable('updatingsession', '0');
                    clearTimeout(resynchTimer);
                    resynchTimer = setTimeout(function(){
                        jqcc[settings.theme].resynch();
                    }, 5000);
                }
            },
            setModuleAlert: function(id, number){
            },
            addPopup: function(id, amount, add){
                if(typeof(id)=='string')
                    id = id.replace( /^\D+/g, '');
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
                    var cometchat_user_id = $("#cometchat_userlist_"+id);
                    var cometchat_activech = $("#cometchat_activech_"+id);
                    if($("#cometchat_activech_"+id).length<1){
                        jqcc[settings.theme].activeChatBoxes();
                        cometchat_activech = $("#cometchat_activech_"+id);
                    }
                    var cometchat_msgcount = cometchat_user_id.find('.cometchat_msgcount');
                    var cometchat_msgcount_a = cometchat_activech.find('.cometchat_msgcount');
                    if(cometchat_msgcount_a.length > 0 && add==1){
                        amount = parseInt(cometchat_msgcount_a.find(".cometchat_msgcounttext").text())+parseInt(amount);
                    }

                    if(amount==0 && add==0){
                        cometchat_msgcount.remove();
                        cometchat_msgcount_a.remove();
                    }else{
                        if(cometchat_msgcount.length>0){
                            cometchat_msgcount.find(".cometchat_msgcounttext").text(amount);
                        }else{
                            cometchat_user_id.prepend("<span class='cometchat_msgcount'><div class='cometchat_msgcounttext'>"+amount+"</div></span>");
                            cometchat_msgcount.find(".cometchat_msgcounttext").text(amount);
                        }
                        if(cometchat_msgcount_a.length>0){
                            cometchat_msgcount_a.find(".cometchat_msgcounttext").text(amount);
                        }else{
                            cometchat_activech.prepend("<span class='cometchat_msgcount'><div class='cometchat_msgcounttext'>"+amount+"</div></span>");
                            cometchat_msgcount_a.find(".cometchat_msgcounttext").text(amount);
                        }
                    }
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id, amount);
                    jqcc.cometchat.orderChatboxes();
                }
                if($("#cometchat_chatroomstab.cometchat_tabclick").length>0){
                    var newOneonOneMessages = 0;
                    jqcc('#cometchat_activechatboxes_popup .cometchat_msgcount').each(function(){
                        newOneonOneMessages += parseInt(jqcc(this).children('.cometchat_msgcounttext').text());
                    });
                    if(newOneonOneMessages>0){
                        $('#cometchat_userstab_text').text('<?php echo $language[88]?> ('+newOneonOneMessages+')');
                    }
                }
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
            },
            getTimeDisplay: function(ts, id){
                ts = parseInt(ts);
                if((ts+"").length == 10){
                    ts = ts*1000;
                }
                var time = getTimeDisplay(ts);
                var timeDataStart = "<span class=\"cometchat_ts\">"+time.hour+":"+time.minute+time.ap;
                var timeDataEnd = "</span>";
                if(ts<jqcc.cometchat.getThemeVariable('todays12am')){
                    return timeDataStart+" "+time.date+time.type+" "+time.month+timeDataEnd;
                }else{
                    return timeDataStart+timeDataEnd;
                }
            },
            createChatbox: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
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
            },
            tooltip: function(id, message, orientation){
                var cometchat_tooltip = $('#cometchat_tooltip');
                $('#cometchat_tooltip').find(".cometchat_tooltip_content").html(message);
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
                            $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).click();
                        }else{
                            jqcc.cometchat.setSessionVariable('openChatboxId', '');
                        }
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));

                    }
                    jqcc[settings.theme].checkPopups();
                });
            },
            chatTab: function(){
                var cometchat_user_search = $("#cometchat_user_search");
                var cometchat_userscontent = $('#cometchat_userscontent');
                cometchat_user_search.click(function(){
                    var searchString = $(this).val();
                    if(searchString==language[18]){
                        cometchat_user_search.val('');
                        cometchat_user_search.addClass('cometchat_search_light');
                    }
                });
                cometchat_user_search.blur(function(){
                    var searchString = $(this).val();
                    if(searchString==''){
                        cometchat_user_search.addClass('cometchat_search_light');
                        cometchat_user_search.val(language[18]);
                    }
                });
                cometchat_user_search.keyup(function(){
                    var searchString = $(this).val();
                    if(searchString.length>0&&searchString!=language[18]){
                        cometchat_userscontent.find('div.cometchat_userlist').hide();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        cometchat_userscontent.find('#cometchat_activechatboxes_popup').hide();
                        var searchcount = cometchat_userscontent.find('div.cometchat_userdisplayname:icontains('+searchString+')').length + cometchat_userscontent.find('span.cometchat_userscontentname:icontains('+searchString+')').length;
                        if(searchcount >= 1 ){
                            cometchat_userscontent.find('div.cometchat_userlist').hide();
                            $('div.cometchat_userdisplayname:icontains('+searchString+')').parents('div.cometchat_userlist').show();
                            $('span.cometchat_userscontentname:icontains('+searchString+')').parents('div.cometchat_userlist').show();
                            $(document).find('#cometchat_userscontent').find('.cc_nousers').remove();
                        } else {
                            if($(document).find('.cc_nousers').length == 0){
                                $(document).find('#cometchat_userscontent').append('<div class="cc_nousers" style= "padding-top:6px;padding-left:6px;">'+language[58]+'</div>');
                            }
                        }
                        cometchat_user_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
                        cometchat_userscontent.find('#cometchat_activechatboxes_popup').show();
                        cometchat_userscontent.find('.cc_nousers').hide();
                    }
                });
                var cometchat_userstab = $('#cometchat_userstab');
                var cometchat_chatroomstab = $('#cometchat_chatroomstab');
                cometchat_userstab.click(function(){
                    jqcc[settings.theme].hideMenuPopup();
                    $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                    if(typeof(newmesscr)!="undefined"){
                        clearInterval(newmesscr);
                    }
                    newmesscr = setInterval(function(){
                        if($("#cometchat_chatroomstab.cometchat_tabclick").length<1){
                            if(hasChatroom == 1){
                                var newCrMessages = jqcc.cometchat.getChatroomVars('newMessages');
                                if(newCrMessages>0){
                                    $('#cometchat_chatroomstab_text').text(language[88]+' ('+newCrMessages+')');
                                }
                                setTimeout(function(){
                                        jqcc.crsynergy.updateChatroomsTabtext();
                                },2000);
                            }
                        }else{
                            if(typeof(newmesscr)!='undefined'){
                                clearInterval(newmesscr);
                            }
                        }
                    },4000);
                    jqcc.cometchat.setSessionVariable('buddylist', '1');
                    $("#cometchat_tooltip").css('display', 'none');
                    $(".cometchat_userscontentavatar").find('img').each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                    $(this).addClass("cometchat_tabclick");
                    cometchat_chatroomstab.removeClass("cometchat_tabclick");
                    $('#cometchat_chatroomstab_popup').removeClass("cometchat_tabopen");
                    $('#cometchat_userstab_popup').addClass("cometchat_tabopen");
                    jqcc[settings.theme].windowResize();
                });
                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomTab();
                }
            },
            optionsButton: function(){
                var cometchat_optionsbutton_popup = $("#cometchat_optionsbutton_popup");
                cometchat_optionsbutton_popup.find('.cometchat_optionstyle_container').click(function(e){
                    e.stopPropagation();
                });
                cometchat_optionsbutton_popup.find("span.cometchat_gooffline").click(function(){
                    jqcc[settings.theme].goOffline();
                });
                $("#cometchat_soundnotifications").click(function(event){
                    event.stopPropagation();
                    var notification = 'false';
                    if($("#cometchat_soundnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"sound", notification, {path: '/', expires: 365});
                });
                $("#cometchat_popupnotifications").click(function(event){
                    event.stopPropagation();
                    var notification = 'false';
                    if($("#cometchat_popupnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"popup", notification, {path: '/', expires: 365});
                });
                $("#cometchat_lastseen").click(function(event){
                    event.stopPropagation();
                    lastseenflag = false;
                    var lastseen = jqcc.cometchat.getThemeArray('buddylistLastseen', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    var dt=eval(lastseen*1000);
                    var myDate = new Date(dt);
                    var year = myDate.getFullYear();
                    var day = myDate.getDate();
                    var month = myDate.getMonth()+1;
                    var h = myDate.getHours();
                    var m = myDate.getMinutes();

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
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup .cometchat_userdisplayname').css('padding','6px 0px 2px 0px');
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
                    event.stopPropagation();
                    messagereceiptflag = 0;
                    jqcc.cometchat.setExternalVariable('messagereceiptsetting', messagereceiptflag);
                    if($("#cometchat_messagereceipt").is(":checked")){
                        messagereceiptflag = 1;
                    }
                    jqcc.cometchat.setExternalVariable('messagereceiptsetting', messagereceiptflag);

                    $.cookie(settings.cookiePrefix+"disablemessagereceipt", messagereceiptflag, {path: '/', expires: 365});
                });
                cometchat_optionsbutton_popup.find("span.available").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='available'){
                        jqcc.cometchat.sendStatus('available');
                    }
                });
                cometchat_optionsbutton_popup.find("span.busy").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='busy'){
                        jqcc.cometchat.sendStatus('busy');
                    }
                });
                cometchat_optionsbutton_popup.find("span.invisible").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                        jqcc.cometchat.sendStatus('invisible');
                    }
                });
                cometchat_optionsbutton_popup.find("div.cometchat_statusbutton").click(function(){
                    jqcc[settings.theme].statusSendMessage();
                });
                $("#guestsname").find("div.cometchat_guestnamebutton").click(function(){
                    jqcc[settings.theme].setGuestName();
                });
                cometchat_optionsbutton_popup.find("textarea.cometchat_statustextarea").keydown(function(event){
                    return jqcc.cometchat.statusKeydown(event, this);
                });
                cometchat_optionsbutton_popup.find("input.cometchat_guestnametextbox").keydown(function(event){
                    return jqcc.cometchat.guestnameKeydown(event, this);
                });
                $('#cometchat_optionsbutton').mouseover(function(){
                    if(!cometchat_optionsbutton_popup.hasClass("cometchat_tabopen")){
                        $(this).addClass("cometchat_tabmouseover");
                    }
                });
                $('#cometchat_optionsbutton').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat_optionsbutton').click(function(){
                    $("#cometchat_tooltip").css('display', 'none');
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
                    if(settings.showSettingsTab==1){
                        $('#cometchat_optionsbutton_popup').toggleClass('cometchat_tabopen');
                    }
                });
                var auth_logout = $("div#cometchat_authlogout");
                logout_click();
                function logout_click(){
                    auth_logout.click(function(event){
                        auth_logout.unbind('click');
                        event.stopPropagation();
                        auth_logout.css('background','url('+baseUrl+'themes/'+settings.theme+'/images/loading.gif) no-repeat 0px 6px');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                             success: function(data){
                                if(data.logoutURL){
                                    if(data.logoutURL=='twitter'){
                                        window.parent.postMessage('twitterlogout', '*');
                                    }else{
                                        window.location.href=data.logoutURL;
                                    }
                                }
                                else{
                                    auth_logout.css('background','url('+baseUrl+'themes/'+settings.theme+'/images/logout.png) no-repeat 0px 8px');
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
                                    clearTimeout(jqcc.cometchat.getChatroomVars('heartbeatTimer'));
                                    if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                                        $.cookie(settings.cookiePrefix+"guest", null, {path: '/'});
                                        $.cookie(settings.cookiePrefix+"data", null, {path: '/'});
                                        var controlparameters = {"type":"extensions", "name":"desktop", "method":"logout", "params":{"chatroommode":"0"}};
                                        controlparameters = JSON.stringify(controlparameters);
                                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                                        jqcc.ccdesktop.logout();
                                    }
                                }
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
                if(event.keyCode==8&&$(chatboxtextarea).val()==''){
                    $(chatboxtextarea).css('height', '25px');
                    if(!iOSmobileDevice){
                        jqcc[settings.theme].windowResize();
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
                    jqcc[settings.theme].windowResize();
                }
                if($('#cometchat_container_smilies').length == 1 && mobileDevice){
                    jqcc[settings.theme].closeModule('smilies');
                    $('#cometchat_user_'+id+'_popup').find('.cometchat_userchatarea').css('display','block');
                    setTimeout(function(){
                        $('#cometchat_tabcontenttext_'+id).css('height',$(window).height()-(jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
                    }, 10);
                    $('.cometchat_textarea').blur();
                }
            },
            chatboxKeydown: function(event, chatboxtextarea, id, force){
                var condition = 1;
                if((event.keyCode==13&&event.shiftKey==0)||force==1){
                    var message = $(chatboxtextarea).val();
                    message = message.replace(/^\s+|\s+$/g, "");
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height', '25px');
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
                            var messageLength = message.length;
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
            scrollDown: function(id){
                if(jqcc().slimScroll && mobileDevice == null){
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
                $("#cometchat_tabcontenttext_"+id).find('.cometchat_message_container').html('');
                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'&&data.hasOwnProperty('messages')){
                    jqcc[settings.theme].addMessages(data['messages']);
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                       if(typeof $("#cometchat_user_"+id+"_popup").find("div.cometchat_chatboxmessage:last-child").attr('id') != 'undefined'){
                            var messageid = $("#cometchat_user_"+id+"_popup").find("div.cometchat_chatboxmessage:last-child").attr('id').split('_')[2];
                        }
                        var message = {"id": messageid, "from": id, "self": 0};
                        if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[id] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[id]==0){
                                jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                    }
                }
                jqcc[settings.theme].scrollDown(id);
            },
            windowResize: function(silent){
                var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],winWidth=w.innerWidth||e.clientWidth||g.clientWidth,winHt=w.innerHeight||e.clientHeight||g.clientHeight;
                var searchbar_Height = $('#cometchat_user_searchbar').is(':visible') ? $('#cometchat_user_searchbar').outerHeight(true) : 0;
                var jabber_Height = $('#jabber_login').is(':visible') ? $('#jabber_login').outerHeight(true) : 0;
                var usercontentHeight = winHt-$('#cometchat_self_container').outerHeight(true)-$('#cometchat_tabcontainer').outerHeight(true)-$('#cometchat_trayicons').outerHeight(true)-searchbar_Height-jabber_Height+'px';
                var useSlimscroll = jqcc().slimScroll && mobileDevice == null;
                var landscapeMobile = (winWidth > winHt)&&(mobileDevice != null);
                var tabsubtitleHt = $(".cometchat_userchatarea").find('.cometchat_tabsubtitle').outerHeight(true);
                if(landscapeMobile){
                    $("html, body").scrollTop($(document).height());
                }
                if(useSlimscroll){
                    $('#cometchat_userscontent').parent('.slimScrollDiv').css('height',usercontentHeight);
                }
                $('#cometchat_userscontent').css('height',usercontentHeight);
                var openChatboxId = jqcc.cometchat.getThemeVariable('openChatboxId');
                var openChatbox;
                if(typeof jqcc('#cometchat_user_'+openChatboxId+'_popup').css('z-index') != 'undefined' && jqcc('#cometchat_user_'+openChatboxId+'_popup').css('z-index') > 0){
                    openChatbox = $("#cometchat_user_"+openChatboxId+"_popup");
                } else{
                    openChatbox = $("#currentroom");
                }
                if(landscapeMobile && openChatbox.find('#plugin_container').is(":visible")){
                     openChatbox.find('#plugin_container').css('max-height',(winHt-tabsubtitleHt-5));
                } else{
                    openChatbox.find('#plugin_container').css('max-height','');
                }
                if(landscapeMobile){
                     $("html, body").scrollTop($(document).height());
                }
                var chatboxHeight = winHt-openChatbox.find('.cometchat_ad').outerHeight(true)-openChatbox.find('.cometchat_tabsubtitle').outerHeight(true)-openChatbox.find('.cometchat_prependMessages').outerHeight(true)-openChatbox.find(".cometchat_tabinputcontainer").outerHeight(true);
                if(useSlimscroll){
                    $(".cometchat_userchatbox").find(".cometchat_tabcontent").find("div.slimScrollDiv").css('height', chatboxHeight+'px');
                }
                $(".cometchat_userchatbox").find("div.cometchat_tabcontenttext").css('height',chatboxHeight+'px');
                if(iOSmobileDevice){
                    $('#cometchat_user_'+openChatboxId+'_popup').find('.cometchat_userchatarea').css('display','block');
                    $('#cometchat_tabcontenttext_'+openChatboxId).css('height',$(window).height()-(jqcc('#cometchat_user_'+openChatboxId+'_popup').find('.cometchat_tabsubtitle').outerHeight()+jqcc('#cometchat_user_'+openChatboxId+'_popup').find('.cometchat_tabinputcontainer').outerHeight(true)+jqcc('#cometchat_user_'+openChatboxId+'_popup').find('.cometchat_prependMessages').outerHeight(true)));
                }
                if($('#cometchat_container_stickers').length == 1 && mobileDevice != null){
                    jqcc[settings.theme].stickersKeyboard(winWidth,winHt,openChatboxId);
                    jqcc[settings.theme].keyboardResize('stickers',winHt,openChatbox);
                } else if($('#cometchat_container_smilies').length == 1 && mobileDevice != null){
                    jqcc[settings.theme].smiliesKeyboard(winWidth,winHt,openChatboxId);
                    jqcc[settings.theme].keyboardResize('smilies',winHt,openChatbox);
                }
                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomWindowResize();
                }
                if(document.activeElement.tagName == "INPUT" && mobileDevice){
                    window.setTimeout(function(){
                     document.activeElement.scrollIntoViewIfNeeded();
                 },0);
                }
            },
            chatWith: function(id){
                jqcc('#cometchat_userlist_'+id+" .cometchat_msgcount").remove();
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
                if(jqcc.cometchat.getThemeVariable('loggedout')==0 && jqcc.cometchat.getUserID() != id){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                    }
                    jqcc.cometchat.setThemeVariable('trayOpen','');
                    jqcc.cometchat.setThemeVariable('chatroomOpen','');
                    jqcc.cometchat.setThemeVariable('openChatboxId', [id+'']);
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                       if(typeof $("#cometchat_user_"+id+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id') != 'undefined'){
                            var messageid = $("#cometchat_user_"+id+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id').split('_')[2];
                        }
                        var message = {"id": messageid, "from": id, "self": 0};
                        if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[id] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[id]==0){
                                jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                    }
                    if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                        jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                    }
                }
            },
            scrollFix: function(){
                var elements = ['cometchat_tabcontainer', 'cometchat_userstab_popup', 'cometchat_optionsbutton_popup', 'cometchat_tooltip', 'cometchat_hidden'];
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    elements.push('cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup');
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

            },
            launchModule: function(id){
                if($('#cometchat_container_'+id).length == 0){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            toggleModule: function(id){
                if($('#cometchat_container_'+id).length == 0){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            closeModule: function(id){
                if(jqcc(document).find('#cometchat_closebox_'+id).length > 0){
                    jqcc(document).find('#cometchat_closebox_'+id)[0].click();
                }
            },
            closeAllModule: function(){
                if(settings.showModules==1){
                    trayicon = jqcc.cometchat.getTrayicon();
                    for(x in trayicon){
                        if(x!='home' && x!='scrolltotop' && x!='chatrooms'){
                            if(jqcc('#cometchat_container_'+x).length > 0){
                                jqcc('#cometchat_container_'+x).detach();
                            }
                        }
                    }
                }
            },
            joinChatroom: function(roomid, inviteid, roomname){
                jqcc.cometchat.chatroom(roomid,roomname,0,inviteid,1,1);
            },
            closeTooltip: function(){
                $("#cometchat_tooltip").css('display', 'none');
            },
            scrollToTop: function(){
                $("html,body").animate({scrollTop: 0}, {"duration": "slow"});
            },
            reinitialize: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==1){
                    $('#cometchat').html(cometchat_lefttab);
                    $('#cometchat').append(cometchat_righttab);
                    jqcc[settings.theme].windowResize();
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                }
            },
            updateHtml: function(id, temp){
                if($("#cometchat_user_"+id+"_popup").length>0){
                    $("#cometchat_user_"+id+"_popup").find("#cometchat_tabcontenttext_"+id).find('.cometchat_message_container').html('<div>'+temp+'</div>');
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
                if(jqcc.cometchat.getThemeVariable('offline')==0){
                    $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                }
                if(jqcc.cometchat.getThemeVariable('jabberOnlineNumber')>settings.searchDisplayNumber){
                    $('#cometchat_user_searchbar').css('display', 'block');
                }
            },
            userClick: function(listing){
                var id = $(listing).attr('id');
                if(typeof id==="undefined"||$(listing).attr('id')==''){
                    id = $(listing).parents('div.cometchat_userlist').attr('id');
                }
                id = id.substr(19);
                jqcc.cometchat.setThemeVariable('trayOpen','');
                jqcc.cometchat.setThemeVariable('chatroomOpen','');
                jqcc.cometchat.setThemeVariable('openChatboxId', [id+'']);
                if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                   if(typeof $("#cometchat_user_"+id+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id') != 'undefined'){
                        var messageid = $("#cometchat_user_"+id+"_popup .cometchat_message_container").find('.cometchat_chatboxmessage ').last().attr('id').split('_')[2];
                    }
                    var message = {"id": messageid, "from": id, "self": 0};
                    if(typeof jqcc.cometchat.getCcvariable().lastmessagereadstatus[id] != "undefined" && jqcc.cometchat.getCcvariable().lastmessagereadstatus[id]==0){
                            jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                    }
                }
                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                }
                $("#cometchat_userlist_"+id).find(".cometchat_msgcount").remove();
                $("#cometchat_activech_"+id).find(".cometchat_msgcount").remove();
                $(listing).find(".cometchat_msgcount").remove();
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
                jqcc[settings.theme].hideMenuPopup();
            },
            hideMenuPopup: function(){
                $('#cometchat_plugins').removeClass('cometchat_tabopen');
                $('.cometchat_pluginsOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('#cometchat_moderator_opt').removeClass('cometchat_tabopen');
                $('.cometchat_chatroomModOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('#chatroomusers_popup').removeClass('cometchat_tabopen');
                $('.cometchat_chatroomUsersOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('.menuOptionPopup.cometchat_tabpopup.cometchat_tabopen').removeClass('cometchat_tabopen');
            },
            messageBeep: function(baseUrl){
                $('<audio id="messageBeep" style="display:none;"><source src="'+baseUrl+'sounds/beep.mp3" type="audio/mpeg"><source src="'+baseUrl+'sounds/beep.ogg" type="audio/ogg"><source src="'+baseUrl+'sounds/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
            },
            ccClicked: function(id){
                $(id).click();
            },
            ccAddClass: function(id, classadded){
                $(id).addClass(classadded);
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
                jqcc[settings.theme].hideMenuPopup();
                if(hasChatroom == 1){
                    jqcc[settings.theme].minimizeChatrooms();
                }
                jqcc('#cometchat_optionsbutton_popup.cometchat_tabopen').removeClass('cometchat_tabopen');
                jqcc('.cometchat_user_closebox,.cometchat_closebox').click();
            },
            iconNotFound: function(image, name){
                $('.'+name+'icon').attr({'src': baseUrl+'modules/'+name+'/icon.png', 'width': '16px'});
            },
            minimizeOpenChatbox: function(){
                jqcc('.cometchat_tabpopup.cometchat_tabopen[id!=cometchat_userstab_popup]').find('.cometchat_minimizebox').click()[0];
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
                            var fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            var fromavatar = '';
                            if(parseInt(incoming.self)==1){
                                fromname = language[10];
                                selfstyle = ' cometchat_self';
                            }else{
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
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_time cometchat_time_'+msg_date_class+' '+date_class+'" msg_format="'+msg_date_format+'">'+msg_date+'</div><div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage'+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagecontent">'+message+'</span><span id="cometchat_chatboxseen_'+incoming.id+'"></span></div></div>', selfstyle);
                            oldMessages+=msg;
                        });
                    }
                });

                jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_message_container').prepend(oldMessages);
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }else{
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.synergy.prependMessagesInit('+id+')');
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
                if(date == 1 || date == 21 || date == 31) { type = 'st'; }
                else if(date == 2 || date == 22) { type = 'nd'; }
                else if(date == 3 || date == 23) { type = 'rd'; }

                if(ts < todays12am) {
                    return hour+":"+minute+ap+' '+date+type+' '+months[month];
                } else {
                    return hour+":"+minute+ap;
                }
            },
            showLastseen:function(id,lastactivity){
                var lastseen = lastactivity;
                var timest = jqcc[settings.theme].getLastseenTime(lastseen);
                if($('#cometchat_user_'+id+'_popup').find('#cometchat_lastseen_'+id).length == 0){
                    $('#cometchat_user_'+id+'_popup .cometchat_userdisplayname').css('padding','0px 0px 2px 0px');
                    $('#cometchat_user_'+id+'_popup div.cometchat_userdisplaystatus').after('<div id="cometchat_lastseen_'+id+'" class="cometchat_lastseenmessage" title="'+language[109]+': '+timest+'"> Last seen at: '+timest+'</div>');
                }
            },
            hideLastseen:function(id){
                $('#cometchat_lastseen_'+id).remove();
                $('#cometchat_user_'+id+'_popup .cometchat_userdisplayname').css('padding','6px 0px 2px 0px');
            },
            formatlang: function(str) {
                return str[0].toUpperCase()+str.substr(1).toLowerCase();
            },
            onLoad: function() {
                var options = {
                    sourceLanguage: 'en',
                    destinationLanguage: ['ta'],
                    shortcutKey: 'ctrl+g',
                    transliterationEnabled: true
                };
                setTimeout(function(){
                    var control = new google.elements.transliteration.TransliterationControl(options);
                    var ids = ["cometchat_textarea" ];
                    control.makeTransliteratable(ids);
                },500);

                $("textarea#cometchat_textarea").keyup(function(event) {
                    return jqcc[settings.theme].chatboxKeydown1(event);
                });

            },
            pushcontents: function() {
                var data = document.getElementById('cometchat_textarea').value;
                document.getElementById('cometchat_textarea').value = '';
                var controlparameters = {"type":"plugins", "name":"cctransliterate", "method":"appendMessage", "params":{"to":jqcc.cometchat.getThemeVariable('openChatboxId'), "data":data, "chatroommode": "0", "caller": jqcc.cometchat.getThemeVariable('userid')}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(window.opener) == 'undefined' || window.opener == null){
                    parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                }else{
                    window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
                setTimeout('document.getElementById(\'cometchat_textarea\').focus()',100);
                setTimeout('document.getElementById(\'cometchat_textarea\').focus()',1000);
            },
            changeLanguage: function() {
                setCookie('{$cookiePrefix}language','',0);
                location.href = 'index.php?cc_theme={$cc_theme}&caller={$caller}&id={$toId}&embed={$embed}&basedata={$baseData}&chatroommode={$chatroommode}';
            },
            setCookie: function(cookie_name, cookie_value, cookie_life) {
                var today = new Date()
                var expiry = new Date(today.getTime() + cookie_life * 24*60*60*1000)
                var cookie_string =cookie_name + "=" + escape(cookie_value)
                if(cookie_life){ cookie_string += "; expires=" + expiry.toGMTString()}
                cookie_string += "; path=/"
                document.cookie = cookie_string
            },
            chatboxKeydown1: function(event) {
                if(event.keyCode == 13 && event.shiftKey == 0)  {
                    jqcc[settings.theme].pushcontents();
                }
            },
            stickersKeyboard: function(winWidth,winHt,id) {
                if(typeof id == 'undefined') {id = 0};
                var tabcontenttext_height = (winHt*30)/100; /*30% height is given so that it looks similar in all phones*/
                var openChatbox;
                if(typeof jqcc('#cometchat_user_'+id+'_popup').css('z-index') != 'undefined' && jqcc('#cometchat_user_'+id+'_popup').css('z-index') > 0){
                    openChatbox = $("#cometchat_user_"+id+"_popup");
                } else{
                    openChatbox = $("#currentroom");
                }
                $('#cometchat_tabcontenttext_'+id).css('height',tabcontenttext_height);
                openChatbox.find('.cometchat_userchatarea').css('display','block');
                if(winWidth > winHt){
                    openChatbox.find('.cometchat_userchatarea').css('display','none');
                }
                $('.cometchat_container').css({'left':'','top':''});
                $('#cometchat_container_stickers').css({'width':openChatbox.outerWidth(true),'height':(winHt-openChatbox.outerHeight(true))});
                $('#cometchat_trayicon_stickers_iframe').css({'width':openChatbox.outerWidth(true),'height':(winHt-openChatbox.outerHeight(true))});
                $('#cometchat_container_stickers').find('.cometchat_container_body').css("cssText","height:"+(winHt-openChatbox.outerHeight(true))+"px !important");
                $('#cometchat_container_stickers').find('.cometchat_container_body').css("border-bottom",0);
                document.getElementById("cometchat_trayicon_stickers_iframe").onload = function() {
                    jqcc[settings.theme].keyboardResize('stickers',winHt,openChatbox);
                };
            },
            smiliesKeyboard: function(winWidth,winHt,id) {
                if(typeof id == 'undefined') {id = 0};
                var tabcontenttext_height = (winHt*35)/100; /*35% height is given so that it looks similar in all phones*/
                var openChatbox;
                if(typeof jqcc('#cometchat_user_'+id+'_popup').css('z-index') != 'undefined' && jqcc('#cometchat_user_'+id+'_popup').css('z-index') > 0){
                    openChatbox = $("#cometchat_user_"+id+"_popup");
                    $('#cometchat_tabcontenttext_'+id).css('height',tabcontenttext_height);
                } else{
                    openChatbox = $("#currentroom");
                    tabcontenttext_height = tabcontenttext_height+42;
                    jqcc('#cometchat_righttab').find('#currentroom_convo').css('height',tabcontenttext_height);
                }
                openChatbox.find('.cometchat_userchatarea').css('display','block');
                if(winWidth > winHt){
                    openChatbox.find('.cometchat_userchatarea').css('display','none');
                }
                $('.cometchat_container').css({'left':'','top':''});
                $('#cometchat_container_smilies').css({'width':openChatbox.outerWidth(true),'height':(winHt-openChatbox.outerHeight(true))});
                $('#cometchat_trayicon_smilies_iframe').css({'width':openChatbox.outerWidth(true),'height':(winHt-openChatbox.outerHeight(true))});
                $('#cometchat_container_smilies').find('.cometchat_container_body').css("cssText","height:"+(winHt-openChatbox.outerHeight(true))+"px !important");
                $('#cometchat_container_smilies').find('.cometchat_container_body').css("border-bottom",0);
                document.getElementById("cometchat_trayicon_smilies_iframe").onload = function() {
                    jqcc[settings.theme].keyboardResize('smilies',winHt,openChatbox);
                };
            },
            keyboardResize: function(plugin,winHt,openChatbox){
                if(plugin == 'smilies'){
                    var tabscontainerheight = $('#cometchat_trayicon_smilies_iframe').contents().find('#tabs').outerHeight(true);
                } else if(plugin == 'stickers'){
                    var tabscontainerheight = $('#cometchat_trayicon_stickers_iframe').contents().find('#tabs_container').outerHeight(true);
                }
                $('#cometchat_trayicon_'+plugin+'_iframe').contents().find('.container_body').css('overflow','scroll');
                $('#cometchat_trayicon_'+plugin+'_iframe').contents().find('.container_body.embed').css("cssText","height:"+(winHt-openChatbox.outerHeight(true)-tabscontainerheight)+"px !important");
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

if(typeof(jqcc.synergy) === "undefined"){
    jqcc.synergy=function(){};
}

jqcc.extend(jqcc.synergy, jqcc.ccsynergy);
