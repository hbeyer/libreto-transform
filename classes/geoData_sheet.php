<?php

class geoData_sheet {

	private $rows = array();

	function insertRow(geoDataRow $row) {
		$this->rows[] = $row;
	}

	public function saveCSV($path = '') {
		$content = '"Name","Address","Description","Longitude","Latitude","TimeStamp","TimeSpan:begin","TimeSpan:end","GettyID",""
';
		foreach ($this->rows as $row) {
			$content .= $row->serializeCSV();
		}
		file_put_contents($path.'bioPlaces.csv', $content);
	}
	
}

?>