<?php

class flatItem {
		public $id;
		public $pageCat;
		public $imageCat;
		public $numberCat;
		public $itemInVolume;
		public $titleCat;
		public $titleBib;
		public $titleNormalized;
		public $author1;
		public $author2;
		public $author3;
		public $author4;
		public $contributor1;
		public $contributor2;
		public $contributor3;
		public $contributor4;
		public $place1;
		public $place2;
		public $publisher;
		public $year;
		public $format;
		public $histSubject;
		public $subjects;
		public $genres;
		public $mediaType;
		public $languages;
		public $systemManifestation;
		public $idManifestation;		
		public $institutionOriginal;
		public $shelfmarkOriginal;
		public $provenanceAttribute;
		public $digitalCopyOriginal;		
		public $targetOPAC;		
		public $searchID;		
		public $titleWork;
		public $systemWork;
		public $idWork;		
		public $bound;
		public $comment;
		public $digitalCopy;
		public $copiesHAB;
}

function makeCSV($data, $folder, $fileName) {

	$handle = fopen($folder.'/'.$fileName.'.csv', "w");
	fwrite($handle, "sep=,\n", 100);
	$test = new flatItem();
	$columns = array();
	foreach($test as $key => $value) {
		$columns[] = $key;
	}
	fputcsv($handle, $columns);
	
	foreach($data as $item) {
		$template = new flatItem;
		$template = templateInsertItem($template, $item);
		$values = array();
		foreach($template as $value) {
			$values[] = $value;
		}
		fputcsv($handle, $values);
	}
}

function templateInsertItem($template, $item) {

	//Einfügen der einfachen Felder
	
	$normalFields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'titleCat', 'titleBib', 'titleNormalized', 'publisher', 'year', 'format', 'histSubject', 'mediaType', 'bound', 'comment', 'digitalCopy');
	
	foreach($normalFields as $field) {
		$template->$field = $item->$field;
	}

	// Einfügen der Bandzahl in itemInVolume
	if($item->volumes > 1) {
		$template->itemInVolume = $item->volumes.'V'.$template->itemInVolume;
	}
	
	// Einfügen der Altsignatur
	if($item->histShelfmark) {
		$template->histSubject = $item->histSubject.'#'.$item->histShelfmark;
	}
	
 	// Einfügen der durch ";" unterteilten Felder
	$arrayFields = array('languages', 'subjects', 'genres', 'copiesHAB');
	foreach($arrayFields as $field) {
		$string = implode(';', $item->$field);
		$template->$field = $string;
	}
	
	// Einfügen der assoziativen Arrays
	$assocArrayFields = array('manifestation', 'originalItem', 'work');
	foreach($assocArrayFields as $field) {
		foreach($item->$field as $key => $value) {
			$template->$key = $value;
		}
	}
	
	// Einfügen der Personendaten
	
	$authors = 1;
	$contributors = 1;
	
	foreach($item->persons as $person) {
		if($person->role == 'author') {
			if($authors <= 4) {
				$persName = $person->persName;
				if($person->gnd) {
					$persName .= '#'.$person->gnd;
					if($person->gender) {
						$persName .= $person->gender;
					}
				}
				$authorKey = 'author'.$authors;
				$template->$authorKey = $persName;
			}
			$authors++;
		}
		if($person->role == 'contributor') {
			if($contributors <= 4) {
				$persName = $person->persName;
				if($person->gnd) {
					$persName .= '#'.$person->gnd;
					if($person->gender) {
						$persName .= $person->gender;
					}
				}
				$contributorKey = 'contributor'.$contributors;
				$template->$contributorKey = $persName;
			}
			$contributors++;
		}		
	}
	
	//Einfügen der Ortsdaten
	
	$places = 1;
	
	foreach($item->places as $place) {
		if($places <= 2) {
			$keyPlace = 'place'.$places;
			$string = $place->placeName;
			if($place->geoNames) {
				$string .= '#geoNames'.$place->geoNames;
			}
			elseif($place->gnd) {
				$string .= '#gnd'.$place->gnd;
			}
			elseif($place->getty) {
				$string .= '#getty'.$place->getty;
			}
			$template->$keyPlace = $string;
		}
		$places++;
	}
	
	return($template);
}

?>