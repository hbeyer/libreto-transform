<?php

class Cache_Signaturen_HAB extends Cache {

	public $folder = 'cache/signaturen_hab';

    private function make_file_name($sig) {
        $sig_fileName = strtr(strtolower($sig), [':' => '', ' ' => '-', '.' => '-', '(' => '-', ')' => 's', '°' => 'f']);
        $sig_fileName = strtr($sig_fileName, ['---' => '-', '--' => '-']);
        $sig_fileName = trim($sig_fileName, '-');
        return($sig_fileName);
    }

	public function getFromWeb($sig) {
        $sig_uri = strtr($sig, [' ' => '+']);
        $sig_fileName = $this->make_file_name($sig);
		$string = file_get_contents('http://localhost/signaturen/public/resolve.php?shelfmark='.$sig_uri.'&format=json');
		if ($string) {
			file_put_contents($this->folder.'/'.$sig_fileName, $string);
			chmod($this->folder.'/'.$sig_fileName, 0777);
			return($string);
		}
		return(null);
	}

	public function getLocal($sig) {
        $sig_fileName = $this->make_file_name($sig);
		if (file_exists($this->folder.'/'.$sig_fileName)) {
			return(file_get_contents($this->folder.'/'.$sig_fileName));
		}
		return(false);
	}


}

?>
