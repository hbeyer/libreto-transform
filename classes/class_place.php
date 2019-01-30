<?php

class place {
	
	public $placeName;
	public $geoNames;
	public $gnd;
	public $getty;
	public $geoData = array('lat' => '', 'long' => '');
	
	public function __set($name, $value) {
		if ($name == 'Ort') {
            $value = trim(removeBrackets($value));
			$this->placeName = $value;
		}
		elseif ($name == 'TGN') {
			$this->getty = strval($value);
		}
		elseif ($name == 'x') {
			$this->geoData['long'] = strval($value);
		}
		elseif ($name == 'y') {
			$this->geoData['lat'] = strval($value);
		}		
	}

    public function enrichByName($places) {
        foreach ($places as $place) {
            if ($this->placeName == $place->placeName) {
                foreach ($place as $key => $value) {
                    if ($key != 'placeName') {
                        $this->$key = $value;
                    }
                }
            return;
            }
        }
        return(null);
    }

    public function addGeoData($geoDataArchive, $type) {
        if ($this->geoData['lat'] and $this->geoData['long']) {
            return(true);
        }
        $entry = null;
        if ($type == 'geonames' and $this->geoNames) {
            $entry = $geoDataArchive->getByGeoNames($this->geoNames);
        }
        elseif ($type == 'gnd' and $this->gnd) {
            $entry = $geoDataArchive->getByGND($this->gnd);
        }
        elseif ($type == 'getty' and $this->getty) {
            $entry = $geoDataArchive->getByGetty($this->getty);
        }
        if (get_class($entry) == 'geoDataArchiveEntry') {
            $this->geoData = array($entry->lat, $entry->long);
            return(true);
        }
        return(false);
    }

	
}

?>
