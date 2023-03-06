<?php

class Section { // A list of items with a title to be displayed as a chapter of a web page

	public $quantifiedLabel;
	public $authority = array('system' => '', 'id' => ''); //An authority which describes the content of the section, especially a persons's GND identifier, cf. class IndexEntry
	public $content = array(); //Objects of the class Item


	public function  __construct($label, $level = 1) {
		$this->label = $label;
		$this->level = $level;
	}

	// Erzeugen eines Ankers für interne Links auf Überschriften
	public function makeAnchor() {
		if ($this->authority['system'] == 'gnd' and $this->authority['id']) {
		    $gnd = $this->authority['id'];
		    return('person'.$gnd);
		}
	    return(translateAnchor($this->label));
	}

	// Erzeugen eines Ankers für die Ausklappbaren Informationsicons neben den Überschriften
	public function makeAnchorCollapse() {
		if ($this->authority['system'] == 'gnd' and $this->authority['id']) {
    		return($this->authority['id']);
    	}
    	return(null);
	}

	public function getSize() {
		$size = 0;
		foreach ($this->content as $obj) {
			if (get_class($obj) == 'Item') {
				$size += 1;
			}
			elseif (get_class($obj) == 'Volume') {
				$size += count($obj->content);
			}
		}
		return($size);
	}

}

?>
