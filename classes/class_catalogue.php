<?php

class catalogue {

	public $id;

	// Die folgenden Variablen müssen auf die Ebene reconstruction gezogen werden:
	public $heading;
	public $owner; // A person or institution that owned the collection
	public $ownerGND; // The GND of this person or institution
	public $fileName; //The file name to be used in the URL
	public $description;
	public $geoBrowserStorageID;
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
	// Zusätzliche Variable
	public $persons = array();
	public $sections = array();

}

?>