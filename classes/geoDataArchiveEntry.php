<?php

class geoDataArchiveEntry {
	public $label = 'kein Ortsname';
	public $lat;
	public $long;
	public $getty;
	public $geoNames;
	public $gnd;
	public $altLabels = array();
	function addAltLabel($altLabel) {
		$this->altLabels[] = $altLabel;
	}
	
	function testIfSame($otherEntry) {
		$same = 0;
		if($this->label == $otherEntry->label) {
			$same = 1;
		}
		elseif($this->getty == $otherEntry->getty and $this->getty != '') {
			$same = 1;
		}
		elseif($this->geoNames == $otherEntry->geoNames and $this->geoNames != '') {
			$same = 1;
		}
		elseif($this->gnd == $otherEntry->gnd and $this->gnd != '') {
			$same = 1;
		}		
		elseif((($this->lat == $otherEntry->lat) and ($this->long == $otherEntry->long)) and $this->lat != '') {
			$same = 1;
		}
		return($same);
	}
}

// Nur für ein spezifisches Datenbankmodell geeignet, daher separat.
function load_from_mysql($database) {
	$dbGeo = new mysqli('localhost', 'root', '', $database);
	$dbGeo->set_charset("utf8");
		if (mysqli_connect_errno()) {
			die ('Konnte keine Verbindung zur Datenbank aufbauen: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
		}
		
	$sql1 = 'SELECT distinct tgn FROM ort WHERE tgn IS NOT NULL';

	$archive = new geoDataArchive();

	if($result = $dbGeo->query($sql1)) {
		$count = 1;
		while($rowPlaces = $result->fetch_assoc()) {
			$tgn = $rowPlaces['tgn'];
			$sql2 = 'SELECT * FROM ort WHERE tgn='.$tgn.'';
			if($result2 = $dbGeo->query($sql2)) {
				while($rowPlaceData = $result2->fetch_assoc()) {
					if(substr($rowPlaceData['ort'], 0, 1) != '[') {
						$count++;
						
						$entry = new geoDataArchiveEntry();
						$entry->label = $rowPlaceData['ort'];
						$entry->getty = strval($tgn);
						$entry->long = cleanCoordinate($rowPlaceData['x']);
						$entry->lat = cleanCoordinate($rowPlaceData['y']);
						
						$archive->insertEntryifNew($entry);
						
					}
				}
			}
		}
	}
	$archive->saveToFile('getty');
	
}

?>
