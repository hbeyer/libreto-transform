<?php

function validateXML($path, $pathSchema, $pathMODS) {
	$xml = new DOMDocument();
	$xml->load($path);
	if($xml == FALSE) {
		return('Das Dokument ist offenbar nicht wohlgeformt.');
	}
	$mods = testIfMODS($xml);
	if($mods == 1) {
		$validMODS = $xml->schemaValidate($pathMODS);
		if($validMODS == TRUE) {
			return('mods');
		}
		else {
			return('Die Validierung des MODS-Dokuments ist fehlgeschlagen.');
		}		
	}
	elseif($mods == 0) {
		$valid = $xml->schemaValidate($pathSchema);
		if($valid == TRUE) {
			return(1);
		}
		else {
			return('Die Validierung gegen das <a href="'.$pathSchema.'" target="_blank">Schema</a> ist fehlgeschlagen.');
		}		
	}
}

function loadXML($path) {
	$xml = new DOMDocument();
	$xml->load($path);
	$metadataNode = $xml->getElementsByTagName('metadata');
	if($metadataNode->item(0)) {
		loadMetadataFromNode($metadataNode->item(0));
	}
	$resultArray = array();
	$nodeList = $xml->getElementsByTagName('item');
	foreach ($nodeList as $node) {
		$item = makeItemFromNode($node);
		$resultArray[] = $item;
	}
	return($resultArray);
}

function loadMetadataFromNode($node) {
	$children = $node->childNodes;
	$metadataFields = array('heading', 'owner', 'ownerGND', 'fileName', 'title', 'base', 'placeCat', 'year', 'institution', 'shelfmark', 'description', 'geoBrowserStorageID');
	$catalogue = new catalogue();
	foreach($children as $child) {
		$field = strval($child->nodeName);
		if(in_array($field, $metadataFields)) {
			$catalogue->$field = $child->nodeValue;
		}
	}
	$catalogueSer = serialize($catalogue);
	$_SESSION['catalogueObject'] = $catalogueSer;
    return($catalogue);
}

function makeItemFromNode($node) {
	$simpleFields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'volumes', 'titleCat', 'titleBib', 'titleNormalized', 'publisher', 'year', 'format', 'histSubject', 'mediaType', 'bound', 'comment');
	$multiValuedFields = array('subjects', 'genres', 'languages', 'copiesHAB');
	$subFieldFields = array('manifestation', 'originalItem', 'work');
	$item = new item;
	$children = $node->childNodes;
	foreach($children as $child) {
		$field = strval($child->nodeName);
		if(in_array($field, $simpleFields)) {
			$item->$field = trim($child->nodeValue);
		}
		elseif(in_array($field, $multiValuedFields)) {
			$item = insertMultiValued($item, $field, $child);
		}
		elseif(in_array($field, $subFieldFields)) {
			$item = insertSubFields($item, $field, $child);
		}
		elseif($field == 'persons') {
			$item = insertPersons($item, $child);
		}
		elseif($field == 'places') {
			$item = insertPlaces($item, $child);			
		}
		unset($field);
	}
	return($item);
}

function insertMultiValued($item, $field, $node) {
	$insert = array();
	$children = $node->childNodes;
	foreach($children as $child) {
		if($child->nodeName != '#text') {
			$insert[] = trim($child->nodeValue);
		}
	}
	$item->$field = $insert;
	return($item);
}

function insertSubFields($item, $field, $node) {
	$insert = array();
	$children = $node->childNodes;
	foreach($children as $child) {
		if($child->nodeName != '#text') {
			$insert[$child->nodeName] = trim($child->nodeValue);
		}
	}
	$item->$field = array_merge($item->$field, $insert);
	return($item);	
}

function insertPersons($item, $node) {
	$children = $node->childNodes;
	foreach($children as $child) {
		if($child->nodeName != '#text') {
			$person = makePersonFromNode($child);
			$item->persons[] = $person;
		}
	}
	return($item);
}

function insertPlaces($item, $node) {
	$children = $node->childNodes;
	foreach($children as $child) {
		if($child->nodeName != '#text') {
			$place = makePlaceFromNode($child);
			$item->places[] = $place;
		}
	}
	return($item);
}

function makePersonFromNode($node) {
	$properties = array('persName', 'gnd', 'gender', 'role');
	$children = $node->childNodes;
	$person = new person;
	foreach($children as $child) {
		$field = strval($child->nodeName);
		if(in_array($child->nodeName, $properties)) {
			$person->$field = trim($child->nodeValue);
		}
		/*elseif($field == 'beacon') {
			$person = insertMultiValued($person, 'beacon', $child);
		}*/
	}
	return($person);
}

function makePlaceFromNode($node) {
	$properties = array('placeName', 'geoNames', 'gnd', 'getty');
	$children = $node->childNodes;
	$place = new place;
	foreach($children as $child) {
		$field = strval($child->nodeName);
		if(in_array($child->nodeName, $properties)) {
			$place->$field = trim($child->nodeValue);
		}
		elseif($field == 'geoData') {
			$place = insertSubFields($place, 'geoData', $child);
		}
	}
	return($place);
}

function testIfMODS($dom) {
	$rootElement = $dom->documentElement;
	$rootTag = $rootElement->tagName;
	if($rootTag == 'modsCollection') {
		return(1);
	}
	else {
		return(0);
	}
}

function transformMODS($fileName) {
	$mods = new DOMDocument();
	$mods->load('upload/files/'.$fileName.'.xml');
	$xsl = new DOMDocument();
	$xsl->load('transformMODS.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
	$dom = $proc->transformToDoc($mods);
	$dom->formatOutput = true;
	$result = $dom->saveXML();
	$handle = fopen('download/'.$fileName.'.xml', "w");
	fwrite($handle, $result, 3000000);
}

?>
