<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2012
 */

class AnnotationClass extends \ReflectionClass {
	const SINGLE_PATTERN = "/@(\w+)\s*[=|:]\s*(\"[^\"]*\"|'[^']*'|[^\"'\s>]*)/";
	const MULTI_PATTERN = "/@(\w+)\s*(\([^\(]*\))/";
	//const PROPERTY_PATTERN = "/(\w+)\s*[=|:]\s*(\"[^\"]*\"|'[^']*'|[^\"'\s>]*)/";
	const PROPERTY_PATTERN = "/\s*(\w+|[^=,]+)\s*[=|:]\s*(\"(?:\\\.|[^\"\\\]+)*\"|(?:\\\.|[^,\"\\\]+)*)/";
	const VALUE_PATTERN = "/\s*(\w+|[^=,]+)(\"(?:\\\.|[^\"\\\]+)*\"|(?:\\\.|[^,\"\\\]+)*)/";
	const EMPTY_PATTERN = "/@(\w+)(\s*|\r|\n)/";
	const DESC_PATTERN = "/(\/\*)|(\*\/)|(\*\s*)/";

	private $annotations;

	public function __construct($class) {
		parent::__construct($class);
		$this->annotations = array();
		$this->annotations["class"] = array();
		$this->annotations["properties"] = array();
		$this->extractAnnotations();
	}

	protected function extractAnnotations() {
		$doc = $this->getDocComment();
		$this->annotations["class"]["desc"] = NULL;
		if ($doc !== FALSE) {
			$this->extractSingleValueAnnotations($doc, "class");
			$this->extractMultiPropertyAnnotations($doc, "class");
			$this->extractEmptyAnnotations($doc, "class");
			$this->annotations["class"]["desc"] = trim(preg_replace(self::DESC_PATTERN, "", $doc));
		}

		$properties = $this->getProperties();
		foreach($properties as $property) {
			$doc = $property->getDocComment();
			$name = strtolower($property->getName());
			$this->extractSingleValueAnnotations($doc, "property", $name);
			$this->extractMultiPropertyAnnotations($doc, "property", $name);
			$this->extractEmptyAnnotations($doc, "property", $name);
			$this->annotations["properties"][$name]["desc"] = trim(preg_replace(self::DESC_PATTERN, "", $doc));
		}
	}

	protected function extractEmptyAnnotations(&$doc, $type = "property", $propertyName = NULL) {
		if (is_null($doc) || strlen($doc) === 0)
			return;
		$matches = NULL;
		preg_match_all(self::EMPTY_PATTERN, $doc, $matches, PREG_SET_ORDER);
		if ($matches !== NULL) {
			foreach($matches as $match) {
				$doc = str_replace($match[0], "", $doc);
				if ($type === "class")
					$this->annotations["class"][strtolower($match[1])] = NULL;
				else
					$this->annotations["properties"][$propertyName][strtolower($match[1])] = NULL;
			}
		 }
	}

	protected function extractSingleValueAnnotations(&$doc, $type = "property", $propertyName = NULL) {
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
				if ($type === "class")
					$this->annotations["class"][$name] = $value;
				else
					$this->annotations["properties"][$propertyName][$name] = $value;
			}
		 }
	 }

	protected function extractMultiPropertyAnnotations(&$doc, $type = "property", $propertyName = NULL) {
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
				if ($type === "class")
					$this->annotations["class"][$name] = $this->parse($value);
				else
					$this->annotations["properties"][$propertyName][$name] = $this->parse($value);
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

	public function getAnnotations() {
		return $this->annotations;
	}

	public function hasClassAnnotation($annotation) {
		if (array_key_exists(strtolower($annotation), $this->annotations["class"]))
			return TRUE;
		return FALSE;
	}

	public function hasClassAnnotationProperty($annotation, $property) {
		if (!$this->hasClassAnnotation($annotation))
			return FALSE;
		if (array_key_exists(strtolower($property), $this->annotations["class"][strtolower($annotation)]))
			return TRUE;
		return FALSE;
	}

	public function getClassDescription() {
		return $this->annotations["class"]["desc"];
	}

	public function getClassAnnotation($annotation) {
		return $this->annotations["class"][strtolower($annotation)];
	}

	public function getClassAnnotationProperty($annotation, $property) {
		return $this->annotations["class"][strtolower($annotation)][strtolower($property)];
	}

	public function hasProperty($property) {
		if (array_key_exists(strtolower($property), $this->annotations["properties"]))
			return TRUE;
		return FALSE;
	}

	public function hasPropertyAnnotation($property, $annotation) {
		if (!$this->hasProperty($property))
			return FALSE;
		if (array_key_exists(strtolower($annotation), $this->annotations["properties"][strtolower($property)]))
			return TRUE;
		return FALSE;
	}

	public function hasPropertyAnnotationProperty($property, $annotation, $aproperty) {
		if (!$this->hasPropertyAnnotation($property, $annotation))
			return FALSE;
		if (array_key_exists(strtolower($aproperty), $this->annotations["properties"][strtolower($property)][strtolower($annotation)]))
			return TRUE;
		return FALSE;
	}

	public function getPropertyAnnotation($property, $annotation) {
		return $this->annotations["properties"][strtolower($property)][strtolower($annotation)];
	}

	public function getPropertyAnnotations($property) {
		return $this->annotations["properties"][strtolower($property)];
	}

	public function getPropertyAnnotationProperty($property, $annotation, $aproperty) {
		return $this->annotations["properties"][strtolower($property)][strtolower($annotation)][strtolower($aproperty)];
	}

	public function hasPropertyAnnotationValue($property, $annotation, $value, $ignoreCase = FALSE) {
		if (!$this->hasPropertyAnnotation($property, $annotation))
			return FALSE;
		$values = $this->getPropertyAnnotation($property, $annotation);
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

	public function setPropertyValue($object, $property, $value) {
		if ($property->isProtected() || $property->isPrivate())
			$property->setAccessible(TRUE);
		$property->setValue($object, $value);
	}

	public function getPropertyValue($object, $property) {
		if ($property->isProtected() || $property->isPrivate())
			$property->setAccessible(TRUE);
		return $property->getValue($object);
	}

	public function __destruct() {
		unset($this->annotations);
	}
}
?>