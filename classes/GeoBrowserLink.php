<?php

class GeoBrowserLink {
	
	const PATTERN = 'https://geobrowser.de.dariah.eu/?csv1=https://cdstar.de.dariah.eu/dariah/{ID}';
	const PATTERNYEAR = '&currentStatus=mapChanged=Historical+map+of+{YEAR}';
	const MAPDATES = array(400, 600, 800, 1000, 1279, 1492, 1530, 1650, 1715, 1783, 1815, 1880, 1914, 1920, 1938, 1949, 1994, 2006);

	static function url($id, $year = null) {
		$url = strtr(GeoBrowserLink::PATTERN, array('{ID}' => $id)).GeoBrowserLink::layer($year);
		return($url);
	}

	static function layer($year) {
		if (!preg_match('~^[12]?[0-9]{3}$~', $year)) {
			return('');
		}
		$year = intval($year);
		$selectedYear = GeoBrowserLink::assignYear($year);
		$layer = strtr(GeoBrowserLink::PATTERNYEAR, array('{YEAR}' => $selectedYear));
		return($layer);
	}

	static function assignYear($year) {
		$selectedYear = 400;
		$diversionOld = 10000;
		foreach(GeoBrowserLink::MAPDATES as $mapDate) {
			$diversion = abs($mapDate - $year);
			if($diversion < $diversionOld) {
				$selectedYear = $mapDate;
			}
			$diversionOld = $diversion;
		}
		return($selectedYear);
	}

}

?>