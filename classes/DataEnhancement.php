<?php

class DataEnhancement {

    public static function setHistSubjectHAB($item, $overwrite = true) {
    
        $resolver = 'http://localhost/signaturen/public/resolve.php?shelfmark={SIG}&format=json';

        if ($overwrite != true and $item->histSubject) {
            return($item);
        }

        $hab = ['hab', 'herzog august bibliothek', 'herzog august bibliothek wolfenbüttel', 'wolfenbüttel, herzog august bibliothek', 'wolfenbüttel: herzog august bibliothek', 'wolfenbüttel, hab', 'hab wolfenbüttel'];

        if (!in_array(strtolower($item->originalItem['institutionOriginal']), $hab)) {
            return($item);
        }

        $sig = trim($item->originalItem['shelfmarkOriginal']);
        if (empty($sig)) {
            return($item);
        }       

        //Holen der Signaturinformationen aus dem Cache statt über HTTP
        //$cache = new Cache_Signaturen_HAB();
        //$resp = $cache->getFromWeb($sig);

        $url = strtr($resolver, ['{SIG}' => urlencode($sig)]);
        $resp = file_get_contents($url);

        if ($resp == null) {
            return($item);
        }
        //BOM vom Anfang entfernen
        $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
        /*
        //Alternative Lösung für das BOM-Problem:
        while (substr($resp, 0, 1) != '{') {
            $resp = substr($resp, 1);
        }   
        */
        $json = json_decode($resp);
        if ($json == null) {
            echo json_last_error()."\n";
            return($item);
        }
        if (empty($json->label[0])) {
            return($item);
        }
        $item->histSubject = $json->notation[0].': '.$json->label[0];
        echo $item->histSubject;
        return($item);

    }

}

?>
