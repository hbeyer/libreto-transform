<?php

function makeGraphPageContent($data) {
	$series = array();
	$persColl = array();
	foreach ($data as $item) {
		$name = $item->makeItemName();
		$series[] = array('id' => $item->id, 'name' => $name, 'color' => '#035151');
		foreach ($item->persons as $person) {
			if (isset($persColl[strval($person)])) {
				$persColl[strval($person)]['linkWith'][] = $item->id;
			}
			else {
				$persColl[strval($person)] = array('name' => $person->persName, 'linkWith' => array($item->id));
			}
		}
	}
	foreach ($persColl as $key => $persArr) {
		$series[] = array('id' => $key, 'name' => $persArr['name'], 'color' => '#a08246', 'linkWith' => $persArr['linkWith']);
	}
	$series = json_encode($series);
	ob_start();
	include 'templates/graph.phtml';
	$return = ob_get_contents();
	ob_end_clean();
	return($return);
}

?>