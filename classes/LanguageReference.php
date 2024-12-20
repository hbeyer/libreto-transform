<?php

abstract class LanguageReference {

    //Language codes according to ISO 639.2
    static $languageCodes = array(
	    'gez' => 'Altäthiopisch',
	    'ang' => 'Altenglisch',
	    'fro' => 'Altfranzösisch',
	    'grc' => 'Altgriechisch',
	    'qhe' => 'Althebräisch',
	    'goh' => 'Althochdeutsch',
	    'sga' => 'Altirisch',
	    'non' => 'Altnordisch',
	    'peo' => 'Altpersisch',
	    'pro' => 'Altprovenzalisch',
	    'chu' => 'Kirchenslawisch',
	    'ara' => 'Arabisch',
	    'arg' => 'Aragonisch',
	    'arc' => 'Aramäisch',
	    'arm' => 'Armenisch',
	    'aze' => 'Aserbaidschanisch',
	    'ast' => 'Asturisch',
	    'ave' => 'Avestisch',
	    'baq' => 'Baskisch',
	    'bre' => 'Bretonisch',
	    'bul' => 'Bulgarisch',
	    'chi' => 'Chinesisch',
	    'dan' => 'Dänisch',
	    'dar' => 'Dari',
	    'ger' => 'Deutsch',
	    'eng' => 'Englisch',
	    'epo' => 'Esperanto',
	    'est' => 'Estnisch',
	    'per' => 'Persisch',
	    'fin' => 'Finnisch',
	    'fre' => 'Französisch',
	    'fur' => 'Friulisch',
	    'fry' => 'Friesisch',
	    'gla' => 'Gälisch-Schottisch',
	    'glg' => 'Galicisch',
	    'geo' => 'Georgisch',
	    'got' => 'Gotisch',
	    'kal' => 'Grönländisch',
	    'heb' => 'Hebräisch',
	    'hit' => 'Hethitisch',
	    'hin' => 'Hindu',
	    'hsb' => 'Hochsorbisch',
	    'ind' => 'Indonesisch',
	    'gle' => 'Irisch',
	    'ice' => 'Isländisch',
	    'ita' => 'Italienisch',
	    'jpn' => 'Japanisch',
	    'yid' => 'Jiddisch',
	    'lad' => 'Judeo-Español',
	    'csb' => 'Kaschubisch',
	    'spa' => 'Spanisch',
	    'cat' => 'Katalanisch',
	    'cop' => 'Koptisch',
	    'kor' => 'Koreanisch',
	    'cos' => 'Korsisch',
	    'hrv' => 'Kroatisch',
	    'kur' => 'Kurdisch',
	    'wel' => 'Walisisch',
	    'lat' => 'Latein',
	    'lav' => 'Lettisch',
	    'lit' => 'Litauisch',
	    'ltz' => 'Luxemburgisch',
	    'mac' => 'Makedonisch',
	    'mlt' => 'Maltesisch',
	    'mnc' => 'Manchu',
	    'enm' => 'Mittelenglisch',
	    'frm' => 'Mittelfranzösisch',
	    'gmh' => 'Mittelhochdeutsch',
	    'mga' => 'Mittelirisch',
	    'dum' => 'Mittelniederländisch',
	    'pal' => 'Mittelpersisch',
	    'mol' => 'Moldauisch',
	    'mon' => 'Mongolisch',
	    'nap' => 'Neapolitanisch',
	    'gre' => 'Neugriechisch',
	    'nds' => 'Niederdeutsch',
	    'dut' => 'Niederländisch',
	    'dsb' => 'Niedersorbisch',
	    'nor' => 'Norwegisch',
	    'oci' => 'Okzitanisch',
	    'ota' => 'Osmanisch',
	    'pus' => 'Paschtu',
	    'phn' => 'Phönikisch',
	    'pol' => 'Polnisch',
	    'por' => 'Portugiesisch',
	    'roh' => 'Rätoromanisch',
	    'rom' => 'Romani',
	    'rum' => 'Rumänisch',
	    'rus' => 'Russisch',
	    'san' => 'Sanskrit',
	    'srd' => 'Sardisch',
	    'sco' => 'Schottisch',
	    'swe' => 'Schwedisch',
	    'srp' => 'Serbisch',
	    'sla' => 'Slawische Sprachen',
	    'slo' => 'Slowakisch',
	    'slv' => 'Slovenisch',
	    'wen' => 'Sorbisch',
	    'swa' => 'Suaheli',
	    'sux' => 'Sumerisch',
	    'syr' => 'Syrisch',
	    'tha' => 'Thailändisch',
	    'tib' => 'Tibetisch',
	    'cze' => 'Tschechisch',
	    'tur' => 'Türkisch',
	    'tuk' => 'Turkmenisch',
	    'ukr' => 'Ukrainisch',
	    'und' => 'Unbestimmbare Sprache',
	    'hun' => 'Ungarisch',
	    'uzb' => 'Usbekisch',
	    'mis' => 'Verschiedene Sprachen',
	    'vie' => 'Vietnamesisch',
	    'vol' => 'Volapük',
	    'gsw' => 'Waliserdeutsch',
	    'bel' => 'Weißrussisch',
	    'mul' => 'Verschiedene Sprachen'
	);

    static function getLanguage($code) {
        if (isset(LanguageReference::$languageCodes[$code])) {
            return(LanguageReference::$languageCodes[$code]);
        }
        return(null);
    }

    static function getCode($language) {
        foreach (LanguageReference::$languageCodes as $code => $label) {
            if ($label == $language) {
                return($code);
            }
        }
        return(null);
    }

}

?>
