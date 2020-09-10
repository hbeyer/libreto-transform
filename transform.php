<?php

set_time_limit (600);

require __DIR__ .'/vendor/autoload.php';
foreach (glob("classes/class_*.php") as $filename) {
    include $filename;
	}
include('functions/encode.php');

$reconstruction = new reconstruction('', 'sturm', 'sql_dh');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

// Anpassen zum Eingrenzen der darzustellenden Felder
$pages = array('histSubject', 'persName', 'year', 'subjects', 'genres', 'languages', 'placeName', 'format', 'systemManifestation');
$doughnuts = array('format', 'histSubject', 'subjects', 'genres', 'languages', 'systemManifestation', 'beacon');
$clouds = array('subjects', 'genres', 'persName', 'placeName');

$facetList = new facetList($pages, $doughnuts, $clouds);
$frontend = new frontend($reconstruction, $facetList);
$frontend->build();

?>
