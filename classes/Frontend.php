<?php

class Frontend {

    private $facetList;
    private $reconstruction;
    private $path;

    function __construct($reconstruction, $facetList = null) {
        $this->reconstruction = $reconstruction;
        //Behelfsmäßige Übertragung der Variable fileName in das Objekt catalogue, wo sie eigentlich nicht hingehört
        $this->reconstruction->catalogue->fileName = $this->reconstruction->fileName;
        if (get_class($facetList) != 'FacetList') {
            $this->facetList = new FacetList;
        }
        else {
            $this->facetList = $facetList;
        }
        $this->path = Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-';
    }

    public function build($maxLen = 100) {
        // Die folgenden Dateien sind u. U. schon vorher inkludiert, wenn man Reconstruction::saveAllFormats() ausgeführt hat.
        require_once(Reconstruction::INCLUDEPATH.'makeSection.php');
        require('private/settings.php');
        require(Reconstruction::INCLUDEPATH.'makeNavigation.php');
        require(Reconstruction::INCLUDEPATH.'makePage.php');
        require(Reconstruction::INCLUDEPATH.'makeEntry.php');
        require(Reconstruction::INCLUDEPATH.'makeCloudList.php');
        require(Reconstruction::INCLUDEPATH.'makeDoughnutList.php');
        require(Reconstruction::INCLUDEPATH.'makeGraph.php');
        require_once(Reconstruction::INCLUDEPATH.'auxiliaryFunctions.php');
        recurse_copy('assets', Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/assets');

        if (is_int($maxLen)) {

			// Berechnen des Seiteninhalts
			$pages = array();

			foreach ($this->facetList->pages as $facet) {
				$secList = makeSections($this->reconstruction->content, $facet);

				if (in_array($facet, $this->facetList->volumeFields)) {
					foreach($secList as $section) {
						$section = joinVolumes($section);
					}
				}
				$page = new Page($secList, $facet, $maxLen);
				$pages[] = $page;
			}

			// Zusammenführen der Inhaltsverzeichnisdaten
			$tocs = array();
			foreach ($pages as $page) {
				$tocs[$page->facet] = $page->ToC;
			}

			// Abspeichern der einzelnen Seiten
			foreach ($pages as $page) {
				$page->buildSubpages($this->path, $this->reconstruction->catalogue, $tocs, $impressum);
			}
		}

        else {

			//Hier werden die Strukturen (jeweils ein Array aus section-Objekten) gebildet und im Array $structures zwischengespeichert.
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
	            $fileName = Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-'.$facet.'.htm';
				if($count == 0) {
					$firstFileName = $fileName;
				}
				$datei = fopen($fileName,"w");
				fwrite($datei, $content, 10000000);
				fclose($datei);
				$count++;
			}

			unset($structures);
        }

		//Anlegen der Datei index.php mit Weiterleitung auf die Startseite
		$this->makeStartPage();


        // Erzeugen der Seite mit den Word Clouds
		if ($this->facetList->clouds != array()) {
			$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, 'jqcloud');
            $pageContent = makeCloudPageContent($this->reconstruction->content, $this->facetList->clouds, $this->reconstruction->catalogue->fileName);
            $content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, 'jqcloud', $impressum);
			$fileName = Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-wordCloud.htm';
			$datei = fopen($fileName,"w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
		}
		// Erzeugen der Seite mit den Doughnut Charts
		if ($this->facetList->doughnuts != array()) {
			$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, 'doughnut');
            $pageContent = makeDoughnutPageContent($this->reconstruction->content, $this->facetList->doughnuts, $this->reconstruction->catalogue->fileName);
            $content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, 'doughnut', $impressum);
			$fileName = Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-doughnut.htm';
			$datei = fopen($fileName,"w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
		}
		/*
		// Erzeugen der Seite mit dem Graph
		$navigation = makeNavigation($this->reconstruction->catalogue, $tocs, 'graph');
		$pageContent = makeGraphPageContent($this->reconstruction->content);
		$content = makePage($this->reconstruction->catalogue, $navigation, $pageContent, 'graph', $impressum);
		$fileName = Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/'.$this->reconstruction->fileName.'-graph.htm';
		$datei = fopen($fileName,"w");
		fwrite($datei, $content, 10000000);
		fclose($datei);
		*/

		zipFolderContent(Reconstruction::FOLDER.'/'.$this->reconstruction->fileName, $this->reconstruction->fileName);

    }

    private function makeStartPage() {
	    $content = "<?php \r\n header(\"Location: ".$this->reconstruction->fileName."-".$this->facetList->pages[0].".htm\"); ?>";
	    $datei = fopen(Reconstruction::FOLDER.'/'.$this->reconstruction->fileName.'/index.php',"w");
	    fwrite($datei, $content, 1000000);
	    fclose($datei);
    }


}

?>
