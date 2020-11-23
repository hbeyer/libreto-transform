<?php

class reconstruction {

    public $catalogue = null;
    public $catalogues = array();
    public $metadataReconstruction;
    public $content = array();    
    public $fileName;
    const FOLDER = 'projectFiles';
    const INCLUDEPATH = 'functions/';
    public $valid = 0;
    private $GNDList = array();

    function __construct($path, $fileName, $format = 'xml') {
        $this->fileName = $fileName;
        $this->createDirectory();
        if ($format == 'xml') {
            $uploader = new uploader_xml($path, $this->fileName);
        }
        elseif ($format == 'xml_full') {
            $uploader = new uploader_xml_full($path, $this->fileName);
        }
        elseif ($format == 'csv') {
            $uploader = new uploader_csv($path, $this->fileName);
        }
        elseif ($format == 'php') {
            $uploader = new uploader_php($path, $this->fileName);
        }
        elseif ($format == 'sql_dh') {
            $uploader = new uploader_sql_dh($this->fileName);
        }        
        else {
            throw new Exception('Falsche Formatangabe: '.$format.'. Erlaubt sind xml, csv, xml_full, php und sql_dh', 1);
        }
        $this->metadataReconstruction = $uploader->loadMetadata();
        $this->catalogues = $uploader->loadCatalogues($this->fileName);
        //$this->catalogue = $this->catalogues[0];
        $this->convertMetadataToDefault();
        $this->content = $uploader->loadContent($this->fileName); //Unelegant, weil das nur für xml_full gebraucht wird
        $this->insertIDs();
        if ($format == 'csv' or $format == 'xml' or $format == 'php') {
            $this->convertCatalogueToFull();
            $this->convertContentToFull();
        }
        /*elseif ($format == 'xml_full') {
            $this->convertMetadataToDefault();
        }*/
    }
    
    private function convertCatalogueToFull() {
        $this->catalogues[0]->addSections($this->content);
    }

    private function convertContentToFull() {
        foreach ($this->content as $item) {
            $item->convertToFull();
        }
    }

    private function convertMetadataToDefault() {
        if (empty($this->catalogue) and isset($this->catalogues[0])) {
            $this->catalogue = $this->catalogues[0];
        }
        if ($this->metadataReconstruction) {
            $this->catalogue->importFromMetadataSet($this->metadataReconstruction);
        }
    }

    public function enrichData($beaconUpdate = false) {
        $this->addVolumeInformation();
        $this->insertGeoData();
        $this->insertBeacon($beaconUpdate);
    }

    public function saveAllFormats() {
        $ser = new serializer_php($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new serializer_xml($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();        
        $ser = new serializer_csv($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new serializer_tei($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new serializer_solr($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();        
        $ser = new serializer_rdf($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new serializer_kml($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new serializer_geocsv($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
    }

    public function makeBioDataSheet($restrict = array(), $format = 'csv') {
        if (isset($restrict[0])) {
            $this->makeSelectiveGNDList($restrict);
        }
        else {
            $this->makeCompleteGNDList();
        }
        $cache = new cache_gnd;
        $bio = new geoData_sheet_bio($this->GNDList, $cache);
        if ($format == 'csv') {
            $bio->saveCSV(reconstruction::FOLDER.'/'.$this->fileName.'/');
        }
        else {
            echo 'Diese Methode unterstützt nur CSV';
        }
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

    private function makeCompleteGNDList() {
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if ($person->gnd) {
                    $this->GNDList[] = $person->gnd;
                }
            }
        }
        return($this->GNDList);
    }

    private function makeSelectiveGNDList($include = array()) {
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

    static function getPath($folder, $fileName, $ending = '') {
        if ($ending != '') {
            $ending = '.'.$ending;
        }
        return('projectFiles/'.$folder.'/'.$fileName.$ending);
    }

}

?>
