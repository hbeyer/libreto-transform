<?php

class solr_response extends solr_interaction {
    
    public $errorMessage = false;
    public $numFound;
    public $start;
    public $rows = 10;
    private $responsePHP = false;
    
    function __construct($url) {
        $string = @file_get_contents($url);
        if (!$string) {
            $this->errorMessage = 'Apache Solr antwortet nicht';
            return;
        }
        else {
            $this->responsePHP = eval("return ".$string.";");
            unset($string);
            if (isset($this->responsePHP['response']['numFound']) and isset($this->responsePHP['response']['start'])) {
                $this->numFound = $this->responsePHP['response']['numFound'];
                $this->start = $this->responsePHP['response']['start'];
            }
            else {
                $this->responsePHP = false;
                $this->errorMessage = 'Antwort fehlerhaft';
            }
        }
        return;
    }

    public function displayResults() {
        include('templates/docList.php');
    }
    
    public function displayFacets() {
        if ($_GET['refine'] != array()) {
            if ($this->validateRefine($_GET['refine']) == true) {
                include('templates/refines.php');
            }
        }
        include('templates/facets.php');
    }

    private function validateRefine($refines) {
            foreach ($refines as $refine) {
                $explode = explode(':', $refine);
                if (!isset($explode[0]) or !isset($explode[1])) {
                    return(false);
                }
            }
            return(true);
        }

    private function buildGETRequest() {
        $result = 'search.php?';
        $result .= 'field='.$_GET['field'].'&';
        $result .= 'value='.$_GET['value'].'&';
        if ($_GET['fuzzy'] == 'yes') { 
            $result .= 'fuzzy=yes&'; 
        }
        foreach ($_GET['owner'] as $gnd) {
            $result .= 'owner[]='.$gnd.'&';
        }
        $refines = array_unique($_GET['refine']);
        foreach ($refines as $refine) {          
            $result .= 'refine[]='.$refine.'&';
        }
        $result = rtrim($result, '&');
        return($result);
    }

    public function buildPaginationLink($start) {
        $result = $this->buildGETRequest();
        $result .= '&start='.$start;
        return($result);
    }

    public function buildFacetLink($addRefine) {
        if (in_array($addRefine, $_GET['refine'])) {
            return('#');
        }
        $result = $this->buildGETRequest();
        $result .= '&refine[]='.$addRefine;
        return($result);
    }
    
    public function buildRemoveLink($removeRefine) {
        $result = $this->buildGETRequest();
        $translate = array('&refine[]='.$removeRefine => '');
        $result = strtr($result, $translate);
        $result = rtrim($result, '&');
        return($result);
    }
    public function getRefineContent($refine) {
        $explode = explode(':', $refine);
        $field = array_shift($explode);
        $content = array_shift($explode);
        $label = $this->facet_fields[$field];
        return(array('label' => $label, 'content' => $content));    
    }

    
}

?>
