<?php

require __DIR__ .'/vendor/autoload.php';
foreach (glob("classes/class_*.php") as $filename)
{
    include $filename;
}
include('functions/classDefinition.php');
include('functions/encode.php');

/*
$reconstruction = new reconstruction('{Dateipfad mit Endung}', '{Dateiname für Projekt}');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();
*/

?>
