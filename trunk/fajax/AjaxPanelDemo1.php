<?php
require_once("Classes/class.Ajax.php");

/**
* @AjaxHandler(myCb)
*/
function doRequest($sender, $e) {
    if(count($e->Arguments) > 0) {
		echo("  <div>\n");
		foreach($e->Arguments as $key => $value)
			echo("    <label>[" . $value . "]</label>\n");
		echo("  </div>\n");
	}
    if(count($e->Parameters) !== 0) {
        echo("<table border=\"1\" cellspacing=\"0\" bordercollapse=\"collapse\"><tr><th>Key</th><th>Value</th></tr>");
        foreach($e->Parameters as $key => $value) {
            if(is_array($value)) {
                echo("<tr><td>" . $key . "</td><td>");
                foreach($value as $item)
                    echo("      <label>[" . $item . "]</label>\n");
                echo("    </td></tr>");
            }
            else
                echo("<tr><td>" . $key . "</td><td>" . $value . "</td></tr>");
        }
        echo("  </table>\n");
    }
}

/**
* @AjaxHandler(myCbA)
*/
function doRequestA($sender, $e) {
    echo("<div>Callback 2 was fired.</div><script language=\"javascript\">alert(\"Content 2 was processed.\");</script><div>I'm here</div>");
    echo("<script language=\"javascript\">var el = document.getElementById(\"haha\"); if(el) el.value=\"he he, hi hi, hu hu\";</script>");
    echo("<div>Is that cool? :)</div>");
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
	     if (console)
	         console.log("onrequest was fired.");
     }

     function onCallbackComplete(value) {
	     if (console)
		     console.log("oncomplete was fired.");
     }


     function onCallbackError(status, text, error) {
	     if (console)
		     console.log("onerror was fired.");
     }
</script>
<style type="text/css" rel="stylesheet">
	.myCb {
		border: 1px solid #666;
		width: 50%;
	}
</style>
</head>
<body>
<form name="frmMain" method="post">
<input type="text" name="action" />
<input type="button" name="haha" id="haha" value="Callback 1" onclick="doCallback();" />
<input type="button" name="haha1" value="Callback 2" onclick="doCallbackA();" />

<ajax id="myCb" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError" debug="true" ajaxPanel="true"
loadingPanelTemplate="waiting.html" cssClass="myCb">
	<div id="prepage" style="font-family: arial; font-size: 16px; background-color: white;">
		<table width="100%">
		<tr>
			<td><b>Yooour Content Here!</b></td>
		</tr>
		</table>
	</div>
</ajax>
<div>
aS as ASA D SAD SA
SAD SAD SAD SAD SA
</div>
<ajax id="myCbA" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError" debug="true" ajaxPanel="true">
	<div id="prepage" style="font-family: arial; font-size: 16px; background-color: white;">
		<table width="100%">
		<tr>
			<td><b>Yooooooooour Content Here!</b></td>
		</tr>
		</table>
	</div>
</ajax>
</form>
</body>
</html>
<?php
	Ajax::init();
?>