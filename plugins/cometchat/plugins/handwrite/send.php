<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

$data = explode(';',$_REQUEST['tid']);
$_REQUEST['basedata'] = $data[1];

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."plugins.php");
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."extensions".DIRECTORY_SEPARATOR."mobileapp".DIRECTORY_SEPARATOR."config.php");

if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

$data = explode(';',$_REQUEST['tid']);
$_REQUEST['tid'] = $data[0];
$_REQUEST['embed'] = $data[2];
$randomImage = md5(rand(0,9999999999).time());
if (!empty($_REQUEST['image'])) {
    $image = explode('data:image/png;base64,',$_REQUEST['image']);
    $png = base64_decode($image[1]);
} else {
    $inputSocket = fopen('php://input','rb');
    $png = stream_get_contents($inputSocket);
    fclose($inputSocket);
}

if(defined('AWS_STORAGE') && AWS_STORAGE == '1') {
    include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."storage".DIRECTORY_SEPARATOR."s3.php");
    $s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
    if($s3->putObject($png, AWS_BUCKET, $bucket_path.'handwrite/'.$randomImage.".png", S3::ACL_PUBLIC_READ)) {
        $linkToImage = '//'.$aws_bucket_url.'/'.$bucket_path.'handwrite/'.$randomImage.".png";
    }
}else {
    $file = fopen(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."writable".DIRECTORY_SEPARATOR."handwrite".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$randomImage.".png","w");
    fwrite($file,$png);
    fclose($file);
    if(file_exists(dirname(dirname(dirname(__FILE__)))."/writable/handwrite/uploads/".$randomImage.".png")){
        $linkToImage = BASE_URL."writable/handwrite/uploads/".$randomImage.".png";
    }
}

if(isset($linkToImage)) {
    $text = '<a href="'.$linkToImage.'" target="_blank" style="display:inline-block;margin-bottom:3px;margin-top:3px;max-width:100%;"><img class="cc_handwrite_image" src="'.$linkToImage.'" border="0" style="padding:0px;display: inline-block;border:1px solid #666;" height="90" width="134"></a>';
    if (substr($_REQUEST['tid'],0,1) == 'c') {
        $_REQUEST['tid'] = substr($_REQUEST['tid'],1);
        sendChatroomMessage($_REQUEST['tid'],$handwrite_language[3]."<br/>$text",0);
    } else {
        $response = sendMessage($_REQUEST['tid'],$handwrite_language[1]."<br/>$text",0,'handwrite');
        $processedMessage = $_SESSION['cometchat']['user']['n'].": ".$handwrite_language[1];
        pushMobileNotification($_REQUEST['tid'],$response['id'],$processedMessage);

        if(USE_COMET == 1){
            $cometmessage = array();
            $cometresponse = array('to' => $_REQUEST['tid'],'message' => $handwrite_language[1]."<br/>$text", 'dir' => 0,'type' => "handwrite");
            array_push($cometmessage, $cometresponse);
            publishCometMessages($cometmessage,$response['id']);
        }
        /*Uncomment to enable push notifications for CometChat Legacy Apps*/
        /*if (isset($_REQUEST['sendername']) && $pushNotifications == 1) {
                pushMobileNotification($handwrite_language[2], $_REQUEST['sendername'], $_REQUEST['tid'], $_REQUEST['tid']);
        }*/
        /*Uncomment to enable push notifications for CometChat Legacy Apps*/
    }
}
$embed = '';
$embedcss = '';
$close = "setTimeout('window.close()',2000);";

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'web') {
    $embed = 'web';
    $embedcss = 'embed';
    $close = "
        var controlparameters = {'type':'plugins', 'name':'handwrite', 'method':'closeCCPopup', 'params':{'name':'handwrite'}};
        controlparameters = JSON.stringify(controlparameters);
        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        } else {
            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        }";
}

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'mobileapp') {
    $close = "setTimeout(
        function(){
            window.location = 'mobileapp:cc_close_webview'
        },
    100)";
}

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'mobileapp') {

    $close = "setTimeout(
        function(){
            window.location = 'mobileapp:cc_close_webview'
        },
    100)";
}

if (!empty($_REQUEST['embed']) && $_REQUEST['embed'] == 'desktop') {
    $embed = 'desktop';
    $embedcss = 'embed';
    $close = "parentSandboxBridge.closeCCPopup('handwrite');";
}
if(!empty($_REQUEST['other']) && $_REQUEST['other'] == 1){
    echo $close;
} else {
    echo <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>{$handwrite_language[0]} (closing)</title>
<script type="text/javascript">
    function closePopup(){
        var controlparameters = {'type':'plugins', 'name':'handwrite', 'method':'closeCCPopup', 'params':{'name':'handwrite'}};
        controlparameters = JSON.stringify(controlparameters);
        if(typeof(parent) != 'undefined' && parent != null && parent != self){
            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
        } else {
            window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
        }
    }
</script>
</head>
<body onload="closePopup();">
</body>
</html>
EOD;
}
?>