<?php


function loadCSV($path) {
	$fieldNames = getColumnNames($path);
	$data = array();
	$csv = file_get_contents($path);
	$document = str_getcsv($csv, "\n");
    array_shift($document);
    
	foreach($document as $row) {
		$row = str_getcsv($row, ";");
		$row = array_map('convertWindowsToUTF8', $row);
        $row = mapRow($row, $fieldNames);
        $item = makeItemFromAssocArray($row);
        $item->trimTitles();
		$data[] = $item;
	}
	return($data);
}

function mapRow($row, $fieldNames) {
    $newRow = array();
    foreach ($fieldNames as $name => $key) {
        if ($row[$key]) {
            $newRow[$name] = $row[$key];
        }
    }
    return($newRow);
}

function makeItemFromAssocArray($row) {

	$item = new item();
    
    $simpleFields = array('pageCat', 'imageCat', 'numberCat', 'titleCat', 'titleBib', 'titleNormalized', 'publisher', 'year', 'format', 'mediaType', 'bound', 'comment', 'digitalCopy');
    foreach ($simpleFields as $field) {
        if (!empty($row[$field])) {
            $item->$field = trim($row[$field]);
        }
    }

    $semicolonFields = array('subjects', 'genres', 'languages', 'copiesHAB');
    foreach ($semicolonFields as $field) {
        if (!empty($row[$field])) {
           	$item->$field = explode(';', $row[$field]);
        	$item->$field = array_map('trim', $item->$field);            
        }
    }

    if (!empty($row['itemInVolume'])) {
	    preg_match('~^([0-9]+)[bBvV]([0-9]{0,3})$~', $row['itemInVolume'], $hits);
	    if (!empty($hits[1]) and !empty($hits[2])) {
		    $item->volumesMisc = $hits[1];
		    $item->itemInVolume = $hits[2];
	    }
	    elseif(isset($hits[1])) {
		    $item->volumes = $hits[1];
	    }
    }
    if (!empty($row['histSubject'])) {
	    $explodeHistSubject = explode('#', $row['histSubject']);
	    $explodeHistSubject = array_map('trim', $explodeHistSubject);
	    $item->histSubject = $explodeHistSubject[0];
	    if(isset($explodeHistSubject[1])) {
		    $item->histShelfmark = $explodeHistSubject[1];
	    }
    }
    if (!empty($row['systemManifestation']) and !empty($row['idManifestation'])) {
        $item->manifestation['systemManifestation'] = $row['systemManifestation'];
        $item->manifestation['idManifestation'] = $row['idManifestation'];
    }

    $originalFields = array('institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'targetOPAC', 'searchID', 'digitalCopyOriginal');
    foreach ($originalFields as $field) {
        if (!empty($row[$field])) {
            $item->originalItem[$field] = $row[$field];
        }
    }

    $workFields = array('titleWork', 'systemWork', 'idWork');
    foreach ($workFields as $field) {
        if (!empty($row[$field])) {
            $item->work[$field] = $row[$field];
        }
    }

    $personFields = array('author1', 'author2', 'author3', 'author4', 'contributor1', 'contributor2', 'contributor3', 'contributor4');
    foreach ($personFields as $field) {
        if (!empty($row[$field])) {
            $person = new person;
            $parts = explode('#', $row[$field]);
            preg_match('~([^#]+)#?([0-9X]+)?([mf])?~', $row[$field], $hits);
            if (!empty($hits[1])) {
                $person->persName = $hits[1];
            }
            if (!empty($hits[2])) {
                $person->gnd = $hits[2];
            }
            if (!empty($hits[3])) {
                $person->gender = $hits[3];
            }
            if (substr($field, 0, 3) == 'con') {
                $person->role = 'contributor';
            }
            $item->persons[] = $person;
        }
    }

    $placeFields = array('place1', 'place2');
    foreach ($placeFields as $field) {
        if (!empty($row[$field])) {
            $place = new place;
            preg_match('~([^#]+)#?(getty|gnd|geoNames)?(.+)~', $row[$field], $hits);
            if (!empty($hits[1])) {
                $place->placeName = $hits[1];
            }
            if (!empty($hits[2]) and !empty($hits[3])) {
                //Aus irgendeinem Grund führt $place->$hits[2] zur Zuweisung einer Eigenschaft namens "Array", daher so
                if ($hits[2] == 'geoNames') {
                    $place->geoNames = $hits[3];
                }
                elseif ($hits[2] == 'gnd') {
                    $place->gnd = $hits[3];
                }
                elseif ($hits[2] == 'getty') {
                    $place->getty = $hits[3];
                }
                
            }
            unset($hits);
            $item->places[] = $place;
        }
    }

	return($item);

}

/*function makePersonFromCSV($string, $role) {
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
*/

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
