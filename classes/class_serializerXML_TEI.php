<?php

require_once(reconstruction::INCLUDEPATH.'makeSection.php');
require_once(reconstruction::INCLUDEPATH.'makeIndex.php');

class serializerXML_TEI extends serializerXML {

    protected $dom;

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, $this->fileName.'-tei', 'xml');
        $this->makeDOM();
        $this->dom->load('templateTEI.xml');
        $this->insertMetadata();
        $this->insertTranscription();
        $this->insertBibliography();
        $this->insertPageBreaks();
        $this->output = $this->dom->saveXML();
        $this->save();
    }

    protected function insertMetadata() {
        // Insert title of the reconstructed library
        $titleNodeList = $this->dom->getElementsByTagName('title');
        $title = $titleNodeList->item(0);
        $headingText = $this->catalogue->heading;
        if($this->catalogue->year) {
            $headingText .= ' ('.$this->catalogue->year.')';
        }
        $heading = $this->dom->createTextNode($headingText);
        $title->appendChild($heading);
        
        // Insert date of reconstruction
        $dateNodeList = $this->dom->getElementsByTagName('date');
        $date = $dateNodeList->item(0);
        $year = $this->dom->createTextNode(date('Y'));
        $date->appendChild($year);
        $date->setAttribute('when', date('Y-m-d'));
        
        // Insert source information from catalogue object
        $listWitList = $this->dom->getElementsByTagName('listWit');
        $listWit = $listWitList->item(0);
        $witness = $this->dom->createElement('witness');
        $witness->setAttribute('xml:id', 'witness_0');
        $textWitness = $this->dom->createTextNode($this->catalogue->institution.', '.$this->catalogue->shelfmark);
        $witness->appendChild($textWitness);
        $listWit->appendChild($witness);
    }

    private function insertTranscription() {
        
        $bodyNodeList = $this->dom->getElementsByTagName('body');
        $body = $bodyNodeList->item(0);
        $lastPageCat = '';
        
        $index = makeIndex($this->data, 'histSubject');
        $structuredData = array();
        foreach($index as $entry) {
            $section = new section();
            $section->label = $entry->label;
            foreach($entry->content as $idItem) {
                $section->content[] = $this->data[$idItem];
            }
            $section = joinVolumes($section);
            $structuredData[] = $section;
        }
        
        foreach($structuredData as $section) {
            $transcription = $this->dom->createElement('div');
            $transcription->setAttribute('type', 'transcription');
            $textHead = $this->dom->createTextNode($section->label);
            $head = $this->dom->createElement('head');
            $head->appendChild($textHead);  
            $transcription->appendChild($head);
            foreach($section->content as $object) {
                if(get_class($object) == 'volume') {
                    $this->insertVolumeTrans($transcription, $object);
                }           
                elseif(get_class($object) == 'item') {
                    $div = $this->dom->createElement('div');
                    $div->setAttribute('type', 'volume');
                    $this->insertItemTrans($object, $div);
                    $transcription->appendChild($div);
                }
            }
            $body->appendChild($transcription);
        }
        
    }

    private function insertVolumeTrans($div, $volume) {
        $divSub = $this->dom->createElement('div');
        $divSub->setAttribute('type', 'volume');
        foreach($volume->content as $item) {
            $this->insertItemTrans($item, $divSub);
        }
        $div->appendChild($divSub);
    }

    private function insertItemTrans($item, $target) {
        // Insert a paragraph for each catalogue entry
        $p = $this->dom->createElement('p');
        if($item->numberCat) {
            $p->setAttribute('n', $item->numberCat);
        }
        $p->setAttribute('xml:id', $item->id);
        if($item->titleCat) {
            //Avoid &amp;amp;
            $titleCatText = html_entity_decode($item->titleCat);
            $titleCat = $this->dom->createTextNode($titleCatText);
            $p->appendChild($titleCat);
        } 
        // Add a note to the paragraph
        if($item->comment) {
            //Avoid &amp;amp;
            $text = html_entity_decode($item->comment);
            $commentText = $this->dom->createTextNode($text);
            $comment = $this->dom->createElement('note');
            $comment->appendChild($commentText);
            $p->appendChild($comment);
        }
        $target->appendChild($p);
    }

    private function insertBibliography() {

        $bodyNodeList = $this->dom->getElementsByTagName('body');
        $body = $bodyNodeList->item(0);
        $divBibliography = $this->dom->createElement('div');
        $divBibliography->setAttribute('type', 'bibliography');
        $listBibl = $this->dom->createElement('listBibl');

        $count = 0; 

        foreach($this->data as $item) {
            //Check if there is bibliographic data on work or manifestation level
            if($item->titleBib or $item->work['titleWork']) {
                $bibl = $this->dom->createElement('bibl');
                $bibl->setAttribute('xml:id', $item->id.'-reference');
                $bibl->setAttribute('corresp', $item->id);
                $bibl = $this->insertBibliographicData($bibl, $item);
                $listBibl->appendChild($bibl);
            }
            $count++;
        }
        
        $divBibliography->appendChild($listBibl);
        $body->appendChild($divBibliography);
        
    }

    private function insertBibliographicData($bibl, $item) {
        if($item->titleBib) {
            //Avoid &amp;amp;
            $titleBibText = $this->dom->createTextNode(html_entity_decode($item->titleBib));
        }
        elseif($item->work['titleWork']) {
            $titleBibText = $this->dom->createTextNode(html_entity_decode($item->work['titleWork']));
        }
        
        $titleBib = $this->dom->createElement('title');
        $titleBib->appendChild($titleBibText);
        $bibl->appendChild($titleBib);  

        foreach($item->persons as $person) {

            $tagName = translateRoleTEI($person->role);
            if($tagName != 'author' and $tagName != 'editor') {
                $tagName = 'author';        
            }

            $persName = $this->dom->createTextNode($person->persName);
            $personElement = $this->dom->createElement($tagName);

            if($person->gnd) {
                $rs = $this->dom->createElement('rs');
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
            $extent = $this->dom->createElement('extent');
            $extentText = $this->dom->createTextNode($item->volumes.' Bde.');
            $extent->appendChild($extentText);
            $bibl->appendChild($extent);
        }
        foreach($item->places as $place) {
            $placeName = $this->dom->createTextNode($place->placeName);
            $pubPlace = $this->dom->createElement('pubPlace');
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
                $rs = $this->dom->createElement('rs');
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
        foreach($item->publishers as $publisher) {
            $publisherText = $this->dom->createTextNode(html_entity_decode($publisher));
            $publisherElement = $this->dom->createElement('publisher');
            $publisherElement->appendChild($publisherText);
            $bibl->appendChild($publisherElement);  
        }
        if($item->year) {
            $yearText = $this->dom->createTextNode($item->year);
            $year = $this->dom->createElement('date');
            $year->appendChild($yearText);
            $when = normalizeYear($item->year);
            if(preg_match('~[12][0-9]{3}~', $when) == TRUE) {
                $year->setAttribute('when', $when);
            }
            $bibl->appendChild($year);
        }
        if($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
            $idnoText = $this->dom->createTextNode($item->manifestation['idManifestation']);  
            $idno = $this->dom->createElement('idno');
            $idno->appendChild($idnoText);
            $type = translateIdNo($item->manifestation['systemManifestation']);
            $idno->setAttribute('type', $type);
            $bibl->appendChild($idno);
        }
        if($item->work['systemWork'] and $item->work['idWork']) {
            $idnoWorkText = $this->dom->createTextNode($item->work['idWork']);
            $idnoWork = $this->dom->createElement('idno');
            $idnoWork->appendChild($idnoWorkText);
            $typeWork = translateIdNo($item->work['systemWork']);
            $idnoWork->setAttribute('type', $typeWork);
            $bibl->appendChild($idnoWork);
        }
        
        return($bibl);
    }

    private function insertPageBreaks() {
        
        $firstItems = array();
        $urls = array();
        $lastPageCat = '';
        foreach($this->data as $item) {
            $pageCat = $item->pageCat;
            if($pageCat != $lastPageCat) {
                $firstItems[$item->id] = $pageCat;
                if($this->catalogue->base) {
                    $urls[$item->id] = $this->catalogue->base.$item->imageCat;
                }
            }
            $lastPageCat = $pageCat;
        }
        
        foreach($firstItems as $id => $pageNo) {
            $pb = $this->dom->createElement('pb');
            $pb->setAttribute('n', $pageNo);
            if($this->catalogue->base and isset($urls[$id]))  {
                $pb->setAttribute('facs', $urls[$id]);
            }
            
            $xp = new DOMXPath($this->dom);
            
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

}   

?>
