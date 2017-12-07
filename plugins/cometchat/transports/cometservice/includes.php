<?php
 /*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/
 $callbackfn = '';
 if(!empty($_GET['callbackfn']) && $_GET['callbackfn'] == 'desktop'){
    $desktopmode = 1;
 }else{
    $desktopmode = 0;
 }
 include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'comet.js');
 ?>
var cometid = '';
var comet = COMET.init({
    'desktop': '<?php echo $desktopmode;?>',
    'baseurl': '<?php echo BASE_URL;?>',
    'ssl': (window.location.protocol=='https:') ? true : false
});
function cometcall_function(id, td, calleeAPI){
    cometid = id;
    comet.subscribe({
        channel: id
    }, function(incoming){
        <?php
         if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php')) {
             include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php');
             if($useGoogle == 1 && !empty($googleKey)){
         ?>
                if(jqcc.cookie('<?php echo $cookiePrefix;?>lang') && (incoming.message).indexOf('CC^CONTROL_') == -1){
                    var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
                    jqcc.ajax({
                        url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                        data: {q: incoming.message, target: lang},
                        dataType: 'jsonp',
                        success: function(data){
                            if(typeof(data.data)!="undefined"){
                                incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                            }
                            if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                                jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td, "direction": incoming.direction}]);
                            }
                        }
                    });
                }else{
                    if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                        jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td, "direction": incoming.direction}]);
                    }
                }
                <?php
                 } else {
                ?>
                    if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                        jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td, "direction": incoming.direction}]);
                    }
                <?php
                 }
         } else { ?>
            if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td, "direction": incoming.direction}]);
            }
        <?php
        }
        ?>
    });
}
function chatroomcall_function(id,userid){
    comet.subscribe({
        channel: id,
        timetoken: 0
    }, function(incoming){
        <?php
         if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php')) {
         include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php');
         if($useGoogle == 1 && !empty($googleKey)){
         ?>
        if(jqcc.cookie('<?php echo $cookiePrefix;?>lang') && incoming.fromid != userid && (incoming.message).indexOf('CC^CONTROL_') == -1){
            var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
            jqcc.ajax({
                url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                data: {q: incoming.message, target: lang},
                dataType: 'jsonp',
                success: function(data){
                    if(typeof(data.data)!="undefined"){
                        incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                    }
                    $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
                    $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from,'0', incoming.roomid);
                }
            });
        }else{
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from,'0', incoming.roomid);
        }
        <?php
         } else { ?>
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from,'0', incoming.roomid);
        <?php
         }
         } else { ?>
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, incoming.id, '1', parseInt(incoming.sent), incoming.from,'0', incoming.roomid);
        <?php } ?>
    });
}
function cometuncall_function(id){
    comet.unsubscribe({channel: id});
}