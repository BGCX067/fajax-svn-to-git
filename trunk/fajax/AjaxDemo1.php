<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */
    require_once("Classes/class.Ajax.php");

    function parseInclude($path) {
        ob_start();
        include($path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    $cb = new Ajax("cb");
    $cb->OnRequest = "onCallback";
    $cb->OnComplete = "onCallbackComplete";
    $cb->OnError = "onCallbackError";
    $cb->OnAbort = "onAbort";
    $cb->Debug = true;
    //$cb->RefreshInterval = 5000;
    $cb->LoadingPanelTemplate = parseInclude("waiting.html");
    $cb->LoadingPanelFadeDuration = 2;
    $cb->RequestPrefix = "/pajax/AjaxHandler.php";

    $cbA = new Ajax("cbA");
    $cbA->OnRequest = "onCallback";
    $cbA->OnComplete = "onCallbackComplete";
    $cbA->OnError = "onCallbackError";
    $cbA->Debug = true;
    $cbA->LoadingPanelTemplate = parseInclude("waiting.html");
    $cbA->LoadingPanelFadeDuration = 2;
    $cbA->RequestPrefix = "/pajax/AjaxHandler.php";

?>
<html>
<head>
<title>CallbackHandler Demo</title>
<script language="javascript">
     function doCallback() {
         cb.addParameter("Name", "Pham Van Dung");
         cb.addParameter("Email", "pvdung@gmail.com");
         cb.addParameter("Phone", ["2543243", "2554545", "35435435"]);
         cb.request("hi hi", "khi khi");
     }
     function doCallbackA() {
         cbA.addParameter("Name", "Pham Van Dung");
         cbA.addParameter("Email", "pvdung@gmail.com");
         cbA.addParameter("Phone", ["2543243", "2554545", "35435435"]);
         cbA.request("hi hi", "khi khi");
     }

     function onCallback() {
         //alert("hehe");
     }

     function onCallbackComplete(src) {
         document.forms["frmMain"].content.value = src.content;
        var scripts = document.getElementsByTagName("SCRIPT");
        for (var i = 0; i < scripts.length; i++) {
            //if (typeof(scripts[i].src) == "undefined" || scripts[i].src == null || scripts[i].src == "")
	            //console.log(scripts[i].text);
	        //else
		        //console.log(scripts[i].src);
        }
     }

    /**
     *
     * @access public
     * @return void
     **/
    function onAbort(){
        alert("Callback aborted!");
    }

     function onCallbackError(src) {
         document.forms["frmMain"].content.value = src.error;
     }
</script>
</head>
<body>
<form name="frmMain" method="post">
<input type="text" name="action" />
<input type="button" name="haha" id="haha" value="Callback 1" onclick="doCallback();" />
<input type="button" name="haha1" value="Callback 2" onclick="doCallbackA();" /><br />
<textarea id="content" name="content" rows="18" cols="85"></textarea>
<?php
     $cb->Render();
     $cbA->Render();
?>
<!--
<script type="text/javascript" src="js/ajax_sample.js"></script>
-->
</form>
</body>
</html>