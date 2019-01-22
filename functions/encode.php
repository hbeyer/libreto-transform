<?php

function replaceArrowBrackets($string) {
	$translate = array('&lt;' => '', '&gt;' => '', '<' => '', '>' => '', '&amp;lt;' => '', '&amp;gt;' => '');
	$string = strtr($string, $translate);
	return($string);
}

function replaceSlash($string) {
	$translate = array('/' => ' ');
	$string = strtr($string, $translate);
	return($string);
}

function replaceAmpChar($string) {
	$translate = array('&amp;' => '&');
	$string = strtr($string, $translate);
	return($string);
}

function removeBlanks($string) {
	$translate = array(' ' => '');
	$string = strtr($string, $translate);
	return($string);
}
	
function translateAnchor($anchor) {
	$translate = array('Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', ' ' => '', '&' => 'et');
	$anchor = strtr($anchor, $translate);
	return($anchor);
}

function fileNameTrans($fileName) {
	$translation = array('Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', ' ' => '', '&' => 'Et');
	$fileName = strtr($fileName, $translation);
	return($fileName);
}

function encodeXMLID($string) {
	$string = strtolower($string);
	$translation = array(' ' => '_', '–' => '-', '/' => '_', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 's', 'é' => 'e', 'è' => 'e', 'à' => 'a', 'ò' => 'o');
	$id = strtr($string, $translation);
	$id = preg_replace( '/[^a-z0-9_\-]/i', '', $id); 
	return($id);
}

function translateIdNo($string) {
	$translate = array(' ' => '_');
	$string = strtr($string, $translate);
	return($string);	
}

function translateFieldNames($field) {
	$translation = array(
		'catSubjectFormat' => 'Katalog', 
		'numberCat' => 'Katalog', 
		'id' => 'Katalog', 
		'shelfmarkOriginal' => 'Signaturen', 		 		
		'persName' => 'Personen',
		'gender' => 'Gender',
		'beacon' => 'Personenprofil',
		'subjects' => 'Inhalte', 
		'histSubject' => 'Rubriken',
		'histShelfmark' => 'Altsignatur',		
		'year' => 'Datierung', 
		'placeName' => 'Orte', 
		'languages' => 'Sprachen', 
		'publisher' => 'Drucker', 
		'format' => 'Formate', 
		'volumes' => 'B&auml;nde', 
		'mediaType' => 'Materialarten', 
		'systemManifestation' => 'Nachweise', 
		'genres' => 'Gattungen');
	$result = strtr($field, $translation);
	return($result);		
}

function translateFieldNamesButtons($field) {
	$translation = array(
		'shelfmarkOriginal' => 'Signaturen',
		'persName' => 'Personen',
		'beacon' => 'Personenprofil',
		'gender' => 'Gender',
		'subjects' => 'Inhalte', 
		'histSubject' => 'Rubriken',
		'year' => 'Jahre',
		'placeName' => 'Orte', 
		'languages' => 'Sprachen', 
		'publisher' => 'Drucker', 
		'format' => 'Formate', 
		'volumes' => 'B&auml;nde', 
		'mediaType' => 'Materialarten', 
		'systemManifestation' => 'Nachweise', 
		'gnd' => 'GND-Nummern',
		'role' => 'Rollen',
		'institutionOriginal' => 'Institutionen',
		'provenanceAttribute' => 'Provenienzmerkmale',
		'genres' => 'Gattungen',
		'bibliographicalLevel' => 'Bibliographische Gattungen'
		);
	$result = strtr($field, $translation);
	return($result);		
}

function translateCheckboxNames($field) {
	$translation = array(
		'pageCat' => 'Seite im Altkatalog',
		'imageCat' => 'Seite im Digitalisat',
		'bibliographicalLevel' => 'Bibliographisches Level',
		'bound' => 'gebunden',
		'gnd' => 'GND-Nummer',
		'titleWork' => 'Werktitel',
		'institutionOriginal' => 'Besitzende Institution',
		'provenanceAttribute' => 'Provenienzmerkmal',
		'catSubjectFormat' => 'Rubrik und Format', 
		'numberCat' => 'Nummer Altkatalog', 
		'id' => 'ID', 
		'shelfmarkOriginal' => 'Signatur', 		
		'persName' => 'Person',
		'gender' => 'Gender',
		'beacon' => 'Personenprofil',
		'subjects' => 'Inhalt', 
		'histSubject' => 'Rubrik', 
		'histShelfmark' => 'Altsignatur', 
		'year' => 'Erscheinungsjahr', 
		'placeName' => 'Ort', 
		'languages' => 'Sprache', 
		'publisher' => 'Drucker', 
		'format' => 'Format', 
		'volumes' => 'B&auml;nde', 
		'mediaType' => 'Materialart', 
		'systemManifestation' => 'Nachgewiesen in', 
		'genres' => 'Gattung'
		);
	$result = strtr($field, $translation);
	return($result);		
}

function translateGenderAbbr($field) {
	$translation = array(
		'm' => 'Männlich',
		'f' => 'Weiblich',
		'*' => 'Weiteres'
		);
	$result = strtr($field, $translation);
	return($result);		
}

function translateGenderAbbrRDF($value) {
    $value = strtolower($value);
    $translation = array(
		'm' => 'male',
		'f' => 'female',
		'*' => 'other'
		);
	$result = strtr($value, $translation);
	return($result);		
}


function sortingFormat($format) {
	$pattern = '~^([248])°$~';
	$replacement = '0$1°';
	$format = preg_replace($pattern, $replacement, $format);
	return($format);
}

function reverseSortingFormat($format) {
	$pattern = '~^0([248])°$~';
	$replacement = '$1°';
	$format = preg_replace($pattern, $replacement, $format);
	return($format);
}

function insertSpace($genre) {
	$pattern = '~:([^ ])~';
	$replacement = ': $1';
	$genre = preg_replace($pattern, $replacement, $genre);
	return($genre);
}

function cleanCoordinate($coordinate) {
	$translation = array(',' => '.');
	$coordinate = strtr($coordinate, $translation);
	return($coordinate);
}

function removeSpecial($name) {
	$translation = array('Á' => 'A', 'Ł' => 'L', 'Ğ' => 'G', 'Ǧ' => 'G', 'Ĝ' => 'G', 'Ḥ' => 'H', 'ã' => 'a', 'ā' => 'a');
	$name = strtr($name, $translation);
	return($name);	
}

function convertWindowsToUTF8($string) {
  $charset =  mb_detect_encoding($string, "windows-1250, Windows-1252, ISO-8859-1, ISO-8859-15", true);
  $string =  mb_convert_encoding($string, "UTF-8", $charset);
  return $string;
}

function convertUTF8ToWindows($string) {
  $charset =  mb_detect_encoding($string, "UTF-8", true);
  $string =  mb_convert_encoding($string, "Windows-1252", $charset);
  return $string;
}

function convertToWindowsCharset($string) {
  $charset =  mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true);
  $string =  mb_convert_encoding($string, "Windows-1252", $charset);
  return $string;
}

function makeBeaconLink($gnd, $target) {
	$translate = array('{ID}' => $gnd);
	$link = strtr($target, $translate);
	$linkl = urlencode($link);
	return($link);
}

function removeSlashes($path) {
	$translate = array('/' => '');
	$result = strtr($path, $translate);
	return($result);
}

function shortenPath($fileName) {
	preg_match('~.+/([a-zA-Z0-9]+)$~', $fileName, $hits);
	if(isset ($hits[1])) {
		$result = $hits[1];
	}
	else {
		$result = removeSlashes($fileName);
	}
	return($result);
}

// Diese Funktion sollte nur einmal aufgerufen werden und zwar möglichst am Anfang!
function assignID($given, $count, $fileName) {
	if($fileName == '') {
		$fileName = 'library';
	}
	if($given) {
		return($fileName.$given);	
	}
	elseif($count) {
		return($fileName.'-position'.$count);
	}
}

function translateRoleTEI($role) {
	$translate = array('creator' => 'author', 'contributor' => 'editor', 'translator' => 'editor');
	$result = strtr($role, $translate);
	return($result);
}

function makeUploadName($string) {
	$salt = '07zhsuioedfzha87';
	$saltedString = $salt.$string.date('U');
	$name = hash('sha256', $saltedString);
	$name = substr($name, 0, 12);
	return($name);
}

function resolveURN($urn) {
    if (substr($urn, 0, 4) == 'urn:'){
        $urn = 'http://nbn-resolving.de/'.$urn;
    }
    return($urn);
}

function resolveTarget($pattern, $id) {
    $translation = array('{ID}' => $id);
    $text = strtr($pattern, $translation);
	return($text);
}

?>
