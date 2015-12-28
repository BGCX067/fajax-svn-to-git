<?php
require_once("Classes/class.Ajax.php");

function parseInclude($path){
    ob_start();
    include($path);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

$cbA = new Ajax("myCbA");
$cbA->OnRequest = "onCallback";
$cbA->OnComplete = "onCallbackComplete";
$cbA->OnError = "onCallbackError";
$cbA->Debug = true;
$cbA->LoadingPanelTemplate = parseInclude("waiting.html");
$cbA->LoadingPanelFadeDuration = 5;

$cb = new Ajax("myCb");
$cb->OnRequest = "onCallback";
$cb->OnComplete = "onCallbackComplete";
$cb->OnError = "onCallbackError";
$cb->Debug = true;
$cb->LoadingPanelTemplate = parseInclude("waiting.html");
$cb->LoadingPanelFadeDuration = 5;
//$cb->RefreshInterval = 5000;

/**
* @AjaxHandler(myCb)
*/
function doRequest($sender, $e) {
    echo("<root>\n");
    if(count($e->Arguments) > 0) {
		echo("  <parms>\n");
		foreach($e->Arguments as $key => $value)
			echo("    <item>" . $value . "</item>\n");
		echo("  </parms>\n");
	}
    if(count($e->Parameters) !== 0) {
        echo("  <" . $sender->ID. ">\n");
        foreach($e->Parameters as $key => $value) {
            if(is_array($value)) {
                echo("    <" . $key . ">\n");
                foreach($value as $item)
                    echo("      <item>" . $item . "</item>\n");
                echo("    </" . $key . ">\n");
            }
            else
                echo("    <" . $key . ">" . $value . "</" . $key . ">\n");
        }
        echo("  </" . $sender->ID. ">\n");
    }
    echo("</root>");
}

/**
* @AjaxHandler(myCbA)
*/
function doRequestA($sender, $e) {
    echo("Callback 2 was fired.<br /><script language=\"javascript\">alert(\"Content 2 was processed.\");</script>");
    echo("<script language=\"javascript\">var el = document.getElementById(\"haha\"); if(el) el.value=\"he he, hi hi, hu hu\";</script>");
}
?>
<html>
<head>
<title>Callback Demo</title>
<script language="javascript">
     function doCallback() {
         myCb.addParameter("Name", "Pham Van Dung");
         myCb.addParameter("Email", "pvdung@gmail.com");
         myCb.addParameter("Phone", ["2543243", "2554545", "35435435"]);
         myCb.request("hi hi", "khi khi");
     }
     function doCallbackA() {
         myCbA.addParameter("Name", "Pham Van Dung");
         myCbA.addParameter("Email", "pvdung@gmail.com");
         myCbA.addParameter("Phone", ["2543243", "2554545", "35435435"]);
         myCbA.request("hi hi", "khi khi");
     }

     function onCallback() {
         //alert("hehe");
     }

     function onCallbackComplete(value) {
         //alert(value);
     }


     function onCallbackError(status, text, error) {
         //alert(error);
     }
</script>
</head>
<body>
<form name="frmMain" method="post">
<input type="text" name="action" />
<input type="button" name="haha" id="haha" value="Callback 1" onclick="doCallback();" />
<input type="button" name="haha1" value="Callback 2" onclick="doCallbackA();" />
<?php
     $cb->Render();
?>
<?php
     $cbA->Render();
?>
</form>
</body>
</html>