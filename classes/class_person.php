<?php

class person {
	public $persName;
	public $gnd;
	public $gender;
	public $role = 'author'; //author, contributor, etc.
	public $beacon = array(); //Presence in databases is denoted by keys from class beaconData
	
	public function __set($name, $value) {
		$translation = array(
            'Name' => 'persName',
            'GND' => 'gnd'
        );
        $name = strtr($name, $translation);
        if ($name == 'persName') {
            $value = trim(removeBrackets($value));
        }
        if ($name == 'C') {
            return;
        }
        $this->$name = $value;
 	}

    public function enrichByName($persons) {
        foreach ($persons as $person) {
            if ($this->persName == $person->persName) {
                foreach ($person as $key => $value) {
                    if ($key != 'persName' and $key != 'role') {
                        $this->$key = $value;
                    }
                }
            return;
            }
        }
        return(null);
    }
	
}

?>
