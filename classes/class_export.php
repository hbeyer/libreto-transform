<?php

class export {
	
protected $content;
protected $reconstruction;
protected $dom;

public function save($path) {

	file_put_contents($path, $this->content);
	return(true);

	}

protected function makeEmptyDOM() {
	$this->dom = new DOMDocument('1.0', 'UTF-8');
	$this->dom->formatOutput = true;
	$this->dom->loadXML('<collection xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://bibliotheksrekonstruktion.hab.de/libreto-schema-full.xsd"></collection>');
	}

}

?>