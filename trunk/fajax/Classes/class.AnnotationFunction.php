<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2012
 */


class AnnotationFunction extends \ReflectionFunction {
	const SINGLE_PATTERN = "/@(\w+)\s*[=|:]\s*(\"[^\"]*\"|'[^']*'|[^\"'\s>]*)/";
	const MULTI_PATTERN = "/@(\w+)\s*(\([^\(]*\))/";
	//const PROPERTY_PATTERN = "/(\w+)\s*[=|:]\s*(\"[^\"]*\"|'[^']*'|[^\"'\s>]*)/";
	const PROPERTY_PATTERN = "/\s*(\w+|[^=,]+)\s*[=|:]\s*(\"(?:\\\.|[^\"\\\]+)*\"|(?:\\\.|[^,\"\\\]+)*)/";
	const VALUE_PATTERN = "/\s*(\w+|[^=,]+)(\"(?:\\\.|[^\"\\\]+)*\"|(?:\\\.|[^,\"\\\]+)*)/";
	const EMPTY_PATTERN = "/@(\w+)(\s*|\r|\n)/";
	const DESC_PATTERN = "/(\/\*)|(\*\/)|(\*\s*)/";

	protected $annotations;
	protected $funcDesc;

	public function __construct($func) {
		parent::__construct($func);
		$this->annotations = array();
		$this->extractAnnotations();
	}

	protected function extractAnnotations() {
		$doc = $this->getDocComment();
		$this->extractSingleValueAnnotations($doc);
		$this->extractMultiValueAnnotations($doc);
		$this->extractEmptyAnnotations($doc);
		$this->funcDesc = preg_replace(self::DESC_PATTERN, "", $doc);
	}

	protected function extractEmptyAnnotations(&$doc) {
		if (is_null($doc) || strlen($doc) === 0)
			return;
		$matches = NULL;
		preg_match_all(self::EMPTY_PATTERN, $doc, $matches, PREG_SET_ORDER);
		if ($matches !== NULL) {
			foreach($matches as $match) {
				$doc = str_replace($match[0], "", $doc);
				$this->annotations[strtolower($match[1])] = NULL;
			}
		 }
	}

	protected function extractSingleValueAnnotations(&$doc) {
		if (is_null($doc) || strlen($doc) === 0)
			return;
		$matches = NULL;
		preg_match_all(self::SINGLE_PATTERN, $doc, $matches, PREG_SET_ORDER);
		if ($matches !== NULL) {
			foreach($matches as $match) {
				$doc = str_replace($match[0], "", $doc);
				if (($match[2][0] === "\"" || $match[2][0] === "'") && $match[2][0] === $match[2][strlen($match[2]) - 1])
					$match[2] = substr($match[2], 1, -1);
				$name = trim(strtolower($match[1]));
				$value = html_entity_decode($match[2]);
				$this->annotations[$name] = $value;
			}
		 }
	 }

	protected function extractMultiValueAnnotations(&$doc) {
		if (is_null($doc) || strlen($doc) === 0)
			return;
		$matches = NULL;
		preg_match_all(self::MULTI_PATTERN, $doc, $matches, PREG_SET_ORDER);
		if ($matches !== NULL) {
			foreach($matches as $match) {
				$doc = str_replace($match[0], "", $doc);
				if ($match[2][0] === "(" && $match[2][strlen($match[2]) - 1] === ")")
					$match[2] = substr($match[2], 1, -1);
				$name = trim(strtolower($match[1]));
				$value = html_entity_decode($match[2]);
				$this->annotations[$name] = $this->parse($value);
			}
		}
	}

	private function parse(&$attribs) {
		$attribs = trim($attribs);
		if (is_null($attribs) || strlen($attribs) === 0)
			return array();
		$matches = NULL;
		$attrs = NULL;
		preg_match_all(self::PROPERTY_PATTERN, $attribs, $matches, PREG_SET_ORDER);
		if ($matches !== NULL && count($matches) > 0) {
			$attrs = array();
			foreach($matches as $match) {
				$attribs = str_replace($match[0], "", $attribs);
				if (($match[2][0] === "\"" || $match[2][0] === "'") && $match[2][0] === $match[2][strlen($match[2]) - 1])
					$match[2] = substr($match[2], 1, -1);
				$name = trim(strtolower($match[1]));
				$value = html_entity_decode($match[2]);
				$attrs[$name] = $value;
			}
		}
		else {
			preg_match_all(self::VALUE_PATTERN, $attribs, $matches, PREG_SET_ORDER);
			if ($matches !== NULL && count($matches) > 0) {
				$attrs = array();
				foreach($matches as $match) {
					if (($match[1][0] === "\"" || $match[1][0] === "'") && $match[1][0] === $match[1][strlen($match[2]) - 1])
						$match[1] = substr($match[1], 1, -1);
					$attrs[] = $match[1];
				}
			}
		}

		return $attrs;
	}

	public function getFunctionDescription() {
		return $this->funcDesc;
	}

	public function getAnnotations() {
		return $this->annotations;
	}

	public function getAnnotation($annotation) {
		return $this->annotations[strtolower($annotation)];
	}

	public function getAnnotationProperty($annotation, $property) {
		return $this->annotations[strtolower($annotation)][strtolower($property)];
	}

	public function hasAnnotation($annotation) {
		if (array_key_exists(strtolower($annotation), $this->annotations))
			return TRUE;
		return FALSE;
	}

	public function hasAnnotationProperty($annotation, $property) {
		if (!$this->hasAnnotation($annotation))
			return FALSE;
		$ano = $this->getAnnotation($annotation);
		if (is_array($ano) && array_key_exists(strtolower($property), $ano))
			return TRUE;
		return FALSE;
	}

	public function hasAnnotationValue($annotation, $value, $ignoreCase = FALSE) {
		if (!$this->hasAnnotation($annotation))
			return FALSE;
		$values = $this->getAnnotation($annotation);
		if(is_array($values)) {
			foreach($values as $item) {
				if ($ignoreCase) {
					if (strcasecmp($value, $item) === 0)
						return TRUE;
				}
				else {
					if (strcmp($value, $item) === 0)
						return TRUE;
				}
			}
		}
		else {
			if ($ignoreCase) {
				if (strcasecmp($value, $values) === 0)
					return TRUE;
			}
			else {
				if (strcmp($value, $values))
					return TRUE;
			}
		}

		return FALSE;
	}

	public function __destruct() {
		$this->docComment = NULL;
		unset($this->annotations);
	}
}
?>