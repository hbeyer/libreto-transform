<?php
	
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
