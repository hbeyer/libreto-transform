<?php

function makeEntry($item, $base, $count) {
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
	if($persons == array()) {
		return;
	}
	$persArray = array();
	$gnds = array();
	$names = array();
	foreach($persons as $person) {
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
		$placeString = implode($placeArray, '/');
		$result .= $placeString.': ';
	}
	$publisher = $item->publisher;
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
	$provenanceAttribute = $originalItem['provenanceAttribute'];
	$digitalCopyOriginal = $originalItem['digitalCopyOriginal'];
	
	if($institutionOriginal and $shelfmarkOriginal and $targetOPAC == '') {
		$result = 'Originalexemplar: '.$institutionOriginal.', '.$shelfmarkOriginal;
	}
	elseif($institutionOriginal and $shelfmarkOriginal and $targetOPAC and $searchID == '') {
		$link = makeBeaconLink($shelfmarkOriginal, $targetOPAC);
		$result = 'Originalexemplar: <a href="'.$link.'">'.$institutionOriginal.', '.$shelfmarkOriginal.'</a>';
	}
	elseif($institutionOriginal and $shelfmarkOriginal and $targetOPAC and $searchID) {
		$link = makeBeaconLink($searchID, $targetOPAC);
		$result = 'Originalexemplar: <a href="'.$link.'">'.$institutionOriginal.', '.$shelfmarkOriginal.'</a>';
	}
	if($result and $provenanceAttribute) {
		$result .= '; Grund für Zuschreibung: '.$provenanceAttribute;
	}
	if($result and $digitalCopyOriginal) {
		$result .= '; Digitalisat: '.makeDigiLink($digitalCopyOriginal);
	}
	return($result);
}	

function makeSourceLink($item, $base) {
	$result = '';
	$link = '';
	if($base and $item->imageCat) {
		$link = ' <a href="'.$base.$item->imageCat.'" title="Titel im Altkatalog" target="_blank">S. '.$item->pageCat.', Nr. '.$item->numberCat.'</a>';
	}
    elseif ($item->pageCat and $item->numberCat) {
        $link = ' S. '.$item->pageCat.', Nr. '.$item->numberCat;
    }
	if($item->titleCat) {
		$result = '<span class="titleOriginal-single">Titel im Altkatalog: <i>'.$item->titleCat.'</i></span>'.$link;
	}
	return($result);
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
	$base = 'http://opac.lbs-braunschweig.gbv.de/DB=2/SET=31/TTL=1/CMD?ACT=SRCHA&TRM=sgb+';
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
    foreach(reference::PATTERNSYSTEMS as $key => $pattern) {
        preg_match($pattern, $text, $match);
        if (isset($match[1])) {
            $base = reference::BASES[$key];
            $link = strtr($base, array('{ID}' => $match[1]));
            $replacement = '<a href="'.$link.'" target="_blank">'.$match[0].'</a>';
            $text = preg_replace($pattern, $replacement, $text);
        }
    }
    return($text);
}
	
?>	
