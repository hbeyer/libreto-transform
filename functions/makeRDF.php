<?php

function saveRDF($data, $catalogue, $base = 'http://bibliotheksrekonstruktion.hab.de/') {
    
    $graph = new EasyRdf_Graph();
    EasyRdf_Namespace::set('dcmt', 'http://purl.org/dc/terms/');
    EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/spec/#term_');
    EasyRdf_Namespace::set('gn', 'http://www.geonames.org/ontology#');
    EasyRdf_Namespace::set('libreto', 'http://bibliotheksrekonstruktion.hab.de/ontology.php#');
    EasyRdf_Namespace::set('br', $base);
    EasyRdf_Namespace::set('gnd', 'http://d-nb.info/gnd/');
    EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
    EasyRdf_Namespace::set('gndo', 'http://d-nb.info/standards/elementset/gnd#');
    EasyRdf_Namespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');

    EasyRdf_Namespace::set('iso6392', 'http://id.loc.gov/vocabulary/iso639-2/');
    EasyRdf_Namespace::set('xsd', 'https://www.w3.org/TR/2012/REC-xmlschema11-2-20120405/datatypes.html#');

    $graph = addCollectionData($graph, $catalogue);
    $graph = addCatalogueEntries($catalogue, $graph, $data);
    foreach ($data as $item) {
        addItem($graph, $item, $catalogue);
    }
    $graph = addPhysicalContext($graph, $data, $catalogue);

	$serialiser = new EasyRdf_Serialiser_Turtle;
	$turtle = $serialiser->serialise($graph, 'turtle');
	file_put_contents('user/'.$catalogue->fileName.'/'.$catalogue->fileName.'.ttl', $turtle); 

	$serialiserX = new EasyRdf_Serialiser_RdfXml;
	$rdfxml = $serialiserX->serialise($graph, 'rdfxml');
	file_put_contents('user/'.$catalogue->fileName.'/'.$catalogue->fileName.'.rdf', $rdfxml); 
}

function saveRDFtoPath($data, $catalogue, $path, $base = 'http://bibliotheksrekonstruktion.hab.de/') {
    
    $graph = new EasyRdf_Graph();
    EasyRdf_Namespace::set('dcmt', 'http://purl.org/dc/terms/');
    EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/spec/#term_');
    EasyRdf_Namespace::set('gn', 'http://www.geonames.org/ontology#');
    EasyRdf_Namespace::set('libreto', 'http://bibliotheksrekonstruktion.hab.de/ontology.php#');
    EasyRdf_Namespace::set('br', $base);
    EasyRdf_Namespace::set('gnd', 'http://d-nb.info/gnd/');
    EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
    EasyRdf_Namespace::set('gndo', 'http://d-nb.info/standards/elementset/gnd#');
    EasyRdf_Namespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');

    EasyRdf_Namespace::set('iso6392', 'http://id.loc.gov/vocabulary/iso639-2/');
    EasyRdf_Namespace::set('xsd', 'https://www.w3.org/TR/2012/REC-xmlschema11-2-20120405/datatypes.html#');

    $graph = addCollectionData($graph, $catalogue);
    $graph = addCatalogueEntries($catalogue, $graph, $data);
    foreach ($data as $item) {
        addItem($graph, $item, $catalogue);
    }
    $graph = addPhysicalContext($graph, $data, $catalogue);

	$serialiser = new EasyRdf_Serialiser_Turtle;
	$turtle = $serialiser->serialise($graph, 'turtle');
	file_put_contents($path.'.ttl', $turtle); 

	$serialiserX = new EasyRdf_Serialiser_RdfXml;
	$rdfxml = $serialiserX->serialise($graph, 'rdfxml');
	file_put_contents($path.'.rdf', $rdfxml);
}

function addCollectionData($graph, $catalogue) {
    $collection = $graph->resource('br:'.$catalogue->fileName, 'libreto:Collection');
    if ($catalogue->year) {
        $collection->addLiteral('dcmt:date', $catalogue->year, 'xsd:gYear');
    }
    $collection->addLiteral('dcmt:title', $catalogue->heading);
    if ($catalogue->description) {    
        $collection->addLiteral('dcmt:description', $catalogue->description, 'de');
    }
    if ($catalogue->owner and $catalogue->ownerGND) {    
        $owner = $graph->resource('gnd:'.$catalogue->ownerGND, 'libreto:Person');
        $owner->addLiteral('foaf:name', $catalogue->owner);
        $collection->addResource('libreto:collector', $owner);
    }
    elseif ($catalogue->owner) {
        $owner = $graph->resource('br:'.$catalogue->fileName.'/persons/'.urlencode($catalogue->owner), 'libreto:Person');
        $owner->addLiteral('foaf:name', $catalogue->owner);
        $collection->addResource('libreto:collector', $owner);
    }
    return($graph);
}

function addCatalogueEntries($catalogue, $graph, $data) {
    if ($catalogue->institution and $catalogue->shelfmark) {
        $histCatalogue = $graph->resource('br:'.$catalogue->fileName.'/catalogues/'.urlencode($catalogue->institution).'_'.urlencode($catalogue->shelfmark), 'libreto:Catalogue');
        if ($catalogue->year) {
            $histCatalogue->addLiteral('dcmt:date', $catalogue->year, 'xsd:gYear');
        }
        if ($catalogue->shelfmark) {
            $histCatalogue->addLiteral('libreto:shelfmark', $catalogue->shelfmark);
        }
        if ($catalogue->institution) {
            $histCatalogue->addLiteral('libreto:owner', $catalogue->institution);
        }
        if ($catalogue->title) {
            $histCatalogue->addLiteral('dcmt:title', $catalogue->title);
        }
        foreach ($data as $item) {
            if ($item->titleCat and $item->pageCat) {
                $entry = $graph->resource($graph->newBNodeId(), 'libreto:CatalogueEntry');
                $entry->addLiteral('libreto:text', $item->titleCat);
                $entry->addLiteral('libreto:page', $item->pageCat);
                if ($item->histSubject) {
                    $entry->addLiteral('libreto:heading', $item->histSubject);
                }
                if ($item->numberCat) {
                    $entry->addLiteral('libreto:number', $item->numberCat);
                }
                if ($catalogue->base and $item->imageCat) {
                    $entry->addLiteral('libreto:imageURL', $catalogue->base.$item->imageCat);
                }
                $histCatalogue->addResource('libreto:hasEntry', $entry);
                $itemResource = $graph->resource('br:'.$catalogue->fileName.'/item_'.$item->id, 'libreto:Item');
                $entry->addResource('libreto:refersTo', $itemResource);
            }
        }
    }
    return($graph);
}

function addItem($graph, $item, $catalogue) {
    $collection = $graph->resource('br:'.$catalogue->fileName, 'libreto:Collection');
    $itemResource = $graph->resource('br:'.$catalogue->fileName.'/item_'.$item->id, 'libreto:Item');
    $itemResource->addResource('libreto:belongsTo', $collection);

    if ($item->mediaType) {
        $itemResource->addLiteral('dcmt:type', $item->mediaType);    
    }
    if ($item->titleBib) {
        $itemResource->addLiteral('dcmt:title', $item->titleBib);    
    }
    if ($item->year) {
        $itemResource->addLiteral('dcmt:date', $item->year, 'xsd:gYear');
    }
    if ($item->publisher) {
        $itemResource->addLiteral('dcmt:publisher', $item->publisher);    
    }
    if ($item->volumes) {
        $itemResource->addLiteral('dbo:numberOfVolumes', $item->volumes, 'xsd:integer');    
    }
    if ($item->format) {
        $itemResource->addLiteral('libreto:bibliographicalFormat', $item->format);    
    }

    include('AADGenres.php');
    foreach ($item->subjects as $subject) {
        $language = null;
        if (in_array(removeBlanks($subject), $aadgenres)) {
            $language = 'http://uri.gbv.de/terminology/aadgenres/';
        }
        $itemResource->addLiteral('dcmt:subject', $subject, $language); 
    }
    foreach ($item->genres as $genre) {
        $language = null;
        if (in_array(removeBlanks($genre), $aadgenres)) {
            $language = 'http://uri.gbv.de/terminology/aadgenres/';
        }
        $itemResource->addLiteral('dbo:genre', $genre, $language); 
    }

    foreach ($item->languages as $language) {
        $itemResource->addLiteral('dcmt:language', $language, 'iso6392'); 
    }

    
    if ($item->bound === 0 or $item->bound === '0') {
        $itemResource->addLiteral('libreto:physicalForm', 'ungebunden', 'de');
    }
    elseif ($item->bound === 1 or $item->bound === '1')  {
        $itemResource->addLiteral('libreto:physicalForm', 'gebunden', 'de');
    }

    if ($item->histShelfmark) {
        $itemResource->addLiteral('libreto:historicalShelfmark', $item->histShelfmark);    
    }
    if ($item->comment) {
        $itemResource->addLiteral('libreto:comment', $item->comment);    
    }
    if ($item->digitalCopy) {
        $itemResource->addLiteral('dcmt:hasFormat', resolveURN($item->digitalCopy));    
    }

    foreach ($item->places as $place) {
        addPlace($graph, $itemResource, $catalogue, $place);
    }
    foreach ($item->persons as $person) {
        addPerson($graph, $catalogue, $itemResource, $person);
    }

    if ($item->manifestation['systemManifestation'] and $item->manifestation['idManifestation']) {
        addManifestation($graph, $itemResource, $item);
    }
    if ($item->work['systemWork'] and $item->work['idWork']) {
        addWork($graph, $itemResource, $item);
    }
    if ($item->originalItem['institutionOriginal'] and $item->originalItem['shelfmarkOriginal']) {
        addOriginalItem($graph, $itemResource, $item);
    }

    return;
}

function addPlace($graph, $itemResource, $catalogue, $place) {
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
        $uri = 'br:'.$catalogue->fileName.'/places/'.urlencode($place->placeName);
    }
    $placeResource = $graph->resource($uri, 'libreto:Place');
    $placeResource->addLiteral('gn:name', $place->placeName);
    if ($place->geoData['lat'] and $place->geoData['long']) {
        $placeResource->addLiteral('wgs84:lat', $place->geoData['lat'], 'xsd:decimal');
        $placeResource->addLiteral('wgs84:long', $place->geoData['long'], 'xsd:decimal');
    }
    foreach ($identifiers as $identifier)  {
        $placeResource->addLiteral('dcmt:identifier', $identifier);    
    }
    $itemResource->addResource('libreto:hasPlace', $placeResource);
    return;
}

function addPerson($graph, $catalogue, $item, $person) {
    if (!$person->persName) {
        return;
    }
    $uri = '';
    if ($person->gnd)  {
        $uri = 'http://d-nb.info/gnd/'.$person->gnd;
    }
    else {
        $uri = 'br:'.$catalogue->fileName.'/persons/'.urlencode($person->persName);
    }
    $personResource = $graph->resource($uri, 'libreto:Person');
    $personResource->addLiteral('foaf:name', $person->persName);
    if ($person->gender)  {
        $gender = translateGenderAbbrRDF($person->gender);
        if ($gender == 'male' or $gender == 'female' or $gender == 'other') {
            $personResource->addLiteral('foaf:gender', $gender, 'en');
        }
    }
    if ($person->gnd) {
        $identifierGND = new EasyRdf_Literal($person->gnd, null, 'http://d-nb.info/standards/elementset/gnd#gndIdentifier');
        $personResource->add('dcmt:identifier', $identifierGND);

    /*    if ($person->beacon and $person->gnd) {
        include('beaconSources.php');
            foreach ($person->beacon as $key){
                $link = makeBeaconLink($person->gnd, $beaconSources[$key]['target']);
                if ($link) {
                    $personResource->addLiteral('libreto:biographicalInformation', $link);
                }
            }
        }
    */
    }
    $property = 'dcmt:contributor';
    if ($person->role == 'author' or $person->role == 'creator') {
        $property = 'dcmt:creator';
    }
    $item->addResource($property, $personResource);
    return;
}

function addPhysicalContext($graph, $data, $catalogue) {
    $miscellanyNo = 0;
    foreach ($data as $item) {
        $itemResource = $graph->resource('br:'.$catalogue->fileName.'/item_'.$item->id, 'libreto:Item');
        if ($item->itemInVolume == 1)  {
            $miscellany = $graph->resource('br:'.$catalogue->fileName.'/miscellany_'.$miscellanyNo, 'libreto:Miscellany');
            $context = $graph->resource($graph->newBNodeId(), 'libreto:PhysicalContext');
            $context->addLiteral('libreto:position', '1');
            $itemResource->addResource('libreto:hasContext', $context);
            $context->addResource('libreto:inMiscellany', $miscellany);
            $miscellanyNo++;
        }
        elseif($item->itemInVolume > 1) {
            $context = $graph->resource($graph->newBNodeId(), 'libreto:PhysicalContext');
            $context->addLiteral('libreto:position', strval($item->itemInVolume));
            $context->addResource('libreto:inMiscellany', $miscellany);
            $itemResource->addResource('libreto:hasContext', $context);
        }
    }
    return($graph);
}

function addManifestation($graph, $itemResource, $item) {
    $reference = new reference($item->manifestation['systemManifestation'], $item->manifestation['idManifestation']);
    if ($reference->valid == false) {
        return(null);
    }
    $manifestation = $graph->resource($reference->url, 'libreto:Manifestation');
    $manifestation->addLiteral('libreto:database', $reference->nameSystem);
    $manifestation->addLiteral('dcmt:identifier', $item->manifestation['idManifestation']);
    $itemResource->addResource('libreto:hasManifestation', $manifestation);
    return;
}

function addWork($graph, $itemResource, $item) {
    $reference = new reference($item->manifestation['systemManifestation'], $item->manifestation['idManifestation'], 'work');
    if (!$reference) {
        return(null);
    }
    $work = $graph->resource($reference->url, 'libreto:Work');
    if ($item->work['titleWork']) {
        $work->addLiteral('dcmt:title', $item->work['titleWork']);
    }
    $itemResource->addResource('libreto:containsWork', $work);
    return;
}

function addOriginalItem($graph, $itemResource, $item) {
    $targetOPAC = replaceAmpChar($item->originalItem['targetOPAC']);
    if ($item->originalItem['targetOPAC'] and $item->originalItem['searchID']) {
        $uri = resolveTarget($targetOPAC, $item->originalItem['searchID']);
    }
    elseif ($item->originalItem['targetOPAC']) {
        $uri = resolveTarget($targetOPAC, urlencode($item->originalItem['shelfmarkOriginal']));    
    }
    else {
        $uri = $graph->newBNodeId();
    }
    $original = $graph->resource($uri, 'libreto:OriginalItem');
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

?>
