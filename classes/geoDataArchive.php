<?php

class geoDataArchive {
	
	public $date;
	public $content = array();
	public $folder = 'geoDataArchive';
    private $permittedFormats = array('geoNames', 'gnd', 'getty');
	
	function __construct($type = '') {
		$this->date = date("Y-m-d H:i:s");
        if (in_array($type, $this->permittedFormats)) {
            $this->loadFromFile($type);
        }
	}
	
	function insertEntry($entry) {
		if($entry->lat != '' and $entry->long != '' and $entry->label != '') {
			$this->content[] = $entry;
		}
	}
	
	// Inserts an entry into an archive, unless there is one with the same ID (getty, geoNames or gnd)	
    // Die Parameter sind redundant
	function insertEntryIfNew($type, $id, $entry) {
		$check = 0;
		foreach($this->content as $oldEntry) {
			if($oldEntry->$type == $id) {
				if($oldEntry->long and $oldEntry->lat and $oldEntry->label) {
					$check = 1;
				}
				else {
					$this->deleteByID($type, $id);	
				}
			}
		}
		if($check == 0) {
			$this->insertEntry($entry);
            return(true);
		}
        return(false);
	}

	// Inserts an entry into an archive, unless there is one with the same label or the same id or the same coordinates
	function insertEntryIfTotallyNew($entry) {
		$check = 0;
		foreach($this->content as $oldEntry) {
			if($oldEntry->testIfSame($entry) == 1) {
				$check++;
			}
		}
		if($check == 0) {
			$this->insertEntry($entry);
		}
	}
	
	function saveToFile($fileName) {
		$serialize = serialize($this);
		file_put_contents($this->folder.'/'.$fileName, $serialize);
	}
	
	function loadFromFile($fileName) {
		$archiveString = file_get_contents($this->folder.'/'.$fileName);
		$archive = unserialize($archiveString);
		unset($archiveString);
		$this->content = $archive->content;
	}

    public function getFromWeb($id, $type, $user) {
        if ($type == 'geoNames') {
            $entry = $this->getByGeoNames($id);
            if ($entry == null) {
                $entry = $this->makeEntryFromGeoNames($id, $user);
            }
        }
        elseif ($type == 'gnd') {
            $entry = $this->getByGND($id);
            if ($entry == null) {
                $entry = $this->makeEntryFromGNDTTL($id);
            }
        }
        elseif ($type == 'getty') {
            $entry = $this->getByGetty($id);
            if ($entry == null) {
                $entry = $this->makeEntryFromGetty($id);
            }
        }
        if (!empty($entry)) {
        	if (get_class($entry) == 'geoDataArchiveEntry') {
 	           $this->insertEntryIfNew($type, $entry->$type, $entry);
 	           return($entry);
 	       	}
 	    }
        return(null);
    }


    function getByType($id, $type) {
		foreach($this->content as $entry) {
			if($entry->$type == $id) {
				return($entry);
			}
		}
        return(null);        
    }
	
	function getByGeoNames($id) {
		foreach($this->content as $entry) {
			if($entry->geoNames == $id) {
				return($entry);
			}
		}
        return(null);
	}
	
	function getByGND($id) {
		foreach($this->content as $entry) {
			if($entry->gnd == $id) {
				return($entry);
			}
		}
        return(null);
	}	
	
	function getByGetty($id) {
		foreach($this->content as $entry) {
			if($entry->getty == $id) {
				return($entry);
			}
		}
        return(null);
	}
	
	//Returns an entry by label if the label is unique
	function getByName($name) {
		$result = null;
		$name = trim($name);
		$resultLabel = array();
		$resultAltLabels = array();
		$count = 0;
		foreach($this->content as $entry) {
			if($entry->label == $name) {
				$resultLabel[] = $count;
			}
			elseif(in_array($name, $entry->altLabels)) {
				$resultAltLabels[] = $count;
			}
			$count++;
		}
		if(isset($resultLabel[0]) == TRUE and isset($resultLabel[1]) == FALSE) {
			$result = $this->content[$resultLabel[0]];
		}
		elseif(isset($resultAltLabels[0]) == TRUE and isset($resultAltLabels[1]) == FALSE) {
			$result = $this->content[$resultAltLabels[0]];
		}
		return($result);
	}

	function deleteByID($type, $id) {
		$resultArray = array();
		foreach($this->content as $entry) {
			if($entry->$type != $id) {
				$resultArray[] = $entry;
			}
		}
		$this->content = $resultArray;	
	}

    function reloadEntry($type, $id, $user = '') {
        $this->deleteByID($type, $id);
        $entry = $this->getFromWeb($id, $type, $user);
        $test = $this->insertEntryIfNew($type, $id, $entry);
        return($test);
    }
	
	function loadFromGeoBrowserCSV($type, $fileName) {
		$csv = array_map('str_getcsv', file($fileName));
		$this->loadFromFile($type);
		$lastName = '';
		foreach($csv as $row) {
			if($row[0] != $lastName) {
				$entry = new geoDataArchiveEntry();
				$entry->label = $row[0];
				if($entry->label == '') {
					$entry->label = $row[1];
				}
				$entry->long =$row[3];
				$entry->lat = $row[4];
				$entry->getty = $row[8];
				$this->insertEntryIfNew($entry);
			}
			$lastName = $entry->label;
		}
		$this->saveToFile($type);
	}
	
	function makeEntryFromGeoNames($id, $user) {
		$target = 'http://api.geonames.org/getJSON?formatted=true&geonameId='.$id.'&username='.$user;
		$responseString = file_get_contents($target);
		$response = json_decode($responseString);

		$varNames = array();
		foreach($response->alternateNames as $alternate) {
			if(isset($alternate->lang) and preg_match('~^de|la|fr|it|en$~', $alternate->lang) == 1) {
			$varNames[] = $alternate->name;
			}
		}
		$entry = new geoDataArchiveEntry();
		$entry->label = $response->toponymName;
		$entry->lat = $response->lat;
		$entry->long = $response->lng;
		$entry->geoNames = $id;
		$entry->altLabels = $varNames;
		return($entry);
	}
	
	function makeEntryFromGetty($id) {
        if (!preg_match('~[0-9]{5,9}~', $id)) {
            return(null);
        }
		$url = 'https://ref.de.dariah.eu/tgnsearch/tgnquery.xql?id='.$id;
		$prefLabel = null;
		$lat = null;
		$long = null;

		$doc = new DOMDocument;
		$test = $doc->load($url);
		if ($test == null) {
			echo 'Fehler beim Laden von TGN '.$id;
			return(null);
		}

		$xp = new DOMXPath($doc);
		$xp->registerNamespace('tgn', 'http://textgrid.info/namespaces/vocabularies/tgn');

		$labelNodes = $xp->query('//tgn:Preferred_Term/tgn:Term_Text');
		foreach ($labelNodes as $labelNode) {
			if ($labelNode->nodeValue) {
				$prefLabel = $labelNode->nodeValue;
				break;
			}
		}

		$latNodes = $xp->query('//tgn:Latitude/tgn:Decimal');
		if (!empty($latNodes->item(0))) {
			$lat = $latNodes->item(0)->nodeValue;
		}

		$longNodes = $xp->query('//tgn:Longitude/tgn:Decimal');
		if (!empty($longNodes->item(0))) {
			$long = $longNodes->item(0)->nodeValue;
		}

		if ($prefLabel == null or $lat == null or $long == null) {
			return(null);
		}

		$entry = new geoDataArchiveEntry();
		$entry->label = $prefLabel;
		$entry->lat = $lat;
		$entry->long = $long;
		$entry->getty = $id;
		
		return($entry);
	}

	function makeEntryFromGNDTTL($gnd) {
		$target = 'http://d-nb.info/gnd/'.$gnd.'/about/lds';
		$response = file_get_contents($target);
		preg_match('~gndo:preferredNameForThePlaceOrGeographicName "([^"]+)"~', $response, $hitsPrefLabel);
		preg_match('~gndo:variantNameForThePlaceOrGeographicName ([^;]+)~', $response, $hitsAltLabels);
		preg_match('~owl:sameAs <http://sws.geonames.org/([0-9]{6,8})>~', $response, $hitsSameAs);
		preg_match('~Point \( ([+-][0-9]{1,3}\.[0-9]{1,10}) ([+-][0-9]{1,3}\.[0-9]{1,10}) \)~', $response, $hitsPoint);
		
		$entry = new geoDataArchiveEntry();
        $entry->gnd = $gnd;
		if(isset($hitsPrefLabel[1])) {
			$entry->label = replaceArrowBrackets($hitsPrefLabel[1]);
		}
		if(isset($hitsAltLabels[1])) {
			preg_match_all('~"([^"]+)"~', $hitsAltLabels[1], $altLabels);
			$entry->altLabels = $altLabels[1];
		}
		if(isset($hitsSameAs[1])) {
			$entry->geoNames = $hitsSameAs[1];
		}	
		if(isset($hitsPoint[1]) and isset($hitsPoint[2])) {
			$entry->long = $hitsPoint[1];
			$entry->lat = $hitsPoint[2];
            return($entry);
		}		
		return(null);
	}
	
	//Created for entries in RDF/XML-format. Meanwhile, the interface delivers RDF/TTL (see above)
	function makeEntryFromGND($gnd) {
		$target = 'http://d-nb.info/gnd/'.$gnd.'/about/lds';
		$response = file_get_contents($target);
		$RDF = new DOMDocument();
		$RDF->load($target);
			
		$nodePrefName = $RDF->getElementsByTagNameNS('http://d-nb.info/standards/elementset/gnd#', 'preferredNameForThePlaceOrGeographicName');
		$prefName = getTextContent($nodePrefName);
		
		$nodeVarName = $RDF->getElementsByTagNameNS('http://d-nb.info/standards/elementset/gnd#', 'variantNameForThePlaceOrGeographicName');
		$varNameString = getTextContent($nodeVarName);
		$varNameString = replaceArrowBrackets($varNameString);
		$varNames = explode('|', $varNameString);
		
		$nodeGeoData = $RDF->getElementsByTagNameNS('http://www.opengis.net/ont/geosparql#', 'asWKT');
		$geoDataString = getTextContent($nodeGeoData);
		preg_match('~ ([+-][0-9]{1,3}\.[0-9]{1,10}) ([+-][0-9]{1,3}\.[0-9]{1,10}) ~', $geoDataString, $matches);
		$long = '';
		$lat = '';
		if(isset($matches[1]) and isset ($matches[2])) {
			$long = $matches[1];
			$lat = $matches[2];
		}
		
		$nodeSameAs = $RDF->getElementsByTagNameNS('http://www.w3.org/2002/07/owl#', 'sameAs');
		$sameAs = getAttributeFromNodeList($nodeSameAs, 'rdf:resource');
		preg_match('~http://sws.geonames.org/([0-9]{5,10})~', $sameAs, $matches);
		$geoNames = '';
		if(isset($matches[1])) {
			$geoNames = $matches[1];
		}
		
		$entry = new geoDataArchiveEntry();
		$entry->label = replaceArrowBrackets($prefName);
		$entry->lat = $lat;
		$entry->long = $long;
		$entry->gnd = $gnd;
		$entry->geoNames = $geoNames;
		$entry->altLabels = $varNames;
		
		return($entry);
	}
	
}

?>