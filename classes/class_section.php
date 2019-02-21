<?php

class section { // A list of items with a title to be displayed as a chapter of a web page
	public $label;
	public $quantifiedLabel;
	public $level = 1;
	public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the section, especially a persons's GND identifier, cf. class indexEntry
	public $content = array(); //Objects of the class item
}

?>
