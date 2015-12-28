<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2011
 */

class TagAttributeParser {
	const PATTERN = "/(\\w+)\\s*=\\s*(\"[^\"]*\"|'[^']*'|[^\"'\\s>]*)/";

	public static function parse($attribs) {
	 $org = $attribs = trim($attribs);
	 if (is_null($org) || strlen($org) === 0)
		 return NULL;
	 $matches = NULL;
	 $attrs = NULL;
	 preg_match_all(self::PATTERN, $attribs, $matches, PREG_SET_ORDER);
	 if ($matches !== NULL) {
	 	$attrs = array();
	 	foreach($matches as $match) {
			$attribs = str_replace($match[0], "", $attribs);
			if (($match[2][0] === "\"" || $match[2][0] === "'") && $match[2][0] === $match[2][strlen($match[2]) - 1])
			 	$match[2] = substr($match[2], 1, -1);
			$name = trim(strtolower($match[1]));
			$value = html_entity_decode($match[2]);
			if (self::startsWith($value, "$"))
				$value = trim($value, "$");
			$attrs[$name] = $value;
		}
	 }

	 $attribs = trim($attribs);
	 if ($attribs !== "")
		 throw new Exception("parse: can't parse [$org] - not a properly formed attribute string");
	 return $attrs;
	}

	private static function startsWith($haystack, $needle, $case = TRUE) {
		if($case)
			return strpos($haystack, $needle, 0) === 0;
		return stripos($haystack, $needle, 0) === 0;
	}
}
?>