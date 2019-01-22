<?php

function saveSolrXML($data, $catalogue, $fileName) {

    $SOLRArray = makeSOLRArray($data);
    $SOLRArray = addMetaDataSOLR($catalogue, $SOLRArray);
	//The following elements have to be repeated so that SOLR accepts them as multiValued. Delimiter is ";", as defined in flattenItem and resolveLanguages
	$multiValued = array('languages', 'languagesFull', 'genres', 'subjects', 'author', 'contributor');
	$fileNameBare = shortenPath($fileName);
	$folder = '';
	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->formatOutput = true;
	$rootElement = $dom->createElement('add');
	foreach($SOLRArray as $item) {
		$itemElement = $dom->createElement('doc');
		foreach($item as $key => $value) {
			//Repetition of the multi-valued fields
			if(in_array($key, $multiValued)) {
				$values = explode(';', $value);
				foreach($values as $string) {
					$fieldElement = $dom->createElement('field');
					$fieldContent = $dom->createTextNode($string);
					$fieldElement->appendChild($fieldContent);
					$fieldAttribute = $dom->createAttribute('name');
					$fieldAttribute->value = $key;
					$fieldElement->appendChild($fieldAttribute);
					$itemElement->appendChild($fieldElement);
				}
			}
			else {
				$fieldElement = $dom->createElement('field');
				$fieldContent = $dom->createTextNode($value);
				$fieldElement->appendChild($fieldContent);
				$fieldAttribute = $dom->createAttribute('name');
				$fieldAttribute->value = $key;
				$fieldElement->appendChild($fieldAttribute);
				$itemElement->appendChild($fieldElement);
			}
		}
		$rootElement->appendChild($itemElement);
	}
	$dom->appendChild($rootElement);
	$result = $dom->saveXML();
	$handle = fopen($folder.$fileName.'-SOLR.xml', "w");
	fwrite($handle, $result, 3000000);
}

function makeSOLRArray($data) {
	$SOLRArray = array();
	foreach($data as $item) {
		$row = flattenItem($item);
		$row = resolveManifestation($row);
		$row = resolveOriginal($row);
		$row = resolveLanguages($row);
		$row = addNormalizedYear($row);
		$SOLRArray[] = $row;
	}
	return($SOLRArray);
}

function flattenItem($item) {
	$result = array();
	foreach($item as $key => $value) {
		if(is_array($value) == FALSE) {
			if($value) {
				$result[$key] = $value;
			}
		}
		elseif(testArrayType($value) == 'num') {
			// Die folgende Bedingung verhindert leere Felder für genres, subjects und languages
			if($value != array('') and $value != array()) {
				$result[$key] = implode(';', $value);
			}
		}		
		elseif(testArrayType($value) == 'assoc') {
			foreach($value as $key1 => $value1) {
				if($value1) {
					$result[$key1] = $value1; 
				}
			}
		}
		elseif(testArrayType($value) == 'persons') {
			$result = array_merge($result, flattenPersons($value));
		}
		elseif(testArrayType($value) == 'places') {
			$result = array_merge($result, flattenPlaces($value));
		}
	}
	return($result);
}

function flattenPersons($persons) {
	$result = array();
	$collectAuthors = array();
	$collectContributors = array();
	foreach($persons as $person) {
		$gnd = '';
		if($person->gnd) {
			$gnd = '#'.$person->gnd;
			/*if(isset($person->beacon[0])) {
				$fieldNameBeacon = 'beacon_'.$person->gnd;
				$beaconString = resolveBeacon($person->beacon, $person->gnd);
				$result[$fieldNameBeacon] = $beaconString;
			}*/
		}
		if($person->role == 'author') {
			$collectAuthors[] = $person->persName.$gnd;
		}
		else {
			$collectContributors[] = $person->persName.$gnd;
		}
	}
	if(isset($collectAuthors[0])) {
		$result['author'] = implode(';', $collectAuthors);
	}
	if(isset($collectContributors[0])) {
		$result['contributor'] = implode(';', $collectContributors);
	}
	return($result);
}

function flattenPlaces($places) {
	$result = array();
	$count = 1;
	foreach($places as $place) {
		$result['place_'.$count] = $place->placeName;
		if($place->getty) {
			$result['getty_place_'.$count] = $place->getty;
		}
		if($place->geoNames) {
			$result['geoNames_place_'.$count] = $place->geoNames;
		}
		if($place->geoData['lat'] and $place->geoData['long']) {
			$lat = cleanCoordinate($place->geoData['lat']); //Replaces "," by "."
			$long = cleanCoordinate($place->geoData['long']);
			$result['coordinates_place_'.$count] = $lat.','.$long;
		}
		$count++;
	}
	return($result);
}

function testArrayType($array) {
	$result = 'uncertain';
	foreach($array as $key => $value) {
		if(is_string($key)) {
			$result = 'assoc';
			break;
		}
		elseif(is_int($key)) {
			if(isset($value)) {
				if(is_object($value)) {
					if(get_class($value) == 'person') {
						$result = 'persons';
						break;
					}
					elseif(get_class($value) == 'place') {
						$result = 'places';
						break;
					}
				}
				else {
					$result = 'num';
					break;
				}				
			}
		}
	}
	return($result);
}

function resolveManifestation($row) {
	if(!empty($row['systemManifestation']) and !empty($row['idManifestation'])) {
        $reference = new reference($row['systemManifestation'], $row['idManifestation']);
        if ($reference->url) {
            $row['linkManifestation'] = $reference->url;
            $row['nameSystemManifestation'] = $reference->nameSystem;
        }
	}
	return($row);
}

function resolveOriginal($row) {
	if(isset($row['institutionOriginal']) and isset($row['shelfmarkOriginal']) and isset($row['targetOPAC'])) {
		$searchString = $row['shelfmarkOriginal'];
		if(isset($row['searchID'])) {
			if($row['searchID'] != '') {
				$searchString = $row['searchID'];
			}
		}
		$row['originalLink'] = makeBeaconLink($searchString, $row['targetOPAC']);
		unset($row['targetOPAC']);
	}
	return($row);	
}

function resolveLanguages($row) {
	include('languageCodes.php');
	$languagesFull = array();
	if(isset($row['languages'])) {
		$languages = explode(' ', $row['languages']);
		foreach($languages as $code) {
			if(isset($languageCodes[$code])) {
				$languagesFull[] = $languageCodes[$code];
			}
		}
		$languageString = implode(';', $languagesFull);
		if($languageString != '') {
			$row['languagesFull'] = $languageString;
		}
	}
	return($row);
}

function addMetaDataSOLR($catalogue, $flatData) {
	$result = array();
	$metaData = array();
	if($catalogue->owner) {
		$metaData['owner'] = $catalogue->owner;
	}
	if($catalogue->ownerGND) {
		$metaData['ownerGND'] = $catalogue->ownerGND;
	}
	if($catalogue->year) {
		$metaData['dateCollection'] = $catalogue->year;
	}
	if($catalogue->heading) {
		$metaData['nameCollection'] = $catalogue->heading;
	}
	if($catalogue->geoBrowserStorageID) {
		$metaData['GeoBrowserLink'] = 'https://geobrowser.de.dariah.eu/?csv1=http://geobrowser.de.dariah.eu./storage/'.$catalogue->geoBrowserStorageID;
	}
	foreach($flatData as $item) {
		foreach($metaData as $key => $value) {
			$item[$key] = $value;
		}
		$result[] = $item;
	}
	return($result);
}

function addNormalizedYear($row) {
	$normalizedYear = '';
	if(isset($row['year'])) {
		$normalizedYear = normalizeYear($row['year']);
		if($normalizedYear == '') {
			if(isset($row['titleCat'])) {
				$normalizedYear = getYearFromTitle($row['titleCat']);
			}
		}
		if($normalizedYear != '') {
			$row['yearNormalized'] = $normalizedYear;
		}
	}
	return($row);
}

?>
