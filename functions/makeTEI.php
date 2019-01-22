<?php

function makeTEI($data, $folder, catalogue $catalogue) {
	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->formatOutput = true;
	$dom->load('templateTEI.xml');
	insertMetadata($dom, $catalogue);
	insertTranscription($dom, $data, $catalogue);
	insertPageBreaks($dom, $data, $catalogue->base);
	insertBibliography($dom, $data, $catalogue);
	$xml = $dom->saveXML();
	$handle = fopen($folder.'/'.$catalogue->fileName.'-tei.xml', 'w');
	fwrite($handle, $xml, 3000000);
}

function insertMetadata($dom, $catalogue) {
	
	// Insert title of the reconstructed library
	$titleNodeList = $dom->getElementsByTagName('title');
	$title = $titleNodeList->item(0);
	$headingText = $catalogue->heading;
	if($catalogue->year) {
		$headingText .= ' ('.$catalogue->year.')';
	}
	$heading = $dom->createTextNode($headingText);
	$title->appendChild($heading);
	
	// Insert date of reconstruction
	$dateNodeList = $dom->getElementsByTagName('date');
	$date = $dateNodeList->item(0);
	$year = $dom->createTextNode(date('Y'));
	$date->appendChild($year);
	$date->setAttribute('when', date('Y-m-d'));
	
	// Insert source information from catalogue object
	$listWitList = $dom->getElementsByTagName('listWit');
	$listWit = $listWitList->item(0);
	$witness = $dom->createElement('witness');
	$witness->setAttribute('xml:id', 'witness_0');
	$textWitness = $dom->createTextNode($catalogue->institution.', '.$catalogue->shelfmark);
	$witness->appendChild($textWitness);
	$listWit->appendChild($witness);
}

function insertTranscription($dom, $data, catalogue $catalogue) {
	
	$bodyNodeList = $dom->getElementsByTagName('body');
	$body = $bodyNodeList->item(0);
	$lastPageCat = '';
	
	$index = makeIndex($data, 'histSubject');
	$structuredData = array();
	foreach($index as $entry) {
		$section = new section();
		$section->label = $entry->label;
		foreach($entry->content as $idItem) {
			$section->content[] = $data[$idItem];
		}
		$section = joinVolumes($section);
		$structuredData[] = $section;
	}
	
	foreach($structuredData as $section) {
		$transcription = $dom->createElement('div');
		$transcription->setAttribute('type', 'transcription');
		$textHead = $dom->createTextNode($section->label);
		$head = $dom->createElement('head');
		$head->appendChild($textHead);	
		$transcription->appendChild($head);
		foreach($section->content as $object) {
			if(get_class($object) == 'volume') {
				insertVolumeTrans($dom, $transcription, $object);
			}			
			elseif(get_class($object) == 'item') {
				$div = $dom->createElement('div');
				$div->setAttribute('type', 'volume');
				insertItemTrans($dom, $object, $div);
				$transcription->appendChild($div);
			}
		}
		$body->appendChild($transcription);
	}
	
}

function insertVolumeTrans($dom, $div, $volume) {
	$divSub = $dom->createElement('div');
	$divSub->setAttribute('type', 'volume');
	foreach($volume->content as $item) {
		insertItemTrans($dom, $item, $divSub);
	}
	$div->appendChild($divSub);
}

function insertItemTrans($dom, $item, $target) {
	// Insert a paragraph for each catalogue entry
	$p = $dom->createElement('p');
	if($item->numberCat) {
		$p->setAttribute('n', $item->numberCat);
	}
	$p->setAttribute('xml:id', $item->id);
	if($item->titleCat) {
		//Avoid &amp;amp;
		$titleCatText = html_entity_decode($item->titleCat);
		$titleCat = $dom->createTextNode($titleCatText);
		$p->appendChild($titleCat);
	} 
	// Add a note to the paragraph
	if($item->comment) {
		//Avoid &amp;amp;
		$text = html_entity_decode($item->comment);
		$commentText = $dom->createTextNode($text);
		$comment = $dom->createElement('note');
		$comment->appendChild($commentText);
		$p->appendChild($comment);
	}
	$target->appendChild($p);
}

function insertBibliography($dom, $data, $catalogue) {

	$bodyNodeList = $dom->getElementsByTagName('body');
	$body = $bodyNodeList->item(0);
	$divBibliography = $dom->createElement('div');
	$divBibliography->setAttribute('type', 'bibliography');
	$listBibl = $dom->createElement('listBibl');

	$count = 0;	

	foreach($data as $item) {
		//Check if there is bibliographic data on work or manifestation level
		if($item->titleBib or $item->work['titleWork']) {
			$bibl = $dom->createElement('bibl');
			$bibl->setAttribute('xml:id', $item->id.'-reference');
			$bibl->setAttribute('corresp', $item->id);
			$bibl = insertBibliographicData($bibl, $dom, $item);
			$listBibl->appendChild($bibl);
		}
		$count++;
	}
	
	$divBibliography->appendChild($listBibl);
	$body->appendChild($divBibliography);
	
}

function insertBibliographicData($bibl, $dom, $item) {
	if($item->titleBib) {
		//Avoid &amp;amp;
		$titleBibText = $dom->createTextNode(html_entity_decode($item->titleBib));
	}
	elseif($item->work['titleWork']) {
		$titleBibText = $dom->createTextNode(html_entity_decode($item->work['titleWork']));
	}
	
	$titleBib = $dom->createElement('title');
	$titleBib->appendChild($titleBibText);
	$bibl->appendChild($titleBib);	

	foreach($item->persons as $person) {

		$tagName = translateRoleTEI($person->role);
		if($tagName != 'author' and $tagName != 'editor') {
			$tagName = 'author';		
		}

		$persName = $dom->createTextNode($person->persName);
		$personElement = $dom->createElement($tagName);

		if($person->gnd) {
			$rs = $dom->createElement('rs');
			$rs->setAttribute('type', 'person');
			$rs->setAttribute('key', 'gnd_'.$person->gnd);
			$rs->appendChild($persName);
			$personElement->appendChild($rs);
		}
		else {
			$personElement->appendChild($persName);
		}

		$bibl->appendChild($personElement);
				
	}
	if($item->volumes > 1) {
		$extent = $dom->createElement('extent');
		$extentText = $dom->createTextNode($item->volumes.' Bde.');
		$extent->appendChild($extentText);
		$bibl->appendChild($extent);
	}
	foreach($item->places as $place) {
		$placeName = $dom->createTextNode($place->placeName);
		$pubPlace = $dom->createElement('pubPlace');
		$key = '';		
		if($place->geoNames) {
			$key = 'geoNames_'.$place->geoNames;		
		}
		elseif($place->getty) {
			$key = 'getty_'.$place->getty;
		}
		elseif($place->gnd) {
			$key = 'gnd_'.$place->gnd;
		}
		if($key != '') {
			$rs = $dom->createElement('rs');
			$rs->setAttribute('type', 'place');
			$rs->setAttribute('key', $key);
			$rs->appendChild($placeName);
			$pubPlace->appendChild($rs);
		}
		else {
			$pubPlace->appendChild($placeName);			
		}
		$bibl->appendChild($pubPlace);
	}
	if($item->publisher) {
		$publisherText = $dom->createTextNode(html_entity_decode($item->publisher));
		$publisher = $dom->createElement('publisher');
		$publisher->appendChild($publisherText);
		$bibl->appendChild($publisher);	
	}
	if($item->year) {
		$yearText = $dom->createTextNode($item->year);
		$year = $dom->createElement('date');
		$year->appendChild($yearText);
		$when = normalizeYear($item->year);
		if(preg_match('~[12][0-9]{3}~', $when) == TRUE) {
			$year->setAttribute('when', $when);
		}
		$bibl->appendChild($year);
	}
	if($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
		$idnoText = $dom->createTextNode($item->manifestation['idManifestation']);	
		$idno = $dom->createElement('idno');
		$idno->appendChild($idnoText);
		$type = translateIdNo($item->manifestation['systemManifestation']);
		$idno->setAttribute('type', $type);
		$bibl->appendChild($idno);
	}
	if($item->work['systemWork'] and $item->work['idWork']) {
		$idnoWorkText = $dom->createTextNode($item->work['idWork']);
		$idnoWork = $dom->createElement('idno');
		$idnoWork->appendChild($idnoWorkText);
		$typeWork = translateIdNo($item->work['systemWork']);
		$idnoWork->setAttribute('type', $typeWork);
		$bibl->appendChild($idnoWork);
	}
	
	return($bibl);
}

function insertPageBreaks($dom, $data, $base) {
	
	$firstItems = array();
    $urls = array();
	$lastPageCat = '';
	foreach($data as $item) {
		$pageCat = $item->pageCat;
		if($pageCat != $lastPageCat) {
			$firstItems[$item->id] = $pageCat;
            if($base) {
                $urls[$item->id] = $base.$item->imageCat;
            }
		}
		$lastPageCat = $pageCat;
	}
	
	foreach($firstItems as $id => $pageNo) {
		$pb = $dom->createElement('pb');
		$pb->setAttribute('n', $pageNo);
        if($base and isset($urls[$id]))  {
            $pb->setAttribute('facs', $urls[$id]);
        }
		
		$xp = new DOMXPath($dom);
		
		$expression = '//p[@xml:id="'.$id.'"]/parent::div';
 		$divNodes = $xp->evaluate($expression);
		$div = $divNodes->item(0);
		
		$expressionParent = '//p[@xml:id="'.$id.'"]/parent::*/parent::*';
		$parentNodes = $xp->evaluate($expressionParent);
		$parent = $parentNodes->item(0);
		
		$expressionPreceding = '//p[@xml:id="'.$id.'"]/parent::*/preceding-sibling::*';
		$precedingNodes = $xp->evaluate($expressionPreceding);
		$preceding = $precedingNodes->item(0);
		
		if($preceding->tagName == 'head') {
			$parent->insertBefore($pb, $preceding);
		}
		else {
			$parent->insertBefore($pb, $div);
		}	
	}
	
}

?>
