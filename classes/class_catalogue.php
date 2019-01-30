<?php

class catalogue {
	public $owner; // A person or institution that owned the collection
	public $ownerGND; // The GND of this person or institution
	public $fileName; //The file name to be used in the URL
	public $base; //The string to be put before the image number of a digitized catalogue page
	public $heading;
	public $title;
	public $placeCat;
	public $printer;
	public $year;
	public $institution; //Institution possessing the copy of the catalogue
	public $shelfmark; //Shelfmark of the copy of the catalogue
	public $description;
	public $geoBrowserStorageID;
	public $creatorReconstruction;
	public $yearReconstruction;
}

?>