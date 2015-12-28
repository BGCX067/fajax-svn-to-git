<?php

require_once("class.AjaxEventArgs.php");
require_once("class.AnnotationFunction.php");

AjaxHandler::init();

class AjaxHandler {
    private static $_AJAX_HELPER = NULL;
    private $_AJAX_ID = NULL;
    private $_AJAX_CONTENT = NULL;
    private $_AJAX_HANDLERS = NULL;
    private $_AJAX_POST_PARAMETERS = NULL;

    private function __construct() {
        $this->_AJAX_HANDLERS = null;
        if(self::isAjaxRequest()) {
            $this->AjaxId = trim($_REQUEST["Ajax_RequestID"]);
            $this->_AJAX_HANDLERS = $this->findHandlers();
        }
    }

    public function __destruct() {
        if(self::isAjaxRequest())
	        $this->flush();
        else
            ob_end_clean();
    }

    public function __get($propertyName) {
        if($propertyName !== "AjaxId")
            throw new Exception("Invalid property \"$propertyName\"!");
        if(method_exists($this, "get" . $propertyName))
            return call_user_func(array($this, "get" . $propertyName));
        else {
            if($propertyName === "AjaxId")
                return $this->_AJAX_ID;
        }
    }

    public function __set($propertyName, $value) {
        if($propertyName !== "AjaxId")
            throw new Exception("Invalid property \"$propertyName\"!");
        if(method_exists($this, "set" . $propertyName))
            return call_user_func(array($this, "set" . $propertyName));
        else {
            if($propertyName === "AjaxId")
                $this->_AJAX_ID = $value;
        }
    }

    public function exists($ajaxId) {
	    if ($this->_AJAX_HANDLERS == NULL)
		    return FALSE;
        if(array_key_exists($ajaxId, $this->_AJAX_HANDLERS))
            return TRUE;
        return FALSE;
    }

    public function getHandlers() {
		return $this->_AJAX_HANDLERS;
	}

    public function hasHandler($ajaxId) {
        if(!$this->exists($ajaxId))
            return FALSE;
        $handler = $this->_AJAX_HANDLERS[$ajaxId];
        if($handler === NULL)
            return FALSE;
        return TRUE;
    }

    private function flush() {
		$arguments = $this->getAjaxArguments();
		$this->handleAjaxRequest($arguments);
		if($this->_AJAX_CONTENT == NULL)
			$this->_AJAX_CONTENT = "";
		ob_clean();
		header("Content-type: text/xml");
		echo($this->_AJAX_CONTENT);
		ob_end_flush();
	}

    private function getAjaxArguments() {
        $sAjaxArgVar = "Ajax_" . $this->AjaxId . "_Args";
        $arguments = array();
        if($_REQUEST !== NULL && is_array($_REQUEST) && is_array($_REQUEST[$sAjaxArgVar]))
            $arguments = $_REQUEST[$sAjaxArgVar];
        return $arguments;
    }

    private function getRequestParameters() {
        $parameters = array();
        if($_REQUEST !== NULL && is_array($_REQUEST)) {
            $pattern = "Ajax_" . $this->AjaxId . "_RequestParam_";
            foreach($_REQUEST as $key => $value) {
                if(strpos($key, $pattern) === 0) {
                    $subKey = substr($key, strlen($pattern));
                    $parameters[$subKey] = $value;
                }
            }
        }
        return $parameters;
    }

    private function handleAjaxRequest($arguments) {
        $parameters = $this->getRequestParameters();
        $oArgs = new AjaxEventArgs($arguments, $parameters);
        ob_clean();
        $ex = NULL;
        try {
	        $this->onAjaxRequest($oArgs);
        }
        catch(Exception $e) {
			$ex = $e;
		}

		if ($ex != NULL)
			$this->_AJAX_CONTENT = $this->errorFilter($ex);
		else
	        $this->_AJAX_CONTENT = $this->ajaxFilter();
    }

    private function onAjaxRequest($e) {
        if($this->hasHandler($this->AjaxId))
            call_user_func($this->_AJAX_HANDLERS[$this->AjaxId], $this, $e);
    }

    private function ajaxFilter() {
        $buffer = NULL;
        $error = NULL;
        $content = NULL;

		try {
			$buffer = ob_get_contents();
		}
		catch(Exception $e) {
			$error = $this->toErrorXml($e);
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

    private function errorFilter($e) {
	    $error = $this->toErrorXml($e);
        $content = "<error><![CDATA[";
        $content .= str_replace("]]>", "\$\$\$AJAX_CDATA_CLOSE\$\$\$", $error);
        $content .= "]]></error>";

        return $content;
	}

    private function toErrorXml($e, $root = NULL) {
		$error = "<error>";
		$error .= "<code>" . $e->getCode() . "</code>";
		$error .= "<file>" . $e->getFile() . "</file>";
		$error .= "<line>" . $e->getLine() . "</line>";
		$error .= "<message>" . $e->getMessage() . "</message>";
		$count = 0;
		while(($e = $e->getPrevious()) != NULL) {
			$error .= "<error>";
			$error .= "<code>" . $e->getCode() . "</code>";
			$error .= "<file>" . $e->getFile() . "</file>";
			$error .= "<line>" . $e->getLine() . "</line>";
			$error .= "<message>" . $e->getMessage() . "</message>";
			$count++;
		}

		for($i = 0; $i < $count; $i++)
			$error .= "</error>";

		$error .= "</error>";

		return $error;
	}

	private function findHandlers() {
		$methods = get_defined_functions();
		$methods = $methods["user"];
		$handlers = array();
		if(is_array($methods)) {
			$ref = NULL;
			foreach($methods as $method) {
				$ref = new AnnotationFunction($method);
				if($ref->hasAnnotation("AjaxHandler")) {
					$ajaxId = $ref->getAnnotation("AjaxHandler");
					if (is_array($ajaxId))
						$ajaxId = $ajaxId[0];
					if ($ajaxId != NULL && trim($ajaxId) != "")
						$handlers[$ajaxId] = $ref->getName();
				}
			}
		}
		if(count($handlers) === 0)
			$handlers = NULL;
		return $handlers;
	}

    public static function init() {
	    AjaxHandler::beginAjaxRequest();

        if(!isset(self::$_AJAX_HELPER)) {
            $c = __CLASS__;
            self::$_AJAX_HELPER = new $c;
        }
        return self::$_AJAX_HELPER;
    }

    private static function beginAjaxRequest() {
		if(self::isAjaxRequest())
			ob_start();
	}

    private static function isAjaxRequest() {
		$requester = isset($_REQUEST["Ajax_RequestID"]) ? $_REQUEST["Ajax_RequestID"] : NULL;
		if($requester == NULL || trim($requester) == "")
		    return FALSE;
		return TRUE;
    }

    public function __sleep() {}
}
?>