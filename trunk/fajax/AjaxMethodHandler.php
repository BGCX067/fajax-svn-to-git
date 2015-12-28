<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */
     require_once("Classes\class.AjaxMethod.php");
     AjaxMethod::HandleAjaxRequest();
    function _getFullDateTime() {
        return "<root><date>" . date("D M j G:i:s T Y") . "</date></root>";
    }
    function _getFullTime() {
        //throw new Exception("ah ha ha");
        return "<time>" . date("D M j G:i:s T Y") . "</time>";
    }
?>