<?php

class indexEntry {
	public $label;
	public $level = 1;
	public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the index entry, especially a persons's GND identifier, cf. class section
	public $geoData = array('lat' => '', 'long' => ''); //For if the entry corresponds to a place
	public $content = array(); //Indices from an array which contains objects of the class item
}

?>
