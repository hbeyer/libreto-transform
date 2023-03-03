<?php

class uploader_csv extends uploader {
	
    protected $fieldNames;
    protected $rows;

    function __construct($path, $fileName) {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->metaPath = reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-metadata.xml';
	    $string = file_get_contents($this->path);
        $string = convertWindowsToUTF8($string);
        $rows = str_getcsv($string, "\n");
        $this->fieldNames = str_getcsv(array_shift($rows), ';');
        $this->rows = $this->makeAssocRows($rows, $this->fieldNames);  
        $this->valid = $this->validate();
    }

    public function loadContent($fileName = '') {
        $result = array();
        foreach ($this->rows as $row) {
            $result[] = $this->makeItemFromAssocArray($row);
        }
        return($result);
    }

    protected function makeItemFromAssocArray($row) {

        $item = new item();
        
        $simpleFields = array('pageCat', 'imageCat', 'numberCat', 'titleCat', 'titleBib', 'titleNormalized', 'year', 'format', 'mediaType', 'bound', 'comment', 'digitalCopy');
        foreach ($simpleFields as $field) {
            if (!empty($row[$field])) {
                $item->$field = trim($row[$field]);
            }
        }

        $semicolonFields = array('subjects', 'genres', 'languages', 'publishers', 'copiesHAB');
        foreach ($semicolonFields as $field) {
            if (!empty($row[$field])) {
                $item->$field = explode(';', $row[$field]);
                $item->$field = array_map('trim', $item->$field);            
            }
        }

        if (!empty($row['itemInVolume'])) {
            preg_match('~^([0-9]+)[bBvV]([0-9]{0,3})$~', $row['itemInVolume'], $hits);
            if (!empty($hits[1]) and !empty($hits[2])) {
                $item->volumesMisc = $hits[1];
                $item->itemInVolume = $hits[2];
            }
            elseif(isset($hits[1])) {
                $item->volumes = $hits[1];
            }
        }
        if (!empty($row['histSubject'])) {
            $explodeHistSubject = explode('#', $row['histSubject']);
            $explodeHistSubject = array_map('trim', $explodeHistSubject);
            $item->histSubject = $explodeHistSubject[0];
            if(isset($explodeHistSubject[1])) {
                $item->histShelfmark = $explodeHistSubject[1];
            }
        }
        if (!empty($row['systemManifestation']) and !empty($row['idManifestation'])) {
            $item->manifestation['systemManifestation'] = $row['systemManifestation'];
            $item->manifestation['idManifestation'] = $row['idManifestation'];
        }

        $originalFields = array('institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'targetOPAC', 'searchID', 'digitalCopyOriginal');
        foreach ($originalFields as $field) {
            if (!empty($row[$field])) {
                $item->originalItem[$field] = $row[$field];
            }
        }

        $workFields = array('titleWork', 'systemWork', 'idWork');
        foreach ($workFields as $field) {
            if (!empty($row[$field])) {
                $item->work[$field] = $row[$field];
            }
        }

        $personFields = array('author1', 'author2', 'author3', 'author4', 'contributor1', 'contributor2', 'contributor3', 'contributor4');
        foreach ($personFields as $field) {
            if (!empty($row[$field])) {
                $person = new person;
                $parts = explode('#', $row[$field]);
                preg_match('~([^#]+)#?([0-9X]+)?([mf])?~', $row[$field], $hits);
                if (!empty($hits[1])) {
                    $person->persName = $hits[1];
                }
                if (!empty($hits[2])) {
                    $person->gnd = $hits[2];
                }
                if (!empty($hits[3])) {
                    $person->gender = $hits[3];
                }
                if (substr($field, 0, 3) == 'con') {
                    $person->role = 'contributor';
                }
                $item->persons[] = $person;
            }
        }

        $placeFields = array('place1', 'place2');
        foreach ($placeFields as $field) {
            if (!empty($row[$field])) {
                $place = new place;
                preg_match('~([^#]+)#?(getty|gnd|geoNames)?(.+)?~', $row[$field], $hits);
                if (!empty($hits[1])) {
                    $place->placeName = $hits[1];
                }
                if (!empty($hits[2]) and !empty($hits[3])) {
                    if ($hits[2] == 'geoNames') {
                        $place->geoNames = $hits[3];
                    }
                    elseif ($hits[2] == 'gnd') {
                        $place->gnd = $hits[3];
                    }
                    elseif ($hits[2] == 'getty') {
                        $place->getty = $hits[3];
                    }  
                }
                unset($hits);
                $item->places[] = $place;
            }
        }

        return($item);

    }

    protected function makeAssocRows($rows, $fieldNames) {
        $result = array();
        foreach ($rows as $row) {
            $row = str_getcsv($row, ';');
            $newRow = array();
            foreach ($row as $key => $value) {
                $newRow[$fieldNames[$key]] = $value;
            }
            $result[] = $newRow;
        }
        return($result);
    }

    protected function validate() {
        if (empty($this->rows[0])) {
            throw new Exception('Es wurden keine Daten geladen.', 1);
        }
        $fieldsMin = array('id', 'pageCat', 'imageCat', 'numberCat', 'itemInVolume', 'titleCat', 'titleBib', 'titleNormalized', 'author1', 'author2', 'author3', 'author4', 'contributor1', 'contributor2', 'contributor3', 'contributor4', 'place1', 'place2', 'publishers', 'year', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'idManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'digitalCopyOriginal', 'targetOPAC', 'searchID', 'titleWork', 'systemWork', 'idWork', 'bound',  'comment', 'digitalCopy');
        foreach ($fieldsMin as $fieldMin) {
            if (!in_array($fieldMin, $this->fieldNames)) {
                throw new Exception('Fehlende Spalte: '.$fieldMin, 1);
            }
        }
        $width = count($this->fieldNames);
        foreach ($this->rows as $index => $row) {
            if (count($row) != $width) {
				$place = $index + 1;
                throw new Exception("Uneinheitliche Anzahl an Spalten ab Nr. $place", 1);
            }
        }
        return(1);
    }
	
	public static function getByPreference($array) {
		foreach ($array as $var) {
			if (!empty($var)) {
				return($var);
			}
		}
		return(null);
	}	

}

?>