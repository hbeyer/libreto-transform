<?php

#[\AllowDynamicProperties]
class  Serializer_Gephi extends Serializer {

    public function serialize() {
        $this->beaconRep = new BeaconRepository();
        $this->pathNodes = Reconstruction::getPath($this->fileName, $this->fileName.'-nodes', 'csv');
        $this->pathEdges = Reconstruction::getPath($this->fileName, $this->fileName.'-edges', 'csv');
        $this->handleNodes = fopen($this->pathNodes, 'w');
        $this->handleEdges = fopen($this->pathEdges, 'w');
        fputcsv($this->handleNodes, array('ID', 'Label', 'Type'));
        $ownerType = 'Person';
        if (!empty($this->catalogue->ownerGND)) {
            if (strpos('-', $this->catalogue->ownerGND) !== false) {
            	$ownerType = 'Institution';
            }        
        }
        $this->ownerNode = 'gnd_'.$this->catalogue->ownerGND;
        fputcsv($this->handleNodes, array($this->ownerNode, $this->catalogue->owner, $ownerType));
        fputcsv($this->handleEdges, array('Source', 'Target', 'property', 'Date'));
        foreach ($this->beaconRep->beacon_sources as $key => $bdata) {
            fputcsv($this->handleNodes, array('beacon_'.$key, $bdata['label'], 'Personendatenbank'));
        }
        foreach ($this->data as $item) {
            $this->postData($item);
        }
        $this->zipResult();
    }

    private function postData($item) {
    	$bookID = $this->makeBookID($item);
    	fputcsv($this->handleEdges, array($this->ownerNode, $bookID, 'owner', $this->catalogue->year));
    	$timestamp = Index::normalizeYear($item->year);
    	fputcsv($this->handleNodes, array($bookID, $this->makeShortTitle($item), 'Buch'));
    	foreach ($item->persons as $person) {
    		$property = 'contributor';
    		$personID = $person->makeID();
    		if (in_array($person->role, array('creator', 'author', 'VerfasserIn', 'Verfasser'))) {
    			$property = 'creator';
    		}
    		elseif (in_array($person->role, array('borrower'))) {
    			$property = 'borrower';
    		}
    		fputcsv($this->handleNodes, array($personID, $person->persName, 'Person'));
	    	if ($property == 'borrower') {
	    		foreach ($person->dateLending as $dateL) {
	    			fputcsv($this->handleEdges, array($personID, $bookID, $property, $dateL));
	    		}
	    	}
	    	else {
	    		fputcsv($this->handleEdges, array($personID, $bookID, $property, $timestamp));
	    	}
            foreach ($person->beacon as $bkey) {
                if (!empty($this->beaconRep->beacon_sources[$bkey])) {
                    $bdata = $this->beaconRep->beacon_sources[$bkey];
                    fputcsv($this->handleEdges, array('beacon_'.$bkey, $personID, 'contains', date('Y')));
                }
            }
    	}
    	foreach ($item->publishers as $publisher) {
    		fputcsv($this->handleNodes, array($publisher, $publisher, 'Druckende_Verlegende'));
    		fputcsv($this->handleEdges, array($publisher, $bookID, 'publisher', $timestamp));
    	}
    	foreach ($item->places as $place) {
    		$placeID = $place->makeID();
		    fputcsv($this->handleNodes, array($placeID, $place->placeName, 'Ort'));
    		fputcsv($this->handleEdges, array($placeID, $bookID, 'place', $timestamp));
    	}
    	$subjects = array_merge($item->genres, $item->subjects);
    	foreach ($subjects as $subject) {
			fputcsv($this->handleNodes, array($subject, $subject, 'Inhalt_Gattung'));
    		fputcsv($this->handleEdges, array($subject, $bookID, 'subject', $timestamp));
    	}
    }

    private function makeBookID($item) {
    	if ($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
    		return($item->manifestation['systemManifestation'].'_'.$item->manifestation['idManifestation']);
    	}
    	return($item->id);
    }

    private function makeShortTitle($item) {
    	$title = '[ohne Titel]';
    	if ($item->titleNormalized) {
    		$title = $item->titleNormalized;
    	}
    	elseif ($item->titleBib) {
    		$title = $item->titleBib;
    	}
    	elseif ($item->titleCat) {
    		$title = $item->titleCat;
    	}
    	$title = substr($title, 0, 140);
    	if ($item->year) {
    		$title .= ' ('.$item->year.')';
    	}
    	return($title);
    }

    private function zipResult() {
    	$zip = new ZipArchive;
    	$zipFile = Reconstruction::getPath($this->fileName, $this->fileName.'-gephi', 'zip');
		if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
			echo 'Zip-File für Gephi-Export nicht zu öffnen';
			return(false);
		}
		$options = array('add_path' => $this->fileName.'/', 'remove_all_path' => true);
		$zip->addGlob($this->pathNodes, 0, $options);
		$zip->addGlob($this->pathEdges, 0, $options);
		$zip->close();
		return(true);
    }

}

?>
