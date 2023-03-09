<?php

class GeoDataSheet {

	private $rows = array();

	function insertRow(GeoDataRow $row) {
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