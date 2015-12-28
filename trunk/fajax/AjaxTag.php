<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2012
 */
 require_once("Classes/class.Ajax.php");
?>
<html>
<head>
<title>CallbackHandler Demo</title>
</head>
<body>
<form name="frmMain" method="post">
<input type="text" name="action" />
<input type="button" name="haha" id="haha" value="Callback 1" onclick="doCallback();" />
<input type="button" name="haha1" value="Callback 2" onclick="doCallbackA();" /><br />
<textarea id="content" name="content" rows="18" cols="85"></textarea>
<ajax id="nam" onLoad="dsf dsfdsds">213 23 213213 21</ajax>
<div>sadsad sad sa</div>
<ajax id="tuan" onRequest="dsf dsfdsds"/>
<?php
	Ajax::init();
?>
<!--
<script type="text/javascript" src="js/ajax_sample.js"></script>
-->
</form>
</body>
</html>