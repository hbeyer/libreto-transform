<?php

class IndexEntry {
	public $label;
	public $level = 1;
	public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the index entry, especially a persons's GND identifier, cf. class Section
	public $geoData = array('lat' => '', 'long' => ''); //For if the entry corresponds to a place
	public $content = array(); //Indices from an array which contains objects of the class Item

	public function validateGeoData() {
		if($this->geoData['lat'] == '' or $this->geoData['long'] == '' or $this->label == '' or $this->label == 's. l.') {
			return(false);
		}
		return(true);
	}

}

?>