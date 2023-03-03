<?php

// Spezialfall für die Daten zur Bibliothek von Philippine Charlotte mit zwei Altkatalogen
class uploader_csv_hfs extends uploader_csv {

	protected $fieldNames_norm = 'id', 'noTitle', 'schranknummer_cat1', 'rang_cat1', 'order_cat1', 'subject_cat1', 'image_cat1', 'pageCat_cat1', 'titleCat_cat1', 'titleCat_praun_suppl', 'id_cat2', 'image_cat2', 'pageCat_cat2', 'schranknummer_cat2', 'order_cat2', 'titleCat_cat2', 'idManifestation_k10p', 'comment_cat1', 'volumes', 'ppn_copy1', 'shelfmark_copy1', 'provenance_note', 'provenance_dumas', 'provenance_pc', 'provenance_sophie_dorothea', 'provenance_cat', 'shelfmark_copy2', 'provenances', 'bbg', 'languages', 'titleBib', 'year', 'place_vorl', 'place_norm', 'place_gnd', 'place_ans', 'place_tgn', 'author1_forename', 'author1_surname', 'author1_normalized', 'author1_wikidata', 'author1_gender', 'author1_gnd', 'author1_gender_de', 'author2_forename', 'author2_surname', 'author2_gnd', 'author2_gender', 'author3_forename', 'author3_surname', 'author3_gnd', 'author3_gender', 'author4_forename', 'author4_surname', 'author4_gnd', 'author4_gender', 'contributor1_forename', 'contributor1_surname', 'contributor1_gender', 'contributor1_gnd', 'contributor2_forename', 'contributor2_surname', 'contributor2_gnd', 'contributor3_forename', 'contributor3_surname', 'contributor3_gnd', 'contributor4_forename', 'contributor4_surname', 'contributor4_gnd', 'shelfmark_1', 'shelfmark_2', 'shelfmark_3', 'provenance_dumas', 'provenance_pc3', 'provenance_sophie_dorothea4', 'sru_prov';


    protected function makeItem($row) {

        $item = new item();

		if ($row['id']) {
			$item->id = $row['id'];
		}
		$simpleFields = array('year', 'volumes', 'format', 'mediaType', 'comment', 'bound', 'digitalCopy', 'itemInVolume');
		foreach ($simpleFields as $field) {
			if (!empty($row[$field])) {
				$item->$field = $row[$field];
			}
		}
		if ($row['titleBib']) {
			$item->titleBib = $row['titleBib'];
		}
		if ($row['ppn_copy1']) {
			$item->manifestation = array('systemManifestation' => 'K10plus', 'idManifestation' => $row['ppn_copy1']);
		}
		if ($row['languages']) {
			$item->languages = explode(';', $row['languages']);
		}
		if ($row['subject_cat1']) {
			$item->subjects[] = $row['subject_cat1'];
		}

		$placeName = uploader::getByPreference(array($row['place_norm'], $row['place_ans'], $row['place_vorl']));
		if ($placeName != null) {
			$place = new place;
			$place->placeName= $placeName;
			if (!empty($row['place_gnd'])) {
				$place->gnd = $row['place_gnd'];
			}
			if(!empty($row['place_tgn'])) {
				$place->getty = $row['place_tgn'];
			}
			$item->places[] = $place;
		}

		if (strstr('Sammlungszugehörigkeit nachgewiesen', $row['provenance_note']) != false) {
			$item->originalItem = array('institutionOriginal' => 'Herzog August Bibliothek Wolfenbüttel', 'shelfmarkOriginal' => $row['shelfmark_copy1'], 'targetOPAC' => 'https://opac.lbs-braunschweig.gbv.de/DB=2/XMLPRS=N/PPN?PPN={ID}', 'searchID' => $row['ppn_copy1']);
			$provenances = implode('; ', array($row['provenance_pc'], $row['provenance_dumas'], $row['provenance_sophie_dorothea'], $row['provenance_cat'], $row['provenances']));
			if ($provenances) {
				$item->originalItem['provenanceAttribute'] = $provenances;
			}
		}
		elseif ($row['shelfmark_copy1']) {
			$item->copiesHAB[] = $row['shelfmark_copy1'];
		}
		$shelfmarkFields = array('shelfmark_copy2', 'shelfmark_1', 'shelfmark_2', 'shelfmark_3'):
		foreach ($shelfmarkFields as $sf) {
			if (!empty($row[$sf])) {
				$item->copiesHAB[] = $row[$sf];
			}
		}

		if ($row['provenance_note']) {
			$item->comment = $row['provenance_note'];
		}

		if ($row['author1_normalized']) {
			$pers = new person($row['author1_normalized', $row['author1_gnd', $row['author1_gender'], $role = 'creator';
			$item->persons[] = $pers;
		}
		if ($row['author2_surname']) {
			$pers = new person($row['author2_surname'], $row['author2_gnd'], $row['author2_gender']);
			if ($row['author2_forename']) {
				$pers->persName = $row['author2_forename'].' '.$row['author2_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['author3_surname']) {
			$pers = new person($row['author3_surname'], $row['author3_gnd'], $row['author3_gender']);
			if ($row['author3_forename']) {
				$pers->persName = $row['author3_forename'].' '.$row['author3_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['author4_surname']) {
			$pers = new person($row['author4_surname'], $row['author4_gnd'], $row['author4_gender']);
			if ($row['author4_forename']) {
				$pers->persName = $row['author4_forename'].' '.$row['author4_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['contributor1_surname']) {
			$pers = new person($row['contributor1_surname'], $row['contributor1_gnd'], $row['contributor1_gender'], 'contributor');
			if ($row['contributor1_forename']) {
				$pers->persName = $row['contributor1_forename'].' '.$row['contributor1_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['contributor2_surname']) {
			$pers = new person($row['contributor2_surname'], $row['contributor2_gnd'], null, 'contributor');
			if ($row['contributor2_forename']) {
				$pers->persName = $row['contributor2_forename'].' '.$row['contributor2_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['contributor3_surname']) {
			$pers = new person($row['contributor3_surname'], $row['contributor3_gnd'], null, 'contributor');
			if ($row['contributor3_forename']) {
				$pers->persName = $row['contributor3_forename'].' '.$row['contributor3_surname'];
			}
			$item->persons[] = $pers;
		}
		if ($row['contributor4_surname']) {
			$pers = new person($row['contributor4_surname'], $row['contributor4_gnd'], null, 'contributor');
			if ($row['contributor4_forename']) {
				$pers->persName = $row['contributor4_forename'].' '.$row['contributor4_surname'];
			}
			$item->persons[] = $pers;
		}

		if ($row["titleCat_cat1"]) {
			$entry = new catalogue_entry;
			$entry->idCat = 'cat1';
			$entry->titleCat = $row['titleCat_cat1'];
			if ($row['titleCat_praun_suppl']) {
				$entry->titleCat = $entry->titleCat.' '.$row['titleCat_praun_suppl'];
			}
			$entry->numberCat = $row['noTitle']
			$entry->pageCat = $row['pageCat_cat1'];
			$entry->imageCat = $row['image_cat1'];
			$entry->histSubject = implode(' ', array($row['schranknummer_cat1'], $row['rang_cat1']));
			$item->catEntries[$entry];
		}
		if ($row["titleCat_cat2"]) {
			$entry->idCat = 'cat2';
			$entry->titleCat = $row['titleCat_cat2'];
			$entry->pageCat = $row['pageCat_cat2'];
			$entry->imageCat = $row['image_cat2'];
			$entry->numberCat = $row['id_cat2'];
			$entry->histSubject = $row['schranknummer_cat2'];
			$item->catEntries[$entry];
		}

		return($item);

	}


    private function validate() {
        if (empty($this->rows[0])) {
            throw new Exception('Es wurden keine Daten geladen.', 1);
        }
        $fieldsMin = array('noTitle', 'schranknummer_cat1', 'rang_cat1', 'order_cat1', 'subject_cat1', 'image_cat1', 'pageCat_cat1', 'numberCat_cat1', 'titleCat_praun_suppl', 'id_cat2', 'image_cat2', 'pageCat_cat2', 'schranknummer_cat2', 'order_cat2', 'titleCat_cat2', 'idManifestation_k10p', 'comment_cat1', 'volumes', 'ppn_copy1', 'shelfmark_copy1', 'provenance_note', 'provenance_dumas', 'provenance_pc', 'provenance_sophie_dorothea', 'provenance_cat', 'provenances', 'bbg', 'languages', 'titleBib', 'year', 'place_vorl', 'place_norm', 'place_gnd', 'place_ans', 'place_tgn', 'author1_forename', 'author1_surname', 'author1_normalized', 'author1_wikidata', 'author1_gender', 'author1_gnd', 'author1_gender_de', 'author2_forename', 'author2_surname', 'author2_gnd', 'author2_gender', 'author3_forename', 'author3_surname', 'author3_gnd', 'author3_gender', 'author4_forename', 'author4_surname', 'author4_gnd', 'author4_gender', 'contributor1_forename', 'contributor1_surname', 'contributor1_gender', 'contributor1_gnd', 'contributor2_forename', 'contributor2_surname', 'contributor2_gnd', 'contributor3_forename', 'contributor3_surname', 'contributor3_gnd', 'contributor4_forename', 'contributor4_surname', 'contributor4_gnd', 'shelfmark_1', 'shelfmark_2', 'shelfmark_3', 'provenance_dumas', 'provenance_pc3', 'provenance_sophie_dorothea4');
        foreach ($fieldsMin as $fieldMin) {
            if (!in_array($fieldMin, $this->fieldNames)) {
                throw new Exception('Fehlende Spalte: '.$fieldMin, 1);
            }
        }
        $width = count($this->fieldNames);
        foreach ($this->rows as $index => $row) {
            if (count($row) != $width) {
				$place = $index + 1;
                throw new Exception("Uneinheitliche Anzahl an Spalten ab Nr. $place", 1);
            }
        }
        return(true);
    }

}

?>
