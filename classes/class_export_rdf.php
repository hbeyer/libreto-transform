<?php

class export_rdf extends export {

	private $graph;

	function __construct($reconstruction) {

		$this->graph = new EasyRdf_Graph();

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

	    $this->graph->resource('br:test', 'libreto:Collection');

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
	
}

?>