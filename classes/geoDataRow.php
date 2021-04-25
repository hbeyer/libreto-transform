<?php

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

		public function serializeCSV() {
			$row = '"'.$this->label.'","'.$this->address.'","'.$this->description.'","'.$this->long.'","'.$this->lat.'","'.$this->timeStamp.'","","'.$this->getty.'",""
';	
			return($row);
		}

		public function insertPlaceFromGNDRequest($request, $type, $geoDataArchive = null) {
			$translate = array('Birth' => 'Geburts', 'Death' => 'Sterbe', 'Activity' => 'Wirkungs');
			if (!isset($translate[$type])) {
				return(false);
			}
			if ($type == 'Activity') {
				$place = array_shift($request->placesActivity);
			}
			else {
				$property = 'place'.$type; 
				$place = $request->$property;
			}
			$this->label = $place->placeName;
			$this->address = $place->placeName;
			$this->description = $place->placeName.' ('.$translate[$type].'ort von '.$request->preferredName.')';
			if ($geoDataArchive != null) {
				$place->addGeoData($geoDataArchive, 'gnd');
				if ($place->geoData['lat'] and $place->geoData['long']) {
					$this->lat = $place->geoData['lat'];
					$this->long = $place->geoData['long'];
				}
			}
			return(true);
		}

}
	
?>