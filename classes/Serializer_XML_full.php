<?php

class Serializer_XML_full extends Serializer_XML {

	public $catalogues = array();
	public $metadataReconstruction;

    public function __construct($catalogues, MetadataReconstruction $metadata, $data) {
		foreach ($catalogues as $cat) {
			if (get_class($cat) == 'Catalogue') {
				$this->catalogues[] = $cat;
			}
		}
		$this->metadataReconstruction = $metadata;
		$this->fileName = $this->metadataReconstruction->fileName;
		$this->path = Reconstruction::getPath($this->fileName, $this->fileName."-full", 'xml');
		$this->data = $data;
		$this->serialize();
    }

    protected function insertContent() {
        $rootElement = $this->dom->createElement('collection');
        $this->dom->appendChild($rootElement);
        $this->insertMetadata();
        $this->insertData();
    }

    protected function insertMetadata() {
        $root = $this->dom->documentElement;
        $metadata = $this->dom->createElement('metadata');
        $fields = array('heading', 'owner', 'ownerGND', 'fileName', 'description', 'geoBrowserStorageID', 'geoBrowserStorageID_bio', 'creatorReconstruction', 'yearReconstruction');
        foreach ($this->metadataReconstruction as $key => $value) {
            if (in_array($key, $fields) and $value) {
                $element = $this->dom->createElement($key);
                $text = $this->dom->createTextNode($value);
                $element->appendChild($text);
                $metadata->appendChild($element);
            }
        }
		foreach ($this->catalogues as $cat) {
			$catElement = $this->dom->createElement('catalogue');
			$catElement->setAttribute("id", $cat->id);
			foreach($cat->persons as $pers) {
				$persEl = $this->dom->createElement('person');
				if (!$pers->role) {
					$pers->role = 'VerfasserIn';
				}
				$persEl->setAttribute('role', $pers->role);
				if ($person->gnd) {
					$persEl->setAttribute('gnd', $pers->gnd);
				}
				if ($person->gender) {
					$persEl->setAttribute('gender', $pers->gender);
				}
				$text = $this->dom->createTextNode($pers->persName);
				$persEl->appendChild($text);
				$catEl->appendChild($persEl);
			}
			$fields = array('title', 'placeCat', 'printer', 'year', 'institution', 'shelfmark');
			foreach ($cat as $key => $value) {
				if (in_array($key, $fields)) {
					$element = $this->dom->createElement($key);
					$text = $this->dom->createTextNode($value);
					$element->appendChild($text);
					$catElement->appendChild($element);
				}
			}
			$sectionsEl = $this->dom->createElement('sections');
			foreach ($cat->sections as $section) {
				$sectionEl = $this->dom->createElement('section');
				$sectionEl->setAttribute("id", $section->id);
				$text = $this->dom->createTextNode($section->label);
				$sectionEl->appendChild($text);
				$sectionsEl->appendChild($sectionEl);
			}
			$catElement->appendChild($sectionsEl);
			$metadata->appendChild($catElement);
		}
        $root->appendChild($metadata);
    }

    protected function insertData() {
		$root = $this->dom->documentElement;
		$lastEntries = null;
		$miscEl = null;
        foreach ($this->data as $item) {
            $itemElement = $this->dom->createElement('item');
            $itemElement = $this->fillDOMItem($itemElement, $item);
			if ($item->itemInVolume <= 1) {
				if ($miscEl != null) {
					$root->appendChild($miscEl);
				}
				if ($item->itemInVolume == 1) {
					$miscEl = $this->dom->createElement('miscellany');
					$this->insertPageBreaks($miscEl, $item->catEntries, $lastEntries);
					$miscEl->appendChild($itemElement);
				}
				elseif ($item->itemInVolume == 0) {
					$this->insertPageBreaks($root, $item->catEntries, $lastEntries);
					$root->appendChild($itemElement);
					$miscEl = null;
				}
			}
			elseif ($item->itemInVolume > 1) {
				$this->insertPageBreaks($miscEl, $item->catEntries, $lastEntries);
				$miscEl->appendChild($itemElement);
			}
			$lastEntries = $item->catEntries;
        }
		if ($miscEl != null) {
			$root->appendChild($miscEl);
		}
		return(true);
    }

	protected function insertPageBreaks($root, $catEntries, $lastEntries) {
		if ($lastEntries == null) {
			foreach ($catEntries as $entry) {
				$pbEl = $this->dom->createElement('pb');
				$pbEl->setAttribute('cat', $entry->idCat);
				$pbEl->setAttribute('no', $entry->pageCat);
				$pbEl->setAttribute('image', $entry->imageCat);
			}
		return(true);
		}
		foreach ($catEntries as $entry) {
			foreach ($lastEntries as $lentry) {
				if ($entry->idCat == $lentry->idCat and ($entry->pageCat != $lentry->pageCat)) {
					$pbEl = $this->dom->createElement('pb');
					$pbEl->setAttribute('cat', $entry->idCat);
					$pbEl->setAttribute('no', $entry->pageCat);
					$pbEl->setAttribute('image', $entry->imageCat);
				}
			}
		}
		return(true);
	}

    function fillDOMItem($itemElement, $item) {
		$unique_fields = array('id', 'titleBib', 'volumes', 'volumesMisc', 'titleNormalized', 'year', 'format', 'mediaType', 'bound', 'comment', 'digitalCopy');
		foreach ($unique_fields as $field) {
			if ($item->$field) {
				$newEl = $this->dom->createElement($field);
				$text = $this->dom->createTextNode($item->$field);
				$newEl->appendChild($text);
				$itemElement->appendChild($newEl);
			}
		}
		$array_fields = array('publishers', 'subjects', 'genres', 'languages');
		foreach ($array_fields as $field) {
			if (!empty($item->$field)) {
				foreach ($item->$field as $value) {
					$newEl = $this->dom->createElement(substr($field, 0, -1));
					$text = $this->dom->createTextNode($value);
					$newEl->appendChild($text);
					$itemElement->appendChild($newEl);
				}
			}
		}
		foreach ($item->catEntries as $entry) {
			$entryEl = $this->dom->createElement("titleCat");
			$text = $this->dom->createTextNode($entry->titleCat);
			$entryEl->appendChild($text);
			$entryEl->setAttribute('cat', $entry->idCat);
			$entryEl->setAttribute('section', $entry->idSect);
			$itemElement->appendChild($entryEl);
		}
		foreach ($item->persons as $pers) {
			$persEl = $this->dom->createElement("person");
			$text = $this->dom->createTextNode($pers->persName);
			$persEl->appendChild($text);
			$properties = array('role', 'gnd', 'gender', 'dateLending', 'beacon');
			foreach ($properties as $field) {
				if (empty($pers->$field)) {
					continue;
				}
				if ($field == 'dateLending' or $field == 'beacon') {
					$text = implode(';', $pers->$field);
				}
				else {
					$text = $pers->$field;
				}
				$persEl->setAttribute($field, $text);
			}
			$itemElement->appendChild($persEl);
		}
		foreach ($item->places as $place) {
			$placeEl = $this->dom->createElement("place");
			$text = $this->dom->createTextNode($place->placeName);
			$placeEl->appendChild($text);
			$properties = array('getty', 'gnd', 'geoNames', 'geodata');
			foreach ($properties as $field) {
				if (empty($pers->$field) or $pers->$field == array('lat' => '', 'long' => '')) {
					continue;
				}
				if ($field == 'geodata') {
					$text = $pers->$field['lat'].','.$pers->$field['long'];
				}
				else {
					$text = $pers->$field;
				}
				$placeEl->setAttribute($field, $text);
			}
			$itemElement->appendChild($placeEl);
		}
		if ($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
			$manEl = $this->dom->createElement("manifestation");
			$manEl->setAttribute("system", $item->manifestation['systemManifestation']);
			$manEl->setAttribute("id", $item->manifestation['idManifestation']);
			$itemElement->appendChild($manEl);
		}
		if ($item->work['titleWork']) {
			$workEl = $this->dom->createElement("work");
			$titleEl = $this->dom->createElement("title");
			$text = $this->dom->createTextNode($item->work['titleWork']);
			$titleEl->appendChild($text);
			$workEl->appendChild($titleEl);
			$workEl->setAttribute("system", $item->work['systemWork']);
			$workEl->setAttribute("id", $item->work['idWork']);
			$itemElement->appendChild($workEl);
		}
		if ($item->originalItem['institutionOriginal'] and $item->originalItem['shelfmarkOriginal']) {
			$copyEl = $this->dom->createElement("originalItem");
			$insertArray = array(
				'institution' => $item->originalItem['institutionOriginal'],
				'shelfmark' => $item->originalItem['shelfmarkOriginal'],
				'provenanceAttribute' => $item->originalItem['provenanceAttribute'],
				'digi' => $item->originalItem['digitalCopyOriginal']);
			if ($item->originalItem['OPACLink']) {
				$insertArray['url'] = $item->originalItem['OPACLink'];
			}
			elseif ($item->originalItem['targetOPAC'] and $item->originalItem['searchID']) {
				$url = translate($item->originalItem['targetOPAC'], array('{ID}', $item->originalItem['searchID']));
				$insertArray['url'] = $url;
			}
			$this->appendAssocArrayToDOM($copyEl, $insertArray);
			$itemElement->appendChild($copyEl);
		}

		foreach ($item->copiesHAB as $shelfmark) {
			$copyEl = $this->dom->createElement("copy");
			$text = $this->dom->createTextNode($shelfmark);
			$copyEl->appendChild($text);
			$copyEl->setAttribute('institution', 'Herzog August Bibliothek Wolfenbüttel');
			$itemElement->appendChild($copyEl);
		}
		return($itemElement);
    }

}

?>
