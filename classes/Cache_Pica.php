<?php

class Cache_Pica extends Cache {

	public $folder = 'cache/pica';

	public function getFromWeb($url) {
		$string = @file_get_contents($url);
		$fileName = md5($url);
		if ($string) {
			file_put_contents($this->folder.'/'.$fileName, $string);
			chmod($this->folder.'/'.$fileName, 0777);
			return($string);
		}
		return(false);
	}

	public function getLocal($url) {
		$fileName = md5($url);
		if (file_exists($this->folder.'/'.$fileName)) {
			return(file_get_contents($this->folder.'/'.$fileName));
		}
		return(false);
	}

}

?>
