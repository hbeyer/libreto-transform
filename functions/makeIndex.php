<?php

function makeIndex($data, $field) {
	require('fieldList.php');
	$index = '';
	
	if(in_array($field, $normalFields)) {
		$collect = collectIDs($data, $field);
	}
	elseif($field == 'beacon') {
		$collect = collectIDsBeacon($data);
	}
	elseif($field == 'persName') {
		$collect = collectIDsPersons($data);
	}
	elseif($field == 'placeName') {
		$collect = collectIDsPlaces($data);
	}
	elseif(in_array($field, $personFields)) {
		$collect = collectIDsSubObjects($data, 'persons', $field);
	}
	elseif(in_array($field, $placeFields)) {
		$collect = collectIDsSubObjects($data, 'places', $field);
	}
	elseif(in_array($field, $manifestationFields)) {
		$collect = collectIDsAssocArrayValues($data, 'manifestation', $field);
	}
	elseif(in_array($field, $workFields)) {
		$collect = collectIDsAssocArrayValues($data, 'work', $field);
	}
	elseif(in_array($field, $originalItemFields)) {
		$collect = collectIDsAssocArrayValues($data, 'originalItem', $field);
	}			
	elseif(in_array($field, $arrayFields)) {
		$collect = collectIDsArrayValues($data, $field);
	}
	
	if($field == 'histSubject') {
		$index = makeEntries($collect, $field);
	}
	elseif(isset($collect)) {
		$collect = sortCollect($collect);
		$index = makeEntries($collect, $field);
	}
	elseif($field == 'catSubjectFormat') {
		$collect1 = collectIDs($data, 'histSubject');
		$index1 = makeEntries($collect1);
		unset($collect1);
		$collect2 = collectIDs($data, 'format');
		$index2 = makeEntries($collect2);
		unset($collect2);
		$index = mergeIndices($index1, $index2);
	}
	
	foreach($index as $entry) {
		$entry->label = postprocessFields($field, $entry->label);
	}
	
	return($index);
}

function makeEntries($collect, $field = '') {
	$collectLoop = $collect['collect'];
	$index = array();
	foreach($collectLoop as $value => $IDs) {
		$entry = new indexEntry();
		// Prüfen, ob Personennamen in einem eigenen Array hinterlegt wurden (Funktion collectIDsPersons)
		if(isset($collect['concordanceGND'])) {
			$entry->label = $collect['concordanceGND'][$value];
			if(preg_match('~[0-9X]{9}~', $value)) {
				$entry->authority['system'] = 'gnd';
				$entry->authority['id'] = strval($value);
			}
		}
		else {
			$entry->label = $value;
		}
		// Bei Facettierung nach Personen oder Jahren wird der Anzeigelevel auf 2 gesetzt (Standard 1) und damit die Anzeige der einzelnen Personen oder Jahre im Inhaltsverzeichnis unterdrückt
		if($field == 'persName') {
			$entry->level = 2;
		}
		elseif($field == 'year' and $value != 9999) {
			$entry->level = 2;
		}
		elseif($field == 'id' or $field == 'numberCat') {
			$entry->level = 0;
		}
		// Prüfen, ob eine Konkordanz der Place-Objekte übergeben wurde
		if(isset($collect['placeObjects'])) {
			$placeObject = $collect['placeObjects'][$value];
			$entry->geoData = $placeObject->geoData;
			if($placeObject->geoNames) {
				$entry->authority['system'] = 'geoNames';
				$entry->authority['id'] = $placeObject->geoNames;
			}
			elseif($placeObject->getty) {
				$entry->authority['system'] = 'getty';
				$entry->authority['id'] = $placeObject->getty;				
			}
			elseif($placeObject->gnd) {
				$entry->authority['system'] = 'gnd';
				$entry->authority['id'] = $placeObject->gnd;				
			}			
		}
		$entry->content = $IDs;
		$index[] = $entry;
	}
	return($index);
}

function mergeIndices($index1, $index2) {
	$commonIndex = array();
	foreach($index1 as $entry1) {
		$higherEntry = new indexEntry();
		$higherEntry->label = $entry1->label;
		$higherEntry->authority = $entry1->authority;
		$higherEntry->geoData = $entry1->geoData;
		$commonIndex[] = $higherEntry;
		foreach($index2 as $entry2) {
			$intersection = array_intersect($entry1->content, $entry2->content);
			if($intersection) {
				$lowerEntry = new indexEntry();
				$lowerEntry->level = 2;
				$lowerEntry->label = $entry2->label;
				$lowerEntry->authority = $entry2->authority;
				$lowerEntry->geoData = $entry2->geoData;
				$lowerEntry->content = $intersection;
				$commonIndex[] = $lowerEntry;
			}
		}
	}
	return($commonIndex);
}

function collectIDs($data, $field) {
	$collect = array();
	$count = 0;
	foreach($data as $item) {
		$key = preprocessFields($field, $item->$field, $item);
		if(array_key_exists($key, $collect) == FALSE) {
			$collect[$key] = array();
		}
		$collect[$key][] = $count;
		$count ++;
	}
	$return = array('collect' => $collect);
	return($return);
}

function collectIDsArrayValues($data, $field) {
	$collect = array();
	$count = 0;
	foreach($data as $item) {
		foreach($item->$field as $key) {
			$key = preprocessFields($field, $key, $item);
			if(array_key_exists($key, $collect) == FALSE) {
				$collect[$key] = array();
			}
			$collect[$key][] = $count;
		}
		$count ++;
	}
	$return = array('collect' => $collect);
	return($return);
}

function collectIDsAssocArrayValues($data, $field, $subfield) {
	$collect = array();
	$count = 0;
	foreach($data as $item) {
		// Der folgende Umweg wird nötig, weil $item->$field[$subfield] eine Fehlermeldung produziert.
		$keyArray = $item->$field;
		$index = $subfield;
		$key = $keyArray[$subfield];
		$key = preprocessFields($subfield, $key, $item);
		if(array_key_exists($key, $collect) == FALSE) {
			$collect[$key] = array();
		}
		$collect[$key][] = $count;
		$count ++;
	}
	$return = array('collect' => $collect);
	return($return);
}

function collectIDsSubObjects($data, $field, $subField) {
	$collect = array();
	$count = 0;
	foreach($data as $item) {
		foreach($item->$field as $subItem) {
			$key = preprocessFields($subField, $subItem->$subField, $item);
			if(array_key_exists($key, $collect) == FALSE) {
				$collect[$key] = array();
			}
			$collect[$key][] = $count;
		}
	$count ++;
	}
	$return = array('collect' => $collect);
	return($return);
}

function collectIDsBeacon($data) {
    $beaconSources = $_SESSION['beaconRepository']->beacon_sources;
	$collect = array();
	$count = 0;
	foreach($data as $item) {
		foreach($item->persons as $person) {
			foreach($person->beacon as $beacon) {
				$key = $beaconSources[$beacon]['label'];
				if(array_key_exists($key, $collect) == FALSE) {
					$collect[$key] = array();
				}
				$collect[$key][] = $count;
			}
		}
	$count ++;
	}
	$return = array('collect' => $collect);
	return($return);
}

function collectIDsPersons($data) {
	$collectGND = array();
	$collectName = array();
	$count = 0;
	foreach($data as $item) {
		$gnds = array();
		foreach($item->persons as $person) {
			$key = $person->gnd;
			$name = preprocessFields('persName', $person->persName, $item);
			if($key == '') {
					$key = $name;
			}
			if(array_key_exists($key, $collectGND) == FALSE) {
				$collectGND[$key] = array();
				$collectName[$key] = $name;
			}
			if (in_array($key, $gnds) == FALSE) {
				$collectGND[$key][] = $count;
				$gnds[] = $key;
			}
		}
		$count++;
	}
	$return = array('collect' => $collectGND, 'concordanceGND' => $collectName);
	return($return);
}

function collectIDsPlaces($data) {
	$collectPlaceName = array();
	$collectGeoData = array();
	$collectPlaceObjects = array();
	$count = 0;
	foreach($data as $item) {
		foreach($item->places as $place) {
			$key = preprocessFields('placeName', $place->placeName, $item);
			if(array_key_exists($key, $collectPlaceName) == FALSE) {
				$collectPlaceName[$key] = array();
				$collectGeoData[$key] = $place->geoData;
				$collectPlaceObjects[$key] = $place;
			}
			$collectPlaceName[$key][] = $count;
		}
	$count++;
	}
	$return = array('collect' => $collectPlaceName, 'placeObjects' => $collectPlaceObjects);
	return($return);
}

function preprocessFields($field, $value, $item) {
	$value = trim($value, '[]');
	if($field == 'persName') {
		$value = removeSpecial($value);
		$value = replaceArrowBrackets($value);
	}
	elseif($field == 'placeName') {
		$value = trim($value, '[]');
		$test = preg_match('~[oOsS][\. ]?[OlL]|[oO]hne Ort|[sS]ine [lL]oco|[oO]hne Druckort|[oO]hne Angabe~', $value);
		if($value == '' or $test == 1) {
			$value = 's. l.';
		}
	}
	elseif($field == 'publisher') {
		$value = trim($value, '[]');
		$test = preg_match('~[sSoO]\.? ?[nN]\.?|ohne Angabe|unbekannt|ohne Namen~', $value);
		if($value == '' or $test == 1) {
			$value = 's. n.';
		}
	}	
	elseif($field == 'year') {
		$value = normalizeYear($value);
		if($value == '') {
			$value = getYearFromTitle($item->titleCat);
		}
		if($value == '') {
			$value = 9999; // Makes empty year fields be sorted to the end
		}
	}
	elseif($field == 'format') {
		$value = sortingFormat($value);
		if($value == '') {
			$value = 'ohne Angabe';
		}		
	}
	elseif($field == 'titleWork') {
		if($value == '') {
			$value = 'ohne Werktitel';
		}
	}
	elseif($value == '') {
		$value = 'ohne Kategorie';
	}
	return($value);
}

function postprocessFields($field, $value) {
	/* Ist nicht ideal, weil auch label vom Typ histSubject erfasst werden, aber vermutl. 
	keine praktische Auswirkung, weil die Ersetzungsfunktion sehr eng gefasst ist. */
	if($field == ('format' or 'catSubjectFormat')) {
		$value = reverseSortingFormat($value);
	}
	if($field == 'languages') {
		if($value == '' or $value == 'ohne Kategorie') {
			$value = 'ohne Angabe';
		}
		else {
			include('languageCodes.php');
			$value = $languageCodes[$value];
		}
	}
	if($field == 'gender') {
		$value = translateGenderAbbr($value);
		if($value == '') {
			$value = 'ohne Angabe';
		}
	}
	if($field == 'year') {
		if($value == 9999) {
			$value = 'ohne Jahr';
		}
	}
	return($value);
}

function sortCollect($collect) {
	if(isset($collect['concordanceGND'])) {
		$sortingConcordance = array_flip($collect['concordanceGND']);
		ksort($sortingConcordance, SORT_STRING | SORT_FLAG_CASE);
		$new = array();
		foreach($sortingConcordance as $name => $gnd) {
			$new[$gnd] = $collect['collect'][$gnd];
		}
		$collect['collect'] = $new;
	}
	else {
		ksort($collect['collect'], SORT_STRING | SORT_FLAG_CASE);
	}
	$collect['collect'] = postponeVoid($collect['collect']);
	return($collect);
}

/* The function puts all "void" categories to the end of the array that contains labels and indices. "ohne Jahr" does not appear, because it is treated by the functions preprocessFields and postprocessFields  */
function postponeVoid($collect) {
	$voidTerms = array('ohne Kategorie', 's. n.', 's. l.', 'Unbestimmbare Sprache', 'ohne Werktitel');
	foreach($collect as $key => $value) {
		$test1 = in_array($key, $voidTerms);
		if($test1) {
			if(isset($voidKey)) {
				echo 'Fehler: Mehr als ein leeres Feld in dem Index, der mit '.$collect[0].' beginnt.';
			}
			else {
				$voidKey = $key;
				$voidValue = $value;
			}
		}
	}
	if(isset($voidKey)) {
		unset($collect[$voidKey]);
		$collect[$voidKey] = $voidValue;
	}
	return($collect);
}

function normalizeYear($year) {
	if(preg_match('~([12][0-9][0-9][0-9])[-– ]{1,3}([12][0-9][0-9][0-9])~', $year, $treffer)) {
		$yearAssign = intval(($treffer[1] + $treffer[2]) / 2);
	}
	elseif(preg_match('~[12][0-9][0-9][0-9]~', $year, $treffer)) {
		$yearAssign = $treffer[0];
	}
	else {
		$yearAssign = '';
	}
	return($yearAssign);
}
	
function getYearFromTitle($title) {
	$yearAssign = '';
	if(preg_match('~ ([12][0-9][0-9][0-9])$| ([12][0-9][0-9][0-9])[^0-9]~', $title, $treffer)) {
		if(isset($treffer[2])) {
			$yearAssign = $treffer[2];
		}
		elseif(isset($treffer[1])) {
			$yearAssign = $treffer[1];
		}
	}
	return($yearAssign);
}

?>
