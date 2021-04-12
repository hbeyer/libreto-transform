<?php

class uploader_sru extends uploader {
	
	protected $source = 'http://sru.k10plus.de/gvk';
	protected $version = '2.0';
	protected $xmlSets = array();
	public $content = array();
	protected $tempDOM = null;
	protected $tempXP = null;
	public $numHits = 0;

	//$bib: Diese Lösung ist nur dann befriedigend, wenn 209A $f mit dem Bibliotheksnamen besetzt ist, was bei der HAB z. B. nicht der Fall ist. Man könnte einen regulären Ausdruck zur Selektion der Signatur übergeben, was aber zusätzlich zu $bib geschehen müsste
	//Weiteres Desiderat: Ansprechen einzelner OPACs per SRU, s. die Liste unter http://uri.gbv.de/database/opac und https://wiki.k10plus.de/display/K10PLUS/Datenbanken
	public function __construct($query, $fileName, $sru = null, $bib = null, $regexSig = null) {
		if (substr($sru, 0, 10) == 'http://sru') {
			$this->source = trim($sru, '/');
		}
		$this->fileName = $fileName;
		$this->query = $query;
		$this->loadSets();
		while ($xml = array_shift($this->xmlSets)) {
			$recordNodes = $this->getNodes($xml);
			foreach ($recordNodes as $recNode) {
				$item = picaConverter::makeItem($recNode, $this->tempXP, $bib, $regexSig);
				if ($item) {
					$this->content[] = $item;
				}
			}
		}
		$this->tempDOM = null;
	}

	protected function loadSets() {
		$cache = new cache_pica;
		$url = $this->makeURL('1', '0');
		$string = $cache->get($url);
		preg_match('~<zs:numberOfRecords>([0-9]+)</zs:numberOfRecords>~', $string, $hits);
		if (!empty($hits[1])) {
			$this->numHits = intval($hits[1]);
		}
		$first = 1;
		while ($first < $this->numHits) {
			$url = $this->makeURL(strval($first));
			$this->xmlSets[] = $cache->get($url);
			$first += 500;
		}
	}

	protected function makeURL($start = '1', $maxRec = '500', $format = 'picaxml') {
		return($this->source.'?version='.$this->version.'&operation=searchRetrieve&query='.$this->query.'&maximumRecords='.$maxRec.'&startRecord='.$start.'&recordSchema='.$format);
	}

	protected function getNodes($xml) {
		$this->tempDOM = new DOMDocument;
		$this->tempDOM->loadXML($xml);
		$this->tempXP = new DOMXPath($this->tempDOM);
		$this->tempXP->registerNamespace('pica', 'info:srw/schema/5/picaXML-v1.0');
		$nodes = $this->tempXP->query('//pica:record');
		return($nodes);
	}

}

?>