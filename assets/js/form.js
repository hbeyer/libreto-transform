function checkFormat(path) {
	ending = path.split(".").pop();
	allowed = ["csv", "xml"]
	if (allowed.includes(ending)) {
		adjustSelection(ending);
	}
	else {
		adjustSelection("");
	}
	return(true);
}

function adjustSelection(ending) {
	if (ending == "xml") {
		document.getElementById("optionCSV").style.display = "none";
		document.getElementById("optionSQL").style.display = "none";
		document.getElementById("optionPHP").style.display = "none";
		document.getElementById("optionXML").style.display = "block";
		document.getElementById("optionXMLFull").style.display = "block";
		return(true);
	}
	if (ending == "csv") {
		document.getElementById("optionXML").style.display = "none";
		document.getElementById("optionXMLFull").style.display = "none";
		document.getElementById("optionSQL").style.display = "none";
		document.getElementById("optionPHP").style.display = "none";
		document.getElementById("optionCSV").style.display = "block";
		return(true);
	}
	if (ending == "") {
		document.getElementById("optionCSV").style.display = "none";
		document.getElementById("optionXML").style.display = "none";
		document.getElementById("optionXMLFull").style.display = "none";
		document.getElementById("optionPHP").style.display = "block";
		document.getElementById("optionSQL").style.display = "block";
		return(true);
	}
	return(false);
}

function loadMetadata(fileName) {
	pathXML = "projectFiles/" + fileName + "/" + fileName + ".xml";
	$.ajax({
		url:pathXML,
		type:'HEAD',
		error: function()
		{
			clearMetadata();
			adjustMetaForm();
		},
		success: function()
		{
			loadXML(pathXML);
		}
	});
	return(true);
}

function loadXML(path) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			insertMetadata(this);
		}
	};
	xhttp.open("GET", path, true);
	xhttp.send();
	return(true);
}

function insertMetadata(xml) {
	var i;
	var xmlDoc = xml.responseXML;
	var md = xmlDoc.getElementsByTagName("metadata")[0];
	numnodes = md.childNodes.length;
	for (i=0; i<numnodes; i++) {
		name = md.childNodes[i].nodeName;
		if (name == "#text") {
			continue;
		}
		value = md.childNodes[i].textContent;
		document.getElementById(name).value = value;
	}
}

function clearMetadata() {
	fields = getMetaFields();
	for (i=0; i<fields.length; i++) {
		document.getElementById(fields[i]).value = "";
	}
	return(true);
}

function adjustMetaForm() {
	var format = getSelectedFormat();
	var xfor = ["XML", "XML (erweitert)"];
	if (xfor.includes(format)) {
		document.getElementById("metadata_info").innerHTML = "<div class=\"well\">Hinweis: Die Metadaten werden bei diesem Format aus der Quelldatei gelesen. Sie müssen daher nicht händisch eingegeben werden.</div>";
		return(1);
	}
	document.getElementById("metadata_info").innerHTML = "";
	/*
	if (xfor.includes(format)) {
		document.getElementById("metadata_form").innerHTML = "<label  class=\"control-label col-sm-3\">Hinweis</label><div class=\"col-sm-9\">Die Metadaten werden bei diesem Format aus der Ausgangsdatei geladen. Wenn das einmal geschehen ist, können sie über das Formular angepasst werden.</div>";
	}
	else {
		var orig = getOriginalMetaForm();
		document.getElementById("metadata_form").innerHTML = orig;		
	}
	*/
	return(0);
}

function getSelectedFormat() {
	var e = document.getElementById("format_file");
	var value = e.value;
	var text = e.options[e.selectedIndex].text;
	return(text);
}

function getMetaFields() {
	return(["owner", "ownerGND", "heading", "year", "title", "placeCat", "printer", "institution", "shelfmark", "base", "description", "creatorReconstruction", "yearReconstruction", "geoBrowserStorageID"]);
}

/*
function getOriginalMetaForm() {
	return('<label  class=\"control-label col-sm-3\" for=\"owner\" maxlength=\"160\">Eigentümer</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"owner\" /><p class=\"form-text text-muted\">Historischer Eigentümer der rekonstruierten Bibliothek</p></div><label  class=\"control-label col-sm-3\" for=\"ownerGND\">GND</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"ownerGND\" pattern=\"[0-9-X]{8,12}\" maxlength=\"12\"/><p class=\"form-text text-muted\">GND-Nummer des Eigentümers</p></div><label  class=\"control-label col-sm-3\" for=\"heading\">Bibliotheksname</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"heading\" maxlength=\"160\" required /><p class=\"form-text text-muted\">Bezeichnung der rekonstruierten Bibliothek, z. B. &bdquo;Bibliothek von Hartmann Schedel&rdquo;</p></div><label  class=\"control-label col-sm-3\" for=\"year\">Jahr</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"year\" pattern=\"[12]?[0-9]{3}\" maxlength=\"4\" required /><p class=\"form-text text-muted\">Stichjahr der Bibliotheksrekonstruktion, bei Katalogen: Erscheinungsjahr</p></div><label  class=\"control-label col-sm-3\" for=\"title\">Titel Katalog</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"title\" maxlength=\"400\" /><p class=\"form-text text-muted\">Titel des handschriftlichen oder gedruckten Katalogs, wenn vorhanden</p></div><label  class=\"control-label col-sm-3\" for=\"placeCat\">Erscheinungsort Katalog</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"placeCat\" maxlength=\"80\" /><p class=\"form-text text-muted\">Erscheinungs- oder Entstehungsort des Katalogs, wenn vorhanden</p></div><label  class=\"control-label col-sm-3\" for=\"printer\">Drucker</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"printer\" maxlength=\"160\" /><p class=\"form-text text-muted\">Drucker/Verleger des Katalogs, wenn vorhanden</p></div><label  class=\"control-label col-sm-3\" for=\"institution\">Institution</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"institution\" maxlength=\"160\" /><p class=\"form-text text-muted\">Institution, die den Katalog besitzt, wenn vorhanden</p></div><label  class=\"control-label col-sm-3\" for=\"shelfmark\" maxlength=\"80\">Signatur</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"shelfmark\" /><p class=\"form-text text-muted\">Signatur des Katalogs, wenn vorhanden</p></div><label  class=\"control-label col-sm-3\" for=\"base\">Digitalisat</label><div class=\"col-sm-9\"><input type=\"url\" class=\"form-control\" id=\"base\" maxlength=\"160\" /><p class=\"form-text text-muted\">Pfad für das Digitalisat das Altkatalogs. Die Image-Nummer wird durch {No} wiedergegeben, fehlt das, wird sie angehängt.</p></div><label  class=\"control-label col-sm-3\" for=\"description\">Abstract</label><div class=\"col-sm-9\"><textarea class=\"form-control\" rows=\"3\" id=\"description\" maxlength=\"4000\"></textarea><p class=\"form-text text-muted\">Beschreibung der rekonstruierten Bibliothek</p></div><label  class=\"control-label col-sm-3\" for=\"creatorReconstruction\">Verantwortliche Rekonstruktion</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"creatorReconstruction\" maxlength=\"160\" required /><p class=\"form-text text-muted\">Für die Rekonstruktion verantwortliche Person oder Personen (Freitextfeld)</p></div><label  class=\"control-label col-sm-3\" for=\"yearReconstruction\">Jahr Rekonstruktion</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"yearReconstruction\" pattern=\"20[0-9]{2}\" maxlength=\"4\" required /><p class=\"form-text text-muted\">Jahr der Rekonstruktion</p></div><label  class=\"control-label col-sm-3\" for=\"geoBrowserStorageID\">ID GeoBrowser</label><div class=\"col-sm-9\"><input type=\"text\" class=\"form-control\" id=\"geoBrowserStorageID\" maxlength=\"160\" /><p class=\"form-text text-muted\">ID der zugehörigen Geodaten beim DARIAH-GeoBrowser (die Datei &bdquo;printingPlaces.csv&rdquo; muss in einem ersten Durchgang erzeugt und dort manuell hochgeladen werden)</p></div>');
}
*/