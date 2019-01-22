<?php

//$test = getColumnNames('Material\\Antoinette-Amalie.csv');
//var_dump($test);

function loadCSV($path) {
	$fieldNames = getColumnNames($path);
	$data = array();
	$csv = file_get_contents($path);
	$document = str_getcsv($csv, "\n");
	$firstRow = TRUE;
	foreach($document as $row) {
		$fields = str_getcsv($row, ";");
		$fields = array_map('convertWindowsToUTF8', $fields);
		$fields = array_map('trim', $fields);
		if($firstRow == TRUE) {
			$firstRow = FALSE;
		}
		elseif($firstRow == FALSE) {
			$item = makeItemFromCSVRow($fields, $fieldNames);
			$data[] = $item;
		}
	}
	return($data);
}

function makeItemFromCSVRow($row, $fieldNames) {
	$item = new item();
	$item->id = $row[$fieldNames['id']];
	$item->pageCat = $row[$fieldNames['pageCat']];
	$item->imageCat = $row[$fieldNames['imageCat']];
	$item->numberCat = $row[$fieldNames['numberCat']];
	preg_match('~^([0-9]+)[bBvV]([0-9]{0,3})$~', $row[$fieldNames['itemInVolume']], $hits);
	if(isset($hits[1]) and $hits[2] != '') {
		$item->volumesMisc = $hits[1];
		$item->itemInVolume = $hits[2];
	}
	elseif(isset($hits[1])) {
		$item->volumes = $hits[1];
	}
	elseif(is_int($row[4])) {
		$item->itemInVolume = $row[$fieldNames['itemInVolume']];
		$item->volumesMisc = 1;
	}
	$item->titleCat = trim($row[$fieldNames['titleCat']], '. ');
	$item->titleBib = trim($row[$fieldNames['titleBib']], '. ');
	$item->titleNormalized = trim ($row[$fieldNames['titleNormalized']], '. ');
	$item->publisher = $row[$fieldNames['publisher']];
	$item->year = $row[$fieldNames['year']];
	$item->format = $row[$fieldNames['format']];
	$explodeHistSubject = explode('#', $row[$fieldNames['histSubject']]);
	$explodeHistSubject = array_map('trim', $explodeHistSubject);
	$item->histSubject = $explodeHistSubject[0];
	if(isset($explodeHistSubject[1])) {
		$item->histShelfmark = $explodeHistSubject[1];
	}
	$item->subjects = explode(';', $row[$fieldNames['subjects']]);
	$item->subjects = array_map('trim', $item->subjects);
	$item->genres = explode(';', $row[$fieldNames['genres']]);
	$item->genres = array_map('trim', $item->genres);
	$item->mediaType = $row[$fieldNames['mediaType']];
	$item->manifestation = array('systemManifestation' => $row[$fieldNames['systemManifestation']], 'idManifestation' => $row[$fieldNames['idManifestation']]);
	$item->originalItem =  array('institutionOriginal' => $row[$fieldNames['institutionOriginal']], 'shelfmarkOriginal' => $row[$fieldNames['shelfmarkOriginal']], 'provenanceAttribute' => $row[$fieldNames['provenanceAttribute']], 'digitalCopyOriginal' => $row[$fieldNames['digitalCopyOriginal']], 'targetOPAC' => $row[$fieldNames['targetOPAC']], 'searchID' => $row[$fieldNames['searchID']]);
	$item->work = array('titleWork' => $row[$fieldNames['titleWork']], 'systemWork' => $row[$fieldNames['systemWork']], 'idWork' => $row[$fieldNames['idWork']]);		
	$item->bound = $row[$fieldNames['bound']];
	$item->comment = $row[$fieldNames['comment']];
	$item->digitalCopy = $row[$fieldNames['digitalCopy']];
	$item->languages = explode(';', $row[$fieldNames['languages']]);
	$item->languages = array_map('trim', $item->languages);
	$authorFields = array($row[$fieldNames['author1']], $row[$fieldNames['author2']], $row[$fieldNames['author3']], $row[$fieldNames['author4']]);
	foreach($authorFields as $authorString) {
		if($authorString != '') {
			$item->persons[] = makePersonFromCSV($authorString, 'author');
		}
	}
	$contributorFields = array($row[$fieldNames['contributor2']], $row[$fieldNames['contributor2']], $row[$fieldNames['contributor3']], $row[$fieldNames['contributor4']]);
	foreach($contributorFields as $contributorString) {
		if($contributorString != '') {
			$item->persons[] = makePersonFromCSV($contributorString, 'contributor');
		}
	}
	
	$placeFields = array($row[$fieldNames['place1']], $row[$fieldNames['place2']]);
	foreach($placeFields as $placeString) {
		if($placeString != '') {
			$parts = explode('#', $placeString);
			$place = new place();
			$place->placeName = $parts[0];
			if(isset($parts[1])) {
				if(substr($parts[1], 0, 8) == 'geoNames') {
					$geoNames = substr($parts[1], 8);
					$place->geoNames = testGeoNames($geoNames);
				}
				elseif(substr($parts[1], 0, 3) == 'gnd') {
					$gnd = substr($parts[1], 3);
					$place->gnd = testGND($gnd);
				}				
				elseif(substr($parts[1], 0, 5) == 'getty') {
					$getty = substr($parts[1], 5);
					$place->getty = testGetty($getty);
				}
			}
			$item->places[] = $place;
		}
	}
	
	if(isset($fieldNames['copiesHAB'])) {
		$item->copiesHAB = explode(';', $row[$fieldNames['copiesHAB']]);
		$item->copiesHAB = array_map('trim', $item->copiesHAB);
	}
	return($item);
}
	
function makeItemFromCSVRowOld($row) {
	$item = new item();
	$item->id = $row[0];
	$item->pageCat = $row[1];
	$item->imageCat = $row[2];
	$item->numberCat = $row[3];
	preg_match('~^([0-9]+)[bBvV]([0-9]{0,3})$~', $row[4], $hits);
	if(isset($hits[1]) and isset($hits[2])) {
		$item->volumesMisc = $hits[1];
		$item->itemInVolume = $hits[2];
	}
	elseif(isset($hits[1])) {
		$item->volumes = $hits[1];
	}
	elseif(is_int($row[4])) {
		$item->itemInVolume = $row[4];
		$item->volumesMisc = 1;
	}
	$item->titleCat = $row[5];
	$item->titleBib = $row[6];
	$item->titleNormalized = $row[7];
	$item->publisher = $row[18];
	$item->year = $row[19];
	$item->format = $row[20];
	$explodeHistSubject = explode('#', $row[21]);
	$item->histSubject = $explodeHistSubject[0];
	if(isset($explodeHistSubject[1])) {
		$item->histShelfmark = $explodeHistSubject[1];
	}
	$item->subjects = explode(';', $row[22]);
	$item->genres = explode(';', $row[23]);
	$item->mediaType = $row[24];
	$item->manifestation = array('systemManifestation' => $row[26], 'idManifestation' => $row[27]);
	$item->originalItem =  array('institutionOriginal' => $row[28], 'shelfmarkOriginal' => $row[29], 'provenanceAttribute' => $row[30], 'digitalCopyOriginal' => $row[31], 'targetOPAC' => $row[32], 'searchID' => $row[33]);
	$item->work = array('titleWork' => $row[34], 'systemWork' => $row[35], 'idWork' => $row[36]);		
	$item->bound = $row[37];
	$item->comment = $row[38];
	$item->digitalCopy = $row[39];
	$item->languages = explode(';', $row[25]);
	$authorFields = array($row[8], $row[9], $row[10], $row[11]);
	foreach($authorFields as $authorString) {
		if($authorString != '') {
			$item->persons[] = makePersonFromCSV($authorString, 'author');
		}
	}
	
	$contributorFields = array($row[12], $row[13], $row[14], $row[15]);
	foreach($contributorFields as $contributorString) {
		if($contributorString != '') {
			$item->persons[] = makePersonFromCSV($contributorString, 'contributor');
		}
	}

	$placeFields = array($row[16], $row[17]);
	foreach($placeFields as $placeString) {
		if($placeString != '') {
			$parts = explode('#', $placeString);
			$place = new place();
			$place->placeName = $parts[0];
			if(isset($parts[1])) {
				if(substr($parts[1], 0, 8) == 'geoNames') {
					$geoNames = substr($parts[1], 8);
					$place->geoNames = testGeoNames($geoNames);
				}
				elseif(substr($parts[1], 0, 3) == 'gnd') {
					$gnd = substr($parts[1], 3);
					$place->gnd = testGND($gnd);
				}				
				elseif(substr($parts[1], 0, 5) == 'getty') {
					$getty = substr($parts[1], 5);
					$place->getty = testGetty($getty);
				}
			}
			$item->places[] = $place;
		}
	}
	return($item);
}

function makePersonFromCSV($string, $role) {
	$parts = explode('#', $string);
	$person = new person();
	$person->role = $role;
	$person->persName = $parts[0];
	if(isset($parts[1])) {
		$person = insertGND($parts[1], $person);
	}
	return($person);
}

function insertGND($gndString, $person) {
	preg_match('~^([0-9X-]{5,11})?([MmFfWw*]?)$~', $gndString, $hits);
	if(isset($hits[1])) {
		$person->gnd = $hits[1];
	}
	if(isset($hits[2])) {
		$translation = array('W' => 'f', 'w' => 'f');
		$gender = strtr($hits[2], $translation);
		$gender = strtolower($gender);
		$person->gender = $gender;
	}
	return($person);
}

function testGeoNames($id) {
	$return = '';
	if(preg_match('~^[0-9]{5,9}$~', $id) == 1) {
		return($id);
	}
}

function testGND($gnd) {
	$return = '';
	if(preg_match('~^[0-9X-]{9,11}$~', $gnd) == 1) {
		return($gnd);
	}	
}

function testGetty($id) {
	if(preg_match('~^[0-9]{5,9}$~', $id) == 1) {
		return($id);
	}
}

function validateCSV($path, $minColumns) {
	$csv = file_get_contents($path);
	$document = str_getcsv($csv, "\n");
	if($document == NULL) {
		return('Das Dokument konnte nicht gelesen werden.');
	}
	if(isset($document[1]) == FALSE) {
		return('Das Dokument umfasst nur eine Zeile.');
	}
	$widths = array();
	foreach($document as $row) {
		$fields = str_getcsv($row, ";");
		if($fields == NULL) {
			return('Das Dokument konnte nicht gelesen werden.');
		}
		$columns = count($fields);
		$widths[] = $columns;
	}
	if($widths[0] < $minColumns) {
		return('Das Dokument enth&auml;lt nur '.$widths[0].' Spalten. Mindestzahl ist '.$minColumns.'.');
	}	
	$count = 1;
	foreach($widths as $width) {
		if($width != $widths[0]) {
			return('Die Zeilen enthalten ungleich viele Felder (nur '.$width.' Felder in Zeile '.$count.'). Mindestzahl ist '.$minColumns.'.');
		}
		$count++;
	}
	return(1);
}

function getColumnNames($path) {
	$result = array();
	$allowed = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'titleCat', 'titleBib', 'titleNormalized', 'author1', 'author2', 'author3', 'author4', 'contributor1', 'contributor2', 'contributor3', 'contributor4', 'place1', 'place2', 'publisher', 'year', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'idManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'digitalCopyOriginal', 'targetOPAC', 'searchID', 'titleWork', 'systemWork', 'idWork', 'bound', 'comment', 'digitalCopy', 'copiesHAB');
	$csv = file_get_contents($path);
	$document = str_getcsv($csv, "\n");
	$fieldNames = str_getcsv($document[0], ";");
	$count = 0;
	foreach($fieldNames as $fieldName) {
		if(in_array($fieldName, $allowed) == TRUE) {
			$result[$fieldName] = $count;
		}
		$count++;
	}
	return($result);
}

?>