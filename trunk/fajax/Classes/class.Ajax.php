<?php

require_once("class.BrowserInfo.php");
require_once("class.AjaxEventArgs.php");
require_once("class.AnnotationFunction.php");
require_once("class.AjaxTagCompiler.php");

$__AJAX_TAG_SUPPORT__ = FALSE;
$__AJAX_SCRIPT_REGED__ = FALSE;

Ajax::beginAjax();


class Ajax {
    private $properties = array(
        "Id" => NULL,
        "AjaxPanel" => FALSE,
        "RequestUri" => NULL,
        "Content" => NULL,
        "CacheContent" => FALSE,
        "OnTimer" => NULL,
        "RefreshInterval" => -1,
        "Debug" => FALSE,
        "MultiDebug" => FALSE,
        "ClientEvents" => NULL,
        "OnRequest" => NULL,
        "OnLoading" => NULL,
        "OnLoaded" => NULL,
        "OnInteractive" => NULL,
        "OnComplete" => NULL,
        "OnAbort" => NULL,
        "OnError" => NULL,
        "NotifierId" => NULL,
        "LoadingPanelTemplate" => NULL,
        "LoadingPanelFadeDuration" => 2500,
        "LoadingPanelFadeMaxOpacity" => 100,
        "Postback" => NULL,
        "Version" => "2007.1.1617.2",
        "UtilScriptPath" => NULL,
        "ScriptPath" => NULL,
        "CssClass" => NULL
    );

    private $_IS_AJAX_REQUEST = FALSE;
    private $_AJAX_CONTENT = NULL;
    private $_AJAX_HANDLER = NULL;

	public function __construct($id, $ajaxpanel = FALSE) {
    	if($id == null || trim($id) == "")
        	throw new Exception("Ajax::__constructor($id): id cannot be null or empty.");
		$this->Id = $id;
		$this->AjaxPanel = $ajaxpanel;
        GLOBAL $__AJAX_SCRIPT_REGED__;
		if($__AJAX_SCRIPT_REGED__ === FALSE) {
    		$this->ScriptPath = $this->GetAbsoluteApp() . "Scripts/fsoft.ajax.js";
    		$this->UtilScriptPath = $this->GetAbsoluteApp() . "Scripts/fsoft.utils.js";
    	}
    	if($this->IsAjaxRequest())
        	$this->_AJAX_HANDLER = $this->FindHandler();
	}

    public function __destruct() {
	    if ($this->IsAjaxRequest())
			$this->flush();
        else if (self::isAjaxTagSupport())
			ob_end_clean();

		if (isset($properties))
			unset($properties);
		if (isset($_IS_AJAX_REQUEST))
			unset($_IS_AJAX_REQUEST);
		if (isset($_AJAX_CONTENT))
			unset($_AJAX_CONTENT);
		if (isset($_AJAX_HANDLER))
			unset($_AJAX_HANDLER);
    }

	public function __get($propertyName) {
        if(!array_key_exists($propertyName, $this->properties))
            throw new Exception("Invalid property \"$propertyName\"!");
        if(method_exists($this, "get" . $propertyName))
            return call_user_func(array($this, "get" . $propertyName));
        else
            return $this->properties[$propertyName];
    }

    public function __set($propertyName, $value) {
        if(!array_key_exists($propertyName, $this->properties))
            throw new Exception("Invalid property \"$propertyName\"!");
        switch($propertyName) {
            case "Id":
                if($value == null || trim($value) == "")
                    throw new Exception("Id cannot be null or empty.");
                break;
            default:
                break;
        }
        if(method_exists($this, "set" . $propertyName))
            return call_user_func(array($this, "set" . $propertyName), $value);
        else
            $this->properties[$propertyName] = $value;
    }

    public function __isset($propertyName) {
        return isset($this->properties[$propertyName]);
    }

    public function __unset($propertyName) {
        unset($this->properties[$propertyName]);
    }

    public function CausedAjax() {
	    return $this->IsAjaxRequest();
    }

    public function IsAjaxRequest() {
        if(!$this->_IS_AJAX_REQUEST && isset($_REQUEST["Ajax_RequestID"]) && $_REQUEST["Ajax_RequestID"] == $this->Id)
			$this->_IS_AJAX_REQUEST = TRUE;
        return $this->_IS_AJAX_REQUEST;
    }

	public function Render($return = FALSE) {
		if(self::isGlobalAjaxRequest())
			return;

        // write init
        $script = "";
        GLOBAL $__AJAX_SCRIPT_REGED__;
        if($__AJAX_SCRIPT_REGED__ === FALSE) {
	        $script .= "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $this->UtilScriptPath . "\"></script>\n";
            $script .= "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $this->ScriptPath . "\"></script>\n";
            $__AJAX_SCRIPT_REGED__ = TRUE;
        }
        if ($this->AjaxPanel === TRUE) {
			$css = $this->CssClass == NULL ? "" : trim($this->CssClass);
			if ($css != "")
				$css = " class=\"$css\"";
			$script .= sprintf("<div id=\"%s\"%s>%s</div>", $this->Id, $css, $this->Content);
		}
        $script .= "<script type=\"text/javascript\">\n//<![CDATA[\n";
        $script .= "/*** Ajax ".$this->Version . " " . $this->Id . " ***/\n";
        $script .= "window.Ajax_Init_" . $this->Id . " = function() {\n";

        // Include check for whether everything we need is loaded,
        // and a retry after a delay in case it isn't.
        $script .= "if(!window._AJAX_LOADED)\n";
        $script .= "\t{ setTimeout('Ajax_Init_" . $this->Id . "()', 50); return; }\n\n";
        $script .= "window." . $this->Id . " = new Ajax(\"" . $this->Id . "\");\n";
        $url = $this->RequestUri;
        if($url == null)
    		$url = $_SERVER["REQUEST_URI"];            // write properties
        $script .= $this->Id . ".requestUri = \"" . $url . "\";\n";
        $script .= $this->Id . ".requestParamDelimiter = \"" . (count($_REQUEST) > 0 ? "&" : "?") . "\";\n";
        if($this->CacheContent)
            $script .= $this->Id . ".cache = new Object();\n";
        if($this->ClientEvents != null && trim($this->ClientEvents) != "")
            $script .= $this->Id . ".clientEvents = " . $this->ClientEvents . ";\n";
        if($this->OnRequest != null && trim($this->OnRequest) != "")
            $script .= $this->Id . ".onRequest = " . $this->OnRequest . ";\n";
        if($this->OnLoading != null && trim($this->OnLoading) != "")
            $script .= $this->Id . ".onLoading = " . $this->OnLoading . ";\n";
        if($this->OnLoaded != null && trim($this->OnLoaded) != "")
            $script .= $this->Id . ".onLoaded = " . $this->OnLoaded . ";\n";
        if($this->OnInteractive != null && trim($this->OnInteractive) != "")
            $script .= $this->Id . ".onInteractive = " . $this->OnInteractive . ";\n";
        if($this->OnComplete != null && trim($this->OnComplete) != "")
            $script .= $this->Id . ".onComplete = " . $this->OnComplete . ";\n";
        if($this->OnAbort != null && trim($this->OnAbort) != "")
            $script .= $this->Id . ".onAbort = " . $this->OnAbort . ";\n";
        if($this->OnError != null && trim($this->OnError) != "")
            $script .= $this->Id . ".onError = " . $this->OnError . ";\n";
        if($this->Debug)
            $script .= $this->Id . ".debug = true;\n";
        if($this->MultiDebug)
            $script .= $this->Id . ".multiDebug = true;\n";
        if ($this->NotifierId != null && trim($this->NotifierId) != "")
	        $script .= $this->Id . ".notifierId = \"" . $this->notifierId . "\";\n";
        if($this->LoadingPanelTemplate != null && trim($this->LoadingPanelTemplate) != "") {
            $loadingPanel = str_replace("\n", "", $this->LoadingPanelTemplate);
            $loadingPanel = str_replace("\r", "", $loadingPanel);
            $loadingPanel = str_replace("\"", "\\\"", $loadingPanel);
            $script .= $this->Id . ".loadingPanelTemplate = \"" . $loadingPanel . "\";\n";
	        $script .= $this->Id . ".loadingPanelFadeDuration = " . $this->LoadingPanelFadeDuration . ";\n";
	        $script .= $this->Id . ".loadingPanelFadeMaxOpacity = " . $this->LoadingPanelFadeMaxOpacity . ";\n";
        }
        if($this->IsDownLevel())
            $script .= $this->Id . ".isDownLevel = true;\n";
        if($this->Postback != NULL && trim($this->Postback) != "")
            $script .= $this->Id . ".postback = ". $this->Postback . ";\n";
        else
            $script .= $this->Id . ".postback = null;\n";
        if ($this->OnTimer != NULL && trim($this->OnTimer) != "")
	        $script .= $this->Id . ".onTimer = " . $this->OnTimer . ";\n";
        // Do we have a refresh interval?
        if($this->RefreshInterval > 0)
        {
            $script .= $this->Id . ".refreshInterval = " . $this->RefreshInterval . ";\n";
            $script .= $this->Id . ".startTimer();\n";
        }

        $script .= $this->Id . ".init();\n";
        $script .= "}\n";

        // Initiate Ajax creation
        $script .= "Ajax_Init_" . $this->Id . "();\n";

        $script .= "\n//]]>\n</script>\n";
        if ($return)
	        return $script;
	    else
			echo($script);
		unset($script);
		ob_flush();
    }

    private function flush() {
        $arguments = $this->GetAjaxArguments();
        $this->HandleAjaxRequest($arguments);
        if($this->_AJAX_CONTENT == NULL)
            $this->_AJAX_CONTENT = "";
        ob_clean();
        header("Content-type: text/xml");
        echo($this->_AJAX_CONTENT);
        ob_end_flush();
	}

    private function GetAjaxArguments() {
        $sAjaxArgVar = "Ajax_" . $this->Id . "_Args";
        $arguments = array();
        if(array_key_exists($sAjaxArgVar, $_REQUEST))
            $arguments = $_REQUEST[$sAjaxArgVar];
        return $arguments;
    }

    private function GetRequestParameters() {
        $parameters = array();
        if($_REQUEST !== NULL && is_array($_REQUEST)) {
            $pattern = "Ajax_" . $this->Id . "_RequestParam_";
            foreach($_REQUEST as $key => $value) {
                if(strpos($key, $pattern) === 0) {
                    $subKey = substr($key, strlen($pattern));
                    $parameters[$subKey] = $value;
                }
            }
        }
        return $parameters;
    }

    private function HandleAjaxRequest($arguments) {
        $parameters = $this->GetRequestParameters();
        $oArgs = new AjaxEventArgs($arguments, $parameters);
        ob_clean();
        $ex = NULL;
        if($this->_AJAX_HANDLER != NULL) {
            try {
				call_user_func($this->_AJAX_HANDLER, $this, $oArgs);
	        }
	        catch(Exception $e) {
				$ex = $e;
			}
		}

		if ($ex != NULL)
			$this->_AJAX_CONTENT = $this->ErrorFilter($ex);
		else
	        $this->_AJAX_CONTENT = $this->AjaxFilter();
    }

    private function AjaxFilter() {
        $buffer = NULL;
        $error = NULL;
        $content = NULL;

		try {
			$buffer = ob_get_contents();
		}
		catch(Exception $e) {
			$error = $this->ToErrorXml($e);
		}

		if ($error == NULL) {
	        $content = "<content><![CDATA[";
	        $content .= str_replace("]]>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $buffer);
	        $content .= "]]></content>";
		} else {
	        $content = "<error><![CDATA[";
	        $content .= str_replace("]]>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $error);
	        $content .= "]]></error>";
		}

        return $content;
    }

    private function ErrorFilter($e) {
	    $error = $this->ToErrorXml($e);
        $content = "<error><![CDATA[";
        $content .= str_replace("]]>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $error);
        $content .= "]]></error>";

        return $content;
	}

    private function ToErrorXml($e, $root = NULL) {
		$error = "<error>";
		$error .= "<code>" . $e->getCode() . "</code>";
		$error .= "<file>" . $e->getFile() . "</file>";
		$error .= "<line>" . $e->getLine() . "</line>";
		$error .= "<message>" . $e->getMessage() . "</message>";
		$e = $e->getPrevious();
		$i = 0;
		while($e != NULL) {
			$error .= "<error>";
			$error .= "<code>" . $e->getCode() . "</code>";
			$error .= "<file>" . $e->getFile() . "</file>";
			$error .= "<line>" . $e->getLine() . "</line>";
			$error .= "<message>" . $e->getMessage() . "</message>";
			$e = $e->getPrevious();
			$i++;
		}

		$j = 0;
		for($j = 0; $j < $i; $j++)
			$error .= "</error>";

		$error .= "</error>";

		return $error;
	}

	private function FindHandler() {
		$methods = get_defined_functions();
		$methods = $methods["user"];
		$handler = NULL;
		if(is_array($methods)) {
			$ref = NULL;
			foreach($methods as $method) {
				$ref = new AnnotationFunction($method);
				if($ref->hasAnnotation("AjaxHandler")) {
					$ajaxId = $ref->getAnnotation("AjaxHandler");
					if (is_array($ajaxId))
						$ajaxId = $ajaxId[0];
					if ($ajaxId != NULL && trim($ajaxId) == $this->Id) {
						$handler = $ref->getName();
						break;
					}
				}
			}
		}

		return $handler;
	}

    private function IsDownLevel() {
        $browser = new BrowserInfo();
        $iMajorVersion = (int)$browser->Version;
        if(stripos($browser->Name, "Opera") !== FALSE && stripos($browser->Name, "Opera") >= 0 && $iMajorVersion < 8)
            return true;
        else if(// We are good if:
            // 1. We have IE 5 or greater on a non-Mac
            (stripos($browser->Name, "MSIE") !== FALSE && stripos($browser->Name, "MSIE") >= 0 && $iMajorVersion >= 5 && stripos($browser->Platform, "MAC") !== 0) ||

            // 2. We have Gecko-based browser (Mozilla, Netscape 6+)
            (stripos($browser->Name, "Gecko") !== FALSE && stripos($browser->Name, "Gecko") >= 0) ||

            // 3. We have Firefox
            (stripos($browser->Name, "Firefox") !== FALSE && stripos($browser->Name, "Firefox") >= 0 && $iMajorVersion >= 1) ||

            // 4. We have Opera 8 or later
            (stripos($browser->Name, "Opera") !== FALSE && stripos($browser->Name, "Opera") >= 0 && $iMajorVersion >= 8) ||

            // 5. We have safari
            (stripos($browser->Name, "Safari") !== FALSE && stripos($browser->Name, "Safari") >= 0 && $iMajorVersion > 1) ||
            // 6. We have chrome
            (stripos($browser->Name, "Chrome") !== FALSE && stripos($browser->Name, "Chrome") >= 0 && $iMajorVersion > 1)
        )
            return false;
        else
            return true;
    }

    private function GetAbsoluteApp() {
        $url = NULL;
        $host = $_SERVER["HTTP_HOST"];
        if (strpos($host, ":") > 0)
            $host = substr($host, 0, strpos($host, ":"));
        $protocol = $_SERVER["SERVER_PROTOCOL"];
        if(stripos($protocol, "HTTP") === 0)
            $protocol = "http://";
        else
            $protocol = "https://";
        $port = (int)$_SERVER["SERVER_PORT"];
        if($port !== 80)
            $url = $protocol . $host . ":" . $port;
        else
            $url = $protocol . $host;
        $uri = $_SERVER["REQUEST_URI"];
        $i = stripos($uri, "/");
        if($i === 0) {
            $uri = substr($uri, $i + 1);
            $j = stripos($uri, "/");
            if($j !== false && $j > 0)
                $uri = substr($uri, 0, $j);
        }
        else
            $uri = substr($uri, 0, $i);
        $url .= "/" . $uri . "/";
        return $url;
    }

    public function __sleep() {}

	public static function init() {
		GLOBAL $__AJAX_TAG_SUPPORT__;
		if ($__AJAX_TAG_SUPPORT__ === TRUE)
			throw new Exception("Ajax::init(): You cannot call this method more than one time in a page.");

		$buffer = NULL;
		try {
			$buffer = ob_get_contents();
		} catch(Exception $e) {}
		ob_clean();

		$runtime = AjaxTagCompiler::init();
		$runtime->parse($buffer);

		$__AJAX_TAG_SUPPORT__ = TRUE;

		return $runtime;
	}

	public static function isAjaxTagSupport() {
		GLOBAL $__AJAX_TAG_SUPPORT__;
		return $__AJAX_TAG_SUPPORT__;
	}

	public static function beginAjax() {
		if (self::isGlobalAjaxRequest()) {
			ob_start();
			return;
		}

		ob_start();
	}

	public static function isGlobalAjaxRequest() {
		$requester = isset($_REQUEST["Ajax_RequestID"]) ? $_REQUEST["Ajax_RequestID"] : NULL;
		if($requester == NULL || trim($requester) == "")
		    return FALSE;
		return TRUE;
	}
}
?>