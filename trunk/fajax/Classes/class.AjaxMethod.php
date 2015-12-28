<?php

require_once("class.AnnotationFunction.php");

$__AJAX_METHOD_SCRIPT_REGED__ = FALSE;
$__INTERNAL_AJAX_METHODS_REGED__ = FALSE;

AjaxMethod::beginAjaxMethod();


class AjaxMethod {
	public static $Debug = FALSE;
	public static $OnLoading = NULL;
	public static $OnLoaded = NULL;
	public static $OnInteractive = NULL;
	public static $OnAbort = NULL;
	public static $OnComplete = NULL;
	public static $OnError = NULL;

	private static $_INSTANCE = NULL;
	private $_SCRIPT_PATH = NULL;

	/**
	 * Constructor
	 * @access private
	 */
	private function __construct() {
		GLOBAL $__AJAX_METHOD_SCRIPT_REGED__;
		if($__AJAX_METHOD_SCRIPT_REGED__ === FALSE)
			$this->_SCRIPT_PATH = $this->GetAbsoluteApp() . "Scripts/fsoft.ajax.method.js";
	}

	public function __destruct() {
		if(self::IsAjaxCall()) {
			$arParams = self::GetMethodParameters();
			$iNumParams = 0;
			if($arParams !== NULL && is_array($arParams))
				$iNumParams = count($arParams);
			$methodName = $_REQUEST["AjaxMethod"];
			if(function_exists($methodName)) {
				$methodInfo = new ReflectionFunction($methodName);
				$arMethodParams = $methodInfo->getParameters();
				if(is_array($arMethodParams) && count($arMethodParams) === $iNumParams) {
					$arParamObjects = NULL;
					if($arParams !== NULL) {
						$arParamObjects = array();
						for($i = 0; $i < $iNumParams; $i++)
							$arParamObjects[$i] = $arParams[$i];
					}
					if($iNumParams === 0)
						$oReturnValue = $methodInfo->invoke($arParamObjects);
                    else
						$oReturnValue = $methodInfo->invokeArgs($arParamObjects);
					$sResponse = $oReturnValue === NULL ? "" : (string)$oReturnValue;
					ob_clean();
					header("Content-type: text/xml");
					$content = "<return><![CDATA[";
					$content .= str_replace("\]\]\>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $sResponse);
					$content .= "]]></return>";
					echo($content);
					ob_end_flush();
				}
			}
		}
	}

	private static function Initialize() {
		if(!isset(self::$_INSTANCE) && !is_object(self::$_INSTANCE)) {
			$c = __CLASS__;
			self::$_INSTANCE = new $c;
		}

		return self::$_INSTANCE;
	}

	public static function beginAjaxMethod() {
		if(self::IsAjaxCall())
			ob_start();
	}

	private static function IsAjaxCall() {
		if($_REQUEST === NULL || !is_array($_REQUEST) || count($_REQUEST) === 0)
			return FALSE;
		$method = NULL;
		if (array_key_exists("AjaxMethod", $_REQUEST))
			$method = $_REQUEST["AjaxMethod"];
		$method = trim($method);
		if($method === NULL || $method === "")
			return FALSE;
		return TRUE;
	}

	public static function RegAjaxMethods() {
		GLOBAL $__AJAX_METHOD_SCRIPT_REGED__;
		GLOBAL $__INTERNAL_AJAX_METHODS_REGED__;
		if($__INTERNAL_AJAX_METHODS_REGED__ === TRUE)
			throw new Exception("AjaxMethod::RegAjaxMethods(): You cannot call this method more than one time in a page.");
		$ajax = self::Initialize();
		$methods = $ajax->GetMethods();
		if($methods !== NULL && is_array($methods)) {
			if($__AJAX_METHOD_SCRIPT_REGED__ !== TRUE) {
				$script .= "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $ajax->_SCRIPT_PATH . "\"></script>\n";
				$__AJAX_METHOD_SCRIPT_REGED__ = TRUE;
			}
			$script = "<script language=\"javascript\" type=\"text/javascript\">\n//<![CDATA[\n\n";
			$id = self::GetUniqueID();
			$script .= sprintf("window.%s = new AjaxServiceNameSpace.AjaxMethod(\"%s\");\n", $id, $id);
			if(self::$Debug)
				$script .= sprintf("%s.debug = true;\n", $id);
			if (self::$OnLoading)
				$script .= sprintf("%s.onLoading = %s;", $id, self::$OnLoading);
			if (self::$OnLoaded)
				$script .= sprintf("%s.onLoaded = %s;", $id, self::$OnLoaded);
			if (self::$OnInteractive)
				$script .= sprintf("%s.onInteractive = %s;", $id, self::$OnInteractive);
			if (self::$OnAbort)
				$script .= sprintf("%s.onAbort = %s;", $id, self::$OnAbort);
			if(self::$OnComplete !== NULL && trim(self::$OnComplete) !== "")
				$script .= sprintf("%s.onComplete = %s;\n", $id, self::$OnComplete);
			if(self::$OnError !== NULL && trim(self::$OnError) !== "")
				$script .= sprintf("%s.onError = %s;\n", $id, self::$OnError);
			foreach($methods as $method => $async) {
				if($async)
					$script .= sprintf("window.%s = function() { return %s.serviceRequest(\"%s\", arguments, true); }\n", $method, $id, $method);
				else
					$script .= sprintf("window.%s = function() { return %s.syncServiceRequest(\"%s\", arguments, true); }\n", $method, $id, $method);
			}
			$script .= "\n//]]>\n</script>\n";
			echo($script);
			$__INTERNAL_AJAX_METHODS_REGED__ = TRUE;
		}
	}

	public static function RegExtAjaxMethods($servicePath, $methods) {
		GLOBAL $__AJAX_METHOD_SCRIPT_REGED__;
		$ajax = self::Initialize();
		if(is_array($methods)) {
			if($__AJAX_METHOD_SCRIPT_REGED__ !== TRUE) {
				$script = "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $ajax->_SCRIPT_PATH . "\"></script>\n";
				$__AJAX_METHOD_SCRIPT_REGED__ = TRUE;
			}
			$script .= "<script language=\"javascript\" type=\"text/javascript\">\n//<![CDATA[\n\n";
			$id = self::GetUniqueID();
			$script .= sprintf("window.%s = new AjaxServiceNameSpace.AjaxMethod(\"%s\");\n", $id, $id);
			$script .= sprintf("%s.servicePath = \"%s\";\n", $id, $servicePath);
			if(self::$Debug)
				$script .= sprintf("%s.debug = true;\n", $id);
			if (self::$OnLoading)
				$script .= sprintf("%s.onLoading = %s;", $id, self::$OnLoading);
			if (self::$OnLoaded)
				$script .= sprintf("%s.onLoaded = %s;", $id, self::$OnLoaded);
			if (self::$OnInteractive)
				$script .= sprintf("%s.onInteractive = %s;", $id, self::$OnInteractive);
			if (self::$OnAbort)
				$script .= sprintf("%s.onAbort = %s;", $id, self::$OnAbort);
			if(self::$OnComplete !== NULL && trim(self::$OnComplete) !== "")
				$script .= sprintf("%s.onComplete = %s;\n", $id, self::$OnComplete);
			if(self::$OnError !== NULL && trim(self::$OnError) !== "")
				$script .= sprintf("%s.onError = %s;\n", $id, self::$OnError);
			foreach($methods as $method => $async) {
				if (is_string($method)) {
					if($async)
						$script .= sprintf("window.%s = function() { return %s.serviceRequest(\"%s\", arguments, true); }\n", $method, $id, $method);
					else
						$script .= sprintf("window.%s = function() { return %s.syncServiceRequest(\"%s\", arguments, true); }\n", $method, $id, $method);
				}
				else
					$script .= sprintf("window.%s = function() { return %s.syncServiceRequest(\"%s\", arguments, true); }\n", $async, $id, $async);
			}
			$script .= "\n//]]>\n</script>\n";
			echo($script);
		}
	}

	public static function HandleAjaxRequest() {
		if(self::IsAjaxCall()) {
			$arParams = self::GetMethodParameters();
			$iNumParams = 0;
			if($arParams !== NULL && is_array($arParams))
				$iNumParams = count($arParams);
			$methodName = $_REQUEST["AjaxMethod"];
			if(function_exists($methodName)) {
				$methodInfo = new ReflectionFunction($methodName);
				$arMethodParams = $methodInfo->getParameters();
				if(is_array($arMethodParams) && count($arMethodParams) === $iNumParams) {
					$arParamObjects = NULL;
					if($arParams !== NULL) {
						$arParamObjects = array();
						for($i = 0; $i < $iNumParams; $i++)
							$arParamObjects[$i] = $arParams[$i];
					}
					if($iNumParams === 0)
						$oReturnValue = $methodInfo->invoke($arParamObjects);
					else
						$oReturnValue = $methodInfo->invokeArgs($arParamObjects);
					$sResponse = $oReturnValue === NULL ? "" : (string)$oReturnValue;
					ob_clean();
					header("Content-type: text/xml");
					$content = "<return><![CDATA[";
					$content .= str_replace("\]\]\>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $sResponse);
					$content .= "]]></return>";
					echo($content);
					ob_end_flush();
				}
			}
		}
		else
			ob_end_clean();
	}

	private function GetMethods() {
		$methods = get_defined_functions();
		$methods = $methods["user"];
		$ajaxMethods = array();
		if(is_array($methods)) {
			$ref = NULL;
			foreach($methods as $method) {
				$ref = new AnnotationFunction($method);
				if($ref->hasAnnotation("AjaxMethod")) {
					if ($ref->hasAnnotationValue("AjaxMethod", "Async", TRUE))
						$ajaxMethods[$ref->getName()] = TRUE;
					else
						$ajaxMethods[$ref->getName()] = FALSE;
				}
			}
		}
		if(count($ajaxMethods) === 0)
			$ajaxMethods = NULL;
		return $ajaxMethods;
	}

	private static function GetMethodParameters() {
		$sMethodParamVar = "AjaxMethod_Param";
		$arParams = array();
		if($_REQUEST !== NULL && is_array($_REQUEST))
			$arParams = $_REQUEST[$sMethodParamVar];
		return $arParams;
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

	private static function GetUniqueID() {
		$token = self::gen_uuid();
		return ("AjaxMethod_" . $token);
	}

	private static function gen_uuid($len=8) {
		$hex = md5("AjaxMethod" . uniqid("", true));
		$pack = pack("H*", $hex);
		$tmp =  base64_encode($pack);
		$uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);
		$len = max(4, min(128, $len));
		while (strlen($uid) < $len)
			$uid .= gen_uuid(22);
		return substr($uid, 0, $len);
	}

    public function __sleep() {}
}
?>