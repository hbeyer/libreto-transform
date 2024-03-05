<?php

class FormAction {

    public $message = '';
    private $post = null;

    function __construct($post) {
        $this->message = 'Das hat nicht geklappt.';
        if (empty($post['execute'])) {
            return;
        }
        if ($post['execute'] != 'yes') {
            return;
        }
        $this->post = $post;
        $this->message = 'Formulardaten übergeben.';
        if (empty($this->post['path_file']) or empty($this->post['fileName']) or empty($this->post['format_file'])) {
            $this->message = 'Es fehlen essenzielle Informationen zum Laden der Datei.';
            return;
        }
        $cat = $this->getCatalogue();

        if (in_array($this->post['format_file'], array('csv', 'php', 'sql_dh'))) {
            $cat->writeMetadata($this->post['fileName']);
        }
        try {
            $reconstruction = new Reconstruction($this->post['path_file'], $this->post['fileName'], $this->post['format_file']);
        } catch (Exception $e) {
            $this->message = 'Exception beim Einlesen der Daten: '.  $e->getMessage();
        }
        if (in_array($this->post['format_file'], array('xml', 'xml_full'))) {
            $reconstruction->updateCat($cat);
            $cat->writeMetadata($this->post['fileName']);
        }

        try {
            $reconstruction->enrichData();
        } catch (Exception $e) {
            $this->message = 'Exception beim Anreichern der Daten: '.  $e->getMessage();
        }
        try {
            $reconstruction->saveAllFormats();
        } catch (Exception $e) {
            $this->message = 'Exception beim Abspeichern der Daten: '.  $e->getMessage();
        }
        $facetList = $this->makeFacetList();
        $frontend = new Frontend($reconstruction, $facetList);
        try {
            $frontend->build();
        } catch (Exception $e) {
            $this->message = 'Exception beim Erzeugen der Website: '.  $e->getMessage();
        }
        $this->message = 'Transformation erfolgreich. Ergebnisse unter <a href="projectFiles/'.$post['fileName'].'/index.php">projectFiles/'.$post['fileName'].'</a>';

    }

    private function getCatalogue() {
        $cat = new Catalogue;
        foreach ($this->post as $key => $value) {
            if (property_exists('Catalogue', $key)) {
                $cat->$key = $value;
            }
        }
        return($cat);
    }

    private function makeFacetList() {
        $pages = array();
        $doughnuts = array();
        $clouds = array();
        foreach ($this->post as $key => $value) {
            if (substr($key, 0, 5) == 'page_' and $value == 'yes') {
                $pages[] = substr($key, 5);
            }
            elseif (substr($key, 0, 6) == 'cloud_' and $value == 'yes') {
                $clouds[] = substr($key, 6);
            }
            elseif (substr($key, 0, 9) == 'doughnut_' and $value == 'yes') {
                $doughnuts[] = substr($key, 9);
            }
        }
        $facetList = new FacetList($pages, $doughnuts, $clouds);
        return($facetList);
    }

}

?>
