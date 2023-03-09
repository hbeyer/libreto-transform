<?php

class MyDOM {
	
	private $node;

	function __construct($node) {
		if (get_class($node) != 'DOMElement') {
			throw new Exception('Kein gültiges DOMElement übergeben');		
		}
		$this->node = $node;
	}

	public function getChildValues($name) {
		$result = array();
		foreach($this->node->childNodes as $child) {
			if ($child->nodeName == $name) {
				$result[] = trim($child->nodeValue);
			}
		}
		return($result);		
	}

	public function getChildNodes($name) {
		$result = array();
		foreach($this->node->childNodes as $child) {
			if ($child->nodeName == $name) {
				$result[] = new MyDOM($child);
			}
		}
		return($result);
	}

	public function getSingleChildNode($name) {
		foreach($this->node->childNodes as $child) {
			if ($child->nodeName == $name) {
				return(new MyDOM($child));
			}
		}
		return(null);
	}

	public function getChildrenAssoc($names) {
		$result = array();
		foreach ($this->node->childNodes as $child) {
			if (in_array($child->nodeName, $names)) {
				$result[$child->nodeName] = trim($child->nodeValue);
			}
		}
		return($result);
	}

	public function writeTextToObject($object, $property) {
		$object->$property = trim($this->node->nodeValue);
	}

	public function writeChildrenToObject($object, $names) {
		foreach ($this->node->childNodes as $child) {
			$nameChild = $child->nodeName;
			if (in_array($nameChild, $names)) {
				$object->$nameChild = trim($child->nodeValue);
			}
		}
		return($object);
	}

	public function getRepeatedChild($name) {
		$result = array();
		foreach ($this->node->childNodes as $child) {
			if ($child->nodeName == $name) {
				$result[] = trim($child->nodeValue);
			}
		}
		return($result);
	}


	public function writeAttributesToObject($object, $names) {
		foreach ($this->node->attributes as $attribute) {
			$attrName = $attribute->nodeName;
			if (in_array($attrName, $names)) {
				$object->$attrName = trim($attribute->nodeValue);
			}
		}
		return($object);			
	}

	public function getAttributes($names) {
		$result = array();
		foreach ($this->node->attributes as $attribute) {
			$attrName = $attribute->nodeName;
			if (in_array($attrName, $names)) {
				$result[$attrName] = trim($attribute->nodeValue);
			}
		}
		return($result);			
	}

	public function getContent() {
		return(trim($this->node->nodeValue));
	}

	public function getAttribute($name) {
		foreach ($this->node->attributes as $attribute) {
			if ($attribute->nodeName == $name) {
				return(trim($attribute->nodeValue));
			}
		}
		return(null);
	}

}

?>