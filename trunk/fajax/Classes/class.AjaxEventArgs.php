<?php

class AjaxEventArgs {
	private $properties = array(
		"Arguments" => NULL,
		"Parameters" => NULL
	);

	public function __construct($arguments = NULL, $parameters = NULL) {
		$this->Arguments = $arguments;
		$this->Parameters = $parameters;
	}

	public function __get($propertyName) {
		if (!array_key_exists($propertyName, $this->properties))
			throw new Exception("Invalid property \"$propertyName\"!");
		if (method_exists($this, "get" . $propertyName))
			return call_user_func(array($this, "get" . $propertyName));
		else
			return $this->properties[$propertyName];
    }

	public function __set($propertyName, $value) {
		if (!array_key_exists($propertyName, $this->properties))
			throw new Exception("Invalid property \"$propertyName\"!");
		if (method_exists($this, "set" . $propertyName))
			return call_user_func(array($this, "set" . $propertyName), $value);
		else
			$this->properties[$propertyName] = $value;
	}
}
?>