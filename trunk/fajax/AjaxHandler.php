<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */
     require_once("Classes\class.AjaxHandler.php");

	/**
	* @AjaxHandler(cb)
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
	        echo("  <" . $sender->AjaxId . ">\n");
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
            echo("  </" . $sender->AjaxId. ">\n");
        }
        echo("</root>");
    }

	/**
	* @AjaxHandler(cbA)
	*/
    function doRequestA($sender, $e) {
        echo("<script language=\"javascript\">console.log(\"Content 2 was processed.\");</script>");
        echo("<script language=\"javascript\">var el = document.getElementById(\"haha\"); if(el) el.value=\"he he, hi hi, hu hu\";</script>");
        echo("<script language=\"javascript\" src=\"js/ajax_sample.js\" global=\"true\"></script>");
		echo("<person>\n");
		echo("  <name>Your Name</name>\n");
		echo("</person>");
    }
?>
<html>
<head>
	<title>Handling External Ajax Request</title>
</head>
<body>
	<div>Hi! I'm from external url. Are you happy?</div>
</body>
</html>