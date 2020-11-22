<?php

require_once(reconstruction::INCLUDEPATH.'makeIndex.php');

class serializer_kml extends serializer_xml {

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, 'printingPlaces', 'kml');
        $this->makeDOM();
        $this->dom->load('templateKML.xml');
        $this->insertContent();
        $this->output = $this->dom->saveXML();
        $this->save();
    }

    protected function insertContent() {
        $geoData = $this->collectGeoData();
        $folder = $this->dom->getElementsByTagName('Folder')->item(0);
        foreach ($geoData as $geoDataRow) {
            $placemark = $this->dom->createElement('Placemark');

            $address = $this->dom->createElement('address');
            $addressText = $this->dom->createTextNode($geoDataRow->label);
            $address->appendChild($addressText);
            $placemark->appendChild($address);
            
            $when = $this->dom->createElement('when');
            $whenText = $this->dom->createTextNode($geoDataRow->timeStamp);
            $when->appendChild($whenText);
            $timestamp = $this->dom->createElement('TimeStamp');
            $timestamp->appendChild($when);
            $placemark->appendChild($timestamp);
            
            $point = $this->dom->createElement('Point');
            $coordinates = $this->dom->createElement('coordinates');
            $coordinatesText = $this->dom->createTextNode($geoDataRow->long.','.$geoDataRow->lat);
            $coordinates->appendChild($coordinatesText);
            $point->appendChild($coordinates);
            $placemark->appendChild($point);

            $folder->appendChild($placemark);
        }
    }

    protected function collectGeoData() {
        $index1 = makeIndex($this->data, 'placeName');
        $index2 = makeIndex($this->data, 'year');
        $commonIndex = mergeIndices($index1, $index2);
        $result = array();
        $placeName = '';

        // Durchgehen des Index und Abspeichern von standardisierten Datensätzen der Klasse geoDataRow im Array $result
        foreach($commonIndex as $entry) {
            
            // Die im Index mit Level 1 auftretenden Ortseinträge dienen nur zum Speichern von Ortsname und Geodaten
            if($entry->level == 1) {
                // Der Test dient dem Ausschließen von Einträgen ohne Ortsnamen oder Geodaten
                $test = $entry->validateGeoData();
                $placeName = $entry->label;
                $latitude = serializer_kml::cleanCoordinate($entry->geoData['lat']);
                $longitude = serializer_kml::cleanCoordinate($entry->geoData['long']);
            }
            //Für jeden Indexeintrag Level 2 (Jahre) werden so viele Einträge gespeichert, wie Datensätze unter content verzeichnet sind.
            if($entry->level == 2 and $test == true) {
            foreach($entry->content as $occurrence) {
                    $row = new geoDataRow;
                    $year = $entry->label;
                    if(preg_match('~^[12]?[0-9]{3}?~', $year) == false) {
                        $year = '';
                    }
                    $row->timeStamp = $year;
                    $row->label = $placeName;
                    $row->lat = serializer_kml::cleanCoordinate($entry->geoData['lat']);
                    $row->long = serializer_kml::cleanCoordinate($entry->geoData['long']);
                    $row->lat = $latitude;
                    $row->long = $longitude;
                    if($entry->authority['system'] == 'getty') {
                        $row->getty = $entry->authority['id'];
                    }
                    elseif($entry->authority['system'] == 'geoNames') {
                        $row->geoNames = $entry->authority['id'];
                    }
                    $result[] = $row;
                }
            }
        }        

        return($result);
    }

    static function cleanCoordinate($coordinate) {
        $translation = array(',' => '.');
        $coordinate = strtr($coordinate, $translation);
        return($coordinate);
    }       

}   

?>
