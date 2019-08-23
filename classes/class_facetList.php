<?php

class facetList {

    public $pages = array('histSubject', 'persName', 'year', 'placeName', 'subjects', 'genres', 'languages', 'publishers');
    public $doughnuts = array('histSubject', 'placeName', 'subjects', 'genres', 'format', 'mediaType', 'languages');
    public $clouds = array('persName', 'placeName', 'subjects', 'genres', 'publishers');
    public $volumeFields = array('numberCat', 'catSubjectFormat', 'histSubject');


    private $allowedPages = array('numberCat', 'catSubjectFormat', 'shelfmarkOriginal', 'histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'histShelfmark', 'genres', 'languages', 'placeName', 'publishers', 'format', 'volumes', 'mediaType', 'bound', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'pageCat', 'titleWork', 'borrower', 'dateLending');
    private $allowedDoughnuts = array('persName', 'gender', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'bound', 'beacon');
    private $allowedClouds = array('publishers', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'persName', 'gnd', 'role', 'placeName', 'languages', 'systemManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'beacon', 'borrower');


    function __construct($pages = null, $doughnuts = null, $clouds = null) {
        if (is_array($pages)) {
            $this->pages = array_intersect($pages, $this->allowedPages);
        }
        if (is_array($doughnuts)) {
            $this->doughnuts = array_intersect($doughnuts, $this->allowedDoughnuts);
        }
        if (is_array($clouds)) {
            $this->clouds = array_intersect($clouds, $this->allowedClouds);
        } 
    }

}

?>
