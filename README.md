


# libreto-transform
Set of scripts for transforming library reconstruction data into reusable data formats (RDF, TEI, SOLR) and generating a frontend in static HTML

*Autor: Hartmut Beyer (beyer@hab.de)*

## Anforderungen
Die Anwendung erfordert eine Installation von PHP (getestet mit Version 5.6–7.4) und schreibenden Zugriff auf Dateien und Ordner innerhalb des Programmordners.
Unter Windows empfiehlt sich die Verwndung von XAMPP, der Programmordner muss dann unter xampp/htdocs/ liegen. Unter Linux ist es /var/www/html/. Alternativ kann LibReTo von der Kommandozeile aus benutzt werden.

## Installation
Herunterladen des Programmordners, dies kann manuell als ZIP-Datei oder auf der Kommandozeile mit `git clone https://github.com/hbeyer/libreto-transform` geschehen (entfällt bei Nutzung von Docker).

Im Ordner ***private/*** muss die Datei ***settings.php.template*** in ***settings.php*** umbenannt werden. Darin müssen folgenden Angaben stehen:
- Unter `$userGeoNames` der Login eines Accounts bei geoNames (http://www.geonames.org/login)
- Unter `$userAgentHTTP` der Name der benutzenden Person in beliebiger Form
- Unter `$impressum` die URL des Impressums, das für die Publikation der Seite gültig ist

Um eine Datenbankverbindung zu nutzen, kann analog die Datei ***connectionData.php.template*** angepasst werden. Die Datenbank folgt dem Schema in ***schema-dh.sql*** (auf Basis der Datenerfassung von D. Hakelberg).

## LibReTo auf Docker
LibReTo kann einfach mit Docker genutzt werden. Dafür muss Docker installiert sein, s. (https://www.docker.com/). Die Konfiguration der virtuellen Maschine ist in ***docker-compose.yml*** definiert. Zum Starten muss im Wurzelverzeichnis folgendes Kommando ausgeführt werden:

```bash
docker-compose up -d
```
Der Server läuft dann unter http://localhost:84/. Im Wurzelverzeichnis kann etwa ein Skript transform-myproject.php durch Aufruf von [http://localhost:84/transform-myproject.php](http://localhost:84/%7BDateiname%20Transformationsskript%7D.php) ausgeführt werden.

Die Datei ***settings.php*** muss wie unter "Installation" beschrieben angepasst werden.

Eine MySQL-Datenbank startet mit und kann unter http://localhost:85/ mit PHPMyAdmin bearbeitet werden, sofern eine Erfassung per MySQL gewünscht ist (Server: "libreto-db", User: "admin", Passwort: "testpassword"). Das in der Datenbank `libreto` geladene Schema entspricht dem Erfassungsformat 'sql_dh' (s. u.).

## Datenerfassung
Daten können in XML oder in CSV erfasst werden. Zur Anlage eines XML-Dokuments dient das Schema ***libreto-schema.xsd***. Im XML-Dokument werden sowohl die Erschließungsdaten als auch die Metadaten zur Sammlung hinterlegt. Zur Erstellung einer CSV-Datei kann das Beispieldokument ***example.csv*** (Trennzeichen ";", Zeichencodierung "Windows-1252") verwendet werden. Die Metadaten werden in diesem Fall bei der Transformation erfasst. Die Benutzung der einzelnen Felder ist im Word-Dokument ***Dokumentation_CSV.doc*** beschrieben.

## Transformation

### Codebeispiel

```php
set_time_limit(600);
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$reconstruction = new reconstruction('source/myproject.xml', 'myproject', 'xml');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

$pages = array('histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'languages', 'placeName', 'publishers');
$doughnuts = array('histSubject', 'subjects', 'beacon', 'languages');
$clouds = array('publishers', 'subjects', 'persName', 'shelfmarkOriginal');

$facetList = new facetList($pages, $doughnuts, $clouds);
$frontend = new frontend($reconstruction, $facetList);
$frontend->build();
```

### Klasse 'reconstruction'

Ein Transformationsskript kann unter Verwendung der Datei ***transform.php*** erstellt werden. Hierin muss zunächst ein Objekt der Klasse `reconstruction` erzeugt werden:

`reconstruction::__construct(string $path, string $fileName [, string $format = 'xml'])`
- `$path`: Pfad zur Ausgangsdatei mit Dateiname und -Endung.
- `$fileName`: Dateiname für das Projekt
- `$format`: Format der Ausgangsdatei. Vorgesehen sind:
	- 'csv': CSV-Datei 
	- 'xml' (Standardwert): XML-Datei, die gegen das Schema ***uploadXML.xsd*** validiert
	- 'xml_full': XML-Datei, die gegen das Schema ***libreto-schmema-full.xsd*** validiert
	- 'php': Serialisierte PHP-Daten (werden automatisch erzeugt und im Projektordner unter `dataPHP` abgelegt)
	- 'sql_dh': MySQL-Datenbank nach einem proprietären Schema. Die Zugangsdaten werden in der Datei ***private/connectionData.php*** nach der Vorlage ***connectionData.php.template*** eingetragen. Das Datenbankschema liegt unter ***schema-dh.sql***. Der Parameter `$path` kann in diese Fall beliebig gesetzt werden. Beispiel:
	
Die Methode `reconstruction::enrichData()` fügt Geodaten für Orte sowie Links zu biographischen Nachweissystemen bei Personen hinzu und vergibt IDs für Sammelbände.

Die Methode `reconstruction::insertGeoData()` fügt Geodaten für Orte hinzu.

Die Methode `reconstruction::insertBeacon()` erzeugt Links zu biographischen Nachweissystemen für Personen. 

Die Methode `reconstruction::saveAllFormats()` speichert im Ordner ***projectFiles/{Dateiname}*** die Daten in folgenden Formaten ab: CSV, XML, RDF/XML, Gephi (CSV), Turtle, TEI, SOLR-XML. Außerdem werden Geodatenblätter in CSV und KML erzeugt.

Außerdem werden die Geodaten im KML- und CSV-Format ausgegeben. Zur Erzeugung einer Kartenansicht muss die Datei `printingPlaces.csv` im Datasheet Editor (https://geobrowser.de.dariah.eu/edit/) hochgeladen und die ID der Datensammlung als `geoBrowserStorageID` bei den Metadaten eingefügt werden.

## Metadatenanreicherung (entfällt bei XML)
Wurde eine andere Option als 'xml' oder 'xml_full' bei der Erstellung des Objekts der Klasse `reconstruction` gewählt, so kommt beim Ausführen des Skripts zunächst eine Aufforderung zum Ausfüllen der Datei ***projectFiles/{Dateiname}/{Dateiname}-metadata.xml***. Hier müssen alle Angaben in geschweiften Klammern ersetzt bzw. das Element entfernt werden.

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
mediaType | Art des Sammlungsstücks (Handschrift, Druck u. a.) | ja | ja | ja
bound | Angabe ob gebunden oder nicht | ja | ja | nein
systemManifestation | Bibliographisches Nachweissystem, in das Sammlungsstück verzeichnet ist | ja | ja | ja
institutionOriginal | Institution, die das Original besitzt | ja | ja | ja
provenanceAttribute | Provenienzmerkmal des Originalexemplars, auf dem die Zuordnung zu der Sammlung beruht | ja | ja | ja
pageCat | Seite im Altkatalog, auf der das Sammlungsstück verzeichnet ist | ja | nein | nein
titleWork | Titel eines im Sammlungsstück enthaltenen Werkes | ja | nein | nein
borrower | Person, die das Sammlungsstück entliehen hat | ja | nein | nein
dateLending | Datum, an dem das Sammlungsstück entliehen wurde | ja | nein | ja

Anschließend wird ein neues Objekt der Klasse `frontend` erzeugt:

`frontend::__construct($reconstruction, $facetList)`
- `$reconstruction`: Das Objekt der Klasse `reconstruction`
- `$facetList`: Das Objekt der Klasse `facetList`

Die Methode `frontend::build($maxLen = 100)` sorgt dafür, dass die Ergebnisse als statische HTML-Dateien im Verzeichnis ***projectFiles/{Dateiname}*** abgespeichert werden. Der Parameter `maxLen` legt fest, wie viele Einträge maximal auf einer Seite zu sehen sein sollen. Wird null übergeben, erscheinen alle Einträge auf einer Seite.

## Erzeugung einer biographischen Karte
Mit der Methode `reconstruction::makeBioDataSheet()` kann zusätzlich ein Geodatenblatt zu den als AutorInnen oder BeiträgerInnen in der Sammlung enthaltenen und mit GND-Nummer versehenen Personen erzeugt werden (derzeit nur in CSV). Hierzu werden die GND-Normdatensätze geladen und unter ***cache/gnd*** vorgehalten (zum Auffrischen Ordner leeren). Für jede Nennung einer Person wird ein Ort und ein Datum (bevorzugt Geburtsort und -jahr) wiedergegeben. Die Geodaten werden aus der GND geladen. Von Geographika ohne Geodaten wird der bevorzugte Name angegeben, dieser kann dann mit dem GeoDataSheetEditor von DARIAH-DE ergänzt oder manuell nachgetragen werden. Die Datei wird wird im Projektordner unter ***bioPlaces.csv*** abgelegt.

## Direktimport von Daten über die SRU-Schnittstelle
Anstelle der manuellen Datenerfassung in CSV oder XML können die Daten auch über die SRU-Schnittstelle aus einem Bibliothekskatalog geladen werden. Voraussetzung ist, dass die Schnittstelle das Format picaxml unterstützt.
Hierfür wird ein Objekt der Klasse `reconstruction_sru` mit den folgenden Parametern erzeugt:

- `$query`: Die Abfrage in PICA-Syntax. Jedem Suchschlüssel muss "pica." vorangestellt werden. Leerzeichen müssen durch "+" codiert werden.
- `$fileName`: Der Dateiname für das Projekt
- Optional: `$sru`. Die Adresse der SRU-Schnittstelle, sofern nicht die des Gemeinsamen Verbundkatalogs (http://sru.k10plus.de/gvk) abgefragt werden soll. Zu den vorhandenen Schnittstellen s. https://wiki.k10plus.de/display/K10PLUS/Datenbanken und http://uri.gbv.de/database/
- Optional: `$bib`. Der Name der Bibliothek, deren Signaturen eingebunden werden sollen. Sofern der Bibliotheksname in PICA-Feld `209A $f` steht und der Parameter `$regexSig` nicht gesetzt ist, wird von der genannten Bibliothek jeweils die erste Signatur übernommen.
- Optional: `$regexSig`. Ein regulärer Ausdruck, der auf alle Signaturen, die als Originalexemplar angezeigt werden sollen, matcht. Hiervon wird in einem Datensatz jeweils die erste übernommen. Bei Verwendung sollte im Parameter `$bib` der Bibliotheksname stehen.

Die geladenen XML-Daten werden unter ***cache/pica*** zwischengespeichert. Nach Änderungen an den Katalogisaten muss dieser Ordner geleert werden.

### Beispiel

```php
set_time_limit(600);
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$reconstruction = new reconstruction_sru('pica.exk=sammlung+hardt+and+pica.bbg=(aa*+or+af*)', 'hardt', null, 'Herzog August Bibliothek Wolfenbüttel', 'M: Li 5530 Slg');

$facetList = new facetList();
$frontend = new frontend($reconstruction, $facetList);
$frontend->build();
```

