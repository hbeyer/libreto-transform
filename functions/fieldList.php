<?php

/* 
The following defines the fields which can be indexed and displayed. Each field listed in $indexFields can be 
given as $field to the function makeIndex and inserted in setConfiguration.php under $catalogue->facets 
to generate a separate page.
 */

$normalFields = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'volumes', 'volumesMisc', 'titleCat', 'titleBib', 'titleNormalized', 'publisher', 'year', 'format', 'histSubject', 'histShelfmark', 'mediaType', 'bound', 'comment', 'digitalCopy');
$personFields = array('persName', 'gnd', 'gender', 'role', 'beacon');
$placeFields = array('placeName', 'getty', 'geoNames');
//$arrayFields = array('languages', 'subjects', 'genres', 'beacon');
$arrayFields = array('languages', 'subjects', 'genres');
$workFields = array('titleWork', 'systemWork', 'idWork');
$manifestationFields = array('systemManifestation');
$originalItemFields = array('institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'targetOPAC', 'searchID');
// The following values do not correspond to a field, but they can be submitted to the function makeIndex
$virtualFields = array('catSubjectFormat');

$indexFields = array_merge($normalFields, $personFields, $placeFields, $arrayFields, $workFields, $manifestationFields, $originalItemFields, $virtualFields);

// The following fields are displayed with miscellanies as unordered lists
$volumeFields = array('numberCat', 'catSubjectFormat', 'histSubject');

// The following fields get additional word clouds or doughnuts if they are selected
$wordCloudFields = array('publisher', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'persName', 'gnd', 'role', 'placeName', 'languages', 'systemManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'beacon');
$doughnutFields = array('persName', 'gender', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'bound', 'beacon');

// Checkbox-Fields
$checkboxFields = array('numberCat', 'catSubjectFormat', 'shelfmarkOriginal', 'histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'histShelfmark', 'genres', 'languages', 'placeName', 'publisher', 'format', 'volumes', 'mediaType', 'bound', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'pageCat', 'titleWork');

?>
