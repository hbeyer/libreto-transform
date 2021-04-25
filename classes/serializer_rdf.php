<?php

class  serializer_rdf extends serializer {

    protected $graph;

    public function serialize() {
        $this->makeGraph();
        $this->path = reconstruction::getPath($this->fileName, $this->fileName, 'rdf');
        $this->generateOutputXML();
        $this->save();
        $this->path = reconstruction::getPath($this->fileName, $this->fileName, 'ttl');
        $this->generateOutputTurtle();
        $this->save();
    }

    protected function makeGraph() {
        $this->aadgenres = aadgenres::getGenres();
		$this->graph = new EasyRdf_Graph();
        EasyRdf_Namespace::set('dc', 'http://purl.org/dc/terms/');
        EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/spec/#term_');
        EasyRdf_Namespace::set('gn', 'http://www.geonames.org/ontology#');
        EasyRdf_Namespace::set('libreto', 'http://bibliotheksrekonstruktion.hab.de/ontology.php#');
        EasyRdf_Namespace::set('br', 'http://bibliotheksrekonstruktion.hab.de/');
        EasyRdf_Namespace::set('gnd', 'http://d-nb.info/gnd/');
        EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
        EasyRdf_Namespace::set('gndo', 'http://d-nb.info/standards/elementset/gnd#');
        EasyRdf_Namespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
        EasyRdf_Namespace::set('iso6392', 'http://id.loc.gov/vocabulary/iso639-2/');
        EasyRdf_Namespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
        $this->addCollectionData();
        $this->addCatalogueEntries();
        foreach ($this->data as $item) {
            $this->addItem($item);
        }
    }

    protected function addCollectionData() {
        $collection = $this->graph->resource('br:'.$this->fileName, 'libreto:Collection');
        if ($this->catalogue->year) {
            $collection->add('dc:date', EasyRdf_Literal::create($this->catalogue->year, null, 'xsd:gYear'));
        }
        $collection->addLiteral('dc:title', $this->catalogue->heading);
        if ($this->catalogue->description) {
            $collection->add('dc:description', EasyRdf_Literal::create($this->catalogue->description, 'de', null));
        }
        if ($this->catalogue->owner and $this->catalogue->ownerGND) {
            $owner = $this->graph->resource('gnd:'.$this->catalogue->ownerGND, 'libreto:Person');
            $owner->addLiteral('foaf:name', $this->catalogue->owner);
            $collection->addResource('libreto:collector', $owner);
        }
        elseif ($this->catalogue->owner) {
            $owner = $this->graph->resource('br:'.$this->fileName.'/persons/'.urlencode($this->catalogue->owner), 'libreto:Person');
            $owner->addLiteral('foaf:name', $this->catalogue->owner);
            $collection->addResource('libreto:collector', $this->catalogue->owner);
        }
    }

    protected function addCatalogueEntries() {
        if ($this->catalogue->institution and $this->catalogue->shelfmark) {
            $histCatalogue = $this->graph->resource('br:'.$this->fileName.'/catalogues/'.urlencode($this->catalogue->institution).'_'.urlencode($this->catalogue->shelfmark), 'libreto:Catalogue');
            if ($this->catalogue->year) {
                $histCatalogue->add('dc:date', EasyRdf_Literal::create($this->catalogue->year, null, 'xsd:gYear'));
            }
            if ($this->catalogue->shelfmark) {
                $histCatalogue->addLiteral('libreto:shelfmark', $this->catalogue->shelfmark);
            }
            if ($this->catalogue->institution) {
                $histCatalogue->addLiteral('libreto:owner', $this->catalogue->institution);
            }
            if ($this->catalogue->title) {
                $histCatalogue->addLiteral('dc:title', $this->catalogue->title);
            }
            foreach ($this->data as $item) {
                if ($item->titleCat and $item->pageCat) {
                    $entry = $this->graph->resource($this->graph->newBNodeId(), 'libreto:CatalogueEntry');
                    $entry->addLiteral('libreto:text', $item->titleCat);
                    $entry->addLiteral('libreto:page', $item->pageCat);
                    if ($item->histSubject) {
                        $entry->addLiteral('libreto:heading', $item->histSubject);
                    }
                    if ($item->numberCat) {
                        $entry->addLiteral('libreto:number', $item->numberCat);
                    }
                    if ($this->catalogue->base and $item->imageCat) {
                        $entry->addLiteral('libreto:imageURL', $this->catalogue->base.$item->imageCat);
                    }
                    $histCatalogue->addResource('libreto:hasEntry', $entry);
                    $itemResource = $this->graph->resource('br:'.$this->fileName.'/item_'.$item->id, 'libreto:Item');
                    $entry->addResource('libreto:refersTo', $itemResource);
                }
            }
        }
    }

    protected function addItem($item) {
        $collection = $this->graph->resource('br:'.$this->fileName, 'libreto:Collection');
        $itemResource = $this->graph->resource('br:'.$this->fileName.'/item_'.$item->id, 'libreto:Item');
        $itemResource->addResource('libreto:belongsTo', $collection);

        if ($item->mediaType) {
            $itemResource->addLiteral('dc:type', $item->mediaType);    
        }
        if ($item->titleBib) {
            $itemResource->addLiteral('dc:title', $item->titleBib);    
        }
        if ($item->year) {
            $itemResource->add('dc:date', EasyRdf_Literal::create($item->year, null, 'xsd:gYear'));
        }
        if ($item->volumes) {
            $itemResource->add('dbo:numberOfVolumes', EasyRdf_Literal::create($item->volumes, null, 'xsd:integer'));
        }
        if ($item->format) {
            $itemResource->addLiteral('libreto:bibliographicalFormat', $item->format);    
        }
        foreach ($item->publishers as $publisher) {
            $itemResource->addLiteral('dc:publisher', $publisher); 
        }
        foreach ($item->subjects as $subject) {
            $dataType = null;
            if (in_array(removeBlanks($subject), $this->aadgenres)) {
                $dataType = 'http://uri.gbv.de/terminology/aadgenres/';
            }
            $itemResource->add('dc:subject', EasyRdf_Literal::create($subject, null, $dataType));
        }
        foreach ($item->genres as $genre) {
            $dataType = null;
            if (in_array(removeBlanks($genre), $this->aadgenres)) {
                $dataType = 'http://uri.gbv.de/terminology/aadgenres/';
            }
            $itemResource->add('dbo:genre', EasyRdf_Literal::create($genre, null, $dataType));
        }
        foreach ($item->languages as $language) {
            $itemResource->add('dc_language', EasyRdf_Literal::create($language, null, 'iso6392'));
        }    
        if ($item->bound === 0 or $item->bound === '0') {
            $itemResource->add('libreto:physicalForm', EasyRdf_Literal::create('ungebunden', 'de', null));
        }
        elseif ($item->bound === 1 or $item->bound === '1')  {
            $itemResource->add('libreto:physicalForm', EasyRdf_Literal::create('gebunden', 'de', null));
        }

        if ($item->histShelfmark) {
            $itemResource->addLiteral('libreto:historicalShelfmark', $item->histShelfmark);    
        }
        if ($item->comment) {
            $itemResource->addLiteral('libreto:comment', $item->comment);    
        }
        if ($item->digitalCopy) {
            $itemResource->addLiteral('dc:hasFormat', resolveURN($item->digitalCopy));    
        }

        foreach ($item->places as $place) {
            $this->addPlace($itemResource, $place);
        }
        foreach ($item->persons as $person) {
            $this->addPerson($itemResource, $person);
        }

        if ($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
            $this->addManifestation($itemResource, $item);
        }
        if ($item->work['systemWork'] and $item->work['idWork']) {
            $this->addWork($itemResource, $item);
        }
        if ($item->originalItem['institutionOriginal'] and $item->originalItem['shelfmarkOriginal']) {
            $this->addOriginalItem($itemResource, $item);
        }

        return;
    }

    protected function addPlace($itemResource, $place) {
        if (!$place->placeName) {
            return;
        }
        $uri = '';
        $identifiers = array();
        if ($place->geoNames) {
            $uri = 'http://www.geonames.org/'.$place->geoNames;
            if ($place->gnd) {
                $identifiers[] = 'gnd_'.$place->gnd;
            }
            if ($place->getty) {
                $identifiers[] = 'getty_'.$place->getty;
            }    
        }
        elseif ($place->gnd) {
            $uri = 'http://d-nb.info/gnd/'.$place->gnd;
            if ($place->getty) {
                $identifiers[] = 'getty_'.$place->getty;
            }           
        }
        elseif ($place->getty) {
            $uri = 'http://vocab.getty.edu/tgn/'.$place->getty;       
        }
        else {
            $uri = 'br:'.$this->fileName.'/places/'.urlencode($place->placeName);
        }
        $placeResource = $this->graph->resource($uri, 'libreto:Place');
        $placeResource->addLiteral('gn:name', $place->placeName);
        if ($place->geoData['lat'] and $place->geoData['long']) {
            $placeResource->add('wgs84:lat', EasyRdf_Literal::create($place->geoData['lat'], null, 'xsd:decimal'));
            $placeResource->add('wgs84:long', EasyRdf_Literal::create($place->geoData['long'], null, 'xsd:decimal'));
        }
        foreach ($identifiers as $identifier)  {
            $placeResource->addLiteral('dc:identifier', $identifier);    
        }
        $itemResource->addResource('libreto:hasPlace', $placeResource);
        return;
    }

    protected function addPerson($item, $person) {
        if (!$person->persName) {
            return;
        }
        $uri = '';
        if ($person->gnd)  {
            $uri = 'http://d-nb.info/gnd/'.$person->gnd;
        }
        else {
            $uri = 'br:'.$this->fileName.'/persons/'.urlencode($person->persName);
        }
        $personResource = $this->graph->resource($uri, 'libreto:Person');
        $personResource->addLiteral('foaf:name', $person->persName);
        if ($person->gender)  {
            $gender = translateGenderAbbrRDF($person->gender);
            if ($gender == 'male' or $gender == 'female' or $gender == 'other') {
                $personResource->add('foaf:gender', EasyRdf_Literal::create($gender, 'en', null));
            }
        }
        if ($person->gnd) {
            $identifierGND = new EasyRdf_Literal($person->gnd, null, 'http://d-nb.info/standards/elementset/gnd#gndIdentifier');
            $personResource->add('dc:identifier', $identifierGND);
        }
        if ($person->role == 'borrower') {
            foreach ($person->dateLending as $dateL) {
                $lending = $this->graph->newBNode('libreto:Lending');
                $lending->add('libreto:dateLending', EasyRdf_Literal::create($dateL, null, 'xsd:date'));
                $lending->addResource('libreto:borrower', $personResource);                
            }
        }
        else {    
            $property = 'dc:contributor';
            if ($person->role == 'author' or $person->role == 'creator') {
                $property = 'dc:creator';
            }
            $item->addResource($property, $personResource);
        }
        return;
    }

    protected function addPhysicalContext() {
        $miscellanyNo = 0;
        foreach ($this->data as $item) {
            $itemResource = $this->graph->resource('br:'.$this->fileName.'/item_'.$item->id, 'libreto:Item');
            if ($item->itemInVolume == 1)  {
                $miscellany = $this->graph->resource('br:'.$this->fileName.'/miscellany_'.$miscellanyNo, 'libreto:Miscellany');
                $context = $this->graph->resource($this->graph->newBNodeId(), 'libreto:PhysicalContext');
                $context->addLiteral('libreto:position', '1');
                $itemResource->addResource('libreto:hasContext', $context);
                $context->addResource('libreto:inMiscellany', $miscellany);
                $miscellanyNo++;
            }
            elseif($item->itemInVolume > 1) {
                $context = $this->graph->resource($this->graph->newBNodeId(), 'libreto:PhysicalContext');
                $context->addLiteral('libreto:position', strval($item->itemInVolume));
                $context->addResource('libreto:inMiscellany', $miscellany);
                $itemResource->addResource('libreto:hasContext', $context);
            }
        }
        return($graph);
    }

    protected function addManifestation($itemResource, $item) {
        $reference = new reference($item->manifestation['systemManifestation'], $item->manifestation['idManifestation']);
        if ($reference->valid == false) {
            return(null);
        }
        $manifestation = $this->graph->resource($reference->url, 'libreto:Manifestation');
        $manifestation->addLiteral('libreto:database', $reference->nameSystem);
        $manifestation->addLiteral('dc:identifier', $item->manifestation['idManifestation']);
        $itemResource->addResource('libreto:hasManifestation', $manifestation);
        return;
    }

    protected function addWork($itemResource, $item) {
        $reference = new reference($item->work['systemWork'], $item->work['idWork'], 'work');
        if (!$reference) {
            return(null);
        }
        $work = $this->graph->resource($reference->url, 'libreto:Work');
        if ($item->work['titleWork']) {
            $work->addLiteral('dc:title', $item->work['titleWork']);
        }
        $itemResource->addResource('libreto:containsWork', $work);
        return;
    }

    protected function addOriginalItem($itemResource, $item) {
        $targetOPAC = replaceAmpChar($item->originalItem['targetOPAC']);
        if ($item->originalItem['targetOPAC'] and $item->originalItem['searchID']) {
            $uri = resolveTarget($targetOPAC, $item->originalItem['searchID']);
        }
        elseif ($item->originalItem['targetOPAC']) {
            $uri = resolveTarget($targetOPAC, urlencode($item->originalItem['shelfmarkOriginal']));    
        }
        else {
            $uri = $this->graph->newBNodeId();
        }
        $original = $this->graph->resource($uri, 'libreto:OriginalItem');
        $original->addLiteral('libreto:owner', $item->originalItem['institutionOriginal']);
        $original->addLiteral('libreto:shelfmark', $item->originalItem['shelfmarkOriginal']);
        if ($item->originalItem['provenanceAttribute']) {
            $original->addLiteral('libreto:provenanceAttribute', $item->originalItem['provenanceAttribute']);
        }
        if ($item->originalItem['digitalCopyOriginal']) {
            $original->addLiteral('libreto:hasFormat', $item->originalItem['digitalCopyOriginal']);
        }
        $itemResource->addResource('libreto:hasOriginalItem', $original);
        return;
    }

    protected function generateOutputXML() {
        $serialiserX = new EasyRdf_Serialiser_RdfXml;
        $this->output = $serialiserX->serialise($this->graph, 'rdfxml');
    }

    protected function generateOutputTurtle() {
        $serialiserTTL = new EasyRdf_Serialiser_Turtle;
        $this->output = $serialiserTTL->serialise($this->graph, 'turtle');
    }    

}   

?>