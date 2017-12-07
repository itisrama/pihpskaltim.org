<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
global $getstylesheet;
global $theme;
$options = array(
    "chatboxHeight"                 => array('textbox','Set the Height of the Chat (Minimum Height can be 420)'),
    "chatboxWidth"                 => array('textbox','Set the Width of the Chat (Minimum Width can be 300)'),
    );

if (empty($_GET['process']) && empty($_GET['updatesettings'])) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

    $form = '';
    if(empty($generateembedcodesettings)) {
        $curl = 0;
        $errorMsg = '';

        $chatroom = '';
        $private = '';
        $none = '';

        if ($enableType == '0') {
            $none = "selected";
        } else if ($enableType == '1') {
            $chatroom = "selected";
        } else if ($enableType == '2') {
            $private = "selected";
        }

        echo <<<EOD
        <!DOCTYPE html>

        <html>
        <head>
        $getstylesheet
        <script src="../js.php?type=core&name=jquery"></script>
        <script>
          $ = jQuery = jqcc;

        function resizeWindow() {
            window.resizeTo(($("form").outerWidth(false)+window.outerWidth-$("form").outerWidth(false)), ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
        }
        </script>
        </head>

        <body>
            <form name="themesettings" style="height:100%;" action="?module=dashboard&action=loadthemetype&type=theme&name=synergy&updatesettings=true" method="post">
            <div id="content" style="width:auto;">
                    <h2>Settings</h2><br />
                    <h3 id='data'>You can enable/disable Private chat or Chatroom.</h3>

                    <div style="margin-bottom:10px;">
                            <div class="title">Enable :</div>
                            <div class="element" id="">
                                <select name="enableType" id="TypeSelector">
                                    <option value="0" $none>Both</option>
                                    <option value="1" $chatroom>Only Chatroom</option>
                                    <option value="2" $private>Only One-on-one Chat</option>
                                </select>
                            </div>
                            <div style="clear:both;padding:10px;"></div>

                        <div style="clear:both;padding:5px;"></div>
                    </div>
                    <input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
            </div>
            </form>
        <script type="text/javascript" language="javascript">
            $(function() {
                setTimeout(function(){
                        resizeWindow();
                    },200);
            });
        </script>
        </body>
        </html>
EOD;
    } else {
        foreach ($options as $option => $result) {
            $req = '';
            if($option == 'chatboxHeight' OR $option == 'chatboxWidth') {
                $req = 'required';
            }
            $form .= '<div id="'.$option.'"><div class="titlelong" >'.$result[1].'</div><div class="element">';
            if ($result[0] == 'textbox') {
                $form .= '<input type="text" class="inputbox" name="'.$option.'" value="'.${$option}.'" '.$req.'>';
            }
            $form .= '</div><div style="clear:both;padding:7px;"></div></div>';
        }

        echo <<<EOD
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <script type="text/javascript" src="../js.php?admin=1"></script>
                            <script type="text/javascript" language="javascript">
                                $(function() {
                                    setTimeout(function(){
                                        resizeWindow();
                                    },200);
                                });

                                function resizeWindow() {
                                    window.resizeTo(($("form").outerWidth()+window.outerWidth-$("form").outerWidth()), ($('form').outerHeight()+window.outerHeight-window.innerHeight));
                                }
                            </script>
                            $getstylesheet
                        </head>
                    <body>
                        <form style="height:100%" action="?module=dashboard&action=loadthemetype&type=theme&name=synergy&process=true" onsubmit="return validate();" method="post">
                        <div id="content" style="width:auto">
                                <h2>Generate Embed Code</h2>
                                <h3>If you are unsure about any value, please proceed with default value</h3>
                                <div>
                                    <div id="centernav" style="width:700px">
                                        $form
                                    </div>
                                </div>
                                <div style="clear:both;padding:7.5px;"></div>
                                <input type="submit" value="Generate Code" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
                             <div style="clear:both"></div>
                               </div>
                            </form>
                    </body>
                    <script>
                        function validate(){
                            var cbHeight = parseInt($("input:[name=chatboxHeight]").val());
                            $("input:[name=chatboxHeight]").val(cbHeight)
                            var cbWidth = parseInt($("input:[name=chatboxWidth]").val());
                            $("input:[name=chatboxWidth]").val(cbWidth);

                            if(cbHeight < 420) {
                                alert('Height must be greater than 420');
                                return false;
                            } else if(cbWidth < 300){
                                alert('Width must be greater than 300');
                                return false;
                            } else {
                                return true;
                            }
                        }
                    </script>
                    </html>
EOD;
            }
        } else if (!empty($_GET['updatesettings']) && $_GET['updatesettings'] == true) {
            if (isset($_POST['enableType'])) {
                configeditor(array('synergy_settings' => $_POST));
            }
            header("Location:?module=dashboard&action=loadthemetype&type=theme&name=synergy");
        } else {
        	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');
            $embed_code = '&lt;div id="cometchat_embed_synergy_container" style="width:'.$_POST['chatboxWidth'].'px;height:'.$_POST['chatboxHeight'].'px;max-width:100%;border:1px solid #CCCCCC;" &gt;&lt;/div&gt;&lt;script src="'.BASE_URL.'js.php?type=core&name=embedcode" type="text/javascript"&gt;&lt;/script&gt;&lt;script&gt;var iframeObj = {};iframeObj.module="synergy";iframeObj.style="min-height:420px;min-width:300px;";iframeObj.width="'.$_POST['chatboxWidth'].'px";iframeObj.height="'.$_POST['chatboxHeight'].'px";iframeObj.src="'.BASE_URL.'cometchat_popout.php"; if(typeof(addEmbedIframe)=="function"){addEmbedIframe(iframeObj);}&lt;/script&gt;';
            echo <<<EOD
                <!DOCTYPE html>
                <html>
                    <head>
                        <script type="text/javascript" src="../js.php?admin=1"></script>
                        <script type="text/javascript" language="javascript">
                            $(function() {
                                setTimeout(function(){
                                    resizeWindow();
                                },200);
                            });

                            function resizeWindow() {
                                window.resizeTo((520), (190+window.outerHeight-window.innerHeight));
                            }
                        </script>
                        <style>textarea { border:1px solid #ccc; color: #333; font-family:verdana; font-size:12px; }</style>
                    </head>
                <body style='overflow:hidden'>
                    <textarea readonly="" style="width:500px;height:170px">{$embed_code}</textarea>
                </body>
                </html>
EOD;
       }
?>
