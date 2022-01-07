<?php

//Fremdcode von http://php.net/manual/de/function.copy.php
function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function zipFolderContent($folder, $fileName) {
	$zip = new ZipArchive;
	$zipFile = $folder.'/'.$fileName.'.zip';
	if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
		die('cannot open '.$fileName);
	}	
	$options = array('add_path' => $fileName.'/', 'remove_all_path' => true);
	$optionsCSS = array('add_path' => $fileName.'/assets/css/', 'remove_all_path' => true);
	$optionsFonts = array('add_path' => $fileName.'/assets/fonts/', 'remove_all_path' => true);
	$optionsJS = array('add_path' => $fileName.'/assets/js/', 'remove_all_path' => true);
	$zip->addGlob($folder.'/assets/css/*.css', 0, $optionsCSS);
	$zip->addGlob($folder.'/assets/fonts/*', 0, $optionsFonts);
	$zip->addGlob($folder.'/assets/js/*.js', 0, $optionsJS);
	$zip->addGlob($folder.'/*.htm', 0, $options);
	$zip->addGlob($folder.'/*.x*', 0, $options);
	$zip->addGlob($folder.'/*.js', 0, $options);
	$zip->addGlob($folder.'/*.php', 0, $options);
	$zip->addGlob($folder.'/*.c*', 0, $options);
	$zip->addGlob($folder.'/*.ttl', 0, $options);
	$zip->addGlob($folder.'/*.rdf', 0, $options);
	$zip->addGlob($folder.'/*.kml', 0, $options);
	$zip->addFile($folder.'/dataPHP', $fileName.'/dataPHP');

	$zip->close();
}

function validateFileName($name) {
	if (!preg_match('~^[a-z\d-]{3,40}$~', $name)) {
		return(false);
	}
	if (in_array($name, ['libreto', 'libreto-transform', 'libreto-search', 'assets'])) {
		return(false);
	}
	return(true);
}

/*
// Die Funktion ersetzt kombinierende diakritische Zeichen (hier nicht als solche erkennbar) durch HMLT-Entities, um die versetzte Darstellung der Punkte in Firefox zu beheben.
function replaceUml($string) {
	$translate = array('Ä' => '&Auml;', 'Ö' => '&Ouml;', 'Ü' => '&Uuml;', 'ä' => '&auml;', 'ö' => '&ouml;', 'ü' => '&uuml;', 'ë' => '&euml;');
	$string = strtr($string, $translate);
	return($string);
}
*/

?>
