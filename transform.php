<?php

set_time_limit (600);
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$genders = array(
	"Anna <Frankreich, Königin>" => "f", 
	"Anna <Großbritannien, Königin, 1665-1714>" => "f", 
	"Antoinette Amalie <Braunschweig-Lüneburg, Herzogin>" => "f", 
	"Aulnoy, Marie Catherine LeJumel de Barneville d'" => "f", 
	"Aumont, Louis Marie d' " => "m", 
	"Babel, Christoff von" => "m", 
	"Belegno, Giusto Antonio " => "m", 
	"Boerner, Catharina Elisabeth" => "f", 
	"Burchard, Georg Heinrich" => "m", 
	"Bédacier, Catherine" => "f", 
	"Carolus <Palatinus Rhenus, Comes> " => "m", 
	"Caspar Grubers Witwe" => "f", 
	"Christine Luise <Braunschweig-Lüneburg, Herzogin>" => "f", 
	"Cize, Emmanuel, de" => "m", 
	"Claudia Eleonora <Braunschweig-Lüneburg, Herzogin>" => "f", 
	"Colbert, Jule Armand " => "m", 
	"Cominus, Bartholomeus " => "m", 
	"Coypel, Charles-Antoine" => "m", 
	"Dacier, Anne" => "f", 
	"Della-Faille, Jean Baptiste" => "m", 
	"Du Noyer, Anne Marguerite Petit" => "f", 
	"Edelinck, Nicolas-Etienne" => "m", 
	"Elisabeth Juliane <Braunschweig-Lüneburg, Herzog>104183772w " => "f",
	"Elisabeth Juliane <Braunschweig-Lüneburg, Herzogin>" => "f", 
	"Elisabeth Juliane <Herzogin zu Braunschweig und Lüneburg>" => "f", 
	"Elisabeth Juliane, Braunschweig-Lüneburg, Herzogin" => "f", 
	"Fabius a Prevost, Ludovicus" => "m", 
	"Falckenstein, Johann Heinrich, von" => "m", 
	"Furttenbach, Joseph <der Ältere>" => "m", 
	"Georg Wilhelm <Braunschweig-Lüneburg, Herzog> " => "m", 
	"Harsdörffer, Georg Philipp" => "m", 
	"Johann Christoph Zimmermann & Johann Nikolaus Gerlach" => "m", 
	"Johann Friedrich <Braunschweig-Lüneburg, Herzog>" => "m", 
	"Kaiserliche Akademie der Wissenschaften" => "k", 
	"Karl, VII. <Frankreich, König>" => "m", 
	"Karl, VIII. <Frankreich, König<" => "m", 
	"Katharina <I., Russland, Zarin>" => "f", 
	"La Roche-Guilhem, Anne de" => "f", 
	"La Suze, Henriette de Coligny, de" => "f", 
	"LaMonnerie, J. B. de" => "m", 
	"Lenclos, Ninon, de" => "m", 
	"Leti, Gregorio" => "m", 
	"Ludwig, XI. <Frankreich, König<" => "m", 
	"Ludwig, XIII. <Frankreich, König>" => "m", 
	"Ludwig, XVI. <Frankreich, König>" => "m", 
	"Lussan, Marguerite de" => "f", 
	"Lussan, Marguerite, de" => "f", 
	"Magdalena Sibylla <Württemberg, Herzogin, 1652-1712>" => "f", 
	"Manley, Mary DeLaRivière" => "f", 
	"Montpensier, Anne Marie Louise Henriette D'Orléans, de" => "f", 
	"Morosini, Iseppo " => "m", 
	"Motteville, Françoise Bertaud de Langelois de" => "f", 
	"Möller, Johann Jacob" => "m", 
	"Oldmixon, John" => "m", 
	"Orden von Grandmont" => "k", 
	"Pasqualigo, Filippo " => "m", 
	"Pavillon," => "m", 
	"Philippine Charlotte, Braunschweig-Lüneburg, Herzogin" => "f", 
	"Picart, Bernard" => "m", 
	"Regnier, Nicolas " => "m", 
	"Reichenbach, Elisa Sophia von" => "f", 
	"Reusch, Erhard" => "m", 
	"Rottgießer, Christian " => "m", 
	"Sainctonge, Louise-Geneviève Gillot, de" => "m", 
	"Sappho" => "f", 
	"Satzmann, Balthasar Friedrich " => "m", 
	"Schellhammer, Maria Sophia" => "f", 
	"Scudéry, Madeleine, de" => "f", 
	"Sfondrata, Agata" => "f", 
	"Sig. Cam. Battista Guarini" => "m", 
	"Sophia Eleonora <Braunschweig-Lüneburg, Herzogin>101263503w " => "f", 
	"Sophie <Hannover, Kurfürstin>" => "f", 
	"Sporck, Maria Eleonora Francisca Cajetana Aloysia von" => "f", 
	"Stieber, Casparus" => "m", 
	"Swéerts-Sporck, Anna Katharina, von" => "f", 
	"Sylvio Friderich <Württemberg-Teck, Herzog> " => "m", 
	"Teresa <de Jesús>" => "f", 
	"Thun-Hohenstein, Anna Catharina Gräfin von " => "f", 
	"Tinus, Franciscus" => "m", 
	"Tipograf?ja Imperatorsko? Akadem?i Nauk <Sankt Petersburg>" => "k", 
	"Tridentinum <1545-1563, Trient>" => "k", 
	"Villequier, Madame de " => "f", 
	"Wackerbarth, Christoph August, von" => "m", 
	"Waisenhaus <Halle (Saale)>" => "k", 
	"Weismann, Erich" => "m", 
	"Zampi, Giuseppe Maria " => "m", 
	"Zayas y Sotomayor, María, de" => "m"
	);

$reconstruction = new Reconstruction('source/dataPHP', 'christine-luise', 'php');

	$gender_undef = array();
foreach ($reconstruction->content as $item) {
	$item->subjects = array_map(function($x) { if ($x == 'Bellestristik') { return('Belletristik'); } else {return($x);} },$item->subjects);
	if ($item->mediaType == 'Drukck') {
		$item->mediaType = 'Druck';
	}
	$item->publishers = array_map(function($x) { 
		if ($x == 'Buchdruckerei der Kaiserlichen Akademie der Wissenschaften$gSankt Petersburg') { 
			return('Buchdruckerei der Kaiserlichen Akademie der Wissenschaften Sankt Petersburg'); 
		}
		elseif ($x == 'Block [DRUCKER/VERLEGER]') {
			return('Block');
		} 
		else {
			return($x);
		}},$item->publishers);
	foreach ($item->persons as $person) {
		if (!empty($genders[$person->persName])) {
			$person->gender = $genders[$person->persName];
		}
		$person->persName = trim($person->persName);
		$person->persName = strtr($person->persName, array(
			'Tipograf?ja Imperatorsko? Akadem?i Nauk <Sankt Petersburg>' => 'Kaiserliche Akademie der Wissenschaften', 
			'Sophia Eleonora <Braunschweig-Lüneburg, Herzogin>101263503w' => 'Sophia Eleonora <Braunschweig-Lüneburg, Herzogin>', 
			'Elisabeth Juliane <Braunschweig-Lüneburg, Herzog>104183772w' => 'Elisabeth Juliane <Braunschweig-Lüneburg, Herzogin>'
		));
	}

}


//$reconstruction = new Reconstruction('source/christine-luise_bereinigt.csv', 'christine-luise', 'csv');
//$reconstruction = new Reconstruction('source/christine-luise.xml', 'christine-luise', 'xml');
//var_dump($reconstruction->content[54]);

//$reconstruction->enrichData();
$reconstruction->saveAllFormats();
//$reconstruction->makeBioDataSheet();

// Anpassen zum Eingrenzen der darzustellenden Felder
$pages = array('histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'genres', 'languages', 'placeName', 'publishers', 'format', 'mediaType');
$doughnuts = array('format', 'histSubject', 'subjects', 'gender', 'genres', 'languages', 'systemManifestation', 'beacon');
$clouds = array('publishers', 'subjects', 'genres', 'persName', 'placeName');

$facetList = new FacetList($pages, $doughnuts, $clouds);
$frontend = new Frontend($reconstruction, $facetList);
$frontend->build();

?>
