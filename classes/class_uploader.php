<?php

class  uploader {
    
    public $path;
    public $format;
    public $fileName;
    private $permittedFormats = array('xml', 'csv', 'php');
    public $valid = false;

    function __construct($path, $format = '', $fileName = '') {
        $this->path = $path;
        $fileName = array_pop(explode('/', $path));        
        $fileNameExplode = explode('.', $fileName);
        if (!empty($fileNameExplode[0])) {
            $this->fileName = $fileNameExplode[0];
        }
        if (!empty($fileNameExplode[1])) {
            $this->format = $fileNameExplode[1];
        }
        if ($fileName != '') {
            $this->fileName = $fileName;
        }
        if ($format != '') {
            $this->format = $format;
        }
        if ($this->$path and $this->fileName and in_array($this->format, $this->permittedFormats)) {
            $this->valid = true;
        }
    
    }

    public function load() {
        if ($this->valid == false) {
            return(null);
        }
        if ($format == 'xml') {
            return($this->loadXML());
        }
        elseif($format == 'php') {
            return($this->loadPHP());
        }
        elseif ($format == 'csv') {
            return($this->loadCSV());
        }
    }

}

?>
