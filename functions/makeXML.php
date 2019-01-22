<?php

function saveXML($data, $catalogue, $folderName) {
	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->formatOutput = true;
	$rootElement = $dom->createElement('collection');
	$dom = insertMetadataFromCatalogue($dom, $rootElement, $catalogue);
	foreach($data as $item) {
		$itemElement = $dom->createElement('item');
		$itemElement = fillDOMItem($itemElement, $item, $dom);
		$rootElement->appendChild($itemElement);
	}
	$dom->appendChild($rootElement);
	$result = $dom->saveXML();
	$handle = fopen($folderName.'/'.$catalogue->fileName.'.xml', "w");
	fwrite($handle, $result, 3000000);
}

function insertMetadataFromCatalogue($dom, $root, $catalogue) {
	$metadata = $dom->createElement('metadata');
	$metadataFields = array('heading', 'owner', 'ownerGND', 'fileName', 'title', 'base', 'placeCat', 'year', 'institution', 'shelfmark', 'description', 'geoBrowserStorageID');
	foreach($catalogue as $key => $value) {
		if(in_array($key, $metadataFields) and $value) {
			$element = $dom->createElement($key);
			$text = $dom->createTextNode($value);
			$element->appendChild($text);
			$metadata->appendChild($element);
		}
	}
	$root->appendChild($metadata);
	$dom->appendChild($root);
	return($dom);
}

function fillDOMItem($itemElement, $item, $dom) {
	foreach($item as $key => $value) {
		// Fall 1: Variable ist ein einfacher Wert
		if(is_array($value) == FALSE) {
			//$value = replaceAmp($value);
			$itemProperty = $dom->createElement($key);
			$textProperty = $dom->createTextNode($value);
			$itemProperty->appendChild($textProperty);
			
			$itemElement = appendNodeUnlessVoid($itemElement, $itemProperty);
		}
		else {
			$test1 = testIfAssociative($value);
			//Fall 2.0: Variable ist ein assoziatives Array
			if($test1 == 1) {
				$itemArrayProperty = $dom->createElement($key);
				$itemArrayProperty = appendAssocArrayToDOM($itemArrayProperty, $value, $dom);
				$itemElement = appendNodeUnlessVoid($itemElement, $itemArrayProperty);
			}
			elseif($test1 == 0 and isset($value[0])) {
				//Fall 2.1: Variable ist numerisches Array aus einfachen Werten
				if(is_string($value[0]) or is_integer($value[0])) {
					$itemArrayProperty = $dom->createElement($key);
					$fieldName = makeSubfieldName($key);
					$itemArrayProperty = appendNumericArrayToDOM($itemArrayProperty, $value, $dom, $fieldName);
					$itemElement = appendNodeUnlessVoid($itemElement, $itemArrayProperty);
				}
				//Fall 2.2: Variable ist ein numerisches Array aus Objekten
				elseif(is_object($value[0])) {
					$itemObjectProperty = $dom->createElement($key);
					//Iteration über die Variablen des gefundenen Objekts
					foreach($value as $object) {
						$nameObject = get_class($object);
						$objectElement = $dom->createElement($nameObject);
						foreach($object as $objectKey => $objectValue) {
							//Fall 2.2.1: Variable im Objekt ist ein Array
							if(is_array($objectValue)) {
								$objectVariable = $dom->createElement($objectKey);
								$test = testIfAssociative($objectValue);
								//Fall 2.2.1.1: Variable im Objekt ist ein assoziatives Array
								if($test == 1) {
									$objectVariable = appendAssocArrayToDOM($objectVariable, $objectValue, $dom);
								}
								//Fall 2.2.1.2: Variable im Objekt ist ein numerisches Array
								elseif($test == 0) {
									//Generieren eines Namens für das Subfeld, weil Integer in XML nicht akzeptiert werden
									$fieldName = makeSubfieldName($objectKey);
									$objectVariable = appendNumericArrayToDOM($objectVariable, $objectValue, $dom, $fieldName);
								}
								$objectElement = appendNodeUnlessVoid($objectElement, $objectVariable);
							}
							//Fall 2.2.2: Variable im Objekt ist ein Integer oder String
							elseif(is_int($objectValue) or is_string($objectValue)) {
								$objectVariable = $dom->createElement($objectKey);
								$textObjectVariable = $dom->createTextNode($objectValue);
								$objectVariable->appendChild($textObjectVariable);
								
								$objectElement = appendNodeUnlessVoid($objectElement, $objectVariable);
							}
						}
						$itemObjectProperty->appendChild($objectElement);
					}
					$itemElement = appendNodeUnlessVoid($itemElement, $itemObjectProperty);
				}
			}
		}
	}
	return($itemElement);
}

function appendNodeUnlessVoid($parent, $child) {
	if($child->nodeValue != '') {
		$parent->appendChild($child);
	}
	return($parent);
}

function testIfAssociative($array) {
	$result = 'uncertain';
	foreach($array as $key => $value) {
		if(is_string($key)) {
			$result = 1;
		}
		elseif(is_int($key)) {
			$result = 0;
		}
		break;
	}
	return($result);
}

function appendAssocArrayToDOM($parent, $array, $dom) {
	foreach($array as $key => $value) {
		//$value = replaceAmp($value);
		$node = $dom->createElement($key);
		$textNode = $dom->createTextNode($value);
		$node->appendChild($textNode);
		$parent = appendNodeUnlessVoid($parent, $node);
	}
	return($parent);
}

function appendNumericArrayToDOM($parent, $array, $dom, $fieldName = 'subfield') {
	foreach($array as $value) {
		//$value = replaceAmp($value);
		$node = $dom->createElement($fieldName);
		$textNode = $dom->createTextNode($value);
		$node->appendChild($textNode);		
		$parent = appendNodeUnlessVoid($parent, $node);
	}
	return($parent);
}

function makeSubfieldName($fieldName) {
    if ($fieldName == 'copiesHAB') {
        return('copyHAB');
    }
    elseif (substr($fieldName, -1) == 's') {
        return(substr($fieldName, 0, -1));
    }
    elseif ($fieldName == '') {
        return('subfield');
    }
    return($fieldName);
}

function expandBeaconKeys($data) {
	require_once('beaconSources.php');
	foreach($data as $item) {
		foreach($item->persons as $person) {
			$collectLinks = array();
			foreach($person->beacon as $key) {
				$target = $beaconSources[$key]['target'];
				if($target) {
					$collectLinks[] = makeBeaconLink($person->gnd, $target);
				}
			}
			$person->beacon = $collectLinks;
		}
	}
	return($data);
}

?>
