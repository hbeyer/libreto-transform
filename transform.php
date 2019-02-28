<?php

require __DIR__ .'/vendor/autoload.php';
foreach (glob("classes/class_*.php") as $filename) {
    include $filename;
}
include('functions/encode.php');

$reconstruction = new reconstruction('{Path to file}', '{Name for folder}', '{Format: xml|csv|sql_dh}');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();
$pages = array('histSubject', 'persName', 'year', 'placeName', 'publisher', 'subjects', 'genres', 'languages');
$doughnuts = array('histSubject', 'placeName', 'subjects', 'genres', 'format', 'mediaType', 'languages');
$clouds = array('persName', 'placeName', 'subjects', 'genres', 'publisher');
$facetList = new facetList($pages, $doughnuts, $clouds);
$frontend = new frontend($reconstruction, $facetList);
$frontend->save();

?>
