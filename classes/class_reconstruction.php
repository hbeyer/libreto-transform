<?php

class reconstruction {

    public $catalogue = null;
    public $content = array();
    public $folder = 'projectFiles';
    public $fileName;
    private $includePathFunctions = 'functions/';
    
    function __construct($path, $fileName, $format = 'xml') {
        $this->fileName = $fileName;
        if ($format == 'xml') {
            $this->loadXML($path);
        }
        else {
            if (file_exists($fileName.'-cat.php')) {
                require($fileName.'-cat.php');
                if (isset($catalogue)) {
                    $this->catalogue = $catalogue;
                    $this->catalogue->fileName = $fileName;
                }
            }
            else {
                copy ('templateCat.php', $fileName.'-cat.php');
                echo 'Bitte füllen Sie die Datei '.$fileName.'-cat.php aus und wiederholen Sie den Vorgang.';
                return(null);
            }
            if ($format == 'php') {
                $string = file_get_contents($path);
                $this->content = unserialize($string);
            }
            /*
            elseif ($format == 'csv') {
                require($this->includePathFunctions.'loadCSV.php');
                $this->content = loadCSV($path);
            }*/
        }
        $this->insertIDs();
    }

    public function enrichData() {
        $this->addVolumeInformation();
        $this->insertGeoData();
        $this->insertBeacon();
    }

    public function saveAllFormats() {
        require($this->includePathFunctions.'makeIndex.php');
        if (!is_dir($this->folder.'/'.$this->fileName)) {
            mkdir($this->folder.'/'.$this->fileName, 0777, true);
        }
        $this->saveGeoData();
        $this->saveXML();        
        $this->saveCSV();
        $this->savePHP();
        $this->saveTEI();
        $this->saveRDF();
        $this->saveSolrXML();
    }    

    private function saveXML() {
        require($this->includePathFunctions.'makeXML.php');
        saveXML($this->content, $this->catalogue, $this->folder.'/'.$this->fileName);
    }

    private function saveCSV() {
        require($this->includePathFunctions.'makeCSV.php');
        makeCSV($this->content, $this->folder.'/'.$this->fileName, $this->fileName);
    }

    private function savePHP() {
        $ser = serialize($this->content);
        $handle = fopen($this->folder.'/'.$this->fileName.'/dataPHP', 'w');
	    fwrite($handle, $ser, 3000000);
    }

    private function saveGeoData() {
        require($this->includePathFunctions.'makeGeoDataSheet.php');
        makeGeoDataSheet($this->content, $this->folder.'/'.$this->fileName, 'csv');
        makeGeoDataSheet($this->content, $this->folder.'/'.$this->fileName, 'kml');
    }

    private function saveTEI() {
        require($this->includePathFunctions.'makeTEI.php');
        require($this->includePathFunctions.'makeSection.php');
        require($this->includePathFunctions.'fieldList.php');
        makeTEI($this->content, $this->folder.'/'.$this->fileName, $this->catalogue);
    }

    private function saveRDF() {
        require($this->includePathFunctions.'makeRDF.php');
        saveRDFtoPath($this->content, $this->catalogue, $this->folder.'/'.$this->fileName.'/'.$this->fileName);
    }

    private function saveSolrXML() {
        require($this->includePathFunctions.'makeSolrXML.php');
        saveSolrXML($this->content, $this->catalogue, $this->folder.'/'.$this->fileName.'/'.$this->fileName);
    }

    private function loadXML($path) {
        require($this->includePathFunctions.'loadXML.php');
	    $xml = new DOMDocument();
	    $xml->load($path);
	    $metadataNode = $xml->getElementsByTagName('metadata');
	    if($metadataNode->item(0)) {
		    $this->catalogue = loadMetadataFromNode($metadataNode->item(0));
	    }
	    $resultArray = array();
	    $nodeList = $xml->getElementsByTagName('item');
	    foreach ($nodeList as $node) {
		    $item = makeItemFromNode($node);
		    $resultArray[] = $item;
	    }
	    $this->content = $resultArray;
    }

    private function insertIDs() {
        $count = 0;
        foreach ($this->content as $item) {
            $item->id = $this->fileName.$count; 
            $count++;
        }
    }

    private function addVolumeInformation() {
        $countVolumes = -1;
        $result = array();
        foreach ($this->content as $item) {
            if ($item->itemInVolume == 1) {
                $countVolumes++;
            }
            if ($item->itemInVolume > 0) {
                $item->volumeNote = array('misc' => 'br:'.$this->fileName.'/miscellany_'.$countVolumes, 'positionMisc' => $item->itemInVolume);
            }
            $result[] = $item;
        }
        $this->content = $result;
    }

    public function insertBeacon() {
        $repository = new beacon_repository;
        $gnds = array();
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if ($person->gnd) {
                    $gnds[] = $person->gnd;
                }
            }
        }
        $gnds = array_unique($gnds);
        $matches = $repository->getMatchesMulti($gnds);
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if ($person->gnd) {
                    if (!empty($matches[$person->gnd])) {
                        $person->beacon = $matches[$person->gnd];
                    }
                }
            }
        }
        return(true);
    }

    private function insertGeoData() {

        $geoSystems = array('geoNames' => 'makeEntryFromGeoNames', 'gnd' => 'makeEntryFromGNDTTL', 'getty' => 'makeEntryFromGetty');
        $archives = array();		
        $archives['geoNames'] = new GeoDataArchive();
		$archives['geoNames']->loadFromFile('geoNames');
		$archives['getty'] = new GeoDataArchive();
		$archives['getty']->loadFromFile('getty');
		$archives['gnd'] = new GeoDataArchive();
		$archives['gnd']->loadFromFile('gnd');			
			
		$unidentifiedPlaces = array();
		$placeFromArchive = '';
		$countWebDownloads = 0;
		
		foreach($this->content as $item) {
			foreach($item->places as $place) {
				
				$searchName = trim($place->placeName, '[]');

				$testUnidentified = preg_match('~^[sSoO]\.? ?[oOlL].?$|^[oO]hne Ort|[sS]ine [lL]oco|[oO]hne Angabe~', $searchName);
				if(strstr($searchName, 'fingiert') != FALSE) {
					$testUnidentified = 1;
				}
				elseif($searchName == '') {
					$testUnidentified = 1;
				}
				$testGeoData = 0;
				if($place->geoData['lat'] and $place->geoData['long']) {
					$testGeoData = 1;
				}
				if($testUnidentified == 1) {
					$place->placeName = 's. l.';
				}
				
				if($testUnidentified == 0 and $testGeoData == 0) {

                    foreach ($geoSystems as $system => $function) {
					    if($place->$system) {
						    $placeFromArchive = $archiveGeoNames->getByGeoNames($place->$system);
						    if($placeFromArchive == NULL) {
							    $placeFromWeb = $archiveGeoNames
							    ->$function($place->geoNames, $userGeoNames);
							    if($placeFromWeb) {
								    $archives[$system]->insertEntryIfNew($system, $place->$system, $placeFromWeb);
								    $placeFromArchive = $placeFromWeb;
								    $countWebDownloads++;
							    }
						    }
                        $testGeoData = 1;
                        break;
					    }
                    }
					if ($testGeoData == 0) {
						$placeFromArchive = $archives['geoNames']->getByName($searchName);
					    if ($placeFromArchive) {
						    $place->geoData['lat'] = $placeFromArchive->lat;
						    $place->geoData['long'] = $placeFromArchive->long;
					    }
					    elseif ($testUnidentified == 0) {
						    $unidentifiedPlaces[] = $searchName;
					    }
					}
                }
            }
        }
    	$archives['geoNames']->saveToFile('geoNames');
		$archives['getty']->saveToFile('getty');
		$archives['gnd']->saveToFile('gnd');
    } 

}

?>
