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
        $this->metaPath = reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-metadata.xml';
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
		$set->fileName = $this->fileName;
        return($set);
    }

    protected function loadMetaFile() {
        if (!file_exists($this->metaPath)) {
           copy ('template-metadata.xml', $this->metaPath); //Vorlage muss erstellt werden
           chmod($this->metaPath, 0777);
           die('Bitte Datei '.$this->fileName.'-metadata.xml ausfüllen und den Vorgang wiederholen.');
        }
        else {
            $catalogue = uploader::readMetadata($this->metaPath);
            if (!empty($catalogue)) {
                foreach ($catalogue as $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    if (substr($value, 0, 1) == '{') {
                        die('Bitte führende und schließende geschweifte Klammern aus der Datei '.$this->fileName.'-metadata.xml entfernen und den Vorgang wiederholen.');
                    }
                }
                return($catalogue);
            }
        }
    }

    static function readMetadata($metaPath) {
        $cat = new catalogue;
        $dom = new DOMDocument;
        $dom->load($metaPath);
        $root = $dom->getElementsByTagName('catalogue')->item(0);
        foreach ($root->childNodes as $node) {
            if (property_exists('catalogue', $node->nodeName)) {
                $cat->{$node->nodeName} = $node->nodeValue;
            }
        }
        return($cat);
    }

    public function writeMetadata($cat) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElement('catalogue');
        foreach ($cat as $key => $value) {
            if (is_string($value) == false) {
                continue;
            }
            $new = $dom->createElement($key);
            $text = $dom->createTextNode($value);
            $new->appendChild($text);
            $root->appendChild($new);
        }
        $dom->appendChild($root);
        file_put_contents($this->metaPath, $dom->saveXML());
    }    

}

?>