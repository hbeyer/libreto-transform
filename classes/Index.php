<?php

class Index {
	
	public $field;
	public $entries = array();
	private $data = null;
	private $beaconRep;

	public $normalFields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'volumes', 'volumesMisc', 'titleCat', 'titleBib', 'titleNormalized', 'year', 'format', 'histSubject', 'histShelfmark', 'mediaType', 'bound', 'comment', 'digitalCopy');
	public $personFields = array('persName', 'gnd', 'gender', 'role', 'beacon');
	public $placeFields = array('placeName', 'getty', 'geoNames');
	//$arrayFields = array('languages', 'subjects', 'genres', 'beacon');
	public $arrayFields = array('languages', 'languagesOriginal', 'subjects', 'genres', 'publishers');
	public $workFields = array('titleWork', 'systemWork', 'idWork');
	public $manifestationFields = array('systemManifestation');
	public $originalItemFields = array('institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'targetOPAC', 'searchID');
	// The following values do not correspond to a field, but they can be submitted to the function __construct()
	public $virtualFields = array('catSubjectFormat', 'borrower', 'translator');

	// The following fields are displayed with miscellanies as unordered lists
	public $volumeFields = array('numberCat', 'catSubjectFormat', 'histSubject');
	public $indexFields = array();

	function __construct($data, $field) {
		$this->field = $field;
		$this->data = $data;
		$this->indexFields = array_merge($this->normalFields, $this->personFields, $this->placeFields, $this->arrayFields, $this->workFields, $this->manifestationFields, $this->originalItemFields, $this->virtualFields);
		$this->beaconRep = new BeaconRepository(false);
		if(in_array($this->field, $this->normalFields)) {
			$collect = $this->collectIDs();
		}
		elseif($this->field == 'beacon') {
			$collect = $this->collectIDsBeacon();
		}
		elseif($this->field == 'persName') {
			$collect = $this->collectIDsPersons();
		}
		elseif ($this->field == 'borrower') {
			$collect = $this->collectIDsPersons('borrower');
		}
		elseif ($this->field == 'translator') {
			$collect = $this->collectIDsPersons('translator');
		}
		elseif($this->field == 'placeName') {
			$collect = $this->collectIDsPlaces();
		}
		elseif($this->field == 'dateLending') {
			$collect = $this->collectIDsDateLending();
		}	
		elseif(in_array($this->field, $this->personFields)) {
			$collect = $this->collectIDsSubObjects('persons');
		}
		elseif(in_array($this->field, $this->placeFields)) {
			$collect = $this->collectIDsSubObjects('places');
		}
		elseif(in_array($this->field, $this->manifestationFields)) {
			$collect = $this->collectIDsAssocArrayValues('manifestation');
		}
		elseif(in_array($this->field, $this->workFields)) {
			$collect = $this->collectIDsAssocArrayValues('work');
		}
		elseif(in_array($this->field, $this->originalItemFields)) {
			$collect = $this->collectIDsAssocArrayValues('originalItem');
		}			
		elseif(in_array($this->field, $this->arrayFields)) {
			$collect = $this->collectIDsArrayValues();
		}
		
		if($this->field == 'histSubject') {
			$this->entries = $this->makeEntries($collect);
		}
		elseif($this->field == 'numberCat') {
			$collect = Index::sortCollectInt($collect);
			$this->entries = $this->makeEntries($collect);
		}
		elseif(isset($collect)) {
			$collect = Index::sortCollect($collect);
			$this->entries = $this->makeEntries($collect);
		}
		elseif($this->field == 'catSubjectFormat') {
			$collect1 = $this->collectIDs('histSubject');
			$index1 = $this->makeEntries($collect1);
			unset($collect1);
			$collect2 = $this->collectIDs('format');
			$index2 = $this->makeEntries($collect2);
			$this->entries2 = $this->makeEntries($collect2);
			unset($collect2);
			$this->entries = Index::mergeIndices($index1, $index2);
		}
		
		foreach($this->entries as $entry) {
			$entry->label = Index::postprocessFields($field, $entry->label);
		}
		$this->data = null;
		return(true);

	}

	private function makeEntries($collect) {
		$collectLoop = $collect['collect'];
		$index = array();
		foreach($collectLoop as $value => $IDs) {
			$entry = new IndexEntry();
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
			if($this->field == 'persName') {
				$entry->level = 2;
			}
			elseif($this->field == 'year' and $value != 9999) {
				$entry->level = 2;
			}
			elseif($this->field == 'id' or $this->field == 'numberCat') {
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

	static function mergeIndices($index1, $index2) {
		$commonIndex = array();
		foreach($index1 as $entry1) {
			$higherEntry = new IndexEntry();
			$higherEntry->label = $entry1->label;
			$higherEntry->authority = $entry1->authority;
			$higherEntry->geoData = $entry1->geoData;
			$commonIndex[] = $higherEntry;
			foreach($index2 as $entry2) {
				$intersection = array_intersect($entry1->content, $entry2->content);
				if($intersection) {
					$lowerEntry = new IndexEntry();
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

	private function collectIDs($field = null) {
		if ($field == null) {
			$field = $this->field;
		}
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			$key = Index::preprocessFields($field, $item->$field, $item);
			if(array_key_exists($key, $collect) == FALSE) {
				$collect[$key] = array();
			}
			$collect[$key][] = $count;
			$count ++;
		}
		$return = array('collect' => $collect);
		return($return);
	}

	private function collectIDsArrayValues() {
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			$fieldStr = $this->field;
			foreach($item->$fieldStr as $key) {
				$key = Index::preprocessFields($this->field, $key, $item);
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

	private function collectIDsAssocArrayValues($field) {
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			// Der folgende Umweg wird nötig, weil $item->$field[$subfield] eine Fehlermeldung produziert.
			$keyArray = $item->$field;
			$index = $this->field;
			$key = $keyArray[$this->field];	
			$key = Index::preprocessFields($this->field, $key, $item);
			if(array_key_exists($key, $collect) == FALSE) {
				$collect[$key] = array();
			}
			$collect[$key][] = $count;
			$count ++;
		}
		$return = array('collect' => $collect);
		return($return);
	}


	private function collectIDsSubObjects($subField) {
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			$fieldStr = $this->field;
			foreach($item->$subField as $subItem) {
				$key = Index::preprocessFields($subField, $subItem->$fieldStr, $item);
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

	private function collectIDsBeacon() {
	    $beaconSources = $this->beaconRep->beacon_sources;
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			foreach($item->persons as $person) {
				foreach($person->beacon as $beacon) {
					if (empty($beaconSources[$beacon])) {
						continue;
					}
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

	private function collectIDsDateLending() {
		$collect = array();
		$count = 0;
		foreach($this->data as $item) {
			foreach($item->persons as $person) {
				foreach($person->dateLending as $dateLending) {
					if(array_key_exists($dateLending, $collect) == FALSE) {
						$collect[$dateLending] = array();
					}
					$collect[$dateLending][] = $count;				
				}
			}
		$count++;
		}
		$return = array('collect' => $collect);
		return($return);	
	}

	private function collectIDsPersons($role = '') {
		$collectGND = array();
		$collectName = array();
		$count = 0;
		foreach($this->data as $item) {
			$gnds = array();
			foreach($item->persons as $person) {
                // Das Folgende vereinfachen?
				if ($role == 'borrower' and $person->role != 'borrower') {
					continue;
				}
				if ($role != 'borrower' and $person->role == 'borrower') {
					continue;
				}
				if ($role == 'translator' and $person->role != 'ÜbersetzerIn') {
					continue;
				}
				if ($role != 'translator' and $person->role == 'ÜbersetzerIn') {
					continue;
				}
				$key = $person->gnd;
				$name = Index::preprocessFields('persName', $person->persName, $item);
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

	private function collectIDsPlaces() {
		$collectPlaceName = array();
		$collectGeoData = array();
		$collectPlaceObjects = array();
		$count = 0;
		foreach($this->data as $item) {
			foreach($item->places as $place) {
				$key = Index::preprocessFields('placeName', $place->placeName, $item);
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

	static function preprocessFields($field, $value, $item) {
        if ($value == null) {
            $value = '';
        }
		$value = trim($value, '[]');
		if($field == 'persName') {
			$value = removeSpecial($value);
			$value = replaceArrowBrackets($value);
		}
		elseif($field == 'placeName') {
			$value = trim($value, '[]');
			$test = preg_match('~^\[?([sS]\. ?[lL]\.|o\. ?O\.|[oO]hne Ort|[sS]ine [lL]oco|[oO]hne Druckort|[oO]hne Angabe)\]?$~', $value);
			if($value == '' or $test == 1) {
				$value = 's. l.';
			}
		}
		elseif($field == 'publishers') {
			$value = trim($value, '[]');
			$test = preg_match('~^\[?(Verlag nicht nachweisbar|o\. ?N\.|s\. ?n\.|ohne Angabe|unbekannt|ohne Namen?)\]?$~', $value);
			if($value == '' or $test == 1) {
				$value = 's. n.';
			}
		}	
		elseif($field == 'year') {
			$value = Index::normalizeYear($value);
			if($value == '') {
				$value = Index::getYearFromTitle($item->titleCat);
			}
			if($value == '') {
				$value = 9999; // Makes empty year fields be sorted to the end
			}
		}
		elseif(in_array($field, ['languages', 'languagesOriginal'])) {
			$value = LanguageReference::getLanguage($value);
			if($value == '') {
				$value = 'ohne Angabe';
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

	static function postprocessFields($field, $value) {
		// Ist nicht ideal, weil auch label vom Typ histSubject erfasst werden, aber vermutl. 
		// keine praktische Auswirkung, weil die Ersetzungsfunktion sehr eng gefasst ist.
		if($field == ('format' or 'catSubjectFormat')) {
			$value = reverseSortingFormat($value);
		}
		if($field == 'languages') {
			if($value == '' or $value == 'ohne Kategorie') {
				$value = 'ohne Angabe';
			}
			else {
	            $langName = LanguageReference::getLanguage($value);
	            if ($langName) {
	                $value = $langName;
	            }
			}
		}
		if($field == 'gender') {
			$value = translateGenderAbbr($value);
			if($value == '') {
				$value = 'ohne Angabe';
			}
		}
		if($field == 'mediaType') {
			$value = translateMediaType($value);
			if($value == '') {
				$value = 'ohne Angabe';
			}
		}
		if($field == 'bound') {
			if($value == 0) {
				$value = 'ungebunden';
			}
	        else {
	            $value = 'gebunden';
	        }
		}
		if($field == 'year') {
			if($value == 9999) {
				$value = 'ohne Jahr';
			}
		}
		return($value);
	}

	static function sortCollect($collect) {
		if(isset($collect['concordanceGND'])) {
			$sortingConcordance = array_flip($collect['concordanceGND']);
			uksort($sortingConcordance, 'Index::cmpStr');
			$new = array();
			foreach($sortingConcordance as $name => $gnd) {
				$new[$gnd] = $collect['collect'][$gnd];
			}
			$collect['collect'] = $new;
		}
		else {
			uksort($collect['collect'], 'Index::cmpStr');
		}
		$collect['collect'] = Index::postponeVoid($collect['collect']);
		return($collect);
	}

	static function sortCollectInt($collect) {
		ksort($collect['collect']);
		$collect['collect'] = Index::postponeVoid($collect['collect']);
		return($collect);
	}

	/* The function puts all "void" categories to the end of the array that contains labels and indices. "ohne Jahr" does not appear, because it is treated by the functions preprocessFields and postprocessFields  */
	static function postponeVoid($collect) {
		$voidTerms = array('ohne Kategorie', 's. n.', 's. l.', 'Unbestimmbare Sprache', 'ohne Werktitel');
		foreach($collect as $key => $value) {
			$test1 = in_array($key, $voidTerms);
			if($test1) {
				if(isset($voidKey)) {
					throw new Exception('Mehr als ein leeres Feld in dem Index, der mit '.$collect[0].' beginnt.');
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

	static function normalizeYear($year) {
		if(preg_match('~([12][0-9][0-9][0-9])[-– ]{1,3}([12][0-9][0-9][0-9])~', $year, $treffer)) {
			return(strval(intval(($treffer[1] + $treffer[2]) / 2)));
		}
		elseif(preg_match('~[12][0-9][0-9][0-9]~', $year, $treffer)) {
			return($treffer[0]);
		}
		elseif(preg_match('~([12][0-9])XX~', $year, $treffer)) {
			return($treffer[1].'50');
		}
		elseif(preg_match('~([12][0-9][0-9])X~', $year, $treffer)) {
			return($treffer[1].'5');
		}		
		return('');
	}
		
	static function getYearFromTitle($title) {
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

	static function cmpStr($a, $b) {
		$conc = array('Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'Á' => 'A');
		$a = strtr($a, $conc);
		$b = strtr($b, $conc);
		return strcasecmp($a, $b);
	}


}


?>
