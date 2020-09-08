<?php

class uploader_xml_full extends uploader {
	
	private $dom;

    function __construct($path, $fileName) {
        $this->path = $path;
        $this->fileName = $fileName;
	    $this->dom = new DOMDocument();
	    $this->dom->load($this->path);
        if ($this->dom == FALSE) {
            throw new Exception('XML-Dokument ist nicht wohlgeformt');
        }
        if (!$this->dom->schemaValidate('libreto-schema-full.xsd')) {
            throw new Exception('XML-Dokument validiert nicht gegen das Schma libreto-schema-full.xsd');
        } 
    }	

    public function loadCatalogues($fileName) {
    	$result = array();
    	$catNodes = $this->dom->getElementsByTagName('catalogue');
    	foreach($catNodes as $catNode) {
    		$catalogue = new catalogue();
    		$myNode = new MyDOM($catNode);
    		$myNode->writeAttributesToObject($catalogue, array('id'));
    		$myNode->writeChildrenToObject($catalogue, array('title', 'place', 'year', 'institution', 'shelfmark', 'base'));
    		$persons = $myNode->getChildNodes('person');
    		foreach ($persons as $persNode) {
    			$person = new person;
    			$persNode->writeTextToObject($person, 'persName');
    			$persNode->writeAttributesToObject($person, array('gnd', 'role', 'gender'));
    			$catalogue->persons[] = $person;
    		}
    		$result[] = $catalogue;
    	}
    	return($result);
    }

    public function loadMetadata() {
    	$metadataSet = new metadata_reconstruction;
    	$metadata = $this->dom->getElementsByTagName('metadata')->item(0);
    	$myMetaDom = new MyDOM($metadata);
    	$myMetaDom->writeChildrenToObject($metadataSet, array('heading', 'owner', 'ownerGND', 'description', 'geoBrowserStorageID', 'yearReconstruction'));
        $personNodes = $myMetaDom->getChildNodes('person');
        foreach ($personNodes as $persNode) {
            $person = new person;
            $persNode->writeTextToObject($person, 'persName');
            $persNode->writeAttributesToObject($person, array('gnd', 'role', 'gender'));
            $metadataSet->persons[] = $person;
        }
    	return($metadataSet);
    }

    public function loadContent($fileName) {
    	$result = array();
    	$xp = new DOMXPath($this->dom);
    	$itemList = $xp->query('//item');
    	$countMisc = -1;

    	foreach ($itemList as $itemNode) {

    		$myItem = new MyDOM($itemNode);
    		$item = new item;

            //Laden der Katalogeinträge (der jeweils erste wird aus Gründen der Rückwärtskompatibilität direkt in $item geschrieben)
            $this->loadCatalogueEntries($xp, $item, $itemNode, $myItem);

    		//Bestimmen des Kontextes im Sammelband
    		if ($itemNode->parentNode->nodeName == 'miscellany') {
                $item->itemInVolume = $xp->query('preceding-sibling::item', $itemNode)->length + 1;
    			if ($item->itemInVolume == 1) {
    				$countMisc += 1;
    			}
    			$item->volumeNote = array('misc' => 'br:'.$fileName.'/miscellany_'.$countMisc, 'positionMisc' => $item->itemInVolume);
    		}

	   		//Laden einfacher Properties
    		$myItem->writeChildrenToObject($item, array('id', 'volumes', 'titleBib', 'titleNormalized', 'year', 'histShelfmark', 'mediaType', 'format', 'bound', 'comment', 'digi'));


    		//Laden von wiederholten Feldern
    		$repeatedProperties = array('subject' => 'subjects', 'genre' => 'genres', 'language' => 'languages');
    		foreach ($repeatedProperties as $prop => $target) {
    			$item->$target = $myItem->getRepeatedChild($prop);
    		}

            //Sonderfall: subjectFree wird zu den subjects geladen
            $subjectsFree = $myItem->getRepeatedChild('subjectFree');
            $item->subjects = array_merge($item->subjects, $subjectsFree);

    		//Laden von Personen
    		$personNodes = $xp->query('person', $itemNode);
    		foreach ($personNodes as $pNode) {
    			$person = new person;
    			$person->persName = $pNode->nodeValue;
    			$myPN = new MyDOM($pNode);
    			$myPN->writeAttributesToObject($person, array('gnd', 'role', 'dateLending', 'gender'));
    			$beaconString = $myPN->getAttribute('beacon');
    			if ($beaconString) {
    				$person->beacon = explode(';', $beaconString);
    			}
    			$item->persons[] = $person;
    		}

    		//Laden von Orten
    		$placeNodes = $xp->query('place', $itemNode);
    		foreach ($placeNodes as $pNode) {
    			$place = new place;
    			$place->placeName = $pNode->nodeValue;
    			$myPN = new MyDOM($pNode);
    			$myPN->writeAttributesToObject($place, array('geoNames', 'getty', 'gnd'));
    			$geoDataString = $myPN->getAttribute('geoData');
    			if (strpos($geoDataString, ',') > 2) {
    				$place->geoData['lat'] = explode(',', $geoDataString)[0];
    				$place->geoData['long'] = explode(',', $geoDataString)[1];
    			}
    			$item->places[] = $place;
    		}

    		//Laden von DruckerInnen. Sie werden parallel als Strings und als (bislang ungenutzte) Objekte hinterlegt
    		$printerNodes = $xp->query('publisher', $itemNode);
    		foreach ($printerNodes as $pNode) {
    			$item->publishers[] = $pNode->nodeValue;
    			$myPN = new MyDOM($pNode);
    			$gnd = $myPN->getAttribute('gnd');
    			if ($gnd) {
    				$publisher = new publisher;
    				$publisher->name = $pNode->nodeValue;
    				$publisher->gnd = $gnd;
    				$item->publishersObj[] = $publisher;
    			}
    		}

    		//Laden von Manifestationen
    		$manNode = $myItem->getSingleChildNode('manifestation');
    		if ($manNode) {
    			$item->manifestation['systemManifestation'] = $manNode->getAttribute('system');
    			$item->manifestation['idManifestation'] = $manNode->getAttribute('id');
    			$commentNode = $manNode->getSingleChildNode('comment');
    			if ($commentNode) {
    				$item->manifestation['commentManifestation'] = $commentNode->getContent();
    			}
    		}

    		//Laden von Werken
            $workNode = $myItem->getSingleChildNode('work');
            if ($workNode) {
                $item->work['systemWork'] = $workNode->getAttribute('system');
                $item->work['idWork'] = $workNode->getAttribute('id');
                $item->work['titleWork'] = $workNode->getSingleChildNode('title')->getContent();
                $commentNode = $workNode->getSingleChildNode('comment');
                if ($commentNode) {
                    $item->work['commentWork'] = $commentNode->getContent();
                }
            }

    		//Laden von Originalexemplaren
            $originalNode = $myItem->getSingleChildNode('originalItem');
            if ($originalNode) {
                $properties = $originalNode->getChildrenAssoc(array('institution', 'shelfmark', 'provenanceAttribute', 'digi', 'url', 'comment'));
                $concordance = array('institution' => 'institutionOriginal', 'shelfmark' => 'shelfmarkOriginal', 'provenanceAttribute' => 'provenanceAttribute', 'digi' => 'digitalCopyOriginal', 'url' => 'OPACLink', 'comment' => 'commentOriginal');
                foreach ($properties as $key => $value) {
                    $item->originalItem[$concordance[$key]] = $value;
                }
            }

    		$result[] = $item;
    	}

    	return($result);
    }

    private function loadCatalogueEntries($xp, $item, $itemNode, $myItem) {
        $titleCatNodes = $myItem->getChildNodes('titleCat');
        foreach ($titleCatNodes as $tcn) {
            $entry = new catalogue_entry;
            $entry->titleCat = $tcn->getContent();
            $attributes = $tcn->getAttributes(array('cat', 'no', 'section'));
            if (!empty($attributes['no'])) {
                if (!empty($attributes['cat'])) {
                    $entry->idCat = $attributes['cat'];
                }
                $entry->numberCat = $attributes['no'];
            }
            // Attribut cat ist nicht mehr verbindlich!
            if (!empty($attributes['cat'])) {
                $pbNodes = $xp->query('preceding::pb[@cat="'.$attributes['cat'].'"]', $itemNode);
                if ($pbNodes->length >= 1) {
                    $pb = $pbNodes->item($pbNodes->length - 1);
                    $myPB = new MyDOM($pb);
                    $attributesPB = $myPB->getAttributes(array('no', 'image'));
                    if (isset($attributesPB['no'])) {
                        $entry->pageCat = $attributesPB['no'];
                    }
                    if (isset($attributesPB['image'])) {
                        $entry->imageCat = $attributesPB['image'];
                    }                        
                }
                if (isset($attributes['section'])) {
                    $nodeSection = $xp->query('//catalogue[@id="'.$attributes['cat'].'"]/sections/section[@id="'.$attributes['section'].'"]')->item(0);
                    if ($nodeSection) {
                        $entry->histSubject = $nodeSection->nodeValue;
                    }
                }
            }
            $item->catEntries[] = $entry;
        }
        $item->importFirstCatEntry();
    }


}

?>