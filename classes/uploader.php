<?php

class  uploader {
    
    public $path;
    public $format;
    public $fileName;
    public $valid = 0;

    function __construct($path, $fileName = '', $format = '') {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->format = $format;
    }

    public function loadCatalogues($fileName) {
        $catalogue = $this->loadMetaFile();
        $catalogue->fileName = $fileName;
        if ($catalogue->id == null) {
            $catalogue->id = 'cat1';
        }
        return(array($catalogue));
    }

    public function loadMetadata() {
        $catalogue = $this->loadMetaFile();
        $set = $catalogue->makeMetadataSet();
        return($set);
    }    
    
    protected function loadMetaFile() {
        $metaPath = reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-cat.php';
        if (!file_exists($metaPath)) {
           copy ('templateCat.php', $metaPath);
           chmod($metaPath, 0777);
           throw new Exception('Bitte füllen Sie die Datei '.$this->fileName.'-cat.php aus und wiederholen Sie den Vorgang.', 1);
        }
        else {
            require($metaPath);
            if (!empty($catalogue)) {
                foreach ($catalogue as $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    if (substr($value, 0, 1) == '{') {
                        throw new Exception('Bitte entfernen Sie alle geschweiften Klammern aus der Datei '.$this->fileName.'-cat.php und wiederholen Sie den Vorgang.', 1);
                    }
                }
                return($catalogue);
            }
        }
    }

}

?>
