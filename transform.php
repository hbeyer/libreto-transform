<?php

require __DIR__ .'/vendor/autoload.php';
foreach (glob("classes/class_*.php") as $filename)
{
    include $filename;
}
include('functions/classDefinition.php');
include('functions/encode.php');

$reconstruction = new reconstruction('projectFiles/gandersheim/gandersheim-pod.xml', 'gandersheim', 'xml');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

/*
$reconstruction = new reconstruction('database', 'bahnsen', 'sql_dh');
//$reconstruction->enrichData();
$reconstruction->saveAllFormats();
*/

/*
$reconstruction = new reconstruction('{Dateipfad mit Endung}', '{Dateiname für Projekt}');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();
*/

?>
