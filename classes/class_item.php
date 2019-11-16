<?php

class item	{ //Refers to an item (book, manuscript, etc.) listed in the catalogue
	public $id;
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
	public $persons = array(); //Objects of the class person
	public $places = array(); //Objects of the class place
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
	public $manifestation = array('systemManifestation' => '', 'idManifestation' => '', 'commentManifestation' => ''); //Entry in a bibliographic database or library catalogue			
	public $originalItem = array('institutionOriginal' => '', 'shelfmarkOriginal' => '', 'provenanceAttribute' => '', 'digitalCopyOriginal' => '', 'targetOPAC' => '', 'searchID' => '', 'OPACLink' => '', 'commentOriginal' => '');
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
        $ignore = array('Q');
        if (in_array($name, $ignore)) {
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
			'Digital URN/URL' => 'digitalCopy'
		);
        $name = strtr($name, $translation);

		if ($name == 'Image') {
			$this->imageCat = intval($value);
		}
		elseif ($name == 'Nr.') {
			$this->insertNumber($value);
		}
		elseif (substr($name, 0, 5) == 'Autor') {
			$person = new person;
            $person->persName = removeBrackets($value);
			$person->role = 'author';
			$this->persons[] = $person;
		}
		elseif (substr($name, 0, 5) == 'Betei') {
			$person = new person;
            $person->persName = removeBrackets($value);
			$person->role = 'contributor';
			$this->persons[] = $person;
		}
		elseif (substr($name, 0, 3) == 'Ort') {
			$place = new place;
			$place->placeName = removeBrackets($value);
			$this->places[] = $place;
		}
/*
		elseif (substr($name, 0, 5) == 'Druck') {
			if (!$this->publisher) {
				$this->publisher = removeBrackets($value);
			}
			else {
				$this->publisher .= '/'.removeBrackets($value);
			}
		}
*/
		elseif (substr($name, 0, 5) == 'Druck') {
			$publishers = explode(';', $value);
			array_map('trim', $publishers);
			array_map('removeBrackets', $publishers);
			array_merge($this->publishers, $publishers);
		}		
		elseif ($name == 'Sachbegriff') {
			$this->subjects[] = $value;
		}
		elseif ($name == 'Gattungsbegriff') {
			$this->genres[] = $value;
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
        elseif ($name == 'Onlinebiographien') {
            return;
        }
        else {
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
	
}

?>
