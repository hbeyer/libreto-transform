# libreto-transform
Set of scripts for transforming library reconstruction data into reusable data formats (RDF, TEI, SOLR)

*Autor: Hartmut Beyer (beyer@hab.de)*

## Anforderungen
Die Anwendung erfordert einen Server mit PHP (getestet mit Version 5.6–7.3) und schreibenden Zugriff auf Dateien und Ordner innerhalb des Programmordners.

## Installation
Kopieren Sie alle Dateien in einen Ordner auf dem Server.

Benennen Sie im Ordner ***private/*** die Datei **settings.php.template** um in **settings.php** und tragen Sie die folgenden Angaben ein:
- Unter `$userGeoNames` der Login Ihres Accounts bei geoNames (http://www.geonames.org/login)
- Unter `$userAgentHTTP` Ihren Namen in beliebiger Form
- Unter `$impressum` die URL des Impressums, das für die Publikation der Seite gültig ist

Um eine Datenbankverbindung zu nutzen, kann analog die Datei ***connectionData.php.template*** angepasst werden (proprietäres Schema, entwickelt von D. Hakelberg)

## Datenerfassung
Daten können in XML oder in CSV erfasst werden. Zur Anlage eines XML-Dokuments nutzen Sie das Schema ***libreto-schema.xsd***. Im XML-Dokument werden sowohl die Erschließungsdaten als auch die Metadaten zur Sammlung hinterlegt. Zur Erstellung einer CSV-Datei nutzen Sie das Beispieldokument ***example.csv*** (Trennzeichen ";", Zeichencodierung "Windows-1252"). Die Metadaten werden in diesem Fall bei der Transformation erfasst. Die Benutzung der einzelnen Felder ist im Word-Dokument ***Dokumentation_CSV.doc*** beschrieben.

## Transformation
Ein Transformationsskript kann unter Verwendung der Datei ***transform.php*** erstellt werden. Hierin muss zunächst ein Objekt der Klasse `reconstruction` in folgender Weise erzeugt werden:

`reconstruction::__construct(string $path, string $fileName, [string $format = 'xml'])`
- `$path`: Pfad zur Ausgangsdatei mit Dateiname und -Endung.
- `$fileName`: Dateiname für das Projekt
- `$format`: Format der Ausgangsdatei. Neben 'xml' (Standardwert) und 'csv' sind für kundige Anwender/innen auch die Optionen 'php' (serialisierter PHP-Dateien) und 'sql_dh' (Datenbank mit proprietärem Schema) erlaubt.

Die Methode `reconstruction::enrichData()` fügt Geodaten für Orte sowie Links zu biographischen Nachweissystemen bei Personen hinzu und vergibt IDs für Sammelbände.

Die Methode `reconstruction::insertGeoData()` fügt Geodaten für Orte hinzu.

Die Methode `reconstruction::insertBeacon()` erzeugt Links zu biographischen Nachweissystemen für Personen. 

Die Methode `reconstruction::saveAllFormats()` speichert im Ordner ***projectFiles/{Dateiname}*** die Daten in folgenden Formaten ab: CSV, XML, RDF/XML, Turtle, TEI, SOLR-XML. 

Außerdem werden die Geodaten im KML- und CSV-Format ausgegeben. Zur Erzeugung einer Kartenansicht muss die Datei `printingPlaces.csv` im Datasheet Editor (https://geobrowser.de.dariah.eu/edit/) hochgeladen und die ID der Datensammlung als `geoBrowserStorageID` bei den Metadaten eingefügt werden.

## Metadatenanreicherung (entfällt bei XML)
Wurde eine andere Option als 'xml' bei der Erstellung des Objekts von der Klassse `reconstruction` gewählt, so kommt beim Ausführen des Skripts zunächst eine Aufforderung zum Ausfüllen der Datei ***projectFiles/{Dateiname}/{Dateiname}-cat.php***. Hier müssen alle Angaben in geschweiften Klammern ersetzt bzw. die entsprechende Zeile entfernt werden.

## Erzeugen der Website
Hierzu muss zunächst ein Objekt der Klasse `facetList` erzeugt werden. Bei Erzeugung des Objekts ohne Parameter werden für die Auswahl der darzustellenden Felder Standardsets angewandt. Die Sets können in der folgenden Weise überschrieben werden:

`facetList::__construct([array $pages [array $doughnuts [array $clouds]]])`
- `$pages`: Array mit Namen von Feldern, die als eigene Seite dargestellt werden sollen. Mögliche Werte: `numberCat`, `catSubjectFormat`, `shelfmarkOriginal`, `histSubject`, `persName`, `gender`, `beacon`, `year`, `subjects`, `histShelfmark`, `genres`, `languages`, `placeName`, `publishers`, `format`, `volumes`, `mediaType`, `bound`, `systemManifestation`, `institutionOriginal`, `provenanceAttribute`, `pageCat`, `titleWork`, `borrower`, `dateLending`
- `$doughnuts`: Array mit Namen von Feldern, die als Kreisdiagramm dargestellt werden sollen. Mögliche Werte: 'persName', 'gender', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'languages', 'systemManifestation', 'institutionOriginal', 'provenanceAttribute', 'bound', 'beacon'
- `$clouds`: Array mit Namen von Feldern, die als Wortwolken dargestellt werden sollen. Mögliche Werte: 'publishers', 'format', 'histSubject', 'subjects', 'genres', 'mediaType', 'persName', 'gnd', 'role', 'placeName', 'languages', 'systemManifestation', 'institutionOriginal', 'shelfmarkOriginal', 'provenanceAttribute', 'beacon', 'borrower'

Anschließend wird ein neues Objekt der Klasse `frontend` erzeugt:

`frontend::__construct($reconstruction, $facetList)`
- `$reconstruction`: Das Objekt der Klasse `reconstruction`
- `$facetList`: Das Objekt der Klasse `facetList`

Die Methode `frontend::build()` sorgt dafür, dass die Ergebnisse als statische HTML-Dateien im Verzeichnis ***projectFiles/{Dateiname}*** abgespeichert werden.
