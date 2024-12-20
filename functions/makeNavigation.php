﻿<?php

function makeNavigation($catalogue, $tocs, $facet) {
	/*$tocs is an associative array of arrays created by the function makeToC,
	the index of which is the field the method index::construct() used for the index categories
	$facet is the field used for the actual page
	*/
	ob_start();
	include 'templates/navigation.phtml';
	$return = ob_get_contents();
	ob_end_clean();
    return($return);    
}

function makeToC($structure) {
	$ToC = array();
	foreach($structure as $section) {
		if($section->level == 1) {
			$ToCEntry = array('label' => $section->label, 'quantifiedLabel' => $section->quantifiedLabel, 'anchor' => $section->makeAnchor(), 'extension' => 0);
			$ToC[] = $ToCEntry;
		}
	}
	return($ToC);
}
	
?>
