<?php

class PicaConverter {
	
	public static function makeItem($recNode, $xp, $bib, $regexSig) {
		$item = new Item;
		$singleValued = array('year', 'titleBib', 'format', 'digitalCopy');
		foreach ($singleValued as $field) {
			$array = PicaConverter::getValues($recNode, $xp, $field);
			$value = array_shift($array);
			$item->$field = $value;
		}
		$multiValued = array('languages');
		foreach ($multiValued as $field) {
			$value = PicaConverter::getValues($recNode, $xp, $field);
			$item->$field = $value;
		}
		$array = PicaConverter::getValues($recNode, $xp, 'subjects');
		foreach ($array as $term) {
			if (AADGenres::::getType($term) == 'genre') {
				$item->genres[] = $term;
			}
			else {
				$item->subjects[] = $term;
			}
		}
		$array = PicaConverter::getValues($recNode, $xp, 'titleSupp');
		if (isset($array[0])) {
			$item->titleBib .= '. '.$array[0];
		}

		$array = PicaConverter::getValues($recNode, $xp, 'bbg');
		$bbg = array_shift($array);
		$item->mediaType = PicaConverter::getMediaType($bbg);
		if (substr($bbg, 1, 1) == 'f') {
			$serData = PicaConverter::getNestedValues($recNode, $xp, 'seriesf');
			$serData = array_shift($serData);
			if (isset($serData['title'])) {
				$titleSer = $serData['title'];
				if (isset($serData['vol'])) {
					$titleSer .= ' '.$serData['vol'];
				}
				if ($item->titleBib) {
					$item->titleBib .= '. '.$titleSer;
				}
				else {
					$item->titleBib = $titleSer;
				}
			}
		}
		elseif (substr($bbg, 1, 1) == 'F') {
			$serData = PicaConverter::getNestedValues($recNode, $xp, 'seriesf');
			$serData = array_shift($serData);
			if ($item->titleBib and isset($serData['title']) and isset($serData['vol'])) {
				$item->titleBIb = $serData['title'].' '.$serData['vol'].' '.$item->titleBib;
			}
		}

		$item->titleBib = strtr($item->titleBib, array('@' => '', '..' => '.', '  ' => ' ', '=||' => '', '||' => ''));

		$authorData = PicaConverter::getNestedValues($recNode, $xp, 'author1');
		$authorData2 = PicaConverter::getNestedValues($recNode, $xp, 'author2');
		$authorData = array_merge($authorData, $authorData2);
		foreach ($authorData as $ad) {
			$author = PicaConverter::makePerson($ad);
			$author->role = 'author';
			$item->persons[] = $author;
		}

		$contrData = PicaConverter::getNestedValues($recNode, $xp, 'contributors');
		foreach ($contrData as $cd) {
			$contributor = PicaConverter::makePerson($cd);
			$contributor->role = 'contributor';
			$item->persons[] = $contributor;
		}

		foreach ($item->persons as $person) {
			$person->persName = strtr($person->persName, array('<' => '', '>' => ''));
		}

		$placeData = PicaConverter::getNestedValues($recNode, $xp, 'places');
		foreach ($placeData as $pd) {
			$place = new Place;
			$place->placeName = $pd['placeName'];
			if (!empty($pd['gnd'])) {
				$place->gnd = $pd['gnd'];
			}
			if (substr($place->gnd, 0, 4) == 'gnd/') {
				$place->gnd = substr($place->gnd, 4);
			}
			$item->places[] = $place;
		}

		if ($item->places == array()) {
			$array = PicaConverter::getValues($recNode, $xp, 'placesVorl');
			foreach ($array as $placeName) {
				$place = new Place;
				$place->placeName = $placeName;
				$item->places[] = $place;
			}
		}

		$item->publishers = PicaConverter::getValues($recNode, $xp, 'publishers');
		if (empty($item->publishers)) {
			$item->publishers = PicaConverter::getValues($recNode, $xp, 'publishersVorl');
		}

		$item = PicaConverter::insertManifestation($item, $recNode, $xp);

		if ($bib) {
			$item = PicaConverter::insertShelfmark($item, $recNode, $xp, $bib, $regexSig);
		}

		return($item);
	}

	protected static function getValues($recNode, $xp, $field) {
		$result = array();
		$conf = PicaConverter::getFieldConf($field);
		if ($conf == null) {
			return(null);
		}
		$nodeList = $xp->query('pica:datafield[@tag="'.$conf['field'].'"]/pica:subfield[@code="'.$conf['subfield'].'"]', $recNode);
		foreach ($nodeList as $node) {
			if ($node->textContent) {
				$result[] = $node->textContent;
			}
		}
		return($result);
	}

	protected static function getFieldConf($field) {
		$conf = array(
			'year' => array('field' => '011@', 'subfield' => 'a'),
			'bbg' => array('field' => '002@', 'subfield' => '0'),
			'ppn' => array('field' => '003@', 'subfield' => '0'),
			'vdn' => array('field' => '206X', 'subfield' => '0'),
			'vd16' => array('field' => '007S', 'subfield' => '0'),
			'vd17' => array('field' => '006W', 'subfield' => '0'),
			'vd18' => array('field' => '006M', 'subfield' => '0'),
			'languages' => array('field' => '010@', 'subfield' => 'a'),
			'titleBib' => array('field' => '021A', 'subfield' => 'a'),
			'titleSupp' => array('field' => '021A', 'subfield' => 'd'),
			'format' => array('field' => '034I', 'subfield' => 'a'),
			'subjects' => array('field' => '044S', 'subfield' => 'a'),
			'publishers' => array('field' => '033J', 'subfield' => '8'),
			'publishersVorl' => array('field' => '033A', 'subfield' => 'n'),
			'placesVorl' => array('field' => '033A', 'subfield' => 'p'),
			'digitalCopy' => array('field' => '017D', 'subfield' => 'u')
		);
		if (empty($conf[$field])) {
			return(null);
		}
		return($conf[$field]);
	}

	protected static function getNestedValues($recNode, $xp, $field) {
		$result = array();
		$conf = PicaConverter::getGroupConf($field);
		if ($conf == null) {
			return(null);
		}
		$nodeList = $xp->query('pica:datafield[@tag="'.$conf['field'].'"]', $recNode);
		foreach ($nodeList as $node) {
			$subResult = array();
			foreach ($conf['subfields'] as $name => $code) {
				$subList = $xp->query('pica:subfield[@code="'.$code.'"]', $node);
				if (!empty($subList->item(0))) {
					$subResult[$name] = $subList->item(0)->textContent;
				}
				// Das Folgende ist nötig, weil die Subfeldcodes nicht einheitlich erfasst sind
				if (strtoupper($code) != $code) {
				$subListUpper = $xp->query('pica:subfield[@code="'.strtoupper($code).'"]', $node);
					if (!empty($subListUpper->item(0))) {
						$subResult[$name] = $subListUpper->item(0)->textContent;
					}				
				}
			}
			$result[] = $subResult;
		}
		return($result);
	}

	protected static function getGroupConf($field) {
		$conf = array(
			'author1' => array('field' => '028A', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'author2' => array('field' => '028B', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'contributors' => array('field' => '028C', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'places' => array('field' => '033D', 'subfields' => array('placeName' => 'p', 'gnd' => '7')),
			'shelfmarks' => array('field' => '209A', 'subfields' => array('institution' => 'f', 'shelfmark' => 'a')),
			'seriesf' => array('field' => '036C', 'subfields' => array('title' => 'a', 'vol' => 'l')),
			'seriesF' => array('field' => '036D', 'subfields' => array('title' => '8', 'vol' => 'l'))
		);
		if (empty($conf[$field])) {
			return(null);
		}
		return($conf[$field]);
	}

	protected static function makePerson($persData) {
		$person = new Person;
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

	protected static function insertManifestation(Item $item, $recNode, $xp) {
		$vds = array('16', '17', '18');
		foreach ($vds as $vd) {
			$array = PicaConverter::getValues($recNode, $xp, 'vd'.$vd);
			$vdn = array_shift($array);
			if (substr($vdn, 0, 4) == 'VD'.$vd) {
				$item->manifestation['systemManifestation']  = 'VD'.$vd;
				$item->manifestation['idManifestation']  = substr($vdn, 5);
				return($item);				
			}
		}
		$array = PicaConverter::getValues($recNode, $xp, 'ppn');
		$item->manifestation['systemManifestation']  = 'K10plus';
		$item->manifestation['idManifestation']  = array_shift($array);
		return($item);
	}

	protected static function getMediaType($bbg) {
		$ind = substr($bbg, 0, 1);
		$conc = array('A' => 'Druck', 'H' => 'Handschrift', 'V' => 'Objekt', 'B' => 'Noten');
		if (isset($conc[$ind])) {
			return($conc[$ind]);
		}
		return(null);
	}

	protected static function insertShelfmark($item, $recNode, $xp, $bib, $regexSig) {
		$smData = PicaConverter::getNestedValues($recNode, $xp, 'shelfmarks');
		$ppnAr = PicaConverter::getValues($recNode, $xp, 'ppn');
		$ppn = array_shift($ppnAr);
		if ($regexSig == null) {
			foreach ($smData as $smRow) {
				if (isset($smRow['institution']) and isset($smRow['shelfmark'])) {
					if (trim($smRow['institution']) == $bib) {
						$item->originalItem['institutionOriginal'] = $bib;
						$item->originalItem['shelfmarkOriginal'] = $smRow['shelfmark'];
						$item->originalItem['targetOPAC'] = 'https://kxp.k10plus.de/DB=2.1/PPNSET?PPN={ID}';
						$item->originalItem['searchID'] = $ppn;
						return($item);
					}
				}
			}
		}
		else {
			foreach ($smData as $smRow) {
				if (empty($smRow['shelfmark'])) {
					continue;
				} 
				if (preg_match('~'.$regexSig.'~', $smRow['shelfmark'])) {
					$item->originalItem['institutionOriginal'] = $bib;
					$item->originalItem['shelfmarkOriginal'] = $smRow['shelfmark'];
					$item->originalItem['targetOPAC'] = 'https://kxp.k10plus.de/DB=2.1/PPNSET?PPN={ID}';
					$item->originalItem['searchID'] = $ppn;
					return($item);	
				}
			}
		}
		return($item);
	}	


}

?>