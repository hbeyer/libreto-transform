<?php

class  uploader {
    
    public $path;
    public $format;
    public $fileName;
    private $permittedFormats = array('xml', 'csv', 'php');
    public $valid = 0;

    function __construct($path, $fileName = '', $format = '') {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->format = $format;
        if ($this->path and $this->fileName) {
            if (in_array($this->format, $this->permittedFormats)) {
                $this->valid = 1;
            }
        }
    }

    public function load() {
        if ($this->valid != 1) {
            return(null);
        }
        if ($this->format == 'xml') {
            require(reconstruction::INCLUDE.'loadXML.php');
            return($this->loadXML());
        }
        elseif($this->format == 'php') {
            return($this->loadPHP());
        }
        /*
        elseif ($format == 'csv') {
            return($this->loadCSV());
        }*/
    }

    public function loadMetadata() {
        if ($this->valid == false) {
            return(null);
        }
        if ($this->format == 'xml') {
            return($this->loadMetaXML());
        }
        else {
            return($this->loadMetaFile());
        }
    }

    private function loadXML() {
	    $xml = new DOMDocument();
	    $xml->load($this->path);
	    $resultArray = array();
	    $nodeList = $xml->getElementsByTagName('item');
	    foreach ($nodeList as $node) {
		    $item = makeItemFromNode($node);
		    $resultArray[] = $item;
	    }
	    return($resultArray);
    }

    private function loadPHP() {
        $string = file_get_contents($this->path);
        $content = unserialize($string);
        return($content);
    }
    
    private function loadMetaXML() {
	    $xml = new DOMDocument();
	    $xml->load($this->path);
	    $metadataNode = $xml->getElementsByTagName('metadata');
        $catalogue = new catalogue;
	    if($metadataNode->item(0)) {
		    $catalogue = loadMetadataFromNode($metadataNode->item(0));
	    }
	    return($catalogue);        
    }
    
    private function loadMetaFile() {
        $metaPath = reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-cat.php';
        if (!file_exists($metaPath)) {
           copy ('templateCat.php', $metaPath);
           chmod($metaPath, 0777);
           echo 'Bitte füllen Sie die Datei '.$this->fileName.'-cat.php aus und wiederholen Sie den Vorgang.';
           $this->valid = 0;
           return(null);          
        }
        else {
            require($metaPath);
            if (!empty($catalogue)) {
                foreach ($catalogue as $value) {
                    if (substr($value, 0, 1) == '{') {
                       echo 'Bitte füllen Sie die Datei '.$this->fileName.'-cat.php richtig aus und wiederholen Sie den Vorgang.';
                       $this->valid = 0;
                       return(null);   
                    }
                }
                return($catalogue);
            }
        }
    }

}

?>
