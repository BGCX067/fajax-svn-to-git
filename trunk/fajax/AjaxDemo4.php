<?php
require_once("Classes/class.Ajax.php");

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

<ajax id="myCb" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError" debug="true" ajaxPanel="false">
	<div id="prepage" style="position: absolute; font-family: arial; font-size: 16px; left: 0px; top: 0px; background-color: white; layer-background-color: white; height: 100%; width: 100%;">
		<table width="100%">
		<tr>
			<td><b>Loading ... ... Please wait!</b></td>
		</tr>
		</table>
	</div>
</ajax>

aS as ASA D SAD SA
SAD SAD SAD SAD SA
<ajax id="myCbA" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError" debug="true">
	<div id="prepage" style="position: absolute; font-family: arial; font-size: 16px; left: 0px; top: 0px; background-color: white; layer-background-color: white; height: 100%; width: 100%;">
		<table width="100%">
		<tr>
			<td><b>Loading ... ... Please wait!</b></td>
		</tr>
		</table>
	</div>
</ajax>
</form>
</body>
</html>
<?php
     $parser = Ajax::init();
?>