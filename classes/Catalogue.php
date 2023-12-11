<?php

class Catalogue {

	public $id;

	// Die folgenden Variablen müssen auf die Ebene reconstruction gezogen werden:
	public $heading;
	public $owner; // A person or institution that owned the collection
	public $ownerGND; // The GND of this person or institution
	//public $fileName; //The file name to be used in the URL
	public $description;
	public $geoBrowserStorageID;
	public $geoBrowserStorageID_bio;
	public $creatorReconstruction;
	public $yearReconstruction;

	// Die folgenden Variablen bleiben:
	public $base; //The string to be put before the image number of a digitized catalogue page
	public $title;
	public $placeCat;
	public $printer;
	public $year;
	public $institution; //Institution possessing the copy of the catalogue
	public $shelfmark; //Shelfmark of the copy of the catalogue

	// Zusätzliche Variablen
	public $persons = array(); // Beteiligte der Rekonstruktion als Objekte vom Typ person
	public $places = array();
	public $sections = array();

	public function addSections($items) {
		$index = new Index($items, 'histSubject');
		$count = 1;
		foreach ($index->entries as $entry) {
			$section = new CatalogueSection('sect'.$count, $entry->label);
			$this->sections[] = $section;
			$count++;
		}
	}

	public function getSectID($histSubject) {
	    foreach($this->sections as $sec) {
	    if ($sec->label == $histSubject) {
	            return($sec->id);
	        }
	    }
	    return("");
	}

	public function importFromMetadataSet(MetadataReconstruction $set) {
		foreach ($set as $key => $value) {
			if (property_exists('Catalogue', $key)) {
				$this->$key = $value;
			}
		}
	}

	public function makeMetadataSet() {
		$set = new MetadataReconstruction;
		foreach ($this as $key => $value) {
			if (property_exists('MetadataReconstruction', $key)) {
				$set->$key = $value;
			}
		}
		return($set);
	}

    public function writeMetadata($fileName) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElement('catalogue');
        $fields = array('heading', 'owner', 'ownerGND', 'description', 'geoBrowserStorageID', 'geoBrowserStorageID_bio', 'creatorReconstruction', 'yearReconstruction', 'base', 'title', 'placeCat', 'printer', 'year', 'institution', 'shelfmark');
        foreach ($fields as $field) {
            if (empty($this->$field)) {
                continue;
            }
            $new = $dom->createElement($field);
            $text = $dom->createTextNode($this->$field);
            $new->appendChild($text);
            $root->appendChild($new);
        }
        $dom->appendChild($root);
        $path = Reconstruction::getPath($fileName, $fileName.'-metadata', 'xml');
        file_put_contents($path, $dom->saveXML());
        chmod($path, 0777);
    }

}

?>
