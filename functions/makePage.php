<?php

function makePage($catalogue, $navigation, $pageContent, $facet, $impressum) {
	ob_start();
	include 'templates/page.phtml';
	$return = ob_get_contents();
	ob_end_clean();
    return($return);
}

function makeGeoBrowserLink($storageID, $year) {
	$mapDate = assignMapDate($year);
	$link = 'https://geobrowser.de.dariah.eu/?csv1=http://geobrowser.de.dariah.eu./storage/'.$storageID.'&currentStatus=mapChanged=Historical+map+of+'.$mapDate;
	return($link);
}

function assignMapDate($year) {
	$historicalMaps = array(400, 600, 800, 1000, 1279, 1492, 1530, 1650, 1715, 1783, 1815, 1880, 1914, 1920, 1938, 1949, 1994, 2006);
	$year = intval($year);
	$selectedYear = 400;
	$diversionOld = 10000;
	foreach($historicalMaps as $mapDate) {
		$diversion = abs($mapDate - $year);
		if($diversion < $diversionOld) {
			$selectedYear = $mapDate;
		}
		$diversionOld = $diversion;
	}
	return($selectedYear);
}

?>
