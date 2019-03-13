# libreto-transform
Set of scripts for transforming library reconstruction data into reusable data formats (RDF, TEI, SOLR)

*Autor: Hartmut Beyer (beyer@hab.de)*

## Anforderungen
Die Anwendung erfordert einen Server mit PHP (getestet mit Version 7.0.33) und schreibenden Zugriff auf Dateien und Ordner innerhalb des Programmordners.

## Installation
Kopieren Sie alle Dateien in einen Ordner auf dem Server.
Benennen Sie im Ordner private/ die Datei **settings.php.template** um in **settings.php** und tragen Sie die folgenden Angaben ein:
- Unter `$userGeoNames` der Login Ihres Accounts bei geoNames (http://www.geonames.org/login)
- Unter `$userAgentHTTP` Ihren Namen in beliebiger Form
- Unter `$impressum` die URL des Impressums, das für die Publikation der Seite gültig ist

