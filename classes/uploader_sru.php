<?php

class uploader_sru extends uploader {
	
	protected $source = 'http://sru.k10plus.de/gvk';
	protected $version = '2.0';
	protected $xmlSets = array();
	public $content = array();
	protected $tempDOM = null;
	public $numHits = 0;

	public function __construct($query, $fileName) {
		$this->fileName = $fileName;
		$this->query = $query;
		$this->loadSets();
		while ($xml = array_shift($this->xmlSets)) {
			$recordNodes = $this->getNodes($xml);
			foreach ($recordNodes as $recNode) {
				$item = $this->makeItemFromNode($recNode);
				if ($item) {
					$this->content[] = $item;
				}
			}
		}
		$this->tempDOM = null;
	}

	protected function loadSets() {
		$cache = new cache_pica;
		$url = $this->makeURL('1', '0');
		$string = $cache->get($url);
		preg_match('~<zs:numberOfRecords>([0-9]+)</zs:numberOfRecords>~', $string, $hits);
		if (!empty($hits[1])) {
			$this->numHits = intval($hits[1]);
		}
		$first = 1;
		while ($first < $this->numHits) {
			$url = $this->makeURL(strval($first));
			$this->xmlSets[] = $cache->get($url);
			$first += 500;
		}
	}

	protected function makeURL($start = '1', $maxRec = '500', $format = 'picaxml') {
		return($this->source.'?version='.$this->version.'&operation=searchRetrieve&query='.$this->query.'&maximumRecords='.$maxRec.'&startRecord='.$start.'&recordSchema='.$format);
	}

	protected function getNodes($xml) {
		$this->tempDOM = new DOMDocument;
		$this->tempDOM->loadXML($xml);
		$xp = new DOMXPath($this->tempDOM);
		$xp->registerNamespace('pica', 'info:srw/schema/5/picaXML-v1.0');
		$nodes = $xp->query('//pica:record');
		return($nodes);
	}

	protected function makeItemFromNode($recNode) {
		$item = new item;
		$xpn = new DOMXPath($this->tempDOM);
		$xpn->registerNamespace('pica', 'info:srw/schema/5/picaXML-v1.0');

		$singleValued = array('year', 'titleBib', 'format', 'digitalCopy');
		foreach ($singleValued as $field) {
			$conf = picaConf::getFieldConf($field);
			$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
			$value = array_shift($array);
			$item->$field = $value;
		}

		$multiValued = array('languages');
		foreach ($multiValued as $field) {
			$conf = picaConf::getFieldConf($field);
			$value = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
			$item->$field = $value;
		}

		$conf = picaConf::getFieldConf('subjects');
		$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
		foreach ($array as $term) {
			if (aadgenres::getType($term) == 'genre') {
				$item->genres[] = $term;
			}
			else {
				$item->subjects[] = $term;
			}
		}

		$conf = picaConf::getFieldConf('titleSupp');
		$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
		if (isset($array[0])) {
			$item->titleBib .= $array[0];
		}

		$item->titleBib = strtr($item->titleBib, array('@' => '', '..' => '.', '  ' => ' ', '=||' => '', '||' => ''));

		$conf = picaConf::getGroupConf('author1');
		$authorData = $this->getNestedValues($recNode, $xpn, $conf['field'], $conf['subfields']);
		$conf = picaConf::getGroupConf('author2');
		$authorData2 = $this->getNestedValues($recNode, $xpn, $conf['field'], $conf['subfields']);
		$authorData = array_merge($authorData, $authorData2);
		foreach ($authorData as $ad) {
			$author = $this->makePerson($ad);
			$author->role = 'author';
			$item->persons[] = $author;
		}

		$conf = picaConf::getGroupConf('contributor');
		$contrData = $this->getNestedValues($recNode, $xpn, $conf['field'], $conf['subfields']);
		foreach ($contrData as $cd) {
			$contributor = $this->makePerson($cd);
			$contributor->role = 'contributor';
			$item->persons[] = $contributor;
		}

		foreach ($item->persons as $person) {
			$person->persName = strtr($person->persName, array('<' => '', '>' => ''));
		}

		$conf = picaConf::getGroupConf('places');
		$placeData = $this->getNestedValues($recNode, $xpn, $conf['field'], $conf['subfields']);
		foreach ($placeData as $pd) {
			$place = new place;
			$place->placeName = $pd['placeName'];
			if (!empty($pd['gnd'])) {
				$place->gnd = $pd['gnd'];
			}
			if (substr($place->gnd, 0, 4) == 'gnd/') {
				$place->gnd = substr($place->gnd, 4);
			}
			$item->places[] = $place;
		}

		$conf = picaConf::getFieldConf('publishers');
		$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
		$item->publishers = $array;

		if ($item->places == array()) {
			$conf = picaConf::getFieldConf('places');
			$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
			foreach ($array as $placeName) {
				$place = new place;
				$place->placeName = $placeName;
				$item->places[] = $place;
			}
		}

		$item = $this->getManifestation($item, $recNode, $xpn);

		$conf = picaConf::getFieldConf('bbg');
		$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
		$item->mediaType = $this->getMediaType(array_shift($array));

		return($item);
	}

	protected function getValues($recNode, $xpn, $field, $subfield) {
		$result = array();
		$nodeList = $xpn->query('pica:datafield[@tag="'.$field.'"]/pica:subfield[@code="'.$subfield.'"]', $recNode);
		foreach ($nodeList as $node) {
			if ($node->textContent) {
				$result[] = $node->textContent;
			}
		}
		return($result);
	}

	protected function getNestedValues($recNode, $xpn, $field, $subfields) {
		$result = array();
		$nodeList = $xpn->query('pica:datafield[@tag="'.$field.'"]', $recNode);
		foreach ($nodeList as $node) {
			$subResult = array();
			foreach ($subfields as $name => $code) {
				$subList = $xpn->query('pica:subfield[@code="'.$code.'"]', $node);
				if (!empty($subList->item(0))) {
					$subResult[$name] = $subList->item(0)->textContent;
				}
				// Das Folgende ist nötig, weil die Subfeldcodes nicht einheiltlich erfasst sind
				if (strtoupper($code) != $code) {
				$subListUpper = $xpn->query('pica:subfield[@code="'.strtoupper($code).'"]', $node);
					if (!empty($subListUpper->item(0))) {
						$subResult[$name] = $subListUpper->item(0)->textContent;
					}				
				}
			}
			$result[] = $subResult;
		}
		return($result);
	}

	protected function makePerson($persData) {
		$person = new person;
		if (isset($persData['surname']) and isset($persData['forename'])) {
			$person->persName = $persData['surname'].', '.$persData['forename'];
		}
		elseif (isset($persData['personal']) and isset($persData['from'])) {
			$person->persName = $persData['personal'].' '.$persData['from'];
		}
		elseif (isset($persData['personal'])) {
			$person->persName = $persData['personal'];
		}		
		elseif (isset($persData['surname'])) {
			$person->persName = $persData['surname'];
		}
		if (isset($persData['gnd'])) {
			$person->gnd = $persData['gnd'];
			if (substr($persData['gnd'], 0, 4) == 'gnd/') {
				$person->gnd = substr($persData['gnd'], 4);
			}
		}
		return($person);
	}

	protected function getManifestation(item $item, $recNode, $xpn) {
		$vds = array('16', '17', '18');
		foreach ($vds as $vd) {
			$conf = picaConf::getFieldConf('vd'.$vd);
			$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
			$vdn = array_shift($array);
			if (substr($vdn, 0, 4) == 'VD'.$vd) {
				$item->manifestation['systemManifestation']  = 'VD'.$vd;
				$item->manifestation['idManifestation']  = substr($vdn, 5);
				return($item);				
			}
		}
		$conf = picaConf::getFieldConf('ppn');
		$array = $this->getValues($recNode, $xpn, $conf['field'], $conf['subfield']);
		$item->manifestation['systemManifestation']  = 'K10plus';
		$item->manifestation['idManifestation']  = array_shift($array);
		return($item);
	}

	protected function getMediaType($bbg) {
		$ind = substr($bbg, 0, 1);
		$conc = array('A' => 'Druck', 'H' => 'Handschrift', 'V' => 'Objekt', 'B' => 'Noten');
		if (isset($conc[$ind])) {
			return($conc[$ind]);
		}
		return(null);
	}

}

?>