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

`reconstruction::__construct(string $path, string $fileName [, string $format = 'xml'])`
- `$path`: Pfad zur Ausgangsdatei mit Dateiname und -Endung.
- `$fileName`: Dateiname für das Projekt
- `$format`: Format der Ausgangsdatei. Vorgesehen sind:
	- 'csv': CSV-Datei 
	- 'xml' (Standardwert): XML-Datei, die gegen das Schema `uploadXML.xsd` validiert
	- 'xml_full': XML-Datei, die gegen das Schema `libreto-schmema-full.xsd` validiert
	- 'php': Serialisierte PHP-Daten (werden automatisch erzeugt und im Projektordner unter `dataPHP` abgelegt)
	- 'sql_dh': MySQL-Datenbank nach einem proprietären Schema. Die Zugangsdaten werden in der Datei `private/connectionData.php` nach der Vorlage `connectionData.php.template` eingetragen. Das Datenbankschema liegt unter `schema-dh.sql`

Die Methode `reconstruction::enrichData()` fügt Geodaten für Orte sowie Links zu biographischen Nachweissystemen bei Personen hinzu und vergibt IDs für Sammelbände.

Die Methode `reconstruction::insertGeoData()` fügt Geodaten für Orte hinzu.

Die Methode `reconstruction::insertBeacon()` erzeugt Links zu biographischen Nachweissystemen für Personen. 

Die Methode `reconstruction::saveAllFormats()` speichert im Ordner ***projectFiles/{Dateiname}*** die Daten in folgenden Formaten ab: CSV, XML, RDF/XML, Turtle, TEI, SOLR-XML. Außerdem werden Geodatenblätter in CSV und KML erzeugt.

Außerdem werden die Geodaten im KML- und CSV-Format ausgegeben. Zur Erzeugung einer Kartenansicht muss die Datei `printingPlaces.csv` im Datasheet Editor (https://geobrowser.de.dariah.eu/edit/) hochgeladen und die ID der Datensammlung als `geoBrowserStorageID` bei den Metadaten eingefügt werden.

## Metadatenanreicherung (entfällt bei XML)
Wurde eine andere Option als 'xml' bei der Erstellung des Objekts von der Klassse `reconstruction` gewählt, so kommt beim Ausführen des Skripts zunächst eine Aufforderung zum Ausfüllen der Datei ***projectFiles/{Dateiname}/{Dateiname}-cat.php***. Hier müssen alle Angaben in geschweiften Klammern ersetzt bzw. die entsprechende Zeile entfernt werden.

## Erzeugen der Website
Hierzu muss zunächst ein Objekt der Klasse `facetList` erzeugt werden. Bei Erzeugung des Objekts ohne Parameter werden für die Auswahl der darzustellenden Felder Standardsets angewandt. Die Sets können in der folgenden Weise überschrieben werden:

`facetList::__construct([array $pages [, array $doughnuts [, array $clouds]]])`

Übergeben wird in $pages ein Array mit Feldnamen, die auf eigenen Seiten dargestellt werden sollen. In $doughnuts können analog Felder für Kreisdiagramme angegeben werden, in $clouds Felder für Wortwolken. Welche Felder für welche Visualisierung zugelassen sind, verdeutlicht die folgende Aufstellung.

Feld | Bedeutung | Eigene Seite | Kreisdiagramm | Wortwolke
-----|-----------|--------------|---------------|----------
numberCat | Nummer des Eintrags im Altkatalog | ja | nein | nein
catSubjectFormat | Darstellung nach Rubrik, darunter nach Format | ja | nein | nein
shelfmarkOriginal | Heutige Signatur des Originals | ja | nein | ja
histSubject | Rubrik im Altkatalog | ja | ja | ja
persName | Namen von VerfasserInnen oder Beteiligten | ja | nein | ja
gender | Geschlecht von VerfasserInnen oder Beteiligten  | ja | ja | nein
beacon | Biographische Nachweissysteme, in den VerfasserInnen oder Beteiligte erscheinen | ja | ja | nein
year | Datierung des Sammlungsstückes | ja | nein | nein
subjects | Sachbegriffe, die den Inhalt des Sammlungsstücks beschreiben | ja | ja | ja
histShelfmark | Signatur, die das Sammlungsstück in seinem ursprünglichen Kontext hatte | ja | nein | nein
genres | Gattungsbegriff, der den Inhalt des Sammlungsstücks charakterisiert | ja | ja | ja
languages | Sprache | ja | ja | ja
placeName | Erscheinungs- oder Entstehungsort des Sammlungsstücks | ja | nein | ja
publishers | DruckerInnen oder VerlegerIn | ja | nein | ja
format | Bibliographisches Format | ja | ja | nein
volumes | Zahl der Bände | ja | nein | nein
mediaType | Art des Sammlungsstück (Handschrift, Druck u. a.) | ja | ja | ja
bound | Angabe ob gebunden oder nicht | ja | ja | nein
systemManifestation | Bibliographisches Nachweissystem, in das Sammlungsstück verzeichnet ist | ja | ja | ja
institutionOriginal | Institution, die das Original besitzt | ja | ja | ja
provenanceAttribute | Provenienzmerkmal des Originalexemplars, auf dem die Zuordnung zu der Sammlung beruht | ja | ja | ja
pageCat | Seite im Altkatalog, auf dem das Sammlungsstück verzeichnet ist | ja | nein | nein
titleWork | Titel eines im Sammlungsstück enthaltenen Werkes | ja | nein | nein
borrower | Person, die das Sammlungsstück entliehen hat | ja | nein | nein
dateLending | Beschreibung | ja | nein | ja

Anschließend wird ein neues Objekt der Klasse `frontend` erzeugt:

`frontend::__construct($reconstruction, $facetList)`
- `$reconstruction`: Das Objekt der Klasse `reconstruction`
- `$facetList`: Das Objekt der Klasse `facetList`

Die Methode `frontend::build()` sorgt dafür, dass die Ergebnisse als statische HTML-Dateien im Verzeichnis ***projectFiles/{Dateiname}*** abgespeichert werden.

## Erzeugung einer biographischen Karte
Mit der Methode `reconstruction::makeBioSheet()` kann zusätzlich ein Geodatenblatt zu den als AutorInnen oder BeiträgerInnen in der Sammlung enthaltenen und mit GND-Nummer versehenen Personen erzeugt werden (derzeit nur in CSV). Hierzu werden die GND-Normdatensätze geladen und unter ***cache/gnd*** vorgehalten (zum Auffrischen Ordner leeren). Für jede Nennung einer Person wird ein Ort und ein Datum (bevorzugt Geburtsort und -jahr) wiedergegeben. Die Geodaten werden aus der GND geladen. Von Geographika ohne Geodaten wird der bevorzugte Name angegeben, dieser kann dann mit dem GeoDataSheetEditor von DARIAH-DE ergänzt oder manuell nachgetragen werden. Die Datei wird wird im Projektordner unter ***bioPlaces.csv*** abgelegt.
