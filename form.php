<!DOCTYPE html>
<html>
	<head>
		<title>LibReTo Transformation</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="assets/css/affix.css" />
        <link rel="stylesheet" href="assets/css/proprietary.css" />
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.png" />
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/proprietary.js"></script>
        <script src="assets/js/form.js"></script>
	</head>
	<body>
<?php $active = basename(__FILE__, '.php');
		require __DIR__ .'/vendor/autoload.php';
		require __DIR__ .'/functions/encode.php';
		include('templates/user_interface/navigation.phtml');
?>
	<div class="container" style="min-height:1000px;margin-top:80px;">
		<div class="row">
			<h2>Transformation einer Datensammlung</h2>
<?php if(isset($_POST["fileName"])): ?>
			<!--
			<div class="well">
				<?php var_dump($_POST); ?>
			</div>
			-->
<?php
	$action = new FormAction($_POST);
?>
	<div class="well">
		<?php echo $action->message; ?>
	</div>
<?php endif; ?>
			<form method="post" class="form-horizontal" action="form.php">

				<div class="panel panel-default">
					<div class="panel-heading">Datenquelle</div>
					<div class="panel-body">

						<div class="form-group">
							<div class="formrow">
								<label class="control-label col-sm-3" for="path_file">Pfad zur Datei</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="path_file" name="path_file" maxlength="400" onchange="javascript:checkFormat(this.value)">
									<p class="form-text text-muted">Es kann eine URL oder ein relativer oder absoluter Pfad auf dem lokalen Server übergeben werden.</p>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="formrow">
								<label class="control-label col-sm-3" for="format_file">Format</label>
								<div class="col-sm-2">
									<select class="form-control" id="format_file" name="format_file">
										<option value="csv" id="optionCSV">CSV</option>
										<option value="xml" id="optionXML">XML</option>
										<option value="xml_full" id="optionXMLFull">XML (erweitert)</option>
										<option value="php" id="optionPHP">PHP</option>
										<option value="sql_dh" id="optionSQL">SQL</option>
									</select>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">Metadaten</div>
					<div class="panel-body">

						<span id="metadata_info"></span>

						<label  class="control-label col-sm-3" for="fileName">Dateiname</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="fileName" name="fileName" name="fileName" pattern="[a-z\-]+" maxlength="45" required onchange="javascript:loadMetadata(this.value)" />
							<p class="form-text text-muted">Name für den Projektordner (nur Kleinbuchstaben und Bindestrich erlaubt)</p>
						</div>

						<div id="metadata_form">
							<label  class="control-label col-sm-3" for="owner" maxlength="160">Eigentümer</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="owner" name="owner" />
								<p class="form-text text-muted">Historischer Eigentümer der rekonstruierten Bibliothek</p>
							</div>

							<label  class="control-label col-sm-3" for="ownerGND">GND</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="ownerGND" name="ownerGND" pattern="[0-9-X]{8,12}" maxlength="12"/>
								<p class="form-text text-muted">GND-Nummer des Eigentümers</p>
							</div>

							<label  class="control-label col-sm-3" for="heading">Bibliotheksname</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="heading" name="heading" maxlength="160" />
								<p class="form-text text-muted">Bezeichnung der rekonstruierten Bibliothek, z. B. &bdquo;Bibliothek von Hartmann Schedel&rdquo;</p>
							</div>

							<label  class="control-label col-sm-3" for="year">Jahr</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="year" name="year" pattern="[12]?[0-9]{3}" maxlength="4" />
								<p class="form-text text-muted">Stichjahr der Bibliotheksrekonstruktion, bei Katalogen: Erscheinungsjahr</p>
							</div>

							<label  class="control-label col-sm-3" for="title">Titel Katalog</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="title" name="title" maxlength="400" />
								<p class="form-text text-muted">Titel des handschriftlichen oder gedruckten Katalogs, wenn vorhanden</p>
							</div>

							<label  class="control-label col-sm-3" for="placeCat">Erscheinungsort Katalog</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="placeCat" name="placeCat" maxlength="80" />
								<p class="form-text text-muted">Erscheinungs- oder Entstehungsort des Katalogs, wenn vorhanden</p>
							</div>

							<label  class="control-label col-sm-3" for="printer">Drucker</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="printer" name="printer" maxlength="160" />
								<p class="form-text text-muted">Drucker/Verleger des Katalogs, wenn vorhanden</p>
							</div>

							<label  class="control-label col-sm-3" for="institution">Institution</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="institution" name="institution" maxlength="160" />
								<p class="form-text text-muted">Institution, die den Katalog besitzt, wenn vorhanden</p>
							</div>

							<label  class="control-label col-sm-3" for="shelfmark" maxlength="80">Signatur</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="shelfmark" name="shelfmark" />
								<p class="form-text text-muted">Signatur des Katalogs, wenn vorhanden</p>
							</div>

							<label  class="control-label col-sm-3" for="base">Digitalisat</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="base" name="base" maxlength="160" />
								<p class="form-text text-muted">Pfad für das Digitalisat das Altkatalogs. Die Image-Nummer wird durch {No} wiedergegeben, fehlt das, wird sie angehängt.</p>
							</div>

							<label  class="control-label col-sm-3" for="description">Abstract</label>
							<div class="col-sm-9">
								<textarea class="form-control" rows="3" id="description" name="description" maxlength="4000"></textarea>
								<p class="form-text text-muted">Beschreibung der rekonstruierten Bibliothek</p>
							</div>

							<label  class="control-label col-sm-3" for="creatorReconstruction">Verantwortliche Rekonstruktion</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="creatorReconstruction" name="creatorReconstruction" maxlength="160" />
								<p class="form-text text-muted">Für die Rekonstruktion verantwortliche Person oder Personen (Freitextfeld)</p>
							</div>

							<label  class="control-label col-sm-3" for="yearReconstruction">Jahr Rekonstruktion</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="yearReconstruction" name="yearReconstruction" pattern="20[0-9]{2}" maxlength="4" />
								<p class="form-text text-muted">Jahr der Rekonstruktion</p>
							</div>

							<label  class="control-label col-sm-3" for="geoBrowserStorageID">ID GeoBrowser</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="geoBrowserStorageID" name="geoBrowserStorageID" maxlength="160" />
								<p class="form-text text-muted">ID der zugehörigen Geodaten beim DARIAH-GeoBrowser (die Datei &bdquo;printingPlaces.csv&rdquo; muss in einem ersten Durchgang erzeugt und dort manuell hochgeladen werden)</p>
							</div>
						</div>

					</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">Darzustellende Felder</div>
						<div class="panel-body">

							<label class="control-label col-sm-3" for="pages">Eigene Seite</label>
							<div class="col-sm-9">
								<label class="checkbox-inline" for="page_histSubject"><input type="checkbox" id="page_histSubject" name="page_histSubject" value="yes" checked />Rubrik</label>
								<label class="checkbox-inline" for="page_persName"><input type="checkbox" id="page_persName" name="page_persName" value="yes" checked />Personen</label>
								<label class="checkbox-inline" for="page_year"><input type="checkbox" id="page_year" name="page_year" value="yes" />Jahr</label>
								<label class="checkbox-inline" for="page_subjects"><input type="checkbox" id="page_subjects" name="page_subjects" value="yes" />Inhalt</label>
								<label class="checkbox-inline" for="page_genres"><input type="checkbox" id="page_genres" name="page_genres" value="yes" />Gattung</label>
								<label class="checkbox-inline" for="page_languages"><input type="checkbox" id="page_languages" name="page_languages" value="yes" />Sprache</label>
								<label class="checkbox-inline" for="page_placeName"><input type="checkbox" id="page_placeName" name="page_placeName" value="yes" />Ort</label>
								<label class="checkbox-inline" for="page_publishers"><input type="checkbox" id="page_publishers" name="page_publishers" value="yes" />Drucker/Verleger</label>

								<label class="checkbox-inline" for="page_format"><input type="checkbox" id="page_format" name="page_format" value="yes" />Format</label>
								<label class="checkbox-inline" for="page_mediaType"><input type="checkbox" id="page_mediaType" name="page_mediaType" value="yes" />Medientyp</label>
								<label class="checkbox-inline" for="page_systemManifestation"><input type="checkbox" id="page_systemManifestation" name="page_systemManifestation" value="yes" />Nachweissystem</label>
								<label class="checkbox-inline" for="page_numberCat"><input type="checkbox" id="page_numberCat" name="page_numberCat" value="yes" />Katalognummer</label>
								<label class="checkbox-inline" for="page_catSubjectFormat"><input type="checkbox" id="page_catSubjectFormat" name="page_catSubjectFormat" value="yes" />Rubrik und Format</label>
								<label class="checkbox-inline" for="page_shelfmarkOriginal"><input type="checkbox" id="page_shelfmarkOriginal" name="page_shelfmarkOriginal" value="yes" />Signatur</label>
								<label class="checkbox-inline" for="page_gender"><input type="checkbox" id="page_gender" name="page_gender" value="yes" />Gender</label>
								<label class="checkbox-inline" for="page_beacon"><input type="checkbox" id="page_beacon" name="page_beacon" value="yes" />Personenprofil</label>
								<label class="checkbox-inline" for="page_histShelfmark"><input type="checkbox" id="page_histShelfmark" name="page_histShelfmark" value="yes" />Altsignatur</label>
								<label class="checkbox-inline" for="page_volumes"><input type="checkbox" id="page_volumes" name="page_volumes" value="yes" />Bände</label>
								<label class="checkbox-inline" for="page_bound"><input type="checkbox" id="page_bound" name="page_bound" value="yes" />Gebunden</label>
								<label class="checkbox-inline" for="page_institutionOriginal"><input type="checkbox" id="page_institutionOriginal" name="page_institutionOriginal" value="yes" />Besitzende Institution</label>
								<label class="checkbox-inline" for="page_provenanceAttribute"><input type="checkbox" id="page_provenanceAttribute" name="page_provenanceAttribute" value="yes" />Provenienzmerkmal</label>
								<label class="checkbox-inline" for="page_pageCat"><input type="checkbox" id="page_pageCat" name="page_pageCat" value="yes" />Seite im Altkatalog</label>
								<label class="checkbox-inline" for="page_titleWork"><input type="checkbox" id="page_titleWork" name="page_titleWork" value="yes" />Werktitel</label>
								<label class="checkbox-inline" for="page_borrower"><input type="checkbox" id="page_borrower" name="page_borrower" value="yes" />Entleihende Person</label>
								<label class="checkbox-inline" for="page_dateLending"><input type="checkbox" id="page_dateLending" name="page_dateLending" value="yes" />Leihdatum</label>
							</div>

							<label class="control-label col-sm-3" for="pages">WordCloud</label>
							<div class="col-sm-9">
								<label class="checkbox-inline" for="cloud_persName"><input type="checkbox" id="cloud_persName" name="cloud_persName" value="yes" checked />Personen</label>
								<label class="checkbox-inline" for="cloud_publishers"><input type="checkbox" id="cloud_publishers" name="cloud_publishers" value="yes" checked />Drucker/Verleger</label>
								<label class="checkbox-inline" for="cloud_subjects"><input type="checkbox" id="cloud_subjects" name="cloud_subjects" value="yes" checked />Inhalt</label>
								<label class="checkbox-inline" for="cloud_genres"><input type="checkbox" id="cloud_genres" name="cloud_genres" value="yes" checked />Gattung</label>
								<label class="checkbox-inline" for="cloud_placeName"><input type="checkbox" id="cloud_placeName" name="cloud_placeName" value="yes" checked />Orte</label>
								<label class="checkbox-inline" for="cloud_histSubject"><input type="checkbox" id="cloud_histSubject" name="cloud_histSubject" value="yes" />Rubrik</label>
								<label class="checkbox-inline" for="cloud_mediaType"><input type="checkbox" id="cloud_mediaType" name="cloud_mediaType" value="yes" />Medientyp</label>
								<label class="checkbox-inline" for="cloud_format"><input type="checkbox" id="cloud_format" name="cloud_format" value="yes" />Format</label>
								<label class="checkbox-inline" for="cloud_gnd"><input type="checkbox" id="cloud_gnd" name="cloud_gnd" value="yes" />GND-Nummern</label>
								<label class="checkbox-inline" for="cloud_role"><input type="checkbox" id="cloud_role" name="cloud_role" value="yes" />Rolle</label>
								<label class="checkbox-inline" for="cloud_languages"><input type="checkbox" id="cloud_languages" name="cloud_languages" value="yes" />Sprache</label>
								<label class="checkbox-inline" for="cloud_systemManifestation"><input type="checkbox" id="cloud_systemManifestation" name="cloud_systemManifestation" value="yes" />Nachweissystem</label>
								<label class="checkbox-inline" for="cloud_institutionOriginal"><input type="checkbox" id="cloud_institutionOriginal" name="cloud_institutionOriginal" value="yes" />Besitzende Institution</label>
								<label class="checkbox-inline" for="cloud_shelfmarkOriginal"><input type="checkbox" id="cloud_shelfmarkOriginal" name="cloud_shelfmarkOriginal" value="yes" />Signatur</label>
								<label class="checkbox-inline" for="cloud_provenanceAttribute"><input type="checkbox" id="cloud_provenanceAttribute" name="cloud_provenanceAttribute" value="yes" />Provenienzmerkmal</label>
								<label class="checkbox-inline" for="cloud_beacon"><input type="checkbox" id="cloud_beacon" name="cloud_beacon" value="yes" />Personenprofil</label>
								<label class="checkbox-inline" for="cloud_borrower"><input type="checkbox" id="cloud_borrower" name="cloud_borrower" value="yes" />Entleiher</label>
							</div>

							<label class="control-label col-sm-3" for="pages">Kreisdiagramm</label>
							<div class="col-sm-9">
								<label class="checkbox-inline" for="doughnut_histSubject"><input type="checkbox" id="doughnut_histSubject" name="doughnut_histSubject" value="yes" checked />Rubrik</label>
								<label class="checkbox-inline" for="doughnut_format"><input type="checkbox" id="doughnut_format" name="doughnut_format" value="yes" checked />Format</label>
								<label class="checkbox-inline" for="doughnut_gender"><input type="checkbox" id="doughnut_gender" name="doughnut_gender" value="yes" checked />Gender</label>
								<label class="checkbox-inline" for="doughnut_subjects"><input type="checkbox" id="doughnut_subjects" name="doughnut_subjects" value="yes" checked />Inhalt</label>
								<label class="checkbox-inline" for="doughnut_genres"><input type="checkbox" id="doughnut_genres" name="doughnut_genres" value="yes" checked />Gattung</label>
								<label class="checkbox-inline" for="doughnut_mediaType"><input type="checkbox" id="doughnut_mediaType" name="doughnut_mediaType" value="yes" checked />Medientyp</label>
								<label class="checkbox-inline" for="doughnut_languages"><input type="checkbox" id="doughnut_languages" name="doughnut_languages" value="yes" checked />Sprache</label>
								<label class="checkbox-inline" for="doughnut_systemManifestation"><input type="checkbox" id="doughnut_systemManifestation" name="doughnut_systemManifestation" value="yes" checked />Nachweissystem</label>
								<label class="checkbox-inline" for="doughnut_beacon"><input type="checkbox" id="doughnut_beacon" name="doughnut_beacon" value="yes" checked />Personenprofil</label>
								<label class="checkbox-inline" for="doughnut_persName"><input type="checkbox" id="doughnut_persName" name="doughnut_persName" value="yes" />Personen</label>
								<label class="checkbox-inline" for="doughnut_institutionOriginal"><input type="checkbox" id="doughnut_institutionOriginal" name="doughnut_institutionOriginal" value="yes" />Besitzende Institution</label>
								<label class="checkbox-inline" for="doughnut_provenanceAttribute"><input type="checkbox" id="doughnut_provenanceAttribute" name="doughnut_provenanceAttribute" value="yes" />Provenienzmerkmal</label>
								<label class="checkbox-inline" for="doughnut_bound"><input type="checkbox" id="doughnut_bound" name="doughnut_bound" value="yes" />Gebunden</label>
							</div>

						</div>
					</div>
					<input type="hidden" name="execute" value="yes">
				<button type="submit" class="btn btn-default" onclick="javascript:return confirm('Transformation mit diesen Daten beginnen?');">Abschicken</button>
			</form>
		</div>
	</div>
	</div>
        <footer class="container-fluid">
            <p>
                <a href="index.php" style="color:white">Start</a>&nbsp;&nbsp;
                <a href="https://www.hab.de" style="color:white" target="_blank">HAB</a>&nbsp;&nbsp;
                <a href="http://www.mww-forschung.de" style="color:white" target="_blank">MWW</a>&nbsp;&nbsp;
            </p>
        </footer>
	</body>
</html>
