<?php

class reconstruction_sru extends reconstruction {

    function __construct($query, $fileName, $sru = null, $bib = null, $regexSig = null) {
        $this->fileName = $fileName;
        $this->createDirectory();
        $uploader = new uploader_sru($query, $this->fileName, $sru, $bib, $regexSig);
        if ($uploader->numHits == 0) {
            echo 'Keine Treffer für die Anfrage '.$query;
            return;
        }
        $this->metadataReconstruction = $uploader->loadMetadata();
        $this->catalogues = $uploader->loadCatalogues($this->fileName);
        //$this->catalogue = $this->catalogues[0];
        $this->convertMetadataToDefault();
        $this->content = $uploader->content;
        $this->insertIDs();
        $this->convertCatalogueToFull();
        $this->convertContentToFull();
    }

}

?>
