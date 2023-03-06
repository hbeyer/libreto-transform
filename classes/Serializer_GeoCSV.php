<?php

class Serializer_GeoCSV extends Serializer_KML {

    public $fields = array('Name','Address','Description','Longitude','Latitude','TimeStamp','TimeSpan:begin','TimeSpan:end','GettyID');

    public function serialize() {
        $this->path = Reconstruction::getPath($this->fileName, 'printingPlaces', 'csv');
        $handle = fopen($this->path, 'w');
        fputcsv($handle, $this->fields);        
        $geoData = $this->collectGeoData();
        foreach ($geoData as $geoDataRow) {
            fputcsv($handle, $this->makeCSVRow($geoDataRow));
        }        
    }

    private function makeCSVRow($geoDataRow) {
        return(array($geoDataRow->label, "", "", $geoDataRow->long, $geoDataRow->lat, $geoDataRow->timeStamp, "", "", $geoDataRow->getty));
    }

}   

?>