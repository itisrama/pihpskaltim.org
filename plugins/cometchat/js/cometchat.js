/*/
 * CometChat
 * Copyright (c) 2016 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
 */
 <?php $callbackfn = ''; if (!empty($_GET['callbackfn'])) { $callbackfn = $_GET['callbackfn']; }?>

; if (!Object.keys) Object.keys = function(o) {if (o !== Object(o))throw new TypeError('Object.keys called on a non-object');var k=[],p;for (p in o) if(Object.prototype.hasOwnProperty.call(o,p)) k.push(p);return k;};

(function($){
    $.cometchat = $.cometchat||function(){
        var baseUrl = '<?php echo BASE_URL;?>';
        var ccvariable = {};
        var sendajax = true;
        var broadcastData = [];
        var sendbroadcastinterval = 0;
        var isTapatalk = '<?php echo defined("TAPATALK")?TAPATALK:0;?>';
        var transport = '<?php echo TRANSPORT;?>';
        var webrtcplugins = ['audiovideochat', 'audiochat', 'broadcast', 'screenshare'];
        <?php echo $settings; ?>
        <?php echo $mobileappdetails; ?>
        ccvariable.documentTitle = document.title;
        ccvariable.externalVars = {};
        ccvariable.sendVars = {};
        ccvariable.sessionVars = {};
        ccvariable.internalVars = {};
        ccvariable.openChatboxId = '';
        ccvariable.loggedout = 0;
        ccvariable.offline = 0;
        ccvariable.windowFocus = true;
        ccvariable.resynchronize = 0;
        ccvariable.heartbeatTimer;
        ccvariable.heartbeatTime = settings.minHeartbeat;
        ccvariable.heartbeatCount = 1;
        ccvariable.updateSessionVars = 0;
        ccvariable.timestamp = 0;
        ccvariable.lastOnlineNumber = 0;
        ccvariable.trayOpen = '';
        ccvariable.chatroomOpen = '';
        ccvariable.newMessages = 0;
        ccvariable.buddylistName = {};
        ccvariable.buddylistMessage = {};
        ccvariable.buddylistStatus = {};
        ccvariable.buddylistAvatar = {};
        ccvariable.buddylistLink = {};
        ccvariable.buddylistIsDevice = {};
        ccvariable.buddylistChannelHash = {};
        ccvariable.buddylistLastseen = {};
        ccvariable.buddylistLastseensetting = {};
        ccvariable.isJabber = {};
        ccvariable.jabberOnlineNumber = 0;
        ccvariable.chatBoxesOrder = {};
        ccvariable.trying = {};
        ccvariable.todaysDate = new Date();
        ccvariable.currentTime = ccvariable.idleTime = Math.floor(ccvariable.todaysDate.getTime()/1000);
        ccvariable.todays12am = ccvariable.currentTime -(ccvariable.currentTime%(24*60*60*1000));
        ccvariable.specialChars = /([^\x00-\x80]+)|([&][#])+/;
        ccvariable.idleFlag = 0;
        ccvariable.currentStatus;
        ccvariable.buddyListHash;
        ccvariable.callbackfn = '<?php echo $callbackfn;?>';
        ccvariable.baseData = (settings['ccauth']['enabled']==0)?$.cookie(settings.cookiePrefix+'data'):0;
        ccvariable.mobileDevice = navigator.userAgent.match(/ipad|ipod|iphone|android|windows ce|Windows Phone|IEMobile|blackberry|palm|symbian/i);
        ccvariable.initialized = 0;
        ccvariable.crossDomain = '<?php echo CROSS_DOMAIN;?>';
        ccvariable.dataMethod = 'post';
        ccvariable.dataTimeout = '10000';
        ccvariable.isMini = 0;
        ccvariable.desktopNotification = {};
        ccvariable.runHeartbeat = 1;
        ccvariable.userid = 0;
        ccvariable.ccmobileauth =0 ;
        ccvariable.cometid = '';
        ccvariable.prependLimit = (typeof(settings['prependLimit'])!=="undefined")?settings['prependLimit']:0;
        ccvariable.showAvatar = 1;
        ccvariable.userActive = 0;
        ccvariable.lastmessagereadstatus = {};
        ccvariable.windowFocus = true;
        ccvariable.timedifference = 0;
        var calleeAPI = settings.theme;
        ccvariable.baseData=encodeURIComponent(ccvariable.baseData);
        if(typeof (ccvariable.callbackfn)!='undefined'&&ccvariable.callbackfn!=''&&ccvariable.callbackfn!='desktop'){
            calleeAPI = ccvariable.callbackfn;
        }else if(ccvariable.mobileDevice&&settings.disableForMobileDevices&&calleeAPI!='synergy'){
            calleeAPI = ccvariable.callbackfn = 'ccmobiletab';
        }
        ccvariable.externalVars["callbackfn"] = ccvariable.callbackfn;
        ccvariable.externalVars["lastseensetting"] = 0;
        ccvariable.externalVars["messagereceiptsetting"] = 0;
        ccvariable.externalVars["activeChatboxIds"] = '';
        $.ajaxSetup({scriptCharset: "utf-8", cache: "false"});

        $(window).focus(function(){
            if(!ccvariable.windowFocus){
                jqcc.each( ccvariable.openChatboxId, function( key, value ) {
                    if(typeof ccvariable.lastmessagereadstatus[value] != 'undefined' && ccvariable.lastmessagereadstatus[value] == 0){
                        var messageid = ccvariable.lastmessagereadstatus[value];
                        var message = {"id": messageid, "from": value, "self": 0};
                        if(ccvariable.currentStatus != 'invisible' && isTapatalk == 0 && ccvariable.externalVars["messagereceiptsetting"] == 0){
                            jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                        ccvariable.lastmessagereadstatus[value] = 1;
                    }
                });
            }
            ccvariable.windowFocus = true;
            ccvariable.isMini = 0;
            if(settings.desktopNotifications==1){
                for(x in  ccvariable.desktopNotification){
                    for(y in  ccvariable.desktopNotification[x]){
                        ccvariable.desktopNotification[x][y].close();
                    }
                }
            }
            ccvariable.desktopNotification = {};
        });
        $(window).blur(function(){
            ccvariable.windowFocus = false;
            ccvariable.isMini = 1;
        });
        $(window).on('mouseenter', function() {
            if(!ccvariable.windowFocus){
                jqcc.each( ccvariable.openChatboxId, function( key, value ) {
                    if(typeof ccvariable.lastmessagereadstatus[value] != 'undefined' && ccvariable.lastmessagereadstatus[value] == 0){
                        var messageid = ccvariable.lastmessagereadstatus[value];
                        var message = {"id": messageid, "from": value, "self": 0};
                        if(ccvariable.currentStatus != 'invisible' && isTapatalk == 0 && ccvariable.externalVars["messagereceiptsetting"] == 0){
                            jqcc.cometchat.sendReceipt(message, 'readMessageNotify');
                        }
                        ccvariable.lastmessagereadstatus[value] = 1;
                    }
                });
            }
            ccvariable.windowFocus = true;
        });
        $(window).on('mouseleave', function() {
            ccvariable.windowFocus = false;
        });
        function userClickId(id){
            if(typeof (jqcc[calleeAPI].createChatbox)!=='undefined'){
                jqcc[calleeAPI].createChatbox(id, ccvariable.buddylistName[id], ccvariable.buddylistStatus[id], ccvariable.buddylistMessage[id], ccvariable.buddylistAvatar[id], ccvariable.buddylistLink[id], ccvariable.buddylistIsDevice[id]);
            }
        };
        function branded(){
            $("body").append('<div id="cc_power" style="display:none">Powered By <a href="http://www.cometchat.com">CometChat</a></div>');
            language[1] = 'Powered By <a href="http://www.cometchat.com">CometChat</a>';
        };
        function preinitialize(){
            if(typeof(cc_synergy_enabled)!="undefined" && cc_synergy_enabled == 1){
                return;
            }
            if(jqcc.cometchat.getUserAgent()[0]=="MSIE" && parseInt(jqcc.cometchat.getUserAgent()[1])<9){
                settings.windowFavicon=0;
            };
            if(ccvariable.callbackfn==''&&settings.hideBarCheck==1&&$.cookie(settings.cookiePrefix+"loggedin")!=1){
                $.ajax({
                    url: baseUrl+"cometchat_check.php",
                    data: {'init': '1', basedata: ccvariable.baseData},
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: setPreInitVars
                });
            }else{
                setPreInitVars(1);
            }
            function setPreInitVars(data){          /*child function of preinitialize*/
                if(data!='0'){
                    if(typeof(jqcc[calleeAPI]) == 'undefined'){
                        return;
                    }
                    $.cookie(settings.cookiePrefix+"loggedin", '1', {path: '/'});
                    if(typeof (jqcc[calleeAPI].initialize)!=='undefined'){
                        jqcc[calleeAPI].initialize();
                    }else if(ccvariable.callbackfn!=''&&typeof (jqcc[calleeAPI].init())=='function'){
                        jqcc[calleeAPI].init();
                    }
                    ccvariable.externalVars["buddylist"] = '1';
                    ccvariable.externalVars["initialize"] = '1';
                    ccvariable.externalVars["currenttime"] = ccvariable.currentTime;
                    if (ccvariable.runHeartbeat == 1) {
                      jqcc.cometchat.chatHeartbeat();
                    }
                }
            }
        };
        arguments.callee.getUserAgent = function(){
            var ua= navigator.userAgent, tem,
            M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
            if(/trident/i.test(M[1])){
                tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
                return 'IE '+(tem[1] || '');
            }
            if(M[1]=== 'Chrome'){
                tem= ua.match(/\bOPR\/(\d+)/);
                if(tem!= null) return 'Opera '+tem[1];
            }
            M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
            if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
            return M;
        };
        arguments.callee.startGuestChat = function(name){
            if(typeof(cc_synergy_enabled)!='undefined' && cc_synergy_enabled==1){
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"startGuestChat", "params":{'name':name}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }else{
                ccvariable.externalVars["guest_login"] = 1;
                ccvariable.externalVars["username"] = name;
                jqcc.cometchat.reinitialize();
            }
        };
        arguments.callee.chatHeartbeat = function(force){
            var newMessage = 0;
            if(force==1){
                if(typeof window.cometcall_function=='function'){
                    cometcall_function(ccvariable.cometid, 0, calleeAPI);
                }
            }
            ccvariable.externalVars["blh"] = ccvariable.buddyListHash;
            ccvariable.externalVars["status"] = "";
            if((ccvariable.callbackfn!=''&&ccvariable.callbackfn!='desktop')||calleeAPI=='ccmobiletab'){
                ccvariable.externalVars["status"] = 'available';
            }
            if(force==1){
                ccvariable.externalVars["f"] = 1;
            }else{
                delete ccvariable.externalVars["f"];
            }
            var atleastOneNewMessage = 0;
            var nowTime = new Date();
            var n = {};
            var idleDifference = Math.floor(nowTime.getTime()/1000)-ccvariable.idleTime;
            if(idleDifference>=settings.idleTimeout&&ccvariable.idleFlag==0){
                if(ccvariable.currentStatus=='available'){
                    ccvariable.idleFlag = 1;
                    ccvariable.externalVars["status"] = 'away';
                }
            }
            if(idleDifference<settings.idleTimeout&&ccvariable.idleFlag==1){
                if(ccvariable.currentStatus=='available'){
                    ccvariable.idleFlag = 0;
                    ccvariable.externalVars["status"] = ccvariable.currentStatus;
                }
            }
            if(ccvariable.crossDomain==1){
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"state"] = $.cookie(settings.cookiePrefix+'state');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"hidebar"] = $.cookie(settings.cookiePrefix+'hidebar');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"an"] = $.cookie(settings.cookiePrefix+'an');
                ccvariable.externalVars["cookie_"+settings.cookiePrefix+"loggedin"] = $.cookie(settings.cookiePrefix+'loggedin');
            }
            ccvariable.externalVars['currenttime'] = Math.floor(new Date().getTime()/1000);
            ccvariable.externalVars["basedata"] = ccvariable.baseData;
            ccvariable.externalVars["readmessages"] = jqcc.cometchat.getFromStorage('readmessages');
            ccvariable.externalVars["receivedunreadmessages"] = jqcc.cometchat.getFromStorage('receivedunreadmessages');
            if(settings.theme == "synergy" && settings.enableType == 1 && embeddedchatroomid > 0 && ccvariable.externalVars["initialize"] == 1){
                ccvariable.externalVars["buddylist"] = 0;
            }

            if ((settings.theme == "synergy" && settings.enableType == 1 && ccvariable.externalVars["initialize"] == 1) || (settings.theme == "synergy" && settings.enableType != 1 && embeddedchatroomid == 0) || settings.theme != "synergy") {
                $.ajax({
                    url: baseUrl+"cometchat_receive.php",
                    data: ccvariable.externalVars,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    error: function(){
                        if(!(jqcc(document).find("#cometchat").hasClass('CCReceiveError')) && ccvariable.externalVars["initialize"] == '1'){
                            jqcc(document).find("#cometchat").addClass('CCReceiveError');
                        }
                        clearTimeout(ccvariable.heartbeatTimer);
                        ccvariable.heartbeatTime = settings.minHeartbeat;
                        ccvariable.heartbeatTimer = setTimeout(function(){
                            jqcc.cometchat.chatHeartbeat();
                        }, ccvariable.heartbeatTime);
                    },
                    success: function(data){
                        if(jqcc(document).find("#cometchat").hasClass('CCReceiveError')){
                            jqcc(document).find("#cometchat").removeClass('CCReceiveError');
                        }
                        if(data){
                            jqcc.cometchat.setInternalVariable('allowchatboxpopup', '1');
                            $.each(data, function(type, item){
                                if(type=='blh'){
                                    ccvariable.buddyListHash = item;
                                }
                                if(type=='an'){
                                    if(typeof (jqcc[calleeAPI].newAnnouncement)!=='undefined'){
                                        jqcc[calleeAPI].newAnnouncement(item);
                                    }
                                }
                                if(type=='buddylist'){
                                    if(typeof (jqcc[calleeAPI].buddyList)=='function'){
                                        jqcc[calleeAPI].buddyList(item);
                                    }
                                }
                                if(type=='loggedout'){
                                    jqcc.cometchat.updateToStorage('receivedunreadmessages',{});
                                    jqcc.cometchat.updateToStorage('readmessages',{});
                                    if(typeof(ccvariable.cometid)!='' && typeof(cometuncall_function)==="function"){
                                        cometuncall_function(ccvariable.cometid);
                                        jqcc.cometchat.setThemeVariable('cometid','');
                                    }
                                    $.cookie(settings.cookiePrefix+"loggedin", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"state", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"jabber", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"jabber_type", null, {path: '/'});
                                    $.cookie(settings.cookiePrefix+"hidebar", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"lang", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"theme", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"color", null, {path: '/'});
                                    if(typeof (jqcc[calleeAPI].loggedOut)!=='undefined'){
                                        jqcc[calleeAPI].loggedOut();
                                    }
                                    jqcc.cometchat.setThemeVariable('loggedout', 1);
                                    clearTimeout(ccvariable.heartbeatTimer);
                                }
                                if(type=='userstatus'){
                                    ccvariable.userid = item.id;
                                    ccvariable.buddylistStatus[item.id] = item.s;
                                    ccvariable.buddylistMessage[item.id] = item.m;
                                    ccvariable.buddylistName[item.id] = item.n;
                                    ccvariable.buddylistAvatar[item.id] = item.a;
                                    ccvariable.buddylistLink[item.id] = item.l;
                                    ccvariable.buddylistChannelHash[item.id] = item.ch;
                                    ccvariable.buddylistLastseen[item.id] = item.ls;
                                    ccvariable.ccmobileauth = item.ccmobileauth;
                                    if(typeof (jqcc[calleeAPI].userStatus)!=='undefined'){
                                        jqcc[calleeAPI].userStatus(item);
                                    }
                                    if(settings.messageBeep==1&&ccvariable.callbackfn==""){
                                        if(typeof (jqcc[calleeAPI].messageBeep)!='undefined'){
                                            jqcc[calleeAPI].messageBeep(baseUrl);
                                        }
                                    }
                                    if(ccvariable.callbackfn!=""&&ccvariable.callbackfn=="desktop"){
                                        var ccpluginindex=(settings.plugins).indexOf('screenshare');
                                        (settings.plugins).splice(ccpluginindex,1);
                                    }
                                    if(parseInt(ccvariable.userid) && typeof jqcc.cometchat.subscribeToStorage !== 'undefined'){
                                        jqcc.cometchat.subscribeToStorage('cometchat_user_'+ccvariable.userid);
                                    }
                                }
                                if(type=='cometid'){
                                    ccvariable.cometid = item.id;
                                    cometcall_function(ccvariable.cometid, 0, calleeAPI);
                                }
                                if(type=='init'){
                                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                                }
                                if(type=='initialize'){
                                    ccvariable.timestamp = item;
                                    ccvariable.externalVars["timestamp"] = item;
                                    if(typeof (jqcc[calleeAPI].resynch)!=='undefined'){
                                        jqcc[calleeAPI].resynch();
                                    }
                                    if(typeof (jqcc[calleeAPI].windowResize)!=='undefined'){
                                        jqcc[calleeAPI].windowResize();
                                    }
                                }
                                if(type=='st'){
                                    ccvariable.timedifference = (item*1000) - parseInt(new Date().getTime());
                                }
                                if(type=='messages'){
                                    if(ccvariable.externalVars['initialize'] != 1){
                                        ccvariable.externalVars["timestamp"] = item[Object.keys(item).sort().reverse()[0]].id;
                                    }
                                    if(typeof (jqcc.cometchat.publishToStorage) !== 'undefined'){
                                        jqcc.cometchat.publishToStorage('cometchat_user_'+ccvariable.userid,item);
                                    }
                                    ccvariable.heartbeatCount = 1;
                                    ccvariable.heartbeatTime = settings.minHeartbeat;
                                }
                            });
                            if(ccvariable.externalVars["status"]!=""){
                                if(typeof (jqcc[calleeAPI].removeUnderline2)!=='undefined'){
                                    jqcc[calleeAPI].removeUnderline2();
                                }
                                if(typeof (jqcc[calleeAPI].updateStatus)!=='undefined'){
                                    jqcc[calleeAPI].updateStatus(ccvariable.externalVars["status"]);
                                }
                            }
                            jqcc.cometchat.setExternalVariable('initialize', '0');
                            jqcc.cometchat.setExternalVariable('currenttime', '0');
                            if(ccvariable.loggedout!=1&&ccvariable.offline!=1){
                                ccvariable.heartbeatCount++;
                                if(ccvariable.heartbeatCount>4){
                                    ccvariable.heartbeatTime *= 2;
                                    ccvariable.heartbeatCount = 1;
                                }
                                if(ccvariable.heartbeatTime>settings.maxHeartbeat){
                                    ccvariable.heartbeatTime = settings.maxHeartbeat;
                                }
                                clearTimeout(ccvariable.heartbeatTimer);
                                ccvariable.heartbeatTimer = setTimeout(function(){
                                    jqcc.cometchat.chatHeartbeat();
                                }, ccvariable.heartbeatTime);
                            }
                        }
                    }
                });
            }
        };
        arguments.callee.updateToStorage = function(key,value){
            if(Object.keys(value).length === 0){
                jqcc.jStorage.set(key,{});
            }else{
                jqcc.jStorage.set(key,
                    jqcc.extend(true,
                        {},
                        jqcc.jStorage.get(key,{}),
                        value
                    )
                );
            }
        }
        arguments.callee.getFromStorage = function(key){
            return jqcc.jStorage.get(key,{});
        }
        arguments.callee.publishToStorage = function(key,value){
            if(Object.keys(value).length === 0){
                jqcc.jStorage.publish(key,{});
            }else{
                jqcc.jStorage.publish(key, value);
            }
        }
        arguments.callee.subscribeToStorage = function(key){
            jqcc.jStorage.subscribe(key, function(channel, payload){
                if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                    jqcc[calleeAPI].addMessages(payload);
                }
            });
        }
        arguments.callee.setExternalVariable = function(name, value){
            ccvariable.externalVars[name] = value;
        };
        arguments.callee.setInternalVariable = function(name, value){
            ccvariable.internalVars[name] = value;
        };
        arguments.callee.getSessionVariable = function(name){
            if(ccvariable.sessionVars[name]){
                return ccvariable.sessionVars[name];
            }else{
                return '';
            }
        };
        arguments.callee.incrementThemeVariable = function(name){
            ccvariable[name]++;
        };
        arguments.callee.setThemeVariable = function(name, value){
            ccvariable[name] = value;
        };
        arguments.callee.setThemeArray = function(name, id, value){
            if(typeof(ccvariable[name])==="undefined"){
                ccvariable[name]={};
            }
            ccvariable[name][id] = value;
        };
        arguments.callee.unsetThemeArray = function(name, id){
            if(typeof(ccvariable[name])!=="undefined"){
                delete ccvariable[name][id];
            }else{
                return false;
            }
        };
        arguments.callee.getThemeArray = function(name, id){
            if(typeof(ccvariable[name])!=="undefined"){
                return ccvariable[name][id];
            }else{
                return false;
            }
        };
        arguments.callee.getThemeVariable = function(name){
            return ccvariable[name];
        };
        arguments.callee.userClick = function(listing){
            if(typeof (jqcc[calleeAPI].userClick)!=='undefined'){
                jqcc[calleeAPI].userClick(listing);
            }
        };
        arguments.callee.orderChatboxes = function(){
            var activeids = '';
            var activeChatboxId = '';
            var selfNewMessages = 0;
            for(chatbox in ccvariable.chatBoxesOrder){
                if(ccvariable.chatBoxesOrder.hasOwnProperty(chatbox)){
                    if(ccvariable.chatBoxesOrder[chatbox]!=null){
                        if(!Number(ccvariable.chatBoxesOrder[chatbox])){
                            ccvariable.chatBoxesOrder[chatbox] = 0;
                        }
                        activeids += chatbox.replace('_','')+'|'+ccvariable.chatBoxesOrder[chatbox]+',';
                        activeChatboxId += chatbox.replace('_','')+',';
                        if(ccvariable.chatBoxesOrder[chatbox]>0){
                            selfNewMessages = 1;
                        }
                    }
                }
            }
            ccvariable.newMessages = selfNewMessages;
            activeids = activeids.slice(0, -1);
            activeChatboxId = activeChatboxId.slice(0, -1);
            ccvariable.externalVars["activeChatboxIds"] = activeChatboxId;
            jqcc.cometchat.setSessionVariable('activeChatboxes', activeids);
        };
        arguments.callee.getInternalVariable = function(name){
            if(ccvariable.internalVars[name]){
                return ccvariable.internalVars[name];
            }else{
                return '';
            }
        };
        arguments.callee.getExternalVariable = function(name){
            if(ccvariable.externalVars[name]){
                return ccvariable.externalVars[name];
            }else{
                return '';
            }
        };
        arguments.callee.setSessionVariable = function(name, value){
            ccvariable.sessionVars[name] = value;
            if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                var cc_state = '';
                if(ccvariable.sessionVars['buddylist']){
                    cc_state += ccvariable.sessionVars['buddylist'];
                }else{
                    cc_state += ' ';
                }
                cc_state += ':';
                if(ccvariable.sessionVars['activeChatboxes']){
                    cc_state += ccvariable.sessionVars['activeChatboxes'];
                }else{
                    cc_state += ' ';
                }
                cc_state += ':';
                if(ccvariable.sessionVars['openChatboxId']){
                    cc_state += ccvariable.sessionVars['openChatboxId'];
                }else{
                    cc_state += ' ';
                }
                cc_state += ':'+ccvariable.lastOnlineNumber;
                cc_state += ':'+ccvariable.offline;
                cc_state += ':'+ccvariable.trayOpen;
                $.cookie(settings.cookiePrefix+'state', cc_state, {path: '/'});
            }
        };
        var windowHeights = {};
        arguments.callee.c5 = function(){
            branded();
            preinitialize();
            return;
        };
        arguments.callee.c6 = function(){
            preinitialize();
            return;
        };
        arguments.callee.getBaseData = function(){
            return ccvariable.baseData;
        };
        arguments.callee.getActiveId = function(){
            return ccvariable.openChatboxId;
        };
        arguments.callee.getUserID = function(){
           return ccvariable.userid;
        };
        arguments.callee.getUser = function(id, callbackfn){
          if (typeof (jqcc.cometchat.getUserFromUID) == 'function') {
              jqcc.cometchat.getUserFromUID(id, callbackfn);
          } else {
              $.ajax({
                    url: baseUrl+"cometchat_getid.php",
                    data: {userid: id, basedata: ccvariable.baseData},
                    cache: false,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: function(data){
                        if(data){
                            window[callbackfn](data);
                        }else{
                            window[callbackfn](0);
                        }
                    }
                });
            }
        };
        arguments.callee.getUserAuth = function(platform) {
           var userAuthJson = {};
           userAuthJson['basedata'] = jqcc.cometchat.getBaseData();
           userAuthJson['baseurl'] = jqcc.cometchat.getBaseUrl();
           userAuth = JSON.stringify(userAuthJson);

           if(platform == "1") {
               androidCometchat.sendToMobile(userAuth);
           } else if (platform == "2") {
               return userAuth;
           }
        };
        arguments.callee.ping = function(){
            return 1;
        };
        arguments.callee.getLanguage = function(id){
            if(typeof(id) != 'undefined' && id != null && id != ''){
                if(typeof(language[id]) != 'undefined' ){
                    return language[id];
                }else{
                    return '';
                }
            }
            return language;
        };
        arguments.callee.chatWith = function(id, cccloud){
           if(typeof(jqcc.cometchat.chatWithUID) == 'function' && typeof(cccloud) != 'undefined'){
               jqcc.cometchat.chatWithUID(id);
           }else {
                if(typeof (jqcc[calleeAPI].chatWith)!=='undefined' && jqcc('#cometchat_synergy_iframe').length==0){
                    jqcc[calleeAPI].chatWith(id);
                } else {
                    var controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{"uid":id, "synergy":"1"}};
                    controlparameters = JSON.stringify(controlparameters);
                    if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                        jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                    }
                }
            }
        };
        arguments.callee.getRecentData = function(id){
            $.ajax({
                cache: false,
                url: baseUrl+"cometchat_receive.php",
                data: {chatbox: id, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    if(typeof (jqcc[calleeAPI].loadData)!=='undefined'){
                        jqcc[calleeAPI].loadData(id, data);
                    }
                }
            });
        };
        arguments.callee.getUserDetails = function(id){
            $.ajax({
                url: baseUrl+"cometchat_getid.php",
                data: {userid: id, basedata: ccvariable.baseData},
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                cache: false,
                async: false,
                dataType: 'jsonp',
                success: function(data){
                    ccvariable.buddylistStatus[id] = data.s;
                    ccvariable.buddylistMessage[id] = data.m;
                    ccvariable.buddylistName[id] = data.n;
                    ccvariable.buddylistAvatar[id] = data.a;
                    ccvariable.buddylistLink[id] = data.l;
                    ccvariable.buddylistChannelHash[id] = data.ch;
                    ccvariable.buddylistLastseen[id] = data.ls;
                    if(ccvariable.callbackfn=='mobilewebapp'){
                        jqcc[ccvariable.callbackfn].loadUserData(id, data);
                    }
                }
            });
        };
        arguments.callee.launchModule = function(id){
            if(typeof (jqcc[calleeAPI].launchModule)!=='undefined' && jqcc("#cometchat").length > 0){
                jqcc[calleeAPI].launchModule(id);
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"launchModule", "params":{"uid":id, "synergy":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };
        arguments.callee.toggleModule = function(id){
            if(typeof (jqcc[calleeAPI].toggleModule)!=='undefined'){
                jqcc[calleeAPI].toggleModule(id);
            }
        };
        arguments.callee.closeModule = function(id){
            if(typeof (jqcc[calleeAPI].closeModule)!=='undefined'){
                jqcc[calleeAPI].closeModule(id);
            }
        };
        arguments.callee.closeAllModule = function(){
            if(typeof (jqcc[calleeAPI].closeAllModule)!=='undefined'){
                jqcc[calleeAPI].closeAllModule();
            }
        };
        arguments.callee.joinChatroom = function(roomid, inviteid, roomname){
            if(typeof (jqcc[calleeAPI].joinChatroom)!=='undefined'){
                jqcc[calleeAPI].joinChatroom(roomid, inviteid, roomname);
            }
        };
        arguments.callee.createChatboxSet = function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
            $.ajax({
                url: baseUrl+"cometchat_getid.php",
                data: {userid: id, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                cache: false,
                success: function(data){
                    if(data){
                        ccvariable.buddylistName[id] = data.n;
                        ccvariable.buddylistStatus[id] = data.s;
                        ccvariable.buddylistMessage[id] = data.m;
                        ccvariable.buddylistAvatar[id] = data.a;
                        ccvariable.buddylistLink[id] = data.l;
                        ccvariable.buddylistIsDevice[id] = data.d;
                        ccvariable.buddylistChannelHash[id] = data.ch;
                        ccvariable.buddylistLastseen[id] = data.ls;
                        jqcc[settings.theme].createChatbox(id, data.n, data.s, data.m, data.a, data.l, data.d, silent, tryOldMessages);
                    }
                },
                error: function(data){
                    jqcc.cometchat.setThemeVariable('trying', id, 5);
                }
            });

        };
        arguments.callee.updateChatboxSet = function(id,prepend){
            var postVars={chatbox: id, basedata: ccvariable.baseData};
            if(typeof(prepend)!=="undefined"){
                postVars["prepend"]=prepend;
            }
            $.ajax({
                cache: false,
                url: baseUrl+"cometchat_receive.php",
                data: postVars,
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                dataType: 'jsonp',
                success: function(data){
                    if(data){
                        if(typeof(prepend)!=="undefined"){
                            jqcc[settings.theme].prependMessages(id, data);
                        }else{
                            jqcc[settings.theme].updateChatboxSuccess(id, data);
                        }
                    }
                }
            });
        };
        arguments.callee.chatboxKeydownSet = function(id, message, callbackfn){
            if(typeof(callbackfn) === "undefined" || callbackfn !="") {
                callbackfn = ccvariable.callbackfn;
            }
            ccvariable.sendVars["callbackfn"] = callbackfn;
            if(message.length>1000){
                if(message.indexOf(" ") == -1 || message.indexOf(" ") >= 1000) {
                    message = message.substr(0,999)+" "+message.substr(999,message.length);
                }
                if(message.charAt(999)==' '){
                    messagecurrent = message.substring(0, 1000);
                }else{
                    messagecurrent = message.substring(0, 1000);
                    var spacePos = messagecurrent.length;
                    while(messagecurrent.charAt(spacePos)!=' '){
                        spacePos--;
                    }
                    messagecurrent = message.substring(0, spacePos);
                }
                messagenext = message.substring(messagecurrent.length);
                if(messagenext.length>0){
                    messagecurrent = messagecurrent+"...";
                }
            }else{
                messagecurrent = message;
                messagenext = '';
            }
            message = messagecurrent;

            sendAjax = function (broadcastflag) {
                sendajax = false;
                $.ajax({
                    url: baseUrl+"cometchat_send.php",
                    data: ccvariable.sendVars,
                    dataType: 'jsonp',
                    type: ccvariable.dataMethod,
                    timeout: ccvariable.dataTimeout,
                    success: function(data){
                        ccvariable.sendVars = {};
                        if(data != null && typeof(data) != 'undefined'){

                            if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                                if(broadcastflag){
                                    jqcc[calleeAPI].addMessages(data);
                                }else{
                                    jqcc[calleeAPI].addMessages([{"from": id, "message": data.m, "id": data.id, "broadcast": 0, "direction": data.direction, "calledfromsend": 1}]);
                                    var alreadyreceivedunreadmessages = jqcc.cometchat.getFromStorage('receivedunreadmessages');
                                    var arr = Object.keys(alreadyreceivedunreadmessages).map(function(k) { return alreadyreceivedunreadmessages[k] });
                                    var maxmsgid = Math.max.apply(null,arr);
                                    if( data.id < maxmsgid){
                                        jqcc.cometchat.updateToStorage('receivedunreadmessages',{});
                                    }
                                }
                            }
                        }
                        ccvariable.heartbeatCount = 1;
                        if(ccvariable.heartbeatTime>settings.minHeartbeat){
                            ccvariable.heartbeatTime = settings.minHeartbeat;
                            clearTimeout(ccvariable.heartbeatTimer);
                            ccvariable.heartbeatTimer = setTimeout(function(){
                                jqcc.cometchat.chatHeartbeat();
                            }, ccvariable.heartbeatTime);
                        }
                        ccvariable.sendVars = {};
                        sendajax = true;
                   },
                   error: function(data){
                        sendajax = true;
                        if(broadcastData.length==0){
                            sendbroadcastinterval = 0;
                            clearInterval(sendbroadcastinterval);
                        }
                   }
               });
            }
            $( document ).ajaxStop(function() {
                sendajax = true;
                if(broadcastData.length==0){
                    sendbroadcastinterval = 0;
                    clearInterval(sendbroadcastinterval);
                }
            });
            if(sendajax == true){
                ccvariable.sendVars["basedata"] = ccvariable.baseData;
                if(broadcastData.length == 0){
                    ccvariable.sendVars["to"] = id;
                    ccvariable.sendVars["message"] = message;
                    var broadcastflag = 0;
                }else{
                    broadcastData.push(id,message);
                    ccvariable.sendVars["broadcast"] = broadcastData;
                    var broadcastflag = 1;
                }
                sendAjax(broadcastflag);
            }else{
                broadcastData.push(id,message);
                if(sendbroadcastinterval == 0){
                    sendbroadcastinterval = setInterval(function(){
                        sendbroadcastinterval = 0;
                        clearInterval(sendbroadcastinterval);
                        if(broadcastData.length == 0){
                            clearInterval(sendbroadcastinterval);
                        }
                        if(sendajax == true && broadcastData.length > 0){
                            sendbroadcastinterval = 0;
                            clearInterval(sendbroadcastinterval);
                            ccvariable.sendVars["basedata"] = ccvariable.baseData;
                            ccvariable.sendVars["broadcast"] = broadcastData;
                            sendAjax(1);
                            broadcastData = [];
                        }
                    }, 50);
                }
            }
            if(messagenext.length>0){
                jqcc.cometchat.chatboxKeydownSet(id, '...'+messagenext);
            }
        };

        arguments.callee.sendMessage = function(id, message){
            if(jqcc("#cometchat").length > 0) {
                jqcc.cometchat.chatboxKeydownSet(id,message);
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"sendMessage", "params":{"uid":id, "message":message, "synergy":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };

        arguments.callee.addMessage = function(boxid,message,msgid,nopopup){
            if(typeof(nopopup) === "undefined" || nopopup =="") {
                nopopup = 0;
            }
            if(typeof (jqcc[calleeAPI].addMessages)!=='undefined'){
                jqcc[calleeAPI].addMessages([{"from": boxid, "message": message, "self": 1, "old": 1, "id": msgid, "sent": Math.floor(new Date().getTime()), "nopopup": nopopup}]);
            }
            if(typeof (jqcc[calleeAPI].scrollDown)!=='undefined'){
                jqcc[calleeAPI].scrollDown(boxid);
            }
        };

        arguments.callee.statusSendMessageSet = function(message, statustextarea){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {statusmessage: message, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    jqcc[settings.theme].statusSendMessageSuccess(statustextarea);
                },
                error: function(data){
                    jqcc[settings.theme].statusSendMessageError();
                }
            });
        };
        arguments.callee.setGuestNameSet = function(guestname, guestnametextarea){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {guestname: guestname, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    jqcc[settings.theme].setGuestNameSuccess(guestnametextarea);
                },
                error: function(data){
                    jqcc[settings.theme].setGuestNameError();
                }
            });
        };
        arguments.callee.hideBar = function(){
            if(typeof (jqcc[calleeAPI].hideBar)!=='undefined'){
                jqcc[calleeAPI].hideBar();
            }
        };
        arguments.callee.getBaseUrl = function(){
            return baseUrl;
        };
        arguments.callee.setAlert = function(id, number){
            if(typeof (jqcc[calleeAPI].setModuleAlert)!=='undefined'){
                jqcc[calleeAPI].setModuleAlert(id, number);
            }
        };
        arguments.callee.closeTooltip = function(){
            if(typeof (jqcc[calleeAPI].closeTooltip)!=='undefined'){
                jqcc[calleeAPI].closeTooltip();
            }
        };
        arguments.callee.scrollToTop = function(){
            if(typeof (jqcc[calleeAPI].scrollToTop)!=='undefined'){
                jqcc[calleeAPI].scrollToTop();
            }
        };
        arguments.callee.reinitialize = function(){
            ccvariable.baseData = $.cookie(settings.cookiePrefix+'data');
            if(typeof (jqcc[calleeAPI].reinitialize)!=='undefined'){
                jqcc[calleeAPI].reinitialize();
            }
        };
        arguments.callee.updateHtml = function(id, temp){
            if(typeof (jqcc[calleeAPI].updateHtml)!=='undefined'){
                jqcc[calleeAPI].updateHtml(id, temp);
            }
        };
        arguments.callee.processMessage = function(id, value){
            if(typeof (jqcc[calleeAPI].processMessage)!=='undefined'){
                return jqcc[calleeAPI].processMessage(id, value);
            }
        };
        arguments.callee.replaceHtml = function(id, value){
            replaceHtml(id, value);
        };
        arguments.callee.getSettings = function(e){
            return settings;
        };
        arguments.callee.getMobileappdetails = function(e){
            return mobileappdetails;
        };

        arguments.callee.getTrayicon = function(e){
            return trayicon;
        };
        arguments.callee.getCcvariable = function(e){
            return ccvariable;
        };
        arguments.callee.echo = function(e){
            return "ECHO";
        };
        arguments.callee.getWebrtcPlugins = function(e){
            return webrtcplugins;
        };
        arguments.callee.userAdd = function(id, s, m, n, a, l, ch, ls){
            ccvariable.isJabber[id] = 1;
            ccvariable.buddylistStatus[id] = s;
            ccvariable.buddylistMessage[id] = m;
            ccvariable.buddylistName[id] = n;
            ccvariable.buddylistAvatar[id] = a;
            ccvariable.buddylistLink[id] = l;
            ccvariable.buddylistChannelHash[id] = ch || '';
            ccvariable.buddylistLastseen[id] = ls || '';
        };
        arguments.callee.updateJabberOnlineNumber = function(number){
            if(typeof (jqcc[calleeAPI].updateJabberOnlineNumber)!=='undefined'){
                jqcc[calleeAPI].updateJabberOnlineNumber(number);
            }
        };
        arguments.callee.getName = function(id){
            if(typeof (ccvariable.buddylistName[id])!=='undefined'){
                return ccvariable.buddylistName[id];
            }
        };
        arguments.callee.lightbox = function(name,caller,windowMode){
            var allowpopout = 0;
            var callbackfn='';
            if(ccvariable.callbackfn=='desktop'){
                callbackfn='desktop';
            }
            if(ccvariable.mobileDevice){
                callbackfn='mobilewebapp';
            }
            if(typeof(windowMode)=="undefined"){
                windowMode = 0;
            }
            var callerUrl = "";
            if(typeof(caller) != "undefined"){
                callerUrl = "caller="+caller;
            }
            if(trayicon[name]){
                if(name=='chatrooms'||name=='games'||name=='broadcastmessage'){
                    allowpopout = 1;
                    if(settings.theme == 'lite' && name=='chatrooms'){
                        jqcc[calleeAPI].minimizeOpenChatbox();
                    }
                }
                loadCCPopup(trayicon[name][2]+'?'+callerUrl+'&callbackfn='+callbackfn, trayicon[name][0], "status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+(Number(trayicon[name][4])+2)+",height="+trayicon[name][5]+"", Number(trayicon[name][4])+2, trayicon[name][5], trayicon[name][1], 0, 0, 0, allowpopout,windowMode);
            }
            if(typeof(caller) == 'undefined') {
                ccvariable.openChatboxId = '';
                jqcc.cometchat.setSessionVariable('openChatboxId', ccvariable.openChatboxId);
            }
        };
        arguments.callee.sendStatus = function(message){
            $.ajax({
                url: baseUrl+"cometchat_send.php",
                data: {status: message, basedata: ccvariable.baseData},
                dataType: 'jsonp',
                type: ccvariable.dataMethod,
                timeout: ccvariable.dataTimeout,
                success: function(data){
                    if(message!='away'){
                        ccvariable.currentStatus = message;
                        if(typeof(jqcc[calleeAPI].updateStatus)=='function'){
                            jqcc[settings.theme].removeUnderline();
                            jqcc[calleeAPI].updateStatus(message);
                        }
                    }
                }
            });
        };
        arguments.callee.tryClickSync = function(id){
            if(ccvariable.buddylistName[id]==null||ccvariable.buddylistName[id]==''){
                if(ccvariable.trying[id]<5){
                    setTimeout(function(){
                        jqcc.cometchat.tryClickSync(id);
                    }, 500);
                }
            }else{
                jqcc.cometchat.chatWith(id);
            }
        };
        arguments.callee.userDoubleClick = function(listing){
            var id = listing;
            if(typeof (jqcc[calleeAPI].createChatbox)!=='undefined'){
                jqcc[calleeAPI].createChatbox(id, ccvariable.buddylistName[id], ccvariable.buddylistStatus[id], ccvariable.buddylistMessage[id], ccvariable.buddylistAvatar[id], ccvariable.buddylistLink[id], ccvariable.buddylistIsDevice[id], 1);
            }
        };
        arguments.callee.tryClick = function(id){
            if(ccvariable.buddylistName[id]==null||ccvariable.buddylistName[id]==''){
                if(ccvariable.trying[id]<5){
                    setTimeout(function(){
                        jqcc.cometchat.tryClick(id);
                    }, 500);
                }
            }else{
                if(ccvariable.openChatboxId!=id){
                    jqcc.cometchat.chatWith(id);
                }
            }
        };
        arguments.callee.notify = function(title, image, message, clickEvent, id, msgid){
            if(navigator.userAgent.match(/chrome|firefox/i)&&settings.desktopNotifications==1){
                if(ccvariable.callbackfn=="desktop"&&typeof title!='undefined'&&typeof image!='undefined'&&typeof message!='undefined'){
                    jqcc.ccdesktop.desktopNotify(image, message, ccvariable.buddylistName[id], msgid);
                }else if(ccvariable.idleFlag){
                    if (Notification.permission !== 'denied') {
                        Notification.requestPermission(function (permission) {
                            if(!('permission' in Notification)) {
                                Notification.permission = permission;
                            }
                        });
                    }
                    if(Notification.permission === "granted"&&typeof title!='undefined'&&typeof image!='undefined'&&typeof message!='undefined'){
                        tempMsg = jqcc('<div>'+message+'</div>');
                        jqcc.each(tempMsg.find('img.cometchat_smiley'),function(){
                            jqcc(this).replaceWith('*'+jqcc(this).attr('title')+'*');
                        });
                        message = tempMsg.text();
                        if(typeof id!='undefined'){
                            if(typeof ccvariable.desktopNotification[id]=="undefined"){
                                ccvariable.desktopNotification[id] = {};
                            }
                            ccvariable.desktopNotification[id][msgid] = new Notification(title, {icon: image, body: message});
                            ccvariable.desktopNotification[id][msgid].onclick = function(){
                                if(typeof clickEvent=='function'){
                                    clickEvent();
                                }
                            };
                        }else{
                            ccvariable.desktopNotification[id][msgid] = new Notification(title, {icon: image, body: message});
                            ccvariable.desktopNotification[id][msgid].onclick = function(){
                                if(typeof clickEvent=='function'){
                                    clickEvent();
                                }
                            };
                        }
                    }
                }
            }
        };
        arguments.callee.statusKeydown = function(event, statustextarea){
            if(event.keyCode==13&&event.shiftKey==0){
                if(typeof (jqcc[calleeAPI].statusSendMessage)!=='undefined'){
                    jqcc[calleeAPI].statusSendMessage();
                }
                return false;
            }
        };
        arguments.callee.guestnameKeydown = function(event, statustextarea){
            if(event.keyCode==13&&event.shiftKey==0){
                if(typeof (jqcc[calleeAPI].setGuestName)!=='undefined'){
                    jqcc[calleeAPI].setGuestName(statustextarea);
                }
                return false;
            }
        };
        arguments.callee.minimizeAll = function(){
            if(typeof (jqcc[calleeAPI].setGuestName)!=='undefined' && jqcc("#cometchat").length > 0){
                jqcc[settings.theme].minimizeAll();
            } else {
                var controlparameters = {"type":"modules", "name":"cometchat", "method":"minimizeAll", "params":{"uid":"", "synergy":"1"}};
                controlparameters = JSON.stringify(controlparameters);
                if(typeof(jqcc('#cometchat_synergy_iframe')[0]) != 'undefined'){
                    jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
                }
            }
        };
        arguments.callee.processcontrolmessage = function(incoming){
            if(typeof incoming.message != "undefined" && (incoming.message).indexOf('CC^CONTROL_')!=-1){
                var message = (incoming.message).replace('CC^CONTROL_','');
                var data = incoming.message.split('_');
                var chatroommode = 0;
                if(typeof(data[5])!='undefined' && data[5]==1){
                    chatroommode = 1;
                }
                if(cp = IsJsonString(message)){
                    var type = cp["type"] || ""
                    ,   name = cp["name"] || ""
                    ,   method = cp["method"] || ""
                    ,   params = cp["params"] || {}
                    ;
                    switch(type){
                        case 'core':
                            switch(name){
                                default:
                                    if(typeof jqcc[calleeAPI][method] == "function" && ccvariable.callbackfn != "mobilewebapp")
                                        jqcc[calleeAPI][method](params);
                                    break;
                            }
                            break;
                        case 'plugins':
                            message = JSON.parse(message);
                            return jqcc['cc'+name.toLowerCase()].processControlMessage(params);
                            break;

                        default:
                            break;
                    }
                } else if(data[1]=='PLUGIN'){
                    switch(data[2]){
                        case 'AVCHAT':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'REJECTCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'NOANSWER':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'CANCELCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'BUSYCALL':
                            var controlparameters = {"type":"plugins", "name":"avchat", "method":"busycall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        case 'AUDIOCHAT':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'REJECTCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"rejectcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'NOANSWER':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"noanswer", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'CANCELCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"canceloutgoingcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            case 'BUSYCALL':
                            var controlparameters = {"type":"plugins", "name":"audiochat", "method":"busycall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        case 'BROADCAST':
                        switch(data[3]){
                            case 'ENDCALL':
                            var controlparameters = {"type":"plugins", "name":"broadcast", "method":"endcall", "params":{"grp":data[4], "chatroommode":chatroommode}};
                            break;
                            default :
                            message = '';
                            break;
                        }
                        break;
                        default :
                        break;
                    }
                    if(typeof(data[2]) == 'undefined'){return;}
                    return jqcc['cc'+data[2].toLowerCase()].processControlMessage(controlparameters);
                } else {
                    <?php
                    foreach ($trayicon as $ti) {
                        if (in_array('chatrooms', $ti)) {
                    ?>
                        switch(data[1]){
                            case 'kicked':
                                if (jqcc.cometchat.getChatroomVars('myid') == data[2]) {
                                    jqcc.cometchat.kickChatroomUser(data[1],incoming.id);
                                    alert ('<?php echo $chatrooms_language[36];?>');
                                    jqcc.cometchat.leaveChatroom();
                                }
                                return '';
                                break;
                            case 'banned':
                                if (jqcc.cometchat.getChatroomVars('myid') == data[2]) {
                                    jqcc.cometchat.banChatroomUser(data[1],incoming.id);
                                    alert ('<?php echo $chatrooms_language[37];?>');
                                    jqcc.cometchat.leaveChatroom(data[2], 1);
                                }
                                return '';
                                break;
                            case 'deletemessage':
                                $("#cometchat_message_"+data[2]).parent().remove();
                                return '';
                                break;
                            default :
                                break;
                        }
                    <?php }
                    } ?>
                }
            }else if(typeof incoming.message != "undefined" && ((incoming.message).indexOf('has successfully sent a file')!=-1 || (incoming.message).indexOf('has sent you a file')!=-1)){
                if(ccvariable.callbackfn=="desktop"){
                    if(incoming.message.indexOf('target')>=-1){
                        incoming.message=incoming.message.replace(/target="_blank"/g,'');
                    }
                }
                 return incoming.message;
            }else if(typeof incoming.message != "undefined" && ((incoming.message).indexOf('has successfully sent a handwritten message')!=-1 || (incoming.message).indexOf('has sent you a handwritten message')!=-1)){
                if(jqcc.cometchat.getCcvariable().callbackfn=="desktop"){
                    if(incoming.message.indexOf('href')>=-1){
                        var start = (incoming.message).indexOf('href');
                        var end = (incoming.message).indexOf('target');
                        var HtmlString=(incoming.message).slice(start,end);
                        incoming.message=(incoming.message).replace(HtmlString,'');
                    }
                }
                 return incoming.message;
            } else if(typeof incoming.message != "undefined") {
                if(ccvariable.callbackfn=="desktop"){
                    if((incoming.message).indexOf('has shared a file')!=-1){
                        if(incoming.message.indexOf('target')>=-1){
                            incoming.message=incoming.message.replace(/target="_blank"/g,'');
                        }
                        return incoming.message;
                    }else if((incoming.message).indexOf('has shared a handwritten message')!=-1){
                        if(incoming.message.indexOf('href')>=-1){
                            var start = (incoming.message).indexOf('href');
                            var end = (incoming.message).indexOf('target');
                            var HtmlString=(incoming.message).slice(start,end);
                            incoming.message=(incoming.message).replace(HtmlString,'');
                        }
                        return incoming.message;
                    }else{
                        return incoming.message;
                    }
                }else{
                    return incoming.message;
                }
            }
        };
        arguments.callee.closeCRPopout = function(params){

        };

        arguments.callee.sendReceipt = function(incoming, receipt){
            var fromid = incoming.from;
            var messageid = incoming.id;

            if(typeof incoming.old != "undefined" && incoming.old == 1){
                ccvariable.lastmessagereadstatus[fromid] = 1;
                return;
            }
            if(typeof receipt == "undefined" && incoming.self == 0 && ccvariable.currentStatus != 'invisible'){
                receipt = 'deliveredMessageNotify';
                ccvariable.lastmessagereadstatus[fromid] = 0;
                if(ccvariable['openChatboxId'] == fromid && ccvariable.windowFocus == true){
                    receipt = 'deliveredReadMessageNotify';
                    ccvariable.lastmessagereadstatus[fromid] = 1;
                }
            } else if(ccvariable.lastmessagereadstatus[fromid]==1){
                ccvariable.lastmessagereadstatus[fromid] = 1;
                return;
            }
            if(settings.cometserviceEnabled == 1 && settings.messagereceiptEnabled == 1 && typeof receipt != "undefined" && incoming.id != undefined){
                var channel = '';
                if(ccvariable.callbackfn != 'mobilewebapp'){
                    channel = ccvariable.buddylistChannelHash[fromid];
                } else{
                    channel = jqcc.cometchat.getThemeArray("buddyListChannelHash",fromid);
                }
                var controlmessage = 'CC^CONTROL_{"type":"core","name":"textchat","method":"'+receipt+'","params":{"fromid":'+ccvariable.userid+',"message":'+messageid+'}}';
                if(transport == 'cometservice-selfhosted'){
                	var jsondata = {"channel":"/"+channel,"data":{"from":ccvariable.userid,"message":controlmessage,"sent":parseInt(new Date().getTime()),"self":0},"callback":""}
                } else if(transport == 'cometservice'){
                    var jsondata = {"channel":channel,"message":{"from":ccvariable.userid,"message":controlmessage,"sent":parseInt(new Date().getTime()),"self":0},"callback":""}
                }

                COMET.publish(jsondata);

                }
            }

    };
    function replaceHtml(el, html){
        var oldEl = typeof el==="string" ? document.getElementById(el) : el;
        /*@cc_on // Pure innerHTML is slightly faster in IE
         oldEl.innerHTML = html;
         return oldEl;
         @*/
        var newEl = oldEl.cloneNode(false);
        newEl.innerHTML = html;
        oldEl.parentNode.replaceChild(newEl, oldEl);
        /* Since we just removed the old element from the DOM, return a reference
         to the new element, which can be used to restore variable references. */
        return newEl;
    };
})(jqcc);
jqcc(document).bind('keyup', function(e){
    if(e.keyCode==27){
        jqcc.cometchat.minimizeAll();
    }
});

function cometready(){
    jqcc(document).ready(function(){
        if(typeof CometChathasBeenRun==='undefined'){
            CometChathasBeenRun = true;
        }else{
            return;
        }
        jqcc.cometchat();
        jqcc.cometchat.<?php echo $jsfn; ?>();
    });
};

<?php if(!defined('USE_COMET') || USE_COMET == 0) { ?>
   cometready();
<?php } ?>