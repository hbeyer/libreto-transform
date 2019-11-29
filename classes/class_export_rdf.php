<?php

class export_rdf extends export {

	private $graph;

	function __construct(reconstruction $reconstruction) {

		$this->graph = new EasyRdf_Graph();
		$this->reconstruction = $reconstruction;

		EasyRdf_Namespace::set('dcmt', 'http://purl.org/dc/terms/');
	    EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/spec/#term_');
	    EasyRdf_Namespace::set('gn', 'http://www.geonames.org/ontology#');
	    EasyRdf_Namespace::set('libreto', 'http://bibliotheksrekonstruktion.hab.de/ontology.php#');
	    EasyRdf_Namespace::set('br', 'http://bibliotheksrekonstruktion.hab.de/');
	    EasyRdf_Namespace::set('gnd', 'http://d-nb.info/gnd/');
	    EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
	    EasyRdf_Namespace::set('gndo', 'http://d-nb.info/standards/elementset/gnd#');
	    EasyRdf_Namespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
	    EasyRdf_Namespace::set('iso6392', 'http://id.loc.gov/vocabulary/iso639-2/');
	    EasyRdf_Namespace::set('xsd', 'https://www.w3.org/TR/2012/REC-xmlschema11-2-20120405/datatypes.html#');

	    $this->addMetadata();
	    $this->addCatalogues();

	    return(true);
	}

	public function saveSerialization($path, $format) {
		if ($format == 'turtle') {
			$serialiser = new EasyRdf_Serialiser_Turtle;
			$this->content = $serialiser->serialise($this->graph, 'turtle');
			$this->save($path.'.ttl');
			return(true);
		}
		if ($format == 'rdfxml') {
			$serialiser = new EasyRdf_Serialiser_RdfXml;
			$this->content = $serialiser->serialise($this->graph, 'rdfxml');
			$this->save($path.'.rdf');
			return(true); 
		}
		return(false);		
	}

	private function addMetadata() {
		$set = $this->reconstruction->metadataReconstruction;
		$collection = $this->graph->resource('br:'.$set->fileName, 'libreto:Collection');
	    if ($set->yearReconstruction) {
        	$collection->addLiteral('dcmt:date', $set->yearReconstruction, 'xsd:gYear');
    	}
    	if ($set->description) {    
        	$collection->addLiteral('dcmt:description', $set->description, 'de');
    	}
	    if ($set->owner and $set->ownerGND) {    
	        $owner = $this->graph->resource('gnd:'.$set->ownerGND, 'libreto:Person');
	        $owner->addLiteral('foaf:name', $set->owner);
	        $collection->addResource('libreto:collector', $owner);
	    }
	    elseif ($set->owner) {
	        $owner = $graph->resource('br:'.$set->fileName.'/persons/'.urlencode($set->owner), 'libreto:Person');
	        $owner->addLiteral('foaf:name', $set->owner);
	        $collection->addResource('libreto:collector', $owner);
	    }
	    if (!empty($set->persons[0])) {
	    	//Integration von Personen
	    }
	    elseif ($set->creatorReconstruction) {
	    	$collection->addLiteral('dcmt:creator', $set->creatorReconstruction, 'de');
	    }
	    if ($set->yearReconstruction) {
	    	$collection->addLiteral('dcmt:date', $set->yearReconstruction, 'xsd:gYear');
	    }
	}

	private function addCatalogues() {
		foreach ($this->reconstruction->catalogues as $catalogue) {
			$cat = $this->graph->resource('br:'.$this->reconstruction->metadataReconstruction->fileName.'/catalogues/'.$catalogue->id, 'libreto:Catalogue');

	       	if ($catalogue->year) {
	            $cat->addLiteral('dcmt:date', $catalogue->year, 'xsd:gYear');
	        }
	        if ($catalogue->shelfmark) {
	            $cat->addLiteral('libreto:shelfmark', $catalogue->shelfmark);
	        }
	        if ($catalogue->institution) {
	            $cat->addLiteral('libreto:owner', $catalogue->institution);
	        }
	        if ($catalogue->title) {
	            $cat->addLiteral('dcmt:title', $catalogue->title);
	        }
	        if ($catalogue->base) {
	        	if (substr($catalogue->base, 0, 4 == 'http')) {
	            	$cat->addLiteral('dcmt:hasFormat', strtr($catalogue->base, array('{No}' => '1')));
	        	}
	        }
	        if ($catalogue->placeCat) {
	        	$uri = 'br:'.$this->reconstruction->metadataReconstruction->fileName.'/places/'.urlencode($catalogue->placeCat);
	        	$placeResource = $this->graph->resource($uri, 'libreto:Place');
	        	$placeResource->addLiteral('gn:name', $catalogue->placeCat);
	        	$cat->addResource('libreto:hasPlace', $placeResource);
	        }
	        if ($catalogue->printer) {}        

		}
	}
	
}

?>