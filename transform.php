<?php

require __DIR__ .'/vendor/autoload.php';
foreach (glob("classes/class_*.php") as $filename) {
    include $filename;
	}
include('functions/encode.php');

$reconstruction = new reconstruction('{Pfad zur Datei}', '{Dateiname für das Projekt}', '{xml|csv|php|sql_dh}');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

// Anpassen zum Eingrenzen der darzustellenden Felder
$pages = array('numberCat', 'catSubjectFormat', 'shelfmarkOriginal', 'histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'histShelfmark', 'genres', 'languages', 'placeName', 'publishers', 'format', 'volumes', 'mediaType', 'bound', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'pageCat', 'titleWork', 'borrower', 'dateLending');
$doughnuts = array('persName', 'gender', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'bound', 'beacon');
$clouds = array('publishers', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'persName', 'gnd', 'role', 'placeName', 'languages', 'systemManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'beacon', 'borrower');

$facetList = new facetList($pages, $doughnuts, $clouds);
$frontend = new frontend($reconstruction, $facetList);
$frontend->build();

?>