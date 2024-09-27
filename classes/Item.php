<?php

class Item	{ //Refers to an item (book, manuscript, etc.) listed in the catalogue
	public $id;
	public $anchor = "";
	public $pageCat; //Page in the catalogue where the item was found
	public $imageCat; //Image number of the page in the digitized catalogue
	public $numberCat; //Number of the item as found in catalogue
	public $itemInVolume = 0; //If the item is part of a miscellany, the number indicates its position, otherwise it is 0.
	public $volumes = 1; //Number of volumes corresponding to the entry.
	public $volumesMisc; //If the item is part of a miscellany, the number indicates the number of volumes of the miscellany.
	public $volumeNote = array('misc' => '', 'positionMisc' => '');
	public $titleCat; //The title as found in the catalogue
	public $catEntries = array();
	public $titleBib;	//The title as copied from a bibliographic database (cf. $manifestation)
	public $titleNormalized; // A normalized form of the title to facilitate reading and searching
	public $persons = array(); //Objects of the class Person
	public $places = array(); //Objects of the class Place
	public $publishers = array();
	public $publishersObj = array();
	public $year;
	public $format;
	public $histSubject;
	public $histShelfmark;
	public $subjects = array(); // Contains one ore more indications of subject as string
	public $genres = array(); // Contains one ore more indications of genre as string
	public $mediaType; //Book, Manuscript, Physical Object, etc.
	public $languages = array(); //One or more language codes according to ISO 639.2
    public $languagesOriginal = array(); //The same for the original language of a translation
	//public $manifestation = array('systemManifestation' => '', 'idManifestation' => '', 'commentManifestation' => ''); //Entry in a bibliographic database or library catalogue
	public $manifestation = array('systemManifestation' => '', 'idManifestation' => ''); //Entry in a bibliographic database or library catalogue
	//public $originalItem = array('institutionOriginal' => '', 'shelfmarkOriginal' => '', 'provenanceAttribute' => '', 'digitalCopyOriginal' => '', 'targetOPAC' => '', 'searchID' => '', 'OPACLink' => '', 'commentOriginal' => '');
	public $originalItem = array('institutionOriginal' => '', 'shelfmarkOriginal' => '', 'provenanceAttribute' => '', 'digitalCopyOriginal' => '', 'targetOPAC' => '', 'searchID' => '', 'OPACLink' => '');
	public $work = array('titleWork' => '', 'systemWork' => '', 'idWork' => ''); //Entry for the work in a public database.
	public $bound = 1;
	public $comment;
	public $digitalCopy;
	public $copiesHAB = array(); //Array of shelfmarks from the HAB

	// Das Folgende sorgt für den Import aus einer SQL-Datenbank, in die die Hakelberg'sche Tabelle hochgeladen wurde
	public function __set ($name, $value) {

		if (!$value) {
			return;
		}
		$value = trim($value);
		$translation = array(
			'Seite' => 'pageCat',
			'Titel Vorlage' => 'titleCat',
			'Titel bibliographiert' => 'titleBib',
			'Kurztitel' => 'titleNormalized',
			'Jahr' => 'year',
			'Format' => 'format',
			'Sachgruppe hist.' => 'histSubject',
			'Medium' => 'mediaType',
			'Freitext' => 'comment',
			'Kommentar' => 'comment',
			'Digital URN/URL' => 'digitalCopy',
		);
        $name = strtr($name, $translation);
		if ($name == 'Image') {
			$this->imageCat = intval($value);
		}
		elseif ($name == 'Nr.') {
			$this->insertNumber($value);
		}
		elseif (substr($name, 0, 5) == 'Autor') {
			$person = new Person;
            $person->persName = removeBrackets($value);
			$person->role = 'author';
			$this->persons[] = $person;
		}
		elseif (substr($name, 0, 5) == 'Betei') {
			$person = new Person;
            $person->persName = removeBrackets($value);
			$person->role = 'contributor';
			$this->persons[] = $person;
		}
		elseif (substr($name, 0, 3) == 'Ort') {
			$place = new Place;
			$place->placeName = removeBrackets($value);
			$this->places[] = $place;
		}
		//elseif (in_array($name, array('mögl. Nachweise HAB', 'copiesHAB'))) {
		elseif ($name == 'mögl. Nachweise HAB') {
			$values = explode(';', $value);
			$this->copiesHAB = $values;
		}
		elseif (substr($name, 0, 5) == 'Druck') {
			$publishers = explode(';', $value);
			array_map('trim', $publishers);
			array_map('removeBrackets', $publishers);
			$this->publishers = array_merge($this->publishers, $publishers);
		}
		elseif ($name == 'Sachbegriff') {
			$this->subjects = explode(';', $value);
		}
		elseif ($name == 'Gattungsbegriff') {
			$this->genres = explode(';', $value);
		}
		elseif (substr($name, 0, 5) == 'Sprac') {
			$this->languages[] = $value;
		}
		elseif ($name == 'Nachweis') {
			$this->manifestation['systemManifestation'] = $value;
		}
		elseif ($name == 'ID') {
			$this->manifestation['idManifestation'] = $value;
		}
		elseif ($name == 'Form') {
			if ($value == 'ungebunden') {
				$this->bound = 0;
			}
			elseif ($value == 'gebunden') {
				$this->bound = 1;
			}
		}
		elseif (in_array($name, $translation)) {
			$this->$name = $value;
		}
	}

	public function importFirstCatEntry() {
		if (isset($this->catEntries[0])) {
			foreach ($this->catEntries[0] as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	private function insertNumber($number) {
		$explode = explode('S', $number);
		if (!empty($explode[1])) {
			$this->itemInVolume = $explode[1];
		}
		$this->numberCat = $explode[0];
	}

    public function insertBeacon($matches) {
        $personsNew = array();
        foreach ($this->persons as $person) {
            if ($person->gnd) {
                if (!empty($matches[$person->gnd])) {
                    $person->beacon = $matches[$person->gnd];
                }
            }
        $personsNew[] = $person;
        }
        $this->persons = $personsNew;
    }

    public function trimTitles() {
        $fields = array('titleCat', 'titleBib', 'titleNormalized');
        $translation = array('...' => '…');
        foreach ($fields as $field) {
            $this->$field = strtr($this->$field, $translation);
            $this->$field = trim($this->$field);
        }
    }

    public function convertToFull($sectID) {
    	if ($this->titleCat != null and empty($this->catEntries)) {
	    	$catEntry = new CatalogueEntry;
			$catEntry->idCat = 'cat1';
			$catEntry->idSect = $sectID;
			$catEntry->titleCat = $this->titleCat;
			$catEntry->numberCat = $this->numberCat;
			$catEntry->pageCat = $this->pageCat;
			$catEntry->imageCat = $this->imageCat;
			$catEntry->histSubject = $this->histSubject;
			$this->catEntries[] = $catEntry;
    	}
		if ($this->publishersObj != array()) {
			foreach ($this->publishers as $namePublisher) {
				$publisher = new Publisher;
				$publisher->name = $namePublisher;
				$this->publishersObj[] = $publisher;
			}
		}
	}

	public function getPersonCSV($role, $position) {
		$count = 0;
		foreach ($this->persons as $person) {
			if ($role == 'creator' and in_array($person->role, array('creator', 'VerfasserIn', 'author'))) {
				if ($count == $position) {
					return($person->__toString());
				}
				$count++;
			}
			elseif ($role == 'contributor' and !in_array($person->role, array('creator', 'VerfasserIn', 'author', 'borrower'))) {
				if ($count == $position) {
					return($person->__toString());
				}
				$count++;
			}
		}
		return('');
	}

	public function getPlaceCSV($position) {
		if (!empty($this->places[$position])) {
			return($this->places[$position]->toCSV());
		}
		return('');
	}

	public function makeItemName() {
		return('Druck');
		if (!empty($this->manifestation['systemManifestation']) and !empty($this->manifestation['idManifestation'])) {
			return($this->manifestation['systemManifestation'].' '.$this->manifestation['idManifestation']);
		}

		elseif (!empty($this->originalItem['institutionOriginal']) and !empty($this->originalItem['shelfmarkOriginal'])) {
			return($this->originalItem['institutionOriginal'].', '.$this->originalItem['institutionOriginal']);
		}
		/*
		elseif (!empty($this->work['titleWork'])) {
			return(substr($this->work['titleWork'], 0, 15));
		}
		*/
		elseif (!empty($this->titleBib)) {
			return(substr($this->titleBib, 0, 15));
		}
		elseif (!empty($this->titleCat)) {
			return(substr($this->titleCat, 0, 15));
		}
		$return = $this->id;
		if ($this->mediaType) {
			$return = $return.' ('.$this->mediaType.')';
		}
		return($return);
	}

}

?>
