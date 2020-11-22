<?php

class cache_gnd extends cache {

	public $folder = 'cache/gnd';

	public function getFromWeb($id) {
		$string = @file_get_contents('http://hub.culturegraph.org/entityfacts/'.$id);
		if ($string) {
			file_put_contents($this->folder.'/'.$id, $string);
			chmod($this->folder.'/'.$id, 0777);
			return($string);
		}
		return(false);
	}

}

?>