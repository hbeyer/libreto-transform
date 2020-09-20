<?php

class uploader_sql_dh extends uploader {
	
    private $pdo;

    function __construct($fileName) {
        $this->fileName = $fileName;
        require('private/connectionData.php');
        try { 
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } 
        catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function loadContent($fileName = '') {
        $data = $this->pdo->query('SELECT * FROM Zusammenfassung')->fetchAll(PDO::FETCH_CLASS, 'item');
		$data = $this->insertVolumeNote($data);
        $persons = $this->pdo->query('SELECT * FROM Autor')->fetchAll(PDO::FETCH_CLASS, 'person');
        $data = $this->enrichPersons($data, $persons);
        unset($persons);
        $places = $this->pdo->query('SELECT * FROM Ort')->fetchAll(PDO::FETCH_CLASS, 'place');
        $data = $this->enrichPlaces($data, $places);
        return($data);        
    }

    private function enrichPersons($data, $personList) {
        foreach ($data as $item) {
            foreach ($item->persons as $person) {
                $person->enrichByName($personList);
            }
        }
        return($data);
    }

    private function enrichPlaces($data, $placeList) {
        foreach ($data as $item) {
            foreach ($item->places as $place) {
                $place->enrichByName($placeList);
            }
        }
        return($data);
    }
	
	private function insertVolumeNote($data) {
		$countVolumes = 0;
		foreach ($data as $item) {
			if ($item->itemInVolume != 0) {
				$item->volumeNote['misc'] = 'br:'.$this->fileName.'/miscellany_'.$countVolumes;
				$item->volumeNote['positionMisc'] = $item->itemInVolume;
				if ($item->itemInVolume == 1) {
					$countVolumes++;
				}
			}
		}
		return($data);
	}

}

?>