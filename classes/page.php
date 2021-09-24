<?php

class page {

	public $facet;
	public $maxLen;
	public $subpages = array();
	public $ToC = array();
	public $beaconRep;
	
	public function __construct($sections, $facet, $maxLen) {
		
		$this->facet = $facet;
		$this->maxLen = $maxLen;
		if ($this->facet == 'persName') {
			$this->beaconRep = new beacon_repository();
		}
		$this->makeSubpages($sections);
		$this->makeToC();

	}
	
	public function __toString() {
	   $overallSize = 0;
	   $res = 'Seite '.$this->facet.', '.count($this->subpages).' Unterseiten:'."\n";
	   foreach ($this->subpages as $ind => $subpage) {
	       $secArr = array();
	       foreach ($subpage as $section) {
	           $secArr[] = $section->label.' ('.$section->getSize().')';
	       }
	       $res .= "\n".'Nr. '.$ind."\n";
	       $res .= implode("\n", $secArr)."\n";
	   }
	   return($res);
	   
	}
	
	private function makeSubpages($sections) {
		$collectSec = array();
		$collectSize = 0;		
		while ($next = array_shift($sections)) {
			$size = $next->getSize();
			if ($size > $this->maxLen) {
				if (!empty($collectSec)) {
					$this->subpages[] = $collectSec;
				}
				$this->subpages[] = array($next);
				$collectSize = 0;
			}
			elseif (!empty($collectSec) and ($collectSize + $size) > $this->maxLen) {
				$this->subpages[] = $collectSec;
				$collectSec = array($next);
				$collectSize = $size;
			}
			else {
				$collectSec[] = $next;
				$collectSize += $size;
			}
		}
		if (!empty($collectSec)) {
			$this->subpages[] = $collectSec;
		}
	}

	
	/*private function splitSections($sections) {
		$subpage = array();
		$sizeSubpage = 0;
		while ($sizeSubpage < $this->maxLen) {
			$sec = array_shift($sections);
			$sizeSubpage += count($sec->content);
			$subpage[] = $sec;
		}
		foreach ($sections as $sec) {
			if ((count($sec->content) + $sizeSubpage) > $this->maxLen) {
			 	$this->subpages[] = $subpage;
			 	$subpage = array($sec);
			 	$sizeSubpage = count($sec->content);
			}
			else {
				$subpage[] = $sec;
			}
		}
		if (!empty($subpage)) {
			$this->subpages[] = $subpage;
		}
	}*/
	
	public function buildSubpages($path, $catalogue, $tocs, $impressum) {
		
		$total = count($this->subpages);
		foreach ($this->subpages as $ind => $sub) {
			ob_start();
			include 'templates/subPage.phtml';
			$content = ob_get_contents();
			ob_end_clean();
			$fileName = $path.$this->facet;
			if ($ind > 0) {
				$fileName .= '-'.strval($ind);
			}
			$fileName .= '.htm';
			$datei = fopen($fileName, "w");
			fwrite($datei, $content, 10000000);
			fclose($datei);
		}
		
	}
	
	private function makeToC() {
		
		$count = 0;
		foreach ($this->subpages as $sub) {
			foreach ($sub as $section) {
				if ($section->level == 1) {
					$ToCEntry = array('label' => $section->label, 'quantifiedLabel' => $section->quantifiedLabel, 'anchor' => $section->makeAnchor(), 'extension' => strval($count));	
					$this->ToC[] = $ToCEntry;
				}
			}
			$count += 1;
		}
		
	}

}

?>
