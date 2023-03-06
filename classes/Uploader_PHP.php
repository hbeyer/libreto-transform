<?php

class Uploader_PHP extends Uploader {

    private $unserialized;

    function __construct($path, $fileName) {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->metaPath = Reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-metadata.xml';
	    $string = file_get_contents($this->path);
        $this->unserialized = unserialize($string);
        $this->validate();
    }

    public function loadContent($fileName = '') {
        if ($this->valid != 1) {
            return(false);
        }
        return($this->unserialized);
    }

    private function validate() {
        if (!is_array($this->unserialized)) {
            throw new Exception('Kein Content vorhanden.', 1);
        }
        if (!isset($this->unserialized[0])) {
            throw new Exception('Kein Content vorhanden.', 1);
        }
        if (!is_object($this->unserialized[0])) {
            throw new Exception('Keine Objekte geladen.', 1);
        }
        if (get_class($this->unserialized[0]) != 'Item') {
            throw new Exception('Keine Objekte der Klasse item geladen.', 1);
        }
        $this->valid = 1;
        return(true);
    }

}

?>
