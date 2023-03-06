<?php

class Cache {
	
	public $folder = 'cache/undefined';

	function __construct() {
		if (!is_dir($this->folder)) {
			mkdir($this->folder, 0777, true);
		}
	}	

	public function get($id) {
		if ($this->getLocal($id) == false) {
			return($this->getFromWeb($id));
		}
		return($this->getLocal($id));
	}

	public function getFromWeb($id) {
	}

	public function getLocal($id) {
		if (file_exists($this->folder.'/'.$id)) {
			return(file_get_contents($this->folder.'/'.$id));
		}
		return(false);
	}

}

?>