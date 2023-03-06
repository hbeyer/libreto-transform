<?php

set_time_limit (600);
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$reconstruction = new Reconstruction('{Pfad zur Datei}', '{Dateiname für das Projekt}', '{xml|xml_full|csv|php|sql_dh}');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();
//$reconstruction->makeBioDataSheet();

// Anpassen zum Eingrenzen der darzustellenden Felder
$pages = array('numberCat', 'catSubjectFormat', 'shelfmarkOriginal', 'histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'histShelfmark', 'genres', 'languages', 'placeName', 'publishers', 'format', 'volumes', 'mediaType', 'bound', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'pageCat', 'titleWork', 'borrower', 'dateLending');
$doughnuts = array('persName', 'gender', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'bound', 'beacon');
$clouds = array('publishers', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'persName', 'gnd', 'role', 'placeName', 'languages', 'systemManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'beacon', 'borrower');

$facetList = new FacetList($pages, $doughnuts, $clouds);
$frontend = new Frontend($reconstruction, $facetList);
$frontend->build();

?>