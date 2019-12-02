<?php

class  uploader {
    
    public $path;
    public $format;
    public $fileName;
    public $vaid = 0;

    function __construct($path, $fileName = '', $format = '') {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->format = $format;
    }
    
    protected function loadMetaFile() {
        $metaPath = reconstruction::FOLDER.'/'.$this->fileName.'/'.$this->fileName.'-cat.php';
        if (!file_exists($metaPath)) {
           copy ('templateCat.php', $metaPath);
           chmod($metaPath, 0777);
           throw new Exception('Bitte füllen Sie die Datei '.$this->fileName.'-cat.php aus und wiederholen Sie den Vorgang.', 1);
        }
        else {
            require($metaPath);
            if (!empty($catalogue)) {
                foreach ($catalogue as $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    if (substr($value, 0, 1) == '{') {
                        throw new Exception('Bitte entfernen Sie alle geschweiften Klammern aus der Datei '.$this->fileName.'-cat.php und wiederholen Sie den Vorgang.', 1);
                    }
                }
                return($catalogue);
            }
        }
    }

// Das kommt in eine eigene Klasse
    private function loadSQL_DH() {

        require('private/connectionData.php');

        try {
             $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }

        $data = $pdo->query('SELECT * FROM Zusammenfassung')->fetchAll(PDO::FETCH_CLASS, 'item');
        $persons = $pdo->query('SELECT * FROM Autor')->fetchAll(PDO::FETCH_CLASS, 'person');
        $data = $this->enrichPersons($data, $persons);
        unset($persons);
        $places = $pdo->query('SELECT * FROM Ort')->fetchAll(PDO::FETCH_CLASS, 'place');
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

}

?>
