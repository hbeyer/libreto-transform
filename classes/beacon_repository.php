<?php

class beacon_repository {

    public $errorMessages = array();
    public $lastUpdate;
    public $valid = false;
    public $folder = 'beaconFiles';
    private $update_int = 1209600;
    private $filePermission = 0777;
    private $user = 'Herzog August Bibliothek Wolfenbüttel';
    private $sourcesHAB = array('bahnsen', 'fruchtbringer', 'cph', 'aqhab', 'vkk', 'sandrart'); // Hier wird festgelegt, welche der unten stehenden Quellen als "Ressourcen der HAB" angezeigt werden sollen
    public $beacon_sources = array(
    	'ddb' => array('label' => 'Deutsche Digitale Bibliothek', 'location' => 'https://labs.ddb.de/app/beagen/item/person/all/latest', 'target' => 'https://www.deutsche-digitale-bibliothek.de/person/gnd/{ID}', 'type' => 'default'),
        'apd' => array('label' => 'Archivportal D', 'location' => 'https://www.archivportal-d.de/static/de/beacon-archivportal-persons.txt', 'target' => 'https://www.archivportal-d.de/person/gnd/{ID}', 'type' => 'default'),
        'wkp' => array('label' => 'Wikipedia', 'location' => 'http://tools.wmflabs.org/persondata/beacon/dewiki.txt', 'target' => 'http://tools.wmflabs.org/persondata/redirect/gnd/de/{ID}', 'type' => 'default'),        
        'db' => array('label' => 'Deutsche Biographie', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacon_db_register.txt', 'target' => 'http://www.deutsche-biographie.de/pnd{ID}.html', 'type' => 'default'),
        'dbi' => array('label' => 'Dizionario Biografico degli Italiani', 'location' => 'http://beacon.findbuch.de/downloads/patchwork/pw_dbi-gndbeacon.txt', 'target' => 'http://beacon.findbuch.de/gnd-resolver/pw_dbi/{ID}', 'type' => 'default'),
        'hls' => array('label' => 'Historisches Lexikon der Schweiz', 'location' => 'http://beacon.findbuch.de/downloads/hls/hls-pndbeacon.txt', 'target' => 'http://beacon.findbuch.de/pnd-resolver/hls/{ID}', 'type' => 'default'),		
        'blko' => array('label' => 'Biographisches Lexikon des Kaiserthums Oesterreich', 'location' => 'http://tools.wmflabs.org/persondata/beacon/dewikisource_blkoe.txt', 'target' => 'http://tools.wmflabs.org/persondata/redirect/gnd/ws-blkoe/{ID}', 'type' => 'default'),
        'blgs' => array('label' => 'Biographisches Lexikon zur Geschichte Südosteuropas', 'location' => 'https://www.biolex.ios-regensburg.de/beacon.txt', 'target' => 'http://www.biolex.ios-regensburg.de/BioLexViewlist.php?x_dnb={ID}&z_dnb=LIKE&cmd=search', 'type' => 'default'),
        'phoh' => array('label' => 'Personendatenbank der Höflinge der österreichischen Habsburger', 'location' => 'http://kaiserhof.geschichte.lmu.de/beacon/', 'target' => 'http://kaiserhof.geschichte.lmu.de/Q/GND={ID}', 'type' => 'default'),
        'pbbl' => array('label' => 'Personen in bayrischen historischen biographischen Lexika', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/bsb_personen.php?beacon', 'target' => 'http://personen.digitale-sammlungen.de/pnd/treffer.html?object=liste&suche=pndid:{ID}%20AND%20(bsbID:bsb00000273%20OR%20bsbID:bsb00000274%20OR%20bsbID:bsb00000279%20OR%20bsbID:bsb00000280%20OR%20bsbID:bsb00000281%20OR%20bsbID:bsb00000282%20OR%20bsbID:bsb00000283%20OR%20bsbID:bsb00000284)&pos=1', 'type' => 'default'),
    	'bwbio' => array('label' => 'Biografische Sammelwerke Baden-Württemberg', 'location' => 'http://www.leo-bw.de/documents/10157/0/leo-bw-beacon_kgl_bio.txt', 'target' => 'http://www.leo-bw.de/web/guest/detail/-/Detail/details/PERSON/kgl_biographien/{ID}/biografie', 'type' => 'default'),
    	'hbio' => array('label' => 'Hessische Biografie', 'location' => 'http://www.lagis-hessen.de/gnd.txt', 'target' => 'http://www.lagis-hessen.de/pnd/{ID}', 'type' => 'default'),
	    'rpbio' => array('label' => 'Rheinland-Pfälzische Personendatenbank', 'location' => 'http://www.rlb.de/pnd.txt', 'target' => 'http://www.rlb.de/cgi-bin/wwwalleg/goorppd.pl?db=rnam&index=1&zeilen=1&s1={ID}', 'type' => 'default'),
        'saebi' => array('label' => 'Sächsische Biographie', 'location' => 'http://saebi.isgv.de/pnd.txt', 'target' => 'http://saebi.isgv.de/gnd/{ID}', 'type' => 'default'),
    	'wfg' => array('label' => 'Westfälische Geschichte', 'location' => 'http://www.lwl.org/westfaelische-geschichte/meta/pnd.txt', 'target' => 'http://www.westfaelische-geschichte.de/pnd{ID}', 'type' => 'default'),
        'fpl' => array('label' => 'Frankfurter Personenlexikon', 'location' => 'https://frankfurter-personenlexikon.de/beacon_fpl.txt', 'target' => 'http://frankfurter-personenlexikon.de/beacon/beacon.php?{ID}', 'type' => 'default'),
        'gqdm' => array('label' => 'Geschichtsquellen des deutschen Mittelalters', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/repfont-autoren.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/repfont-autoren.php?pnd={ID}', 'type' => 'default'),
        'trithemius' => array('label' => 'Trithemius: De scriptoribus ecclesiasticis', 'location' => 'http://www.mgh-bibliothek.de/beacon/trithemius', 'target' => 'http://www.mgh.de/index.php?&wa72ci_url=%2Fcgi-bin%2Fmgh%2Fallegro.pl&db=opac&var5=IDN&TYP=&id=438&item5=trithemius_{ID}', 'type' => 'default'),
        'fabricius' => array('label' => 'Fabricius: Bibliotheca latina', 'location' => 'http://www.mgh-bibliothek.de/beacon/fabricius', 'target' => 'http://www.mgh.de/index.php?&wa72ci_url=%2Fcgi-bin%2Fmgh%2Fallegro.pl&db=opac&var5=IDN&TYP=&id=438&item5=fabricius_{ID}', 'type' => 'default'),
        'mav' => array('label' => 'Melchior Adam: Vitae', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/adam.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/adam.php?pnd={ID}', 'type' => 'default'),
		'jen' => array('label' => 'Jewish Encyclopedia 1906', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/jewishenc.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/jewishenc.php?pnd={ID}', 'type' => 'default'),
		'dpr' => array('label' => 'Digitales Portal der Rabbiner', 'location' => 'http://steinheim-institut.de/daten/projekte/work3/beacon.txt', 'target' => 'http://steinheim-institut.de:50580/cgi-bin/bhr?gnd={ID}', 'type' => 'default'),
		'bdjg' => array('label' => 'Bibliografie deutsch-jüdische Geschichte Nordrhein-Westfalen', 'location' => 'http://www.steinheim-institut.de/ebib-djg-nrw/ebib-djg-nrw-beacon.txt', 'target' => 'http://www.steinheim-institut.de/ebib-djg-nrw/query.html?database=NRW-Bibliografie&text1={ID}&kategorie1=gnd', 'type' => 'default'),
        'gspd' => array('label' => 'Germania Sacra Personendatenbank', 'location' => 'http://personendatenbank.germania-sacra.de/beacon.txt', 'target' => 'http://personendatenbank.germania-sacra.de/index/gnd/{ID}', 'type' => 'default'),	
        'biocist' => array('label' => 'Biographia Cisterciensis', 'location' => 'http://www.statistik-bw.de/LABI/Biographia-Cisterciensis.txt', 'target' => '', 'type' => 'specified'),
        'hpk' => array('label' => 'Hamburger Professorinnen- und Professorenkatalog', 'location' => 'https://www.hpk.uni-hamburg.de/hpk_gnd_beacon.txt', 'target' => 'https://www.hpk.uni-hamburg.de/resolve/gnd/{ID}', 'type' => 'default'),
        'cph' => array('label' => 'Helmstedter Professorenkatalog', 'location' => 'http://uni-helmstedt.hab.de/beacon.php', 'target' => 'http://uni-helmstedt.hab.de/index.php?cPage=5&sPage=prof&wWidth=1920&wHeight=957&suche1=gnd&pnd1=&muster1={ID}', 'type' => 'default'),		
        'cpr' => array('label' => 'Rostocker Professorenkatalog', 'location' => 'http://cpr.uni-rostock.de/cpr_pnd_beacon.txt', 'target' => 'http://cpr.uni-rostock.de/pnd/{ID}', 'type' => 'default'),		
        'cpl' => array('label' => 'Leipziger Professorenkatalog', 'location' => 'http://www.uni-leipzig.de/unigeschichte/professorenkatalog/leipzig/cpl-beacon.txt', 'target' => 'http://www.uni-leipzig.de/unigeschichte/professorenkatalog/leipzig/pnd/{ID}', 'type' => 'default'),
        'cpm' => array('label' => 'Catalogus Professorum der Universität Mainz', 'location' => 'http://gutenberg-biographics.ub.uni-mainz.de/gnd/personen/beacon/file.txt', 'target' => 'http://gutenberg-biographics.ub.uni-mainz.de/gnd/{ID}', 'type' => 'default'),
        'mpo' => array('label' => 'Marburger Professorenkatalog online', 'location' => 'https://www.lagis-hessen.de/pkat_mr.txt', 'target' =>  'https://www.uni-marburg.de/uniarchiv/pkat/gnd?id={ID}', 'type' => 'default'),
        'cprm' => array('label' => 'Matrikel der Universität Rostock', 'location' => 'http://matrikel.uni-rostock.de/matrikel_rostock_pnd_beacon.txt', 'target' => 'http://matrikel.uni-rostock.de/gnd/{ID}', 'type' => 'default'),
        'hvuz' => array('label' => 'Historische Vorlesungsverzeichnisse der Universität Zürich 1833–1900', 'location' => 'http://histvv.uzh.ch/pnd.txt', 'target' => 'http://histvv.uzh.ch/pnd/{ID}', 'type' => 'default'),
        'mabk' => array('label' => 'Matrikel der Akademie der Bildenden Künste München', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/adbk.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/adbk.php?pnd={ID}', 'type' => 'default'),	
    	'gesa' => array('label' => 'Gesamtkatalog deutschsprachiger Leichenpredigten', 'location' => 'http://www.online.uni-marburg.de/fpmr/pnd.txt', 'target' => 'https://www.online.uni-marburg.de/fpmr/php/gs/xs2.php?f1=pnd&s1={ID}', 'type' => 'default'),
    	'thulp' => array('label' => 'Digitale Edition autobiographischer Texte aus Thüringer Leichenpredigten', 'location' => 'https://www.online.uni-marburg.de/fpmr/autothuer.txt', 'target' => 'http://www.personalschriften.de/leichenpredigten/digitale-editionen/autothuer/personenregister.html#{ID}', 'type' => 'default'),
        'fruchtbringer' => array('label' => 'Fruchtbringende Gesellschaft', 'location' => 'http://www.die-fruchtbringende-gesellschaft.de/files/fg_beacon.txt', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/fbges.php?pnd={ID}', 'type' => 'default'),		
        'apw' => array('label' => 'Acta Pacis Westfalicae', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/apw-digital.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/apw-digital.php?pnd={ID}', 'type' => 'default'),
        'coco' => array('label' => 'Controversia et Confessio', 'location' => 'http://www.controversia-et-confessio.de/gnd/personen/beacon/file.txt', 'target' => 'http://www.controversia-et-confessio.de/gnd/{ID}', 'type' => 'default'),
        'mmlo' => array('label' => 'Biographisches Lexikon der Münzmeister, Wardeine, Stempelschneider und Medailleure (MMLO)', 'location' => 'http://mmlo.de/beacon', 'target' => 'http://mmlo.de/Q/GND={ID}'),
        'sandrart' => array('label' => 'Sandrart.net', 'location' => 'http://ta.sandrart.net/services/pnd-beacon/', 'target' => 'http://ta.sandrart.net/services/pnd-beacon/?pnd={ID}', 'type' => 'default'),
        'kall' => array('label' => 'Kalliope Verbundkatalog', 'location' => 'http://kalliope.staatsbibliothek-berlin.de/beacon/beacon.txt', 'target' => 'http://kalliope.staatsbibliothek-berlin.de/de/eac?eac.id={ID}', 'type' => 'default'),	
        'zdn' => array('label' => 'Zentrale Datenbank Nachlässe', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/zdn.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/zdn.php?pnd={ID}', 'type' => 'default'), 
        'sf2' => array('label' => 'Schatullrechnungen Friedrichs des Großen', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/friedrich_schatullrechnungen.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/friedrich_schatullrechnungen.php?pnd={ID}', 'type' => 'default'),
        'rarp' => array('label' => 'Registres Académie Royale de Prusse 1746 à 1786', 'location' => 'https://beacon.findbuch.de/downloads/regacad/regacad-pndbeacon.txt', 'target' => 'http://beacon.findbuch.de/pnd-resolver/regacad/{ID}', 'type' => 'default'),
        'berlin1800' => array('label' => 'Briefe und Texte aus dem intellektuellen Berlin um 1800', 'location' => 'https://www.berliner-intellektuelle.eu/beacon-pnd.txt', 'target' => 'http://www.berliner-intellektuelle.eu/pnd.pl?id={ID}', 'type' => 'default'),
        'dta' => array('label' => 'Deutsches Textarchiv', 'location' => 'http://www.deutschestextarchiv.de/api/beacon', 'target' => 'http://www.deutschestextarchiv.de/api/pnd/{ID}', 'type' => 'default'),
        'cors' => array('label' => 'correspSearch – Verzeichnisse von Briefeditionen', 'location' => 'https://correspsearch.net/api/v1.1/gnd-beacon.xql?correspondent=all', 'target' => 'http://correspsearch.bbaw.de/search.xql?correspondent=http://d-nb.info/gnd/{ID}&l=de', 'type' => 'default'),
        'bahnsen' => array('label' => 'Briefwechsel Benedikt Bahnsen', 'location' => 'http://diglib.hab.de/edoc/ed000233/beacon_bahnsen.txt', 'target' => 'http://diglib.hab.de/content.php?dir=edoc/ed000233&distype=optional&metsID=edoc_ed000233_personenregister_transcript&xml=register%2Fregister-person.xml&xsl=http://diglib.hab.de/edoc/ed000233/tei-pers.xsl#{ID}', 'type' => 'default'),
        'humbdig' => array('label' => 'edition humboldt digital', 'location' => 'https://edition-humboldt.de/api/v1/beacon.xql', 'target' => 'https://edition-humboldt.de/register/personen/detail.xql?normid=http://d-nb.info/gnd/{ID}', 'type' => 'default'),
        'cfgb' => array('label' => 'Carl Friedrich Gauss Briefwechsel', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/gauss.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/gauss.php?pnd={ID}'),
        'gauss' => array('label' => 'Briefwechsel von Carl Friedrich Gauß - Korrespondenten', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/gauss.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/gauss.php?pnd={ID}', 'type' => 'default'),
        'muenz' => array('label' => 'Katalog des Münzkabinetts Staatliche Museen zu Berlin', 'location' => 'http://ww2.smb.museum/ikmk/beacon_gnd.php', 'target' => 'http://ww2.smb.museum/ikmk/filter_text.php?filter%5B0%5D%5Bfield%5D=gnd&filter%5B0%5D%5Btext%5D={ID}', 'type' => 'default'),	
        'dpi' => array('label' => 'Digitaler Portraitindex', 'location' => 'http://www.portraitindex.de/pnd_beacon.txt', 'target' => 'http://www.portraitindex.de/dokumente/pnd/{ID}', 'type' => 'default'),
        'vkk' => array('label' => 'Virtuelles Kupferstichkabinett', 'location' => 'http://www.virtuelles-kupferstichkabinett.de/beacon.php', 'target' => 'http://www.virtuelles-kupferstichkabinett.de/index.php?reset=1&subPage=search&selTab=2&habFilter=1&haumFilter=1&selFilter=0&sKey1=pzusatz&sWord1={ID}', 'type' => 'default'),
        'gpa' => array('label' => 'Graphikportal – Akteure', 'location' => 'https://www.graphikportal.org/gnd_beacon_event.txt', 'target' => 'https://www.graphikportal.org/gnd-beacon/{ID}', 'type' => 'default'),
        'gpd' => array('label' => 'Graphikportal – Dargestellte', 'location' => 'https://www.graphikportal.org/gnd_beacon_subject.txt', 'target' => 'https://www.graphikportal.org/gnd-beacon/{ID}', 'type' => 'default'),
        'khmw' => array('label' => 'Kunsthistorisches Museum Wien', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/khm.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/khm.php?pnd={ID}', 'type' => 'default'),
        'albw' => array('label' => 'Albertina Wien', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/albertina.php?beacon', 'target' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/albertina.php?pnd={ID}', 'type' => 'default'),
        'archinf' => array('label' => 'archINFORM Architecture Database', 'location' => 'https://www.archinform.net/service/beacon.txt', 'target' => 'https://www.archinform.net/gnd/{ID}', 'type' => 'default'),
        'imslp' => array('label' => 'International Music Score Library Project', 'location' => 'http://beacon.findbuch.de/downloads/patchwork/pw_imslp-gndbeacon.txt', 'target' => 'http://beacon.findbuch.de/gnd-resolver/pw_imslp/{ID}', 'type' => 'default'),
        'cmvw' => array('label' => 'Carl Maria von Weber Gesamtausgabe (WeGA)', 'location' => 'http://weber-gesamtausgabe.de/pnd_beacon.txt', 'target' => 'http://www.weber-gesamtausgabe.de/de/pnd/{ID}', 'type' => 'default'),
        'bach' => array('label' => 'Bach Digital', 'location' => 'https://www.bach-digital.de/beacon.txt', 'target' => 'https://www.bach-digital.de/gnd/{ID}', 'type' => 'default'),
        'vd16' => array('label' => 'Verzeichnis der Drucke 16. Jahrhunderts (VD 16)', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/vd16.txt', 'target' => 'http://www.gateway-bayern.de/opensearch?rfr_id=LinkedOpenData%3ABeacon&res_id=VD16&rft_id=info%3Apnd%2F{ID}', 'type' => 'default'),
        'ecod' => array('label' => 'e-codices Virtuelle Handschriftenbibliothek der Schweiz', 'location' => 'http://www.historische-kommission-muenchen-editionen.de/beacond/ecodices.php?beacon', 'target' => 'http://www.e-codices.unifr.ch/de/search/all?sSearchField=person_names&sQueryString=pnd_{ID}', 'type' => 'default'),
        'jdg' => array('label' => 'Jahresberichte für deutsche Geschichte', 'location' => 'http://jdgdb.bbaw.de/jdg-gndbeacon.txt', 'target' => 'http://jdgdb.bbaw.de/cgi-bin/jdg?t_idn_erg=x&idn=GND:{ID}', 'type' => 'default'),
	'aqhab' => array('label' => 'Alchemische Bestände der HAB', 'location' => 'http://alchemie.hab.de/beacon.txt', 'target' => 'http://alchemie.hab.de/personen?gnd={ID}', 'type' => 'default')
    );

// '' => array('label' => '', 'location' => '', 'target' => '', 'type' => 'default'),

    function __construct($update = true) {
        $dateArchive = intval(file_get_contents($this->folder.'/changeDate'));
        $this->lastUpdate = date('Y-m-d H:i:s', $dateArchive);      
        if ($update == false) {
            return;
        }
        elseif ($this->validate() == false) {
            if (!is_dir($this->folder)) {
                mkdir($this->folder, 0777);
            }
            $this->update();            
        }
        else {
            if ((date('U') - $dateArchive) > $this->update_int) {
                $this->update();
            }
        }
        
    }

  //   function __construct() {
		// if (!is_dir($this->folder)) {
		// 	mkdir($this->folder);
  //           chmod($this->folder, $this->filePermission);
  //           $this->update();
		// }
  //       $this->valid = $this->validate();
  //   }

    public function secondsSinceLastUpdate() {
        return(date('U') - $this->lastUpdate);
    }

    public function update() {
        ini_set('user_agent', $this->user);
        foreach ($this->beacon_sources as $key => $source) {
            if (!copy($source['location'], $this->folder.'/'.$key)) {
                echo 'Kopieren von '.$source['location'].' nach '.$this->folder.'/'.$key.' schlug fehl.<br />';
            }
            else {
                chmod($this->folder.'/'.$key, $this->filePermission);
            }
        }
        $date = date('U');
        file_put_contents($this->folder.'/changeDate', $date);
        chmod($this->folder.'/changeDate', $this->filePermission);
        $this->lastUpdate = $date;
    }

    public function getLinks(gnd $gnd, $target = '') {
        if ($gnd->valid != true) {
            return(null);
        }
        $result = array();
        $matches = $this->getMatches($gnd->id);
        foreach ($matches as $key) {
			$result[] = $this->makeLink($key, $gnd->id, $target);
        }
        return($result);
    }

    public function getSelectedLinks(gnd $gnd, $hab = true, $target = '') {
        if ($gnd->valid != true) {
            return(null);
        }
        $result = array();
        $matches = $this->getMatches($gnd->id);
        foreach ($matches as $key) {
            if (in_array($key, $this->sourcesHAB) and $hab == false) {
                continue;
            }
            elseif (!in_array($key, $this->sourcesHAB) and $hab == true) {
                continue;
            }            
			$result[] = $this->makeLink($key, $gnd->id, $target);
        }
        return($result);
    }

    public function getLinksMulti($gndArray, $target = '') {
        $result = array();
        $matches = $this->getMatchesMulti($gndArray);
        foreach ($matches as $gnd => $keys) {
            $resultPart = array();
            foreach ($keys as $key) {
                $resultPart[] = $this->makeLink($key, $gnd, $target);
            }
            $result[$gnd] = $resultPart;
        }
        return($result);
    }

    public function showSources($showFailed = false, $sep = '; ') {
        $success = array();
        $failed = array();
        foreach ($this->beacon_sources as $key => $source) {
            if (file_exists($this->folder.'/'.$key)) {
                $success[] = $source['label'];
            }
            else {
                $failed[] = $source['label'];
            }
        }
        if ($showFailed == true) {
            return(implode($sep, $failed));
        }
        return(implode($sep, $success));
    }

    private function getMatches($gnd) {
        $result = array();
        foreach ($this->beacon_sources as $key => $source) {
            $content = file_get_contents($this->folder.'/'.$key);
            if (strpos($content, $gnd) != null) {
                $result[] = $key;
            }
        }
        return($result);
    }

    public function getMatchesMulti($gndArray) {
        $result = array();
        foreach($this->beacon_sources as $key => $source) {
            $content = file_get_contents($this->folder.'/'.$key);
            foreach ($gndArray as $gnd) {
                if (strpos($content, $gnd) != null) {
                $result[$gnd][] = $key;
                }
            }
        }
        return($result);
    }

    private function makeLink($key, $gnd, $target) {
        if (in_array($target, array('_blank', '_self', '_parent', '_top'))) {
            $target = ' target="'.$target.'"';
        }
        if ($this->beacon_sources[$key]['type'] == 'specified') {
            $url = $this->extractURL($gnd, $key);
        }
        else {
            $url = strtr($this->beacon_sources[$key]['target'], array('{ID}' => $gnd));
        }
        $link  = '<a href="'.$url.'"'.$target.'>'.$this->beacon_sources[$key]['label'].'</a>';
        return($link);
    }

    private function extractURL($gnd, $key) {
        $string = file_get_contents('beaconFiles/'.$key);
        preg_match('~'.$gnd.'\|.+(http.+)~', $string, $hits);
        if (!empty($hits[1])) {
            if (substr($hits[1], 0, 4) == 'http') {
                return($hits[1]);
            }
        }
        return('');
    }

    private function validate() {
        if (!is_dir($this->folder)) {
			$this->errorMessages[] = 'Ordner existiert nicht';
            return(false);
        }
        if (!file_exists($this->folder.'/changeDate')) {
			$this->errorMessages[] = 'changeDate existiert nicht';
            return(false);
        }
        $this->lastUpdate = intval(file_get_contents($this->folder.'/changeDate'));
        if ($this->lastUpdate < 1400000000 or $this->lastUpdate > date('U')) {
			$this->errorMessages[] = 'changeDate ist nicht plausibel';
            return(false);
        }
        foreach ($this->beacon_sources as $key => $source) {
            if (!file_exists($this->folder.'/'.$key)) {
				$this->errorMessages[] = 'Beacon Datei für '.$this->beacon_sources[$key]['label'].' nicht lokal vorhanden';
                return(false);
            }
        }
        return(true);
    }

}

?>
