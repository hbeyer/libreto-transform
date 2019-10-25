<?php

class reconstruction {

    public $catalogue = null;
    public $content = array();
    public $fileName;
    const FOLDER = 'projectFiles';
    const INCLUDEPATH = 'functions/';
    public $valid = 0;
    private $GNDList = array();
    
    function __construct($path, $fileName, $format = 'xml') {
        $this->fileName = $fileName;
        $this->createDirectory();
        $uploader = new uploader($path, $this->fileName, $format);
        $this->content = $uploader->load();
        if ($this->content == null) {
           throw new Exception('Kein Content geladen.');
        }
        $this->insertIDs();
        $this->catalogue = $uploader->loadMetadata();
        if (get_class($this->catalogue) == 'catalogue') {
            $this->valid = 1;
            $this->catalogue->fileName = $this->fileName;
        }
    }

    public function enrichData($beaconUpdate = true) {
        if ($this->valid == 0) {
            return;
        }
        $this->addVolumeInformation();
        $this->insertGeoData();
        $this->insertBeacon($beaconUpdate);
    }

    public function saveAllFormats() {
        if ($this->valid == 0) {
            return;
        }
        require(reconstruction::INCLUDEPATH.'makeIndex.php');
        $this->saveGeoData();
        $this->saveXML();        
        $this->saveCSV();
        $this->savePHP();
        $this->saveTEI();
        $this->saveRDF();
        $this->saveSolrXML();
    }    

    private function saveXML() {
        require(reconstruction::INCLUDEPATH.'makeXML.php');
        saveXML($this->content, $this->catalogue, reconstruction::FOLDER.'/'.$this->fileName);
    }

    private function saveCSV() {
        require(reconstruction::INCLUDEPATH.'makeCSV.php');
        makeCSV($this->content, reconstruction::FOLDER.'/'.$this->fileName, $this->fileName);
    }

    private function savePHP() {
        $ser = serialize($this->content);
        $handle = fopen(reconstruction::FOLDER.'/'.$this->fileName.'/dataPHP', 'w');
	    fwrite($handle, $ser, 3000000);
    }

    private function saveGeoData() {
        require(reconstruction::INCLUDEPATH.'makeGeoDataSheet.php');
        makeGeoDataSheet($this->content, reconstruction::FOLDER.'/'.$this->fileName, 'csv');
        makeGeoDataSheet($this->content, reconstruction::FOLDER.'/'.$this->fileName, 'kml');
    }

    private function saveTEI() {
        require(reconstruction::INCLUDEPATH.'makeTEI.php');
        require(reconstruction::INCLUDEPATH.'makeSection.php');
        require(reconstruction::INCLUDEPATH.'fieldList.php');
        makeTEI($this->content, reconstruction::FOLDER.'/'.$this->fileName, $this->catalogue);
    }

    private function saveRDF() {
        require(reconstruction::INCLUDEPATH.'makeRDF.php');
        saveRDFtoPath($this->content, $this->catalogue, reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName);
    }

    private function saveSolrXML() {
        require(reconstruction::INCLUDEPATH.'makeSolrXML.php');
        saveSolrXML($this->content, $this->catalogue, reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName);
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

    public function insertBeacon($beaconupdate = true) {
        $repository = new beacon_repository($beaconupdate);
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
            $item->insertBeacon($matches);
        }
    }

    public function insertGeoData() {    
        require_once('private/settings.php');
        $archiveGeoNames = new geoDataArchive('geoNames');
        $archiveGND = new geoDataArchive('gnd');
        $archiveGetty = new geoDataArchive('getty');
        foreach ($this->content as $item) {
            foreach ($item->places as $place) {
                if ($place->testIfReal() === 0) {
                    continue;
                }
                if ($place->geoNames) {
                    $place->addGeoData($archiveGeoNames, 'geoNames', $userGeoNames);
                }
                elseif ($place->gnd) {
                    $place->addGeoData($archiveGND, 'gnd');
                }
                elseif ($place->getty) {
                    $place->addGeoData($archiveGetty, 'getty');
                }
            }
        }
        $archiveGeoNames->saveToFile('geoNames');
        $archiveGND->saveToFile('gnd');
        $archiveGetty->saveToFile('getty');
    }

    public function makeCompleteGNDList() {
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if ($person->gnd) {
                    $this->GNDList[] = $person->gnd;
                }
            }
        }
        return($this->GNDList);
    }

    public function makeSelectiveGNDList($include = array()) {
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if (in_array($person->role, $include)) {
                    $this->GNDList[] = $person->gnd;
                }
            }
        }
        return($this->GNDList);        
    }

    private function createDirectory() {
        if (!is_dir(reconstruction::FOLDER.'/'.$this->fileName)) {
            mkdir(reconstruction::FOLDER.'/'.$this->fileName, 0777, true);
        }
    }

}

?>
