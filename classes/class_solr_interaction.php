<?php

class solr_interaction {

    const BASE_SELECT = 'http://localhost:8983/solr/libreto/select?';
    const FORMAT = 'php';

    public $search_fields = array(
        'fullText' => 'Volltext', 
        'titleBib' => 'Titel', 
        'titleCat' => 'Titel Altkatalog', 
        'author' => 'Autor(in)', 
        'contributor' => 'Beiträger(in)', 
        'histSubject' => 'Rubrik Altkatalog', 
        'place' => 'Erscheinungsort', 
        'publisher' => 'Drucker(in)', 
        'yearNormalized' => 'Jahr', 
        'subjects' => 'Inhalt', 
        'genres' => 'Gattung', 
        'languagesFull' => 'Sprache', 
        'format' => 'Format',
        'comment' => 'Kommentar',
        'id' => 'ID'
    );
    public $filter_field = 'ownerGND';
    public $filters = array(
        'all' => 'Alle',
        '141678615' => 'Antoinette Amalie von Braunschweig-Wolfenbüttel',
        '128989289' => 'Bahnsen, Benedikt',
        '117671622' => 'Liddel, Duncan',
        '1055708286' => 'Rehlinger, Carl Wolfgang'
    );
    public $facet_fields = array(
        'nameCollection_str' => 'Sammlung',
        'subjects_str' => 'Inhalte',
        'genres_str' => 'Gattung',
        'languagesFull_str' => 'Sprache',
        'format_str' => 'Format',
        'histSubject_str' => 'Rubrik',
        'publisher_str' => 'Drucker(in)'
    );

}

?>
