<?php

class beaconExtract { //A collection of GND identifiers extracted from a certain BEACON file 
	public $label;
	public $key; //An index from the associative array $beaconSources
	public $target;
	public $content = array(); //The GND identifiers
}

?>