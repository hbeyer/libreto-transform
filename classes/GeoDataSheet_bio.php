<?php

class GeoDataSheet_bio extends GeoDataSheet {

	private $geoDataArchive;

	function __construct($GNDList, cache $cache) {
		$this->geoDataArchive = new GeoDataArchive('gnd');
		foreach ($GNDList as $gnd) {
			$request = new GND_request(new GND($gnd), $cache);
			if (!empty($request->preferredName)) {
				$row = $this->makeRowFromRequest($request);
				if ($row) {
					$this->insertRow($row);
				}	
			}
		}
		$this->geoDataArchive->saveToFile('gnd');
	}

	private function makeRowFromRequest(gnd_request $request) {
		$row = new GeoDataRow;
		if (!empty($request->placeBirth->gnd) and $request->dateBirth) {
			$row->insertPlaceFromGNDRequest($request, 'Birth', $this->geoDataArchive);
			$row->timeStamp = GNDRequest::makeTimeStamp($request->dateBirth);
			return($row);
		}
		if (!empty($request->placeDeath->gnd) and $request->dateDeath) {
			$row->insertPlaceFromGNDRequest($request, 'Death', $this->geoDataArchive);
			$row->timeStamp = GNDRequest::makeTimeStamp($request->dateDeath);			
			return($row);
		}
		if (!empty($request->placeBirth->gnd)) {
			$row->insertPlaceFromGNDRequest($request, 'Birth', $this->geoDataArchive);
			return($row);
		}
		if (!empty($request->placeDeath->gnd)) {
			$row->insertPlaceFromGNDRequest($request, 'Death', $this->geoDataArchive);
			return($row);
		}
		if (!empty($request->placesActivity[0]->gnd)) {
			$row->insertPlaceFromGNDRequest($request, 'Activity', $this->geoDataArchive);
			return($row);
		}
		if ($request->placeBirth) {
			$row->insertPlaceFromGNDRequest($request, 'Birth');
			return($row);
		}
		if ($request->placeDeath) {
			$row->insertPlaceFromGNDRequest($request, 'Death');
			return($row);
		}
		if (!empty($request->placesActivity[0])) {
			$row->insertPlaceFromGNDRequest($request, 'Activity');
			return($row);
		}		

		return(null);
	}
	
}

?>