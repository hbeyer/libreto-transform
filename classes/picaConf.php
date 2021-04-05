<?php

class picaConf {
	

	static function getFieldConf($field) {
		$conf = array(
			'year' => array('field' => '011@', 'subfield' => 'a'),
			'bbg' => array('field' => '002@', 'subfield' => '0'),
			'ppn' => array('field' => '003@', 'subfield' => '0'),
			'vdn' => array('field' => '206X', 'subfield' => '0'),
			'vd16' => array('field' => '007S', 'subfield' => '0'),
			'vd17' => array('field' => '006W', 'subfield' => '0'),
			'vd18' => array('field' => '006M', 'subfield' => '0'),
			'languages' => array('field' => '010@', 'subfield' => 'a'),
			'titleBib' => array('field' => '021A', 'subfield' => 'a'),
			'titleSupp' => array('field' => '021A', 'subfield' => 'd'),
			'format' => array('field' => '034I', 'subfield' => 'a'),
			'subjects' => array('field' => '044S', 'subfield' => 'a'),
			'publishers' => array('field' => '033A', 'subfield' => 'n'),
			'placesVorl' => array('field' => '033A', 'subfield' => 'p'),
			'digitalCopy' => array('field' => '017D', 'subfield' => 'u')
		);
		if (empty($conf[$field])) {
			return(null);
		}
		return($conf[$field]);
	}

	static function getGroupConf($field) {
		$conf = array(
			'author1' => array('field' => '028A', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'author2' => array('field' => '028B', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'contributors' => array('field' => '028C', 'subfields' => array('forename' => 'd', 'surname' => 'a', 'personal' => 'p', 'from' => 'l', 'gnd' => '7')),
			'places' => array('field' => '033D', 'subfields' => array('placeName' => 'p', 'gnd' => '7')),
			'shelfmarks' => array('field' => '209A', 'subfields' => array('institution' => 'f', 'shelfmark' => 'a')),
			'seriesf' => array('field' => '036C', 'subfields' => array('title' => 'a', 'vol' => 'l')),
			'seriesF' => array('field' => '036D', 'subfields' => array('title' => '8', 'vol' => 'l'))
		);
		if (empty($conf[$field])) {
			return(null);
		}
		return($conf[$field]);
	}

}

?>