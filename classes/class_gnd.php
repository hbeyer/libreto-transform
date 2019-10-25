<?php

class gnd {
	
	public $id;
	public $valid = false;

	function __construct($id) {
		$this->id = substr($id, 0, 10);
		if (preg_match('~[0-9X-]{9,10}~', $this->id) == 1) {
			$this->valid = true;
		}
	}

	function __toString() {
		return($this->id);
	}

}

?>