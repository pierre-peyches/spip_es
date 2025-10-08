<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
On échappe les carractères spéciaux pour faciliter la requete ES */
function es_escape_query_string($s) {
    $s = (string)$s;//On force le typage de l'argument de la recherche au format texte
    $specials = ['+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '\\', '/'];
    usort($specials, function ($a, $b) { return strlen($b) <=> strlen($a); });
    foreach ($specials as $ch) {
        $s = str_replace($ch, '\\' . $ch, $s);
    }
    return $s;
}


function elastic_search($query) {
 
    $es_url  = 'https://localhost:9200';   // URL de ES
    $index   = 'livres';    //Index que l'on veut interroger                
    $es_user = 'elastic'; //Login de connexion à ES
    $es_pass = 'elastic';                  //Mot de passe de connexion à ES.
    $ca_path = "C:\\elasticsearch-9.1.3\\config\\certs\\http_ca.crt";
    $size_search = 20;

    // --- Construction de la query ---
    $term = es_escape_query_string((string)$query);
    $q    = "(titre:$term OR auteur:$term OR resume:$term)";//On spécifie ici les champs sur lesquels on veut interroger ES

    $url = rtrim($es_url, '/') . '/' . rawurlencode($index)
         . '/_search?q=' . rawurlencode($q) . '&size='. $size_search;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET        => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERPWD        => $es_user . ':' . $es_pass,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
  
    if ($ca_path && is_file($ca_path)) {
        curl_setopt($ch, CURLOPT_CAINFO, $ca_path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $res   = curl_exec($ch);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    $http  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
/*On gère les erreurs */
    if ($errno || $res === false || $http >= 400) {
        spip_log("ES erreur: cURL=$errno ($err), HTTP=$http, URL=$url", 'elasticsearch.' . _LOG_ERREUR);
        if ($res !== false) {
            spip_log("ES body: " . substr($res, 0, 1000), 'elasticsearch.' . _LOG_ERREUR);
        }
        return [];
    }

    $data = json_decode($res, true);
    $hits = $data['hits']['hits'] ?? [];
    spip_log("ES OK: query='".(string)$query."', http=$http, hits=".count($hits), 'elasticsearch');
    return $hits;
}

