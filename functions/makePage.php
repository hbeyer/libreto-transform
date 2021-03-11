<?php

function makePage($catalogue, $navigation, $pageContent, $facet, $impressum) {
	ob_start();
	include 'templates/page.phtml';
	$return = ob_get_contents();
	ob_end_clean();
    return($return);
}

?>
