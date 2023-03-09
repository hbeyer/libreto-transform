<?php

class GNDRequest {

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

    function __construct(GND $gnd, Cache_GND $cache) {
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
                        $this->preferredName = GNDRequest::replaceUml($value);
                    }
                    if ($key == '@type') {
                        $this->type = $value;
                    }
                    if ($key == 'variantName') {
                        $this->variantNames = $value;
                    }
                    if ($key == 'biographicalOrHistoricalInformation') {
                        $this->info = GNDRequest::replaceUml($value);
                    }
                    if ($key == 'dateOfBirth') {
                        $this->dateBirth = $value;
                    }
                    if ($key == 'dateOfDeath') {
                        $this->dateDeath = $value;
                    }
                    if ($key == 'placeOfBirth') {
                        $this->placeBirth = new Place;
                        $this->placeBirth->placeName = GNDRequest::replaceUml($value[0]['preferredName']);
                        if (!empty($value[0]['@id'])) {
                            $this->placeBirth->gnd = substr($value[0]['@id'], 22);
                        }
                    }
                    if ($key == 'placeOfDeath') {
                        $this->placeDeath = new Place;
                        $this->placeDeath->placeName = GNDRequest::replaceUml($value[0]['preferredName']);
                        if (!empty($value[0]['@id'])) {
                            $this->placeDeath->gnd = substr($value[0]['@id'], 22);
                        }
                    }
                    if ($key == 'placeOfActivity') {
                        foreach ($value as $place) {
                            $placeActivity = new Place;
                            $placeActivity->placeName = GNDRequest::replaceUml($place['preferredName']);
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
