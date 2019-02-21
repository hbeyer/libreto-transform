<?php

class frontend {

    private $facetList;
    private $reconstruction;

    function __construct($reconstruction, $facetList = null) {
        $this->reconstruction = $reconstruction;
        if (get_class($facetList) != 'facetList') {
            $this->facetList = new facetList;
        }
        else {
            $this->facetList = $facetList;
        }
    }

    public function save() {
        require(reconstruction::INCLUDEPATH.'makeSection.php');
        require(reconstruction::INCLUDEPATH.'makeIndex.php');
        require(reconstruction::INCLUDEPATH.'makeNavigation.php');
        require(reconstruction::INCLUDEPATH.'makePage.php');
        require(reconstruction::INCLUDEPATH.'makeEntry.php');
        require(reconstruction::INCLUDEPATH.'makeCloudList.php');
        require(reconstruction::INCLUDEPATH.'makeDoughnutList.php');
        require(reconstruction::INCLUDEPATH.'auxiliaryFunctions.php');
        require('private/impressum.php');
        recurse_copy('assets', reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/assets');

    	/* Hier werden die Strukturen (jeweils ein Array aus section-Objekten) gebildet 
		und im Array $structures zwischengespeichert.
		*/
		$structures = array();
		$count = 0;
		foreach($this->facetList->pages as $facet) {
			$structure = makeSections($this->reconstruction->content, $facet);
			if (in_array($facet, $this->facetList->volumeFields)) {
				foreach($structure as $section) {
					$section = joinVolumes($section);
				}
			}
			$structures[] = $structure;
		}
		
		// Zu jeder Struktur wird eine Liste mit Kategorien für das Inhaltsverzeichnis berechnet.
		$count = 0;
		$tocs = array();
		foreach($structures as $structure) {
			$tocs[$this->facetList->pages[$count]] = makeToC($structure);
			$count++;
		}
		        
		// Für jede Struktur wird jetzt eine HTML-Datei berechnet und gespeichert.
		$count = 0;
		
		foreach($structures as $structure) {
			$facet = $this->facetList->pages[$count]; 
			$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, $facet);
            $pageContent = makeList($structure, $this->reconstruction->catalogue);
            $content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, $facet, $impressum);
            $fileName = reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-'.$facet.'.html';
			if($count == 0) {
				$firstFileName = $fileName;
			}
			$datei = fopen($fileName,"w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
			$count++;
		}
		
		unset($structures);
		
		//Anlegen der Datei index.php mit Weiterleitung auf die Startseite
		$this->makeStartPage();
					
        		
        // Erzeugen der Seite mit den Word Clouds
		if($this->facetList->clouds != array()) {
			$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, 'jqcloud');
            $pageContent = makeCloudPageContent($this->reconstruction->content, $this->facetList->clouds, $this->reconstruction->catalogue->fileName);
            $content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, 'jqcloud', $impressum);
			$fileName = reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-wordCloud.html';
			$datei = fopen($fileName,"w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
		}
		// Erzeugen der Seite mit den Doughnut Charts
		if($this->facetList->doughnuts != array()) {
			$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, 'doughnut');
            $pageContent = makeDoughnutPageContent($this->reconstruction->content, $this->facetList->doughnuts, $this->reconstruction->catalogue->fileName);
            $content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, 'doughnut', $impressum);
			$fileName = reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-doughnut.html';
			$datei = fopen($fileName,"w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
		}

		zipFolderContent(reconstruction::FOLDER.'/'.$this->reconstruction->fileName, $this->reconstruction->fileName);

    }

    private function makeStartPage() {
	    $content = "<?php \r\n header(\"Location: ".$this->reconstruction->fileName."-".$this->facetList->pages[0].".html\"); ?>";
	    $datei = fopen(reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/index.php',"w");
	    fwrite($datei, $content, 1000000);
	    fclose($datei);
    }


}


?>
