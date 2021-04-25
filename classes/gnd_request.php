<?php

class gnd_request {

    public $gnd;
    public $errorMessage = null;
    //private $base = 'http://hub.culturegraph.org/entityfacts/';
    private $response;
    
    public $preferredName;
    public $type;
    public $variantNames = array();
    public $info;
    public $dateBirth;
    public $dateDeath;
    public $placeBirth;
    public $placeDeath;
    public $placesActivity = array();
    public $academicDegree;

    function __construct(gnd $gnd, cache_gnd $cache) {
        $this->gnd = $gnd;
        if ($this->gnd->valid == true) {
            $string = $cache->get($this->gnd->id);
            if (!$string) {
                $this->errorMessage = 'Cache liefert keine Daten für GND '.$this->gnd->id;
            }
            else {
                $this->response = json_decode($string, true);
                unset($string);
                foreach ($this->response as $key => $value) {
                    if ($key == 'preferredName') {
                        $this->preferredName = gnd_request::replaceUml($value);
                    }                    
                    if ($key == '@type') {
                        $this->type = $value;
                    }
                    if ($key == 'variantName') {
                        $this->variantNames = $value;
                    }                    
                    if ($key == 'biographicalOrHistoricalInformation') {
                        $this->info = gnd_request::replaceUml($value);
                    }
                    if ($key == 'dateOfBirth') {
                        $this->dateBirth = $value;
                    }
                    if ($key == 'dateOfDeath') {
                        $this->dateDeath = $value;
                    }
                    if ($key == 'placeOfBirth') {
                        $this->placeBirth = new place;
                        $this->placeBirth->placeName = gnd_request::replaceUml($value[0]['preferredName']);
                        if (!empty($value[0]['@id'])) {
                            $this->placeBirth->gnd = substr($value[0]['@id'], 22);
                        }
                    }
                    if ($key == 'placeOfDeath') {
                        $this->placeDeath = new place;
                        $this->placeDeath->placeName = gnd_request::replaceUml($value[0]['preferredName']);
                        if (!empty($value[0]['@id'])) {
                            $this->placeDeath->gnd = substr($value[0]['@id'], 22);
                        }
                    }
                    if ($key == 'placeOfActivity') {
                        foreach ($value as $place) {
                            $placeActivity = new place;
                            $placeActivity->placeName = gnd_request::replaceUml($place['preferredName']);
                            if (!empty($place['@id'])) {
                                $placeActivity->gnd = substr($place['@id'], 22);
                            }
                            $this->placesActivity[] = $placeActivity;
                        }
                    }
                    if ($key == 'academicDegree') {
                        $this->academicDegree = $value[0];
                    }                                  
                }
                $this->response = null;
            }
        }
    }

    static function makeTimeStamp($date) {
        preg_match('~(v?[0-9]{1,4})$~', $date, $hit);
        if (isset($hit[1])) {
            $year = $hit[1];
            $year = strtr($year, 'v', '-');
            return($year);
        }
        return('');
    }
	
	// Die Funktion ersetzt kombinierende diakritische Zeichen (hier nicht als solche erkennbar) durch HMLT-Entities, um die versetzte Darstellung der Punkte in Firefox zu beheben.
	static function replaceUml($string) {
		$translate = array('Ä' => '&Auml;', 'Ö' => '&Ouml;', 'Ü' => '&Uuml;', 'ä' => '&auml;', 'ö' => '&ouml;', 'ü' => '&uuml;', 'ë' => '&euml;');
		$string = strtr($string, $translate);
		return($string);
	}	

}

?>