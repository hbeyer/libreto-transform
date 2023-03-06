<?php

class Person {
	public $persName;
	public $gnd;
	public $gender;
	public $role = 'author'; //author, contributor, etc.
	public $beacon = array(); //Presence in databases is denoted by keys from class BeaconData
	public $dateLending = array();

	function __construct($name = null, $gnd = null, $gender = null, $role = 'creator') {
		$this->persName = $name;
		if ($gnd) {
			$this->gnd = $gnd;
		}
		if ($gender) {
			$this->gender = $gender;
		}
		if ($role) {
			$this->role = $role;
		}
	}

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

    public function __toString() {
        $ret = $this->persName;
        if ($this->gnd) {
            $ret .= '#'.$this->gnd;
            if ($this->gender) {
                $ret .= $this->gender;
            }
        }
        return($ret);
    }

    public function makeID() {
        if ($this->gnd) {
            return('gnd_'.$this->gnd);
        }
        return($this->persName);
    }

}

?>
