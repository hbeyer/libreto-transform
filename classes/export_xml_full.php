<?php

class export_xml_full extends export {

	function __construct(reconstruction $reconstruction) {
		$this->reconstruction = $reconstruction;
		$this->makeEmptyDOM();
		$root = $this->dom->getElementsByTagName('collection')->item(0);
	    $this->addMetadata($root);
	    $this->addContent($root);
	    $this->dom->saveXML();
	    return(true);
	}

	private function addMetadata($root) {
		$metaElement = $this->dom->createElement('metadata');
		foreach ($this->reconstruction->metadataReconstruction as $field => $value) {
			$element = $this->dom->createElement($field);
			$text = $this->dom->createTextNode($value);
			$element->appendChild($text);
			$metaElement->appendChild($element);
		}
		foreach ($this->reconstruction->catalogues as $catalogue) {
			$catElement = $this->dom->createElement('catalogue');
			$catElement->setAttribute('id', $catalogue->id);
			$catFields = array('title', 'year', 'institution', 'shelfmark', 'base');
			foreach ($catalogue as $field => $value) {
				if (in_array($field, $catFields)) {
					$element = $this->dom->createElement($field);
					$text = $this->dom->createTextNode($value);
					$element->appendChild($text);
					$catElement->appendChild($element);
				}
			}
			foreach ($catalogue->persons as $person) {
				$persElement = $this->dom->createElement('person');
				$text = $this->dom->createTextNode($person->persName);
				$persElement->appendChild($text);
				if ($person->gnd) {
					$persElement->setAttribute('gnd', $person->gnd);
				}
				$catElement->appendChild($persElement);
			}
			foreach ($catalogue->places as $place) {
				$placeElement = $this->dom->createElement('place');
				$text = $this->dom->createTextNode($place->placeName);
				$placeElement->appendChild($text);
				if ($place->geoNames) {
					$placeElement->setAttribute('geoNames', $place->geoNames);
				}
				if ($place->gnd) {
					$placeElement->setAttribute('gnd', $place->gnd);
				}
				if ($place->getty) {
					$placeElement->setAttribute('getty', $place->getty);
				}								
				$catElement->appendChild($placeElement);
			}
			if (isset($catalogue->sections[0])) {
				$sectionList = $this->dom->createElement('sections');
				foreach ($catalogue->sections as $section) {
					$sectionElement = $this->dom->createElement('section');
					$text = $this->dom->createTextNode($section->label);
					$sectionElement->appendChild($text);
					$sectionElement->setAttribute('id', $section->id);
					$sectionList->appendChild($sectionElement);
				}
				$catElement->appendChild($sectionList);
			} 			
			$metaElement->appendChild($catElement);
		}
		$root->appendChild($metaElement);
	}

	private function addContent($root) {

	}
}

?>