<?php

class  uploader {
    
    public $path;
    public $format;
    public $fileName;
    private $permittedFormats = array('xml', 'csv', 'php', 'sql_dh', 'xml_full');
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
            require(reconstruction::INCLUDEPATH.'loadXML.php');
            return($this->loadXML());
        }
        elseif ($this->format == 'php') {
            return($this->loadPHP());
        }
        elseif ($this->format == 'csv') {
            require(reconstruction::INCLUDEPATH.'loadCSV.php');
            return($this->loadCSV());
        }
        elseif ($this->format == 'sql_dh') {
            return($this->loadSQL_DH());
        }
    }

    public function loadMetadata() {
        if ($this->valid == false) {
            return(null);
        }
        if ($this->format == 'xml') {
            return($this->loadMetaXML());
        }
        if ($this->format == 'xml_full') {
            return($this->loadMetaFullXML());
        }        
        else {
            return($this->loadMetaFile());
        }
    }

    private function loadXML() {
	    $xml = new DOMDocument();
	    $xml->load($this->path);
        if ($xml == FALSE) {
            throw new Exception('XML-Dokument ist nicht wohlgeformt');
        }
        if (!$xml->schemaValidate('uploadXML.xsd')) {
            throw new Exception('XML-Dokument validiert nicht gegen das Schma uploadXML.xsd');
        }
	    $resultArray = array();
	    $nodeList = $xml->getElementsByTagName('item');
	    foreach ($nodeList as $node) {
		    $item = makeItemFromNode($node);
		    $resultArray[] = $item;
	    }
	    return($resultArray);
    }

    private function loadCSV() {
        $test = validateCSV($this->path, 40);
        if ($test !== 1) {
            $this->valid = 0;
            return;
        }
        return(loadCSV($this->path));
        
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

    private function loadMetaFullXML() {
        $xml = new DOMDocument();
        $xml->load($this->path);
        $metadataNode = $xml->getElementsByTagName('metadata');
        /*
        Hier muss grundlegend umstrukturiert werden: Es gibt Metadaten für die Rekonstruktion und einen oder mehr Kataloge
        $catalogue = new catalogue;
        if($metadataNode->item(0)) {
            $catalogue = loadMetadataFromNode($metadataNode->item(0));
        }
        return($catalogue);        
        */
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

    private function loadSQL_DH() {

        require('private/connectionData.php');

        try {
             $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }

        $data = $pdo->query('SELECT * FROM Zusammenfassung')->fetchAll(PDO::FETCH_CLASS, 'item');
        $persons = $pdo->query('SELECT * FROM Autor')->fetchAll(PDO::FETCH_CLASS, 'person');
        $data = $this->enrichPersons($data, $persons);
        unset($persons);
        $places = $pdo->query('SELECT * FROM Ort')->fetchAll(PDO::FETCH_CLASS, 'place');
        $data = $this->enrichPlaces($data, $places);

        return($data);

    }

    private function enrichPersons($data, $personList) {
        foreach ($data as $item) {
            foreach ($item->persons as $person) {
                $person->enrichByName($personList);
            }
        }
        return($data);
    }

    private function enrichPlaces($data, $placeList) {
        foreach ($data as $item) {
            foreach ($item->places as $place) {
                $place->enrichByName($placeList);
            }
        }
        return($data);
    }

}

?>
