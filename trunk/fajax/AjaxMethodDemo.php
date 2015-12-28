<?php

    /**
     *
     *
     * @version $Id$
     * @copyright 2007
     */

    require_once('Classes\class.AjaxMethod.php');

    /**
    * @AjaxMethod
    */
    function getFullDateTime() {
        return "<date>" . date("D M j G:i:s T Y") . "</date>";
    }

    /**
    * @AjaxMethod
    */
    function getFullTime() {
        //throw new Exception("ah ha ha");
        return "<time>" . date("D M j G:i:s T Y") . "</time>";
    }

    /**
    * @AjaxMethod
    */
    function getRequestTime() {
        //throw new Exception("ah ha ha, what's the time?");
        return "<reqTime>" . $_SERVER["REQUEST_TIME"] . "</reqTime>";
    }

    /**
    * @AjaxMethod
    */
    function Operator($op, $a, $b){
        try {
            switch($op) {
                case "+":
                    return ($a + $b);
                case "-":
                    return ($a - $b);
                case "*":
                    return ($a * $b);
                case "/":
                    if((int)$b === 0)
                        throw new Exception("Division by zero exception.");
                    return ($a / $b);
                default:
                    return "Invalid Operator";
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
    * @AjaxMethod(Async)
    */
    function getServerVariables(){
        $s = "";
        foreach($_SERVER as $key => $value)
            $s .= "$key=$value\r\n";
        return $s;
    }

	/**
	*
	* @AjaxMethod
	*/
    function getFullName($addr, $firstname, $middlename, $lastname){
		return "fullname: " . $firstname . $middlename . $lastname . "|Address: " . $addr;
    }

    /**
    * @AjaxMethod
    */
    function getReqVars(){
        $s = "";
        foreach($_POST as $key => $value)
            $s .= "$key=$value\r\n";
        foreach($_GET as $key => $value)
            $s .= "$key=$value\r\n";
        foreach($_COOKIE as $key => $value) {
            if(is_array($value)) {
                foreach($value as $subK => $subV)
                    $s .= "$key [$subK]=$subV\r\n";
            }
            else
                $s .= "$key=$value\r\n";
        }
        if(!isset($_COOKIE["calledMethod"]));
            setcookie("[calledMethod]", "getReqVars");
        return $s;
    }

    // after the page reloads, print them out
    if (!isset($_COOKIE['cookie'])) {
        // set the cookies
        setcookie("cookie[three]", "cookiethree");
        setcookie("cookie[two]", "cookietwo");
        setcookie("cookie[one]", "cookieone");
    }
?>
<html>
<head>
    <title>AjaxMethod Demo</title>
    <script language="javascript">
        /**
         *
         * @access public
         * @return void
         **/
        function get_FullDateTime(){
            alert(getFullDateTime());
        }
        function get_FullTime(){
            alert(getFullTime());
        }
        function get_RequestTime(){
            alert(getRequestTime());
        }

        /**
         *
         * @access public
         * @return void
         **/
        function opt(op){
            var numA = document.getElementById("numA").value;
            var numB = document.getElementById("numB").value;
            var numRes = document.getElementById("numRes");
            numRes.value = Operator(op, numA, numB);
        }
        /**
         *
         * @access public
         * @return void
         **/
        function get_ServerVars(){
            var el = document.getElementById("oServArea");
            el.value = getServerVariables();
            //getServerVariables();
        }
        function get_ReqVars(){
            var el = document.getElementById("oServArea");
            el.value = getReqVars();
        }

        /**
         *
         * @access public
         * @return void
         **/
        function CallComplete(arg) {
            //alert(arg.Content);
            var el = document.getElementById("oServArea");
            el.value = arg.content;
        }
        function ExtCallComplete(arg) {
            alert(arg.calleeMethod);
        }
    </script>
</head>
<body>
<input type="button" name="callDate" value="Get Full DateTime" onclick="get_FullDateTime();" />
<input type="button" name="callTime" value="Get Full Time" onclick="get_FullTime();" />
<input type="button" name="requestTime" value="Get Request Time" onclick="get_RequestTime();" />
<input type="button" name="requestTime" value="Get Full Name" onclick="getFullName('20 Nguyen Van Cu', 'Tran', 'Anh', 'Tuan');" />
<br />
<div style="padding: 10px; width: 280px; text-align: right">
Num A:&nbsp;<input type="text" name="numA" id="numA" size="5">&nbsp;&nbsp;&nbsp;&nbsp;
Num B:&nbsp;<input type="text" name="numB" id="numB" size="5"><br />
Result:&nbsp;<input type="text" name="numRes" id="numRes" size="25"><br />
<input type="button" name="optAdd" value="+" onclick="opt('+');" />
<input type="button" name="optSubs" value="-" onclick="opt('-');" />
<input type="button" name="optMulti" value="*" onclick="opt('*');" />
<input type="button" name="optDivi" value="/" onclick="opt('/');" />
<input type="button" name="optDivi1" value="Get Fulle DateTime" onclick="alert(_getFullDateTime());" />
</div>
<div>
    <div style="text-align: right">
        <input type="button" id="btnGetServOject" value="Get Server Veriables" onclick="get_ServerVars()">
        <input type="button" id="btnGetReqVars" value="Get Request Veriables" onclick="get_ReqVars()">
    </div>
    <textarea id="oServArea" style="width: 100%; height: 280px;"></textarea>
    <?php
        AjaxMethod::$OnComplete = "ExtCallComplete";
        AjaxMethod::$Debug = TRUE;
        AjaxMethod::RegExtAjaxMethods("AjaxMethodHandler.php", array("_getFullDateTime", "_getFullTime"));
    ?>
</div>
<?php
    AjaxMethod::$OnComplete = "CallComplete";
    AjaxMethod::$Debug = TRUE;
    AjaxMethod::RegAjaxMethods();
?>
</body>
</html>