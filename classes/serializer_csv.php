<?php

class  serializer_csv extends serializer {

    public $fields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'titleCat', 'titleBib', 'titleNormalized', 'author1', 'author2', 'author3', 'author4', 'contributor1', 'contributor2', 'contributor3', 'contributor4', 'place1', 'place2', 'publishers', 'year', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'idManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'digitalCopyOriginal', 'targetOPAC', 'searchID', 'titleWork', 'systemWork', 'idWork', 'bound', 'comment', 'digitalCopy', 'copiesHAB');

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, $this->fileName, 'csv');
        $handle = fopen($this->path, 'w');
        fputcsv($handle, $this->fields);
        foreach ($this->data as $item) {
            fputcsv($handle, $this->makeRow($item));
        }
    }

    private function makeRow($item) {
        $ret = array($item->id, $item->pageCat, $item->imageCat, $item->numberCat, $item->itemInVolume, $item->titleCat, $item->titleBib, $item->titleNormalized, $item->getPersonCSV('creator', 0), $item->getPersonCSV('creator', 1), $item->getPersonCSV('creator', 2), $item->getPersonCSV('creator', 3), $item->getPersonCSV('contributor', 0), $item->getPersonCSV('contributor', 1), $item->getPersonCSV('contributor', 2), $item->getPersonCSV('contributor', 3), $item->getPlaceCSV(0), $item->getPlaceCSV(1), implode(';', $item->publishers), $item->year, $item->format, $item->histSubject, implode(';', $item->subjects), implode(';', $item->genres), $item->mediaType, implode($item->languages), $item->manifestation['systemManifestation'], $item->manifestation['idManifestation'], $item->originalItem['institutionOriginal'], $item->originalItem['shelfmarkOriginal'], $item->originalItem['provenanceAttribute'], $item->originalItem['digitalCopyOriginal'], $item->originalItem['targetOPAC'], $item->originalItem['searchID'], $item->work['titleWork'], $item->work['systemWork'], $item->work['idWork'], $item->bound, $item->comment, $item->digitalCopy, implode(';', $item->copiesHAB));
        return($ret);
    }

}   

?>
