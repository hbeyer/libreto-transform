<?php

function makeEntry($item, $base) {
	$persons = makePersons($item->persons);
	$published = makePublicationString($item);
	$originalLink = makeOriginalLink($item->originalItem);
	$sourceLink = makeSourceLink($item, $base);
    $digiLink = null;
    if ($item->digitalCopy) {
	    $digiLink = makeDigiLink($item->digitalCopy);
    }
	$copiesHAB = makeCopiesHAB($item->copiesHAB);
	include 'templates/entry.phtml';
}

function makePersons($persons) {
	$excludeRoles = array('borrower');
	$persArray = array();
	$gnds = array();
	$names = array();
	foreach($persons as $person) {
		if (in_array($person->role, $excludeRoles)) {
			continue;
		}
		if($person->gnd and in_array($person->gnd, $gnds) == FALSE) {
			$persArray[] = $person->persName;
			$gnds[] = $person->gnd;
			$names[] = $person->persName;
		}
		elseif (in_array($person->persName, $names) == FALSE) {
			$persArray[] = $person->persName;
			$names[] = $person->persName;
		}
	}
	if ($persArray == array()) {
		return(null);
	}
	$result = implode('</span>/<span class="authorName">', $persArray);
	$result = '<span class="authorName">'.$result.'</span>';
	return($result);
}

function makePublicationString($item) {
	$result = '';
	$placeString = '';
	if (isset($item->places[0])) {
		$placeArray = array();
		foreach ($item->places as $place) {
			$placeArray[] = $place->placeName;
		}
		$placeString = implode('/', $placeArray);
		$result .= $placeString.': ';
	}
	$publisher = implode('/', $item->publishers);
	$date = $item->year;
	$sep1 = '';
	$sep2 = '';
	if ($placeString and $publisher) {
		$sep1 = ': ';
	}
	if ($publisher and $date) {
		$sep2 = ', ';
	}
	elseif ($placeString and $date) {
		$sep1 = ' ';
	}
	$result = $placeString.$sep1.$publisher.$sep2.$date;
	return($result);
}

function makeOriginalLink($originalItem) {
	$result = '';
	$institutionOriginal = $originalItem['institutionOriginal'];
	$shelfmarkOriginal = $originalItem['shelfmarkOriginal'];
	$targetOPAC = $originalItem['targetOPAC'];
	$searchID = $originalItem['searchID'];
	$OPACLink = $originalItem['OPACLink'];
	$provenanceAttribute = $originalItem['provenanceAttribute'];
	$digitalCopyOriginal = $originalItem['digitalCopyOriginal'];

	if($institutionOriginal and $shelfmarkOriginal and $targetOPAC == '') {
		$result = 'Originalexemplar: '.$institutionOriginal.', '.$shelfmarkOriginal;
	}
	elseif($institutionOriginal and $shelfmarkOriginal and $OPACLink) {
		$result = 'Originalexemplar: <a href="'.$OPACLink.'" target="_blank">'.$institutionOriginal.', '.$shelfmarkOriginal.'</a>';
	}
	elseif($institutionOriginal and $shelfmarkOriginal and $targetOPAC and $searchID == '') {
		$link = makeBeaconLink($shelfmarkOriginal, $targetOPAC);
		$result = 'Originalexemplar: <a href="'.$link.'" target="_blank">'.$institutionOriginal.', '.$shelfmarkOriginal.'</a>';
	}
	elseif($institutionOriginal and $shelfmarkOriginal and $targetOPAC and $searchID) {
		$link = makeBeaconLink($searchID, $targetOPAC);
		$result = 'Originalexemplar: <a href="'.$link.'" target="_blank">'.$institutionOriginal.', '.$shelfmarkOriginal.'</a>';
	}
	if($result and $provenanceAttribute) {
		$result .= '; Grund für Zuschreibung: '.$provenanceAttribute;
	}
	if($result and $digitalCopyOriginal) {
		$result .= '; Digitalisat: '.makeDigiLink($digitalCopyOriginal);
	}
	return($result);
}

function makeSourceLink($item, $base = '') {
    $citationArray = array();
    if ($item->pageCat) {
        $citationArray[] = 'S. '.$item->pageCat;
    }
    if ($item->numberCat) {
        $citationArray[] = 'N. '.$item->numberCat;
    }
    $citation = implode(', ', $citationArray);
    if ($citation and $base and $item->imageCat) {
    	$link = makeImageLink($base, $item->imageCat);
        $citation = '<a href="'.$link.'" title="Titel im Altkatalog" target="_blank">'.$citation.'</a>';
    }
    if ($item->titleCat) {
        $citation = '<span class="titleOriginal-single">Titel im Altkatalog: <i>'.$item->titleCat.'</i></span> '.$citation;
    }
    return($citation);
}


function makeImageLink($base, $imageNo) {
	if (strpos($base, '{No}') != false) {
		return(strtr($base, array('{No}' => strval($imageNo))));
	}
	if ($base != '') {
		return($base.$imageNo);
	}
	return('');
}

function makeDigiLink($digi) {
	$title = 'Digitalisat';
	$result = '';
	$resolver = '';
	if(substr($digi, 0, 4) == 'KEYP') {
		$title = 'Schl&uuml;sselseiten';
		$digi = substr($digi, 4);
	}
	$urn = strstr($digi, 'urn:');
	if($urn != FALSE) {
		$digi = $urn;
		$resolver = 'http://nbn-resolving.de/';
	}
	$result = '<span class="heading_info">'.$title.': </span><a href="'.$resolver.$digi.'" target="_blank">'.$digi.'</a>';
	return($result);
}

function makeCopiesHAB($copies) {
    if (!$copies) {
        return(NULL);
    }
	$base = 'http://opac.lbs-braunschweig.gbv.de/DB=2/CMD?ACT=SRCHA&TRM=sgb+';
	$links = array();
	$translation = array('(' => '', ')' => '');
	foreach($copies as $copy) {
		$copyOPAC = strtr($copy, $translation);
		$links[] = '<a href="'.$base.urlencode($copyOPAC).'" target="_blank">'.$copy.'</a>';
	}
	$result = implode('; ', $links);
    return('Exemplare der HAB: '.$result);
}

function makeComment($text) {
    //Links für Referenzen auf externe Nachweissysteme in Kommentaren einfügen
    foreach(reference::PATTERNSYSTEMS as $key => $pattern) {
        preg_match($pattern, $text, $match);
        if (isset($match[1])) {
            $base = reference::BASES[$key];
            $link = strtr($base, array('{ID}' => $match[1]));
            $replacement = '<a href="'.$link.'" target="_blank">'.$match[0].'</a>';
            $text = preg_replace($pattern, $replacement, $text);
        }
    }
    //Links für Suchanfragen in Kommentaren einfügen
    $text = insertQueries($text);
    return($text);
}

function insertQueries($string) {
    $searchPatterns = array(
        'VD16' => 'https://gateway-bayern.de/TouchPoint_touchpoint/start.do?SearchProfile=Altbestand&Query=-1%3D%22{REQUEST}%22',
        'VD17' => 'https://gso.gbv.de/DB=1.28/CMD?ACT=SRCHA&TRM={REQUEST}',
        'VD18' => 'https://gso.gbv.de/DB=1.65/CMD?ACT=SRCHA&TRM={REQUEST}',
        'GBV' => 'https://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&TRM={REQUEST}',
        'STCN' => 'http://picarta.nl/DB=3.11/CMD?ACT=SRCHA&IKT=1016&TRM={REQUEST}',
    );
    preg_match('~#([^$]{2,5})\$([^#]{3,100})#~', $string, $match2);
    if (!empty($match2[1]) and !empty($match2[2])) {
        if (isset($searchPatterns[$match2[1]])) {
            $trans = array('{REQUEST}' => $match2[2]);
            $link = strtr($searchPatterns[$match2[1]], $trans);
            $replacement = '<a href="'.$link.'" target="_blank" title="Suchen in '.$match2[1].'">Suchen in '.$match2[1].'</a>';
            $string = preg_replace('~#[^$]{2,5}\$[^#]{3,100}#~', $replacement, $string);
            return($string);
        }
    }
    return($string);
}

?>
