<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */
require_once("Classes/class.Ajax.php");

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
<ajax id="cb" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError"
	onAbort="onAbort" debug="true" requestPrefix="/pajax/AjaxHandler.php">
	<div id="prepage" style="position: absolute; font-family: arial; font-size: 16px; left: 0px; top: 0px; background-color: white; layer-background-color: white; height: 100%; width: 100%;">
		<table width="100%">
		<tr>
			<td><b>Loading ... ... Please wait!</b></td>
		</tr>
		</table>
	</div>
</ajax>
asd sad sad sas
<ajax id="cbA" onRequest="onCallback" onComplete="onCallbackComplete" onError="onCallbackError"
	onAbort="onAbort" debug="true" requestPrefix="/pajax/AjaxHandler.php">
	<div id="prepage" style="position: absolute; font-family: arial; font-size: 16px; left: 0px; top: 0px; background-color: white; layer-background-color: white; height: 100%; width: 100%;">
		<table width="100%">
		<tr>
			<td><b>Loading ... ... Please wait!</b></td>
		</tr>
		</table>
	</div>
</ajax>
<?php
     Ajax::init();
?>
<!--
<script type="text/javascript" src="js/ajax_sample.js"></script>
-->
</form>
</body>
</html>