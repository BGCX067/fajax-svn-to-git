<?php
/*
 * Module	: RadTemplate.php
 * Descr	: PHP Version of the KudzuASP Template Engine.
 *          : Modified tag structure for use in Wordpress.
 * Version  : 1.5.2
 */

require_once("class.TagAttributeParser.php");
require_once("class.Ajax.php");

class AjaxTagConst {
	const _RGX_OPEN_TAG 	= "/<\\/?ajax\\\\?[^\\>]*\\\\?>/";
	const _RGX_CLOSE_TAG 	= "/(\\/?>)|(<\\/?ajax>)/";
	const _FORMAL_END_TAG 	= "</ajax>";
	const _TERMED_TAG 		= "/>";
}

class AjaxTagMatchBuilder {
	private $_matches 			= array();
	private $_result  			= array();
	private $_current_offset 	= 0;
	private $_current_length 	= 0;
	private $_last_offset 		= 0;
	private $_last_length 		= 0;
	private $_next_offset 		= 0;
	private $_input 			= "";
	private $_length 			= 0;

	function getCount() {
		return count($this->_result);
	}

	function getInput() {
		return $this->_input;
	}

	function buildMatches($regex, $input) {
		$this->_last_offset = 0;
		$this->_last_length = 0;
		$this->_input = $input;
		$this->_length = strlen($input);

		preg_match_all($regex, $input, $this->_matches);
		$count = count($this->_matches[0]);

		for($i = 0; $i < $count; $i++) {
			$this->_next_offset = $this->_last_offset + $this->_last_length;
			$this->_current_offset = strpos($input, $this->_matches[0][$i], $this->_next_offset);
			$this->_current_length = strlen($this->_matches[0][$i]);
			if($this->_current_offset > $this->_next_offset)
				$this->addNonMatch();
			$this->addMatch();
			$this->_last_offset = $this->_current_offset;
			$this->_last_length = $this->_current_length;
		}

		$this->_next_offset = $this->_last_offset + $this->_last_length;

		if ($this->_next_offset < $this->_length) {
			$this->_current_offset = $this->_length;
			$this->addNonMatch();
		}
		return count($this->_result);
	}

	function addNonMatch() {
		$res = array();
		$len = $this->_current_offset - $this->_next_offset;
		$res["offset"] = $this->_next_offset;
		$res["length"] = $len;
		$res["match"]  = false;
		array_push($this->_result, $res);
	}

	function addMatch() {
		$res = array();
		$res["offset"] = $this->_current_offset;
		$res["length"] = $this->_current_length;
		$res["match"]  = true;
		array_push($this->_result, $res);
	}

	function hasMatches() {
		return (count($this->_result) > 0);
	}

	public function &getMatches() {
		return $this->_result;
	}

	public function &getMatch($index) {
		return $this->_result[$index];
	}

	function getMatchText($match) {
		return substr($this->_input, $match["offset"], $match["length"]);
	}

	public function __destruct() {
		if (isset($this->_matches))
			unset($this->_matches);
		if (isset($this->_result))
			unset($this->_result);
		if (isset($this->_input))
			unset($this->_input);
	}
}

class AjaxTagCompiler {
	private static $_AJAX_TAG_PARSER;
	private $_parse_stack;
	private $_parse_level;
	private $_node_text;
	private $_tag_encountered;

	private function __construct() {
	}

	public function __destruct() {
		if (isset($this->_parse_stack))
			unset($this->_parse_stack);
	}

    public static function init() {
        if(!isset(self::$_AJAX_TAG_PARSER)) {
            $c = __CLASS__;
            self::$_AJAX_TAG_PARSER = new $c;
        }
        return self::$_AJAX_TAG_PARSER;
    }

	private function initParseStack() {
		$this->_parse_stack = array();
		$this->_parse_level = 0;
		$this->_tag_encountered = FALSE;
		$this->_node_text = NULL;
	}

	private function parsePush($node) {
		$this->_parse_level += 1;
		array_push($this->_parse_stack, $node);
	}

	private function parsePop() {
		$result = array_pop($this->_parse_stack);
		$this->_parse_level -= 1;
		return $result;
	}

	private function parsePeek() {
		return $this->_parse_stack[$this->_parse_level];
	}

	public function parse($template) {
		$this->initParseStack();
		$mb = new AjaxTagMatchBuilder();
		$mb->buildMatches(AjaxTagConst::_RGX_OPEN_TAG, $template);

		$count = $mb->getCount();
		for ($i = 0; $i < $count; $i++) {
			$m = $mb->getMatch($i);
			if ($m["match"])
				$this->handleTagMatch($mb->getMatchText($m));
			else
				$this->handleNodeText($mb->getMatchText($m));
		}
	}

	private function isFormalEndTag($sTag) {
		$formal = (strpos($sTag, AjaxTagConst::_FORMAL_END_TAG) === 0);
		return $formal;
	}

	private function isTermedTag($sTag) {
		$isTermed = (strrpos($sTag, AjaxTagConst::_TERMED_TAG) === (strlen($sTag) - strlen(AjaxTagConst::_TERMED_TAG)));
		return $isTermed;
	}

	private function handleTagMatch($match) {
		if ($this->isFormalEndTag($match))
			$this->parseEndTag($match);
		elseif ($this->isTermedTag($match))
			$this->parseTermedTag($match);
		else
			$this->parseBeginTag($match);
	}

	private function handleNodeText($text) {
		if ($this->_tag_encountered)
			$this->_node_text = $text;
		else if (!Ajax::isGlobalAjaxRequest())
			echo($text);
	}

	private function parseTagProperties($match) {
		$temp = preg_replace(AjaxTagConst::_RGX_CLOSE_TAG, "", $match);
		$temp = trim($temp);
		$tagName = NULL;
		$tagAttribs = NULL;
		$fSpaceIndex = strpos($temp, " ");
		if ($fSpaceIndex === FALSE) {
			$tagName = trim($temp);
		}
		else {
			$tagName = substr($temp, 0, $fSpaceIndex);
			$tagAttribs = substr($temp, $fSpaceIndex + 1);
		}

		$attrs = TagAttributeParser::parse($tagAttribs);
		return $attrs;
	}

	private function parseBeginTag($match) {
		$attributes = $this->parseTagProperties($match);
		$id = NULL;
		if (isset($attributes["id"]))
			$id = $attributes["id"];
		if ($id == NULL || trim($id) == "")
			throw new Exception("ajax tag's id attribute cannot be null or empty.");
		$node = new Ajax($id);
		$this->mapAjaxNodeProperties($node, $attributes);
		$this->parsePush($node);
		$this->_tag_encountered = TRUE;
	}

	private function parseEndTag($match) {
		$this->_tag_encountered = FALSE;
		$node = $this->parsePop();
		if ($node->AjaxPanel === TRUE)
			$node->Content = $this->_node_text;
		else
			$node->LoadingPanelTemplate = $this->_node_text;
		$node->Render();
	}

	private function parseTermedTag($match) {
		$attributes = $this->parseTagProperties($match);
		$id = NULL;
		if (isset($attributes["id"]))
			$id = $attributes["id"];
		if ($id == NULL || trim($id) == "")
			throw new Exception("ajax tag's id attribute cannot be null or empty.");
		$node = new Ajax($id);
		$this->mapAjaxNodeProperties($node, $attributes);
		$this->parsePush($node);
		$node->Render();
	}

	private function mapAjaxNodeProperties($node, $properties) {
		foreach($properties as $name => $value) {
			switch($name) {
				case "id":
					$node->ID = $value;
					break;
				case "ajaxpanel":
					$node->AjaxPanel = strcasecmp("true", $value) === 0 ? TRUE : FALSE;
					break;
				case "requestprefix":
					$node->RequestPrefix = $value;
					break;
				case "onrequest":
					$node->OnRequest = $value;
					break;
				case "onloading":
					$node->OnLoading = $value;
					break;
				case "onloaded":
					$node->OnLoaded = $value;
					break;
				case "oninteractive":
					$node->OnInteractive = $value;
					break;
				case "oncomplete":
					$node->OnComplete = $value;
					break;
				case "onabort":
					$node->OnAbort = $value;
					break;
				case "onerror":
					$node->OnError = $value;
					break;
				case "refreshinterval":
					$node->RefreshInterval = $value;
					break;
				case "loadingpaneltemplate":
					$template = $value;
					try {
						$template = $this->parseInclude($value);
					}
					catch(Exception $e) {}
					$node->LoadingPanelTemplate = $template;
					break;
				case "loadingpanelfadeduration":
					$node->LoadingPanelFadeDuration = $value;
					break;
				case "loadingpanelmaxopacity":
					$node->LoadingPanelMaxOpacity = $value;
					break;
				case "debug":
					$node->Debug = strcasecmp($value, "true") === 0 ? TRUE : FALSE;
					break;
				case "cssclass":
					$node->CssClass = $value;
					break;
				default:
					break;
			}
		}
	}

	private function parseInclude($path) {
        ob_start();
        include($path);
        $content = ob_get_contents();
        ob_end_clean();
        $content = str_replace("\r", "", $content);
        $content = str_replace("\n", "", $content);
        return $content;
	}
}
?>