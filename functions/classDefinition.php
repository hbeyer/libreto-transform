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
		public $listFacets = array();
		public $cloudFacets = array();
		public $doughnutFacets = array();
	}
	
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
			public $titleBib;	//The title as copied from a bibliographic database (cf. $manifestation)
			public $titleNormalized; // A normalized form of the title to facilitate reading and searching
			public $persons = array(); //Objects of the class person
			public $places = array(); //Objects of the class place
			public $publisher;
			public $year;
			public $format;
			public $histSubject;
			public $histShelfmark;
			public $subjects = array(); // Contains one ore more indications of subject as string
			public $genres = array(); // Contains one ore more indications of genre as string
			public $mediaType; //Book, Manuscript, Physical Object, etc.
			public $languages = array(); //One or more language codes according to ISO 639.2
			public $manifestation = array('systemManifestation' => '', 'idManifestation' => ''); //Entry in a bibliographic database or library catalogue			
			public $originalItem = array('institutionOriginal' => '', 'shelfmarkOriginal' => '', 'provenanceAttribute' => '', 'digitalCopyOriginal' => '', 'targetOPAC' => '', 'searchID' => '');
			public $work = array('titleWork' => '', 'systemWork' => '', 'idWork' => ''); //Entry for the work in a public database.			
			public $bound = 1;
			public $comment;
			public $digitalCopy;
			public $copiesHAB = array(); //Array of shelfmarks from the HAB
		}
		
	class person {
		public $persName;
		public $gnd;
		public $gender;
		public $role; //author, contributor, etc.
		public $beacon = array(); //Presence in databases is denoted by keys from class beaconData
	}
		
	class place {
		public $placeName;
		public $geoNames;
		public $gnd;
		public $getty;
		public $geoData = array('lat' => '', 'long' => '');
	}
	
	class volume { // A list of items bound together in one volume
		public $content = array();
		// In case several items have been bound to more than one volume, this can be indicated here.
		public $volumes = 1;
	}

	class section { // A list of items with a title to be displayed as a chapter of a web page
		public $label;
		public $quantifiedLabel;
		public $level = 1;
		public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the section, especially a persons's GND identifier, cf. class indexEntry
		public $content = array(); //Objects of the class item
	}
		
	class indexEntry {
		public $label;
		public $level = 1;
		public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the index entry, especially a persons's GND identifier, cf. class section
		public $geoData = array('lat' => '', 'long' => ''); //For if the entry corresponds to a place
		public $content = array(); //Indices from an array which contains objects of the class item
	}
	
	class beaconData { //A collection of extracts from BEACON files, based on the set of authors present in the catalogue
		public $date;
		public $content = array(); //Objects of the class beaconExtract
	}
	
	class beaconExtract { //A collection of GND identifiers extracted from a certain BEACON file 
		public $label;
		public $key; //An index from the associative array $beaconSources
		public $target;
		public $content = array(); //The GND identifiers
	}

	class geoDataRow { //Row in a file for upload in a geolocation system (KML, CSV or else)
			public $label;
			public $description;
			public $address;
			public $weight;
			public $lat;
			public $long;
			public $timeStamp;
			public $timeSpanBegin;
			public $timeSpanEnd;
			public $getty;
			public $geoNames;
	}
	
?>
