<?php

class uploader_xml extends uploader {
	
	private $dom;
    private $metadataNode;
    private $contentNodes;

    function __construct($path, $fileName) {
        $this->path = $path;
        $this->fileName = $fileName;
	    $this->dom = new DOMDocument();
	    $this->dom->load($this->path);
        if ($this->dom == FALSE) {
            throw new Exception('XML-Dokument ist nicht wohlgeformt');
        }
        if (!$this->dom->schemaValidate('uploadXML.xsd')) {
            throw new Exception('XML-Dokument validiert nicht gegen das Schma uploadXML.xsd');
        }
        $xml = new DOMDocument();
        $xml->load($this->path);
        $this->metadataNode = $xml->getElementsByTagName('metadata')->item(0);
        $this->contentNodes = $xml->getElementsByTagName('item');
    }	

    public function loadCatalogues($fileName) {
        $loadFields = array('base', 'title', 'placeCat', 'printer', 'year', 'institution', 'shelfmark');
    	$catalogue = new catalogue;
        $catalogue->fileName = $fileName;
        foreach ($this->metadataNode->childNodes as $child) {
            if (in_array($child->nodeName, $loadFields)) {
                $field = $child->nodeName;
                $catalogue->$field = $child->nodeValue;
            }
        }        
    	return(array($catalogue));
    }

    public function loadMetadata() {
    	$metadataSet = new metadata_reconstruction;
        foreach ($this->metadataNode->childNodes as $child) {
            if (property_exists('metadata_reconstruction', $child->nodeName)) {
                $field = $child->nodeName;
                $metadataSet->$field = $child->nodeValue;
            }
        }
    	return($metadataSet);
    }

    public function loadContent($fileName = '') {
    	$result = array();
        foreach ($this->contentNodes as $node) {
            $result[] = $this->makeItemFromNode($node);
        }
    	return($result);
    }


    private function makeItemFromNode($node) {
        $simpleFields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'volumes', 'volumensMisc', 'titleCat', 'titleBib', 'titleNormalized', 'year', 'format', 'histSubject', 'histShelfmark', 'mediaType', 'bound', 'digitalCopy', 'comment');
        $multiValuedFields = array('subjects', 'genres', 'languages', 'publishers', 'copiesHAB');
        $subFieldFields = array('manifestation', 'originalItem', 'work', 'volumeNote');
        $item = new item;
        $children = $node->childNodes;
        foreach($children as $child) {
            $field = strval($child->nodeName);
            if(in_array($field, $simpleFields)) {
                $item->$field = trim($child->nodeValue);
            }
            elseif(in_array($field, $multiValuedFields)) {
                $item = $this->insertMultiValued($item, $field, $child);
            }
            elseif(in_array($field, $subFieldFields)) {
                $item = $this->insertSubFields($item, $field, $child);
            }
            elseif($field == 'persons') {
                $item = $this->insertPersons($item, $child);
            }
            elseif($field == 'places') {
                $item = $this->insertPlaces($item, $child);            
            }
            unset($field);
        }
        return($item);
    }

    private function insertMultiValued($item, $field, $node) {
        $insert = array();
        $children = $node->childNodes;
        foreach($children as $child) {
            if($child->nodeName != '#text') {
                $insert[] = trim($child->nodeValue);
            }
        }
        $item->$field = $insert;
        return($item);
    }

    private function insertSubFields($item, $field, $node) {
        $insert = array();
        $children = $node->childNodes;
        foreach($children as $child) {
            if($child->nodeName != '#text') {
                $insert[$child->nodeName] = trim($child->nodeValue);
            }
        }
        $item->$field = array_merge($item->$field, $insert);
        return($item);  
    }

    private function insertPersons($item, $node) {
        $children = $node->childNodes;
        foreach($children as $child) {
            if($child->nodeName != '#text') {
                $person = $this->makePersonFromNode($child);
                $item->persons[] = $person;
            }
        }
        return($item);
    }

    private function insertPlaces($item, $node) {
        $children = $node->childNodes;
        foreach($children as $child) {
            if($child->nodeName != '#text') {
                $place = $this->makePlaceFromNode($child);
                $item->places[] = $place;
            }
        }
        return($item);
    }

    private function makePersonFromNode($node) {
        $properties = array('persName', 'gnd', 'gender', 'role');
        $children = $node->childNodes;
        $person = new person;
        foreach($children as $child) {
            $field = strval($child->nodeName);
            if (in_array($field, array('beacon', 'dateLending'))) {
                foreach ($child->childNodes as $grandChild) {
                    if ($grandChild->nodeValue) {
                        if (trim($grandChild->nodeValue)) {
                            $person->$field[] = trim($grandChild->nodeValue);
                        }
                    }
                }                
            }
            elseif(in_array($child->nodeName, $properties)) {
                $person->$field = trim($child->nodeValue);
            }
        }
        return($person);
    }

    private function makePlaceFromNode($node) {
        $properties = array('placeName', 'geoNames', 'gnd', 'getty');
        $children = $node->childNodes;
        $place = new place;
        foreach($children as $child) {
            $field = strval($child->nodeName);
            if(in_array($child->nodeName, $properties)) {
                $place->$field = trim($child->nodeValue);
            }
            elseif($field == 'geoData') {
                $place = $this->insertSubFields($place, 'geoData', $child);
            }
        }
        return($place);
    }


}

?>