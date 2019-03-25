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

    public function testIfReal() {
        $testName = strtolower($this->placeName);
        $testName = strtr($testName, array(' ' => '', '.' => ''));
        $valuesVoid = array('sl', 'oo', 'unbestimmt', 'ohneort', 'sineloco', 'keineangabe', 'keinortsname', 'keinort', '');
        if (in_array($testName, $valuesVoid)) {
            return(0);
        }
        if (strpos($testName, 'fingiert') !== false) {
            return(0);
        }
        else {
            return(1);
        }
    }

    public function addGeoData($geoDataArchive, $type, $user = '') {
        $entry = $geoDataArchive->getFromWeb($this->$type, $type, $user);
        if (get_class($entry) == 'geoDataArchiveEntry') {
            $this->geoData = array('lat' => $entry->lat, 'long' => $entry->long);
            $geoDataArchive->insertEntryIfNew($type, $this->$type, $entry);
            return(true);
        }
        return(false);
    }
	
}

?>
