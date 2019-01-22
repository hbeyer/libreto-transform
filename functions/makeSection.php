<?php

// The following functions serve to convert an array of objects of the type indexEntry into an array of objects of the type section. The function to select depends on the facet chosen. For the facets cat, persons and year there are special functions. All other facets are covered by the function makeSections.

function makeSections($data, $field) {
	$index = makeIndex($data, $field);
	$structuredData = array();
	foreach($index as $entry) {
		$section = new section();
		$section->label = $entry->label;
		$section->level = $entry->level;
		$section->authority = $entry->authority;
        
		$section->geoData = $entry->geoData;
		$count = 1;
		foreach($entry->content as $idItem) {
			$section->content[] = $data[$idItem];
			$count++;
		}
		$structuredData[] = $section;
	}
	$structuredData = addHigherLevel($structuredData, $field);
	
	quantifyLabels($structuredData);
	return($structuredData);
}

function quantifyLabels($structuredData) {
	$count = 0;
	foreach($structuredData as $section) {
		// Wenn die section den Level 1 hat, also im Inhaltsverzeichnis angezeigt werden soll,
		// wird überprüft, ob dahinter weitere sections mit Level 2 kommen. Ist das der Fall,
		// wird die Anzahl der enthaltenen Datensätze addiert und unter quantifiedLabel gespeichert.
		if($section->level == 1) {
			$countFrom = $count + 1;
			$countEntries = 0;
			if(isset($structuredData[$countFrom])) {
				while($structuredData[$countFrom]->level == 2) {
					$countEntries += count($structuredData[$countFrom]->content);
					$countFrom++;
					if(isset($structuredData[$countFrom]) == FALSE) {
						break;
					}
				}
			}
			// Wenn die section zwar Level 1 hat, aber keine weiteren mit Level 2 folgen,
			// wird die Anzahl der in dieser section enthaltenen Datensätze unter quantified Label gespeichert-
			if($countEntries == 0) {
				$countEntries = count($section->content);
			}
			$section->quantifiedLabel = $section->label.' ('.$countEntries.')';
		}
		$count ++;
	}
}

function addHigherLevel($structuredData, $field) {
	$newStructure = array();
	$previousSection = new section();
	foreach($structuredData as $section) {
		$higherSection = makeHigherSection($section, $previousSection, $field);
		if(is_object($higherSection) == TRUE) {
			$newStructure[] = $higherSection;
		}
		$newStructure[] = $section;
		$previousSection = $section;
	}
	return($newStructure);
}

function makeHigherSection($section, $previousSection, $field) {
	$higherSection = '';
	if($field == 'persName') {
		$previousLetter = strtoupper(substr($previousSection->label, 0, 1));
		$currentLetter = strtoupper(substr($section->label, 0, 1));
		if($previousLetter != $currentLetter) {
			$higherSection = new section();
			$higherSection->label = $currentLetter;
		}
	}
	elseif($field == 'year') {
		if($section->label != 'ohne Jahr') {
			$previousDecade = makeDecadeFromTo($previousSection->label);
			$currentDecade = makeDecadeFromTo($section->label);
			if($previousDecade != $currentDecade) {
				$higherSection = new section();
				$higherSection->label = $currentDecade;
			}
		}
	}
	return($higherSection);
}

// This is an auxiliary function for makeHigherSection
function makeDecadeFromTo($year) {
	$decadeStart = $year - ($year % 10);
	$decadeEnd = $decadeStart + 10;
	$fromTo = $decadeStart.'–'.$decadeEnd;
	return($fromTo);
}

//This function replaces the content of the section-object, thereby putting item-objects with $itemInVolume > 0 into volume-objects

function joinVolumes($section) {
	$newContent = array();
	$buffer = array();
	$lastID = '';
	foreach($section->content as $item) {
		// Case 1: A one-volume item
		if($item->itemInVolume == 0) {
			if(isset($buffer[0])) {
				$newContent[] = makeVolume($buffer);
				$buffer = array();
			}
			$newContent[] = $item;
		}
		// Case 2: An item of a new miscellany
		elseif($item->itemInVolume == 1 and isset($buffer[0])) {
			$newContent[] = makeVolume($buffer);
			$buffer = array();
			$buffer[] = $item;
		}
		// Case 3: An item of a known miscellany
		elseif($item->itemInVolume > 0) {
			$buffer[] = $item;
		}
		$lastID = $item->id;
	}
	if(isset($buffer[0])) {
		$newContent[] = makeVolume($buffer);
	}
	$section->content = $newContent;
	return($section);
}

function makeVolume($buffer) {
	uasort($buffer, 'compareItemInVolume');
	$result = new volume();
	$volumesMisc = $buffer[0]->volumesMisc;
	$volumesMisc = intval($volumesMisc);
	if($volumesMisc) {
		$result->volumes = $buffer[0]->volumesMisc;
	}
	$result->content = $buffer;
	return($result);
}

function compareItemInVolume($a, $b) {
	if($a->itemInVolume == $b->itemInVolume) {
		return 0;
	}
	else {
		return ($a->itemInVolume < $b->itemInVolume) ? -1 : 1;
	}
}

// This function converts an array of objects of the class section into a list in HTML format. The variable $catalogue contains an object of the type catalogue. The function displays content either as text, for monographic entries, or as unordered list, for miscellanies.


function makeList($structure, $catalogue) {
    $count = 0;
	ob_start();
	include 'templates/list.phtml';
	$return = ob_get_contents();
	ob_end_clean();
    return($return);
}

function makeBeaconLinks($gnd, $repository) {
    $linkArray = $repository->getLinks($gnd);
    array_unshift($linkArray, '<a href="http://d-nb.info/gnd/'.$gnd.'" target="_blank">DNB</a>');
    return(implode(' | ', $linkArray));
}

?>
