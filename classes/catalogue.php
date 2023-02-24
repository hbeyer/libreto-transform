<?php

class catalogue {

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
		$index = new index($items, 'histSubject');
		$count = 1;
		foreach ($index->entries as $entry) {
			$section = new catalogue_section('sect'.$count, $entry->label);
			$this->sections[] = $section;
			$count++;
		}
	}

	public function importFromMetadataSet(metadata_reconstruction $set) {
		foreach ($set as $key => $value) {
			if (property_exists('catalogue', $key)) {
				$this->$key = $value;
			}
		}
	}

	public function makeMetadataSet() {
		$set = new metadata_reconstruction;
		foreach ($this as $key => $value) {
			if (property_exists('metadata_reconstruction', $key)) {
				$set->$key = $value;
			}
		}
		return($set);
	}

}

?>