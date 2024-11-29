<?php

#[\AllowDynamicProperties]
class Uploader_XML_full extends Uploader {

	private $dom;

    function __construct($path, $fileName) {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->metaPath = Reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-metadata.xml';
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
    		$catalogue = new Catalogue();
    		$myNode = new MyDOM($catNode);
    		$myNode->writeAttributesToObject($catalogue, array('id'));
    		$myNode->writeChildrenToObject($catalogue, array('title', 'place', 'year', 'institution', 'shelfmark', 'base'));
    		$persons = $myNode->getChildNodes('person');
    		foreach ($persons as $persNode) {
    			$person = new Person;
    			$persNode->writeTextToObject($person, 'persName');
    			$persNode->writeAttributesToObject($person, array('gnd', 'role', 'gender'));
    			$catalogue->persons[] = $person;
    		}
			$sectionParent = $myNode->getSingleChildNode('sections');
			$sections = $sectionParent->getChildNodes('section');
			foreach ($sections as $secNode) {
				$id = $secNode->getAttribute('id');
				$label = $secNode->getContent();
				$catSec = new CatalogueSection($id, $label);
				$catalogue->sections[] = $catSec;
			}
    		$result[] = $catalogue;
    	}
    	return($result);
    }

    public function loadMetadata() {
    	$metadataSet = new MetadataReconstruction();
    	$metadata = $this->dom->getElementsByTagName('metadata')->item(0);
    	$myMetaDom = new MyDOM($metadata);
    	$myMetaDom->writeChildrenToObject($metadataSet, array('heading', 'owner', 'ownerGND', 'description', 'geoBrowserStorageID', 'geoBrowserStorageID_bio', 'yearCollection', 'yearReconstruction'));
        $personNodes = $myMetaDom->getChildNodes('person');
        foreach ($personNodes as $persNode) {
            $person = new Person();
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
    		$item = new Item;

            //Laden der Katalogeinträge (der jeweils erste wird aus Gründen der Rückwärtskompatibilität direkt in $item geschrieben)
            $this->loadCatalogueEntries($xp, $item, $itemNode, $myItem);

    		//Bestimmen des Kontextes im Sammelband
    		if ($itemNode->parentNode->nodeName == 'miscellany') {
                if ($xp->query('parent::miscellany/child::Item', $itemNode)->length == 1) {
                    $item->itemInVolume = 0;
                }
                else {
                    $item->itemInVolume = $xp->query('preceding-sibling::Item', $itemNode)->length + 1;
                }
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
    			$person = new Person;
    			$person->persName = trim($pNode->nodeValue);
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
    			$place = new Place;
    			$place->placeName = trim($pNode->nodeValue);
    			$myPN = new MyDOM($pNode);
    			$myPN->writeAttributesToObject($place, array('geoNames', 'getty', 'gnd'));
    			$geoDataString = $myPN->getAttribute('geoData');
                if (!empty($geoDataString)) {
    				$place->geoData['lat'] = explode(',', $geoDataString)[0];
    				$place->geoData['long'] = explode(',', $geoDataString)[1];
                }
    			$item->places[] = $place;
    		}

    		//Laden von DruckerInnen. Sie werden parallel als Strings und als (bislang ungenutzte) Objekte hinterlegt
    		$printerNodes = $xp->query('publisher', $itemNode);
    		foreach ($printerNodes as $pNode) {
    			$item->publishers[] = trim($pNode->nodeValue);
    			$myPN = new MyDOM($pNode);
    			$gnd = $myPN->getAttribute('gnd');
    			if ($gnd) {
    				$publisher = new Publisher;
    				$publisher->name = trim($pNode->nodeValue);
    				$publisher->gnd = $gnd;
    				$item->publishersObj[] = $publisher;
    			}
    		}

    		//Laden von Manifestationen
    		$manNode = $myItem->getSingleChildNode('manifestation');
    		if ($manNode) {
    			$item->manifestation['systemManifestation'] = trim($manNode->getAttribute('system'));
    			$item->manifestation['idManifestation'] = trim($manNode->getAttribute('id'));
                if (!empty($manNode->getSingleChildNode('comment'))) {
    			    $commentNode = trim($manNode->getSingleChildNode('comment'));
                    $item->manifestation['commentManifestation'] = trim($commentNode->getContent());
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
                $paNode = $originalNode->getSingleChildNode('provenanceAttribute');
                if (!empty($paNode)) {
                    $type = $paNode->getAttribute('type');
                    if ($item->originalItem['provenanceAttribute'] and $type) {
                        $item->originalItem['provenanceAttribute'] = $type.': '.$item->originalItem['provenanceAttribute'];
                    }
                    elseif ($type) {
                        $item->originalItem['provenanceAttribute'] = $type;
                    }
                }

            }

    		$result[] = $item;
    	}

    	return($result);
    }

    private function loadCatalogueEntries($xp, $item, $itemNode, $myItem) {
        $titleCatNodes = $myItem->getChildNodes('titleCat');
        foreach ($titleCatNodes as $tcn) {
            $entry = new CatalogueEntry;
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
                        $entry->histSubject = trim($nodeSection->nodeValue);
                    }
                }
            }
            else {
                $pbNodes = $xp->query('preceding::pb', $itemNode);
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
                    $nodeSection = $xp->query('//catalogue/sections/section[@id="'.$attributes['section'].'"]')->item(0);
                    if ($nodeSection) {
                        $entry->histSubject = trim($nodeSection->nodeValue);
                    }
                }
            }


            $item->catEntries[] = $entry;
        }
        $item->importFirstCatEntry();
    }

}

?>
