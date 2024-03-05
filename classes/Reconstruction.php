<?php

class Reconstruction {

    public $catalogue = null;
    public $catalogues = array();
    public $metadataReconstruction;
    public $content = array();
    public $fileName;
    const FOLDER = 'projectFiles';
    const INCLUDEPATH = 'functions/';
    public $valid = 0;
    protected $GNDList = array();

    function __construct($path, $fileName, $format = 'xml') {
        $this->fileName = $fileName;
        $this->createDirectory();
        if ($format == 'xml') {
            $uploader = new Uploader_XML($path, $this->fileName);
        }
        elseif ($format == 'xml_full') {
            $uploader = new Uploader_XML_full($path, $this->fileName);
        }
        elseif ($format == 'csv') {
            $uploader = new Uploader_CSV($path, $this->fileName);
        }
        elseif ($format == 'php') {
            $uploader = new Uploader_PHP($path, $this->fileName);
        }
        elseif ($format == 'sql_dh') {
            $uploader = new Uploader_SQL($this->fileName);
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
        if ($format != 'sql_dh') {
            $this->convertCatalogueToFull();
            $this->convertContentToFull();
        }
        if (in_array($format, array('xml', 'xml_full'))) {
            $uploader->writeMetadata($this->catalogue);
        }
        /*elseif ($format == 'xml_full') {
            $this->convertMetadataToDefault();
        }*/
    }

    public function updateCat($cat) {
        foreach ($this->catalogue as $key => $value) {
            if (!empty($cat->$key)) {
                $value = $cat->$key;
            }
        }
        $this->convertCatalogueToFull();
    }

    protected function convertCatalogueToFull() {
        $this->catalogues[0]->addSections($this->content);
    }

    protected function convertContentToFull() {
        foreach ($this->content as $item) {
            $sectID = $this->catalogues[0]->getSectID($item->histSubject);
            $item->convertToFull($sectID);
        }
    }

    protected function convertMetadataToDefault() {
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
        $ser = new Serializer_PHP($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_XML($this->catalogue, $this->content, $this->fileName);
		$ser->serialize();
        $ser = new Serializer_XML_full($this->catalogues, $this->metadataReconstruction, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_CSV($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_TEI($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_Solr($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_RDF($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_KML($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_GeoCSV($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
        $ser = new Serializer_Gephi($this->catalogue, $this->content, $this->fileName);
        $ser->serialize();
    }

    public function makeBioDataSheet($restrict = array(), $format = 'csv') {
        if (isset($restrict[0])) {
            $this->makeSelectiveGNDList($restrict);
        }
        else {
            $this->makeCompleteGNDList();
        }
        $cache = new Cache_GND;
        $bio = new GeoDataSheet_bio($this->GNDList, $cache);
        if ($format == 'csv') {
            $bio->saveCSV(Reconstruction::FOLDER.'/'.$this->fileName.'/');
        }
        else {
            echo 'Diese Methode unterstützt nur CSV';
        }
    }

    protected function insertIDs() {
        $count = 0;
        foreach ($this->content as $item) {
            $item->id = $this->fileName.$count;
            $count++;
        }
    }

    protected function addVolumeInformation() {
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

    public function insertBeacon($beaconupdate = false) {
        $repository = new BeaconRepository($beaconupdate);
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
        $archiveGeoNames = new GeoDataArchive('geoNames');
        $archiveGND = new GeoDataArchive('gnd');
        $archiveGetty = new GeoDataArchive('getty');
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

    protected function makeCompleteGNDList() {
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if ($person->gnd) {
                    $this->GNDList[] = $person->gnd;
                }
            }
        }
        return($this->GNDList);
    }

    protected function makeSelectiveGNDList($include = array()) {
        foreach ($this->content as $item) {
            foreach ($item->persons as $person) {
                if (in_array($person->role, $include)) {
                    $this->GNDList[] = $person->gnd;
                }
            }
        }
        return($this->GNDList);
    }

    protected function createDirectory() {
        if (!is_dir(Reconstruction::FOLDER.'/'.$this->fileName)) {
            mkdir(Reconstruction::FOLDER.'/'.$this->fileName, 0777, true);
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
