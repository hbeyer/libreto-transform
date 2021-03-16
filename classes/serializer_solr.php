<?php

class serializer_solr extends serializer_xml {

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, $this->fileName.'-SOLR', 'xml');
        $this->makeDOM();
        $this->insertContent();
        $this->output = $this->dom->saveXML();
        $this->save();
    }

    protected function insertContent() {
        $multiValued = array('languages', 'languagesFull', 'genres', 'subjects', 'author', 'contributor', 'publishers', 'borrower', 'borrower_pers');
        $SOLRArray = $this->makeSOLRArray();
        $SOLRArray = $this->addMetaDataSOLR($SOLRArray);
        $rootElement = $this->dom->createElement('add');
        foreach($SOLRArray as $item) {
            $itemElement = $this->dom->createElement('doc');
            foreach($item as $key => $value) {
                //Repetition of the multi-valued fields
                if(in_array($key, $multiValued)) {
                    $values = explode(';', $value);
                    foreach($values as $string) {
                        $fieldElement = $this->dom->createElement('field');
                        $fieldContent = $this->dom->createTextNode($string);
                        $fieldElement->appendChild($fieldContent);
                        $fieldAttribute = $this->dom->createAttribute('name');
                        $fieldAttribute->value = $key;
                        $fieldElement->appendChild($fieldAttribute);
                        $itemElement->appendChild($fieldElement);
                    }
                }
                else {
                    $fieldElement = $this->dom->createElement('field');
                    $fieldContent = $this->dom->createTextNode($value);
                    $fieldElement->appendChild($fieldContent);
                    $fieldAttribute = $this->dom->createAttribute('name');
                    $fieldAttribute->value = $key;
                    $fieldElement->appendChild($fieldAttribute);
                    $itemElement->appendChild($fieldElement);
                }
            }
            $rootElement->appendChild($itemElement);
        }
        $this->dom->appendChild($rootElement);        
    }

    private function makeSOLRArray() {
        $SOLRArray = array();
        foreach($this->data as $item) {
            $row = $this->flattenItem($item);
            $row = $this->resolveManifestation($row);
            $row = $this->resolveOriginal($row);
            $row = $this->resolveLanguages($row);
            $row = $this->addNormalizedYear($row);
            $SOLRArray[] = $row;
        }
        return($SOLRArray);
    }

    private function addMetaDataSOLR($flatData) {
        $result = array();
        $metaData = array();
        if($this->catalogue->owner) {
            $metaData['owner'] = $this->catalogue->owner;
        }
        if($this->catalogue->ownerGND) {
            $metaData['ownerGND'] = $this->catalogue->ownerGND;
        }
        if($this->catalogue->year) {
            $metaData['dateCollection'] = $this->catalogue->year;
        }
        if($this->catalogue->heading) {
            $metaData['nameCollection'] = $this->catalogue->heading;
        }
        if($this->catalogue->geoBrowserStorageID) {
            $metaData['GeoBrowserLink'] = 'https://geobrowser.de.dariah.eu/?csv1=http://geobrowser.de.dariah.eu./storage/'.$this->catalogue->geoBrowserStorageID;
        }
        foreach($flatData as $item) {
            foreach($metaData as $key => $value) {
                $item[$key] = $value;
            }
            $result[] = $item;
        }
        return($result);
    }    

    private function flattenItem($item) {
        $result = array();
        foreach($item as $key => $value) {
            if(is_array($value) == FALSE) {
                if($value) {
                    $result[$key] = $value;
                }
            }
            elseif(serializer_solr::testArrayType($value) == 'num') {
                // Die folgende Bedingung verhindert leere Felder für genres, subjects und languages
                if($value != array('') and $value != array()) {
                    $result[$key] = implode(';', $value);
                }
            }       
            elseif(serializer_solr::testArrayType($value) == 'assoc') {
                foreach($value as $key1 => $value1) {
                    if($value1) {
                        $result[$key1] = $value1; 
                    }
                }
            }
            elseif(serializer_solr::testArrayType($value) == 'persons') {
                $result = array_merge($result, $this->flattenPersons($value));
            }
            elseif(serializer_solr::testArrayType($value) == 'places') {
                $result = array_merge($result, $this->flattenPlaces($value));
            }
        }
        return($result);
    }

    private function resolveManifestation($row) {
        if(!empty($row['systemManifestation']) and !empty($row['idManifestation'])) {
            $reference = new reference($row['systemManifestation'], $row['idManifestation']);
            if ($reference->url) {
                $row['linkManifestation'] = $reference->url;
                $row['nameSystemManifestation'] = $reference->nameSystem;
                $row['manifestationFull'] = $reference->fullID;
            }
        }
        return($row);
    }    

    private function flattenPersons($persons) {
        $result = array();
        $collectAuthors = array();
        $collectBorrowers = array();
        $collectContributors = array();
        $collectDateLending = array();
        foreach($persons as $person) {
            $gnd = '';
            if($person->gnd) {
                $gnd = '#'.$person->gnd;
            }
            if($person->role == 'author') {
                $collectAuthors[] = $person->persName.$gnd;
            }
            elseif($person->role == 'borrower') {
                $borrower = $person->persName.$gnd;
                foreach ($person->dateLending as $dateLending) {
                    $collectBorrowers[] = $borrower.'~'.$dateLending;
                }
                if ($person->dateLending == array()) {
                    $collectBorrowers[] = $borrower;
                }
                
            }
            else {
                $collectContributors[] = $person->persName.$gnd;
            }
        }
        if(isset($collectAuthors[0])) {
            $result['author'] = implode(';', $collectAuthors);
        }
        if(isset($collectBorrowers[0])) {
            $result['borrower'] = implode(';', $collectBorrowers);
            $borr_pers = array_unique(array_map(function($string) { $split = explode('~', $string); return($split[0]); }, $collectBorrowers));
            $result['borrower_pers'] = implode(';', $borr_pers);
        }
        if(isset($collectDateLending[0])) {
            $result['dateLending'] = implode(';', $collectDateLending);
        }
        if(isset($collectContributors[0])) {
            $result['contributor'] = implode(';', $collectContributors);
        }
        return($result);
    }

    private function flattenPlaces($places) {
        $result = array();
        $count = 1;
        foreach($places as $place) {
            $result['place_'.$count] = $place->placeName;
            if($place->getty) {
                $result['getty_place_'.$count] = $place->getty;
            }
            if($place->geoNames) {
                $result['geoNames_place_'.$count] = $place->geoNames;
            }
            if($place->geoData['lat'] and $place->geoData['long']) {
                $lat = serializer_kml::cleanCoordinate($place->geoData['lat']); //Replaces "," by "."
                $long = serializer_kml::cleanCoordinate($place->geoData['long']);
                $result['coordinates_place_'.$count] = $lat.','.$long;
            }
            $count++;
        }
        return($result);
    }

    private function resolveOriginal($row) {
        if(isset($row['institutionOriginal']) and isset($row['shelfmarkOriginal']) and isset($row['targetOPAC'])) {
            $searchString = $row['shelfmarkOriginal'];
            if(isset($row['searchID'])) {
                if($row['searchID'] != '') {
                    $searchString = $row['searchID'];
                }
            }
            $row['originalLink'] = makeBeaconLink($searchString, $row['targetOPAC']);
            unset($row['targetOPAC']);
        }
        return($row);   
    }

    private function resolveLanguages($row) {
        $languagesFull = array();
        if(isset($row['languages'])) {
            $languages = explode(' ', $row['languages']);
            foreach($languages as $code) {
                $langName = language_reference::getLanguage($code);
                if ($langName) {
                    $languagesFull[] = $langName;
                }
            }
            $languageString = implode(';', $languagesFull);
            if($languageString != '') {
                $row['languagesFull'] = $languageString;
            }
        }
        return($row);
    }

    private function addNormalizedYear($row) {
        $normalizedYear = '';
        if(isset($row['year'])) {
            $normalizedYear = index::normalizeYear($row['year']);
            if($normalizedYear == '') {
                if(isset($row['titleCat'])) {
                    $normalizedYear = index::getYearFromTitle($row['titleCat']);
                }
            }
            if($normalizedYear != '') {
                $row['yearNormalized'] = $normalizedYear;
            }
        }
        return($row);
    }

    static function testArrayType($array) {
        $result = 'uncertain';
        foreach($array as $key => $value) {
            if(is_string($key)) {
                $result = 'assoc';
                break;
            }
            elseif(is_int($key)) {
                if(isset($value)) {
                    if(is_object($value)) {
                        if(get_class($value) == 'person') {
                            $result = 'persons';
                            break;
                        }
                        elseif(get_class($value) == 'place') {
                            $result = 'places';
                            break;
                        }
                    }
                    else {
                        $result = 'num';
                        break;
                    }               
                }
            }
        }
        return($result);
    }    

}   

?>
