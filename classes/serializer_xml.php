<?php

class serializer_xml extends serializer {

    protected $dom;

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, $this->fileName, 'xml');
        $this->makeDOM();
        $this->insertContent();
        $this->output = $this->dom->saveXML();
        $this->save();
    }

    protected function makeDOM() {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
    }

    protected function insertContent() {
        $rootElement = $this->dom->createElement('collection');
        $this->dom->appendChild($rootElement);      
        $this->insertMetadataFromCatalogue();
        $this->insertData();
    }

    protected function insertMetadataFromCatalogue() {
        $root = $this->dom->documentElement;
        $metadata = $this->dom->createElement('metadata');
        $metadataFields = array('heading', 'owner', 'ownerGND', 'fileName', 'title', 'base', 'placeCat', 'year', 'institution', 'shelfmark', 'description', 'geoBrowserStorageID', 'creatorReconstruction', 'yearReconstruction');
        foreach($this->catalogue as $key => $value) {
            if(in_array($key, $metadataFields) and $value) {
                $element = $this->dom->createElement($key);
                $text = $this->dom->createTextNode($value);
                $element->appendChild($text);
                $metadata->appendChild($element);
            }
        }
        $root->appendChild($metadata);
    }

    protected function insertData() {
        $root = $this->dom->documentElement;
        foreach ($this->data as $item) {
            $itemElement = $this->dom->createElement('item');
            $itemElement = $this->fillDOMItem($itemElement, $item);
            $root->appendChild($itemElement);
        }
    }

    function fillDOMItem($itemElement, $item) {
        $exclude = array('catEntries');
        foreach($item as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            // Fall 1: Variable ist ein einfacher Wert
            if(is_array($value) == FALSE) {
                //$value = replaceAmp($value);
                $itemProperty = $this->dom->createElement($key);
                $textProperty = $this->dom->createTextNode($value);
                $itemProperty->appendChild($textProperty);
                $itemElement = serializer_xml::appendNodeUnlessVoid($itemElement, $itemProperty);
            }
            else {
                $test1 = serializer_xml::testIfAssociative($value);
                //Fall 2.0: Variable ist ein assoziatives Array
                if($test1 == 1) {
                    $itemArrayProperty = $this->dom->createElement($key);
                    $itemArrayProperty = $this->appendAssocArrayToDOM($itemArrayProperty, $value);
                    $itemElement = serializer_xml::appendNodeUnlessVoid($itemElement, $itemArrayProperty);
                }
                elseif($test1 == 0 and isset($value[0])) {
                    //Fall 2.1: Variable ist numerisches Array aus einfachen Werten
                    if(is_string($value[0]) or is_integer($value[0])) {
                        $itemArrayProperty = $this->dom->createElement($key);
                        $fieldName = serializer_xml::makeSubfieldName($key);
                        $itemArrayProperty = $this->appendNumericArrayToDOM($itemArrayProperty, $value, $fieldName);
                        $itemElement = serializer_xml::appendNodeUnlessVoid($itemElement, $itemArrayProperty);
                    }
                    //Fall 2.2: Variable ist ein numerisches Array aus Objekten
                    elseif(is_object($value[0])) {
                        $itemObjectProperty = $this->dom->createElement($key);
                        //Iteration über die Variablen des gefundenen Objekts
                        foreach($value as $object) {
                            $nameObject = get_class($object);
                            $objectElement = $this->dom->createElement($nameObject);
                            foreach($object as $objectKey => $objectValue) {
                                //Fall 2.2.1: Variable im Objekt ist ein Array
                                if(is_array($objectValue)) {
                                    $objectVariable = $this->dom->createElement($objectKey);
                                    $test = serializer_xml::testIfAssociative($objectValue);
                                    //Fall 2.2.1.1: Variable im Objekt ist ein assoziatives Array
                                    if($test == 1) {
                                        $objectVariable = $this->appendAssocArrayToDOM($objectVariable, $objectValue);
                                    }
                                    //Fall 2.2.1.2: Variable im Objekt ist ein numerisches Array
                                    elseif($test == 0) {
                                        //Generieren eines Namens für das Subfeld, weil Integer in XML nicht akzeptiert werden
                                        $fieldName = serializer_xml::makeSubfieldName($objectKey);
                                        $objectVariable = $this->appendNumericArrayToDOM($objectVariable, $objectValue, $fieldName);
                                    }
                                    $objectElement = serializer_xml::appendNodeUnlessVoid($objectElement, $objectVariable);
                                }
                                //Fall 2.2.2: Variable im Objekt ist ein Integer oder String
                                elseif(is_int($objectValue) or is_string($objectValue)) {
                                    $objectVariable = $this->dom->createElement($objectKey);
                                    $textObjectVariable = $this->dom->createTextNode($objectValue);
                                    $objectVariable->appendChild($textObjectVariable);
                                    
                                    $objectElement = serializer_xml::appendNodeUnlessVoid($objectElement, $objectVariable);
                                }
                            }
                            $itemObjectProperty->appendChild($objectElement);
                        }
                        $itemElement = serializer_xml::appendNodeUnlessVoid($itemElement, $itemObjectProperty);
                    }
                }
            }
        }
        return($itemElement);
    }

    protected function appendAssocArrayToDOM($parent, $array) {
        foreach($array as $key => $value) {
            $node = $this->dom->createElement($key);
            $textNode = $this->dom->createTextNode($value);
            $node->appendChild($textNode);
            $parent = serializer_xml::appendNodeUnlessVoid($parent, $node);
        }
        return($parent);
    }

    protected function appendNumericArrayToDOM($parent, $array, $fieldName = 'subfield') {
        foreach($array as $value) {
            $node = $this->dom->createElement($fieldName);
            $textNode = $this->dom->createTextNode($value);
            $node->appendChild($textNode);      
            $parent = serializer_xml::appendNodeUnlessVoid($parent, $node);
        }
        return($parent);
    }

    static function appendNodeUnlessVoid($parent, $child) {
        if($child->nodeValue != '') {
            $parent->appendChild($child);
        }
        return($parent);
    }

    static function testIfAssociative($array) {
        $result = 'uncertain';
        foreach($array as $key => $value) {
            if(is_string($key)) {
                $result = 1;
            }
            elseif(is_int($key)) {
                $result = 0;
            }
            break;
        }
        return($result);
    }

    static function makeSubfieldName($fieldName) {
        if ($fieldName == 'copiesHAB') {
            return('copyHAB');
        }
        elseif (substr($fieldName, -1) == 's') {
            return(substr($fieldName, 0, -1));
        }
        elseif ($fieldName == '') {
            return('subfield');
        }
        return($fieldName);
    } 

}   

?>