<?php

function makeGeoDataSheet($data, $folderName, $format) {
	$ending = strtolower($format);
	
	// Anlegen eines gemeinsamen Index für Orte und Jahre, so dass beide Kategorien abgerufen werden können.
	$index1 = makeIndex($data, 'placeName');
	$index2 = makeIndex($data, 'year');
	$commonIndex = mergeIndices($index1, $index2);
	
	$rowArray = array();
	$placeName = '';
	
	// Durchgehen des Index und Abspeichern von standardisierten Datensätzen der Klasse geoDataRow im Array $rowArray
	foreach($commonIndex as $entry) {
		
		// Die im Index mit Level 1 auftretenden Ortseinträge dienen nur zum Speichern von Ortsname und Geodaten
		if($entry->level == 1) {
			// Der Test dient dem Ausschließen von Einträgen ohne Ortsnamen oder Geodaten
			$test = testEntry($entry);
			$placeName = $entry->label;
			$latitude = cleanCoordinate($entry->geoData['lat']);
			$longitude = cleanCoordinate($entry->geoData['long']);
		}
		//Für jeden Indexeintrag Level 2 (Jahre) werden so viele Einträge gespeichert, wie Datensätze unter content verzeichnet sind.
		if($entry->level == 2 and $test == 1) {
		foreach($entry->content as $occurrence) {
				$row = new geoDataRow;
				$year = $entry->label;
				if(preg_match('~^[12]?[0-9]{3}?~', $year) == FALSE) {
					$year = '';
				}
				$row->timeStamp = $year;
				$row->label = $placeName;
				$row->lat = cleanCoordinate($entry->geoData['lat']);
				$row->long = cleanCoordinate($entry->geoData['long']);
				$row->lat = $latitude;
				$row->long = $longitude;
				if($entry->authority['system'] == 'getty') {
					$row->getty = $entry->authority['id'];
				}
				elseif($entry->authority['system'] == 'geoNames') {
					$row->geoNames = $entry->authority['id'];
				}
				$rowArray[] = $row;
			}
		}
	}		
		// Jetzt werden die Objekte der Klasse geoDataRow in Einträge übersetzt, abhängig vom gewählten Format 1678
		if($ending == 'csv') {
			// Kopfdaten für CSV-Dateien
			$content = '"Name","Address","Description","Longitude","Latitude","TimeStamp","TimeSpan:begin","TimeSpan:end","GettyID",""
';
			foreach($rowArray as $row) {
				$content .= makePlaceEntryCSV($row);
			}

		}
		elseif($ending == 'kml') {
			// Kopfdaten für KML-Dateien
			$content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
	<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:xal="urn:oasis:names:tc:ciq:xsdschema:xAL:2.0">
		<Folder>';
				foreach($rowArray as $row) {
					$content .= makePlaceEntryKML($row);
				}
			// Fußdaten für KML-Dateien
			$content .= '
		</Folder>
	</kml>';
		}
	
	// Abspeichern der im String $content zwischengespeicherten Daten in einer Datei im Projektordner
	$fileName = $folderName.'/printingPlaces.'.$ending;
	$datei = fopen($fileName,"w");
	fwrite($datei, $content, 30000000);
	fclose($datei);
	
}

function makePlaceEntryCSV($rowObject) {
		$row = '"'.$rowObject->label.'","'.$rowObject->label.'","'.$rowObject->label.'","'.$rowObject->long.'","'.$rowObject->lat.'","'.$rowObject->timeStamp.'","","'.$rowObject->getty.'",""
';	
	return($row);
}
	
function makePlaceEntryKML($rowObject) {
	$row = '
		<Placemark>
			<address>'.$rowObject->label.'</address>';
	if($rowObject->timeStamp) {
		$row .=	'
			<TimeStamp>
				<when>'.$rowObject->timeStamp.'</when>
			</TimeStamp>';
	}
	$row .= '
			<Point>
				<coordinates>'.$rowObject->long.','.$rowObject->lat.'</coordinates>
			</Point>
		</Placemark>';
	return($row);
}

function testEntry($entry) {
	$test = 1;
	if($entry->geoData['lat'] == '' or $entry->geoData['long'] == '') {
		$test = 0;
	}
	if($entry->label == '' or $entry->label == 's. l.') {
		$test = 0;
	}
	return($test);
}
	
?>