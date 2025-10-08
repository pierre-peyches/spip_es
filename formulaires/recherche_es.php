<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

/** CHARGER : valeurs par défaut du formulaire */
function formulaires_recherche_es_charger_dist() {
  
    $valeurs = [];
    $valeurs['query'] = _request('query');
    return $valeurs;
}

/** VERIFIER : contrôles basiques */
function formulaires_recherche_es_verifier_dist() {
    $erreurs = [];
    $q = trim((string)_request('query'));
    if ($q === '') {
    
    }
    return $erreurs;
}

/** TRAITER : exécute la recherche et renvoie les résultats au fichier de template  */
function formulaires_recherche_es_traiter_dist() {
    include_spip('inc/elasticsearch');
    $query = (string)_request('query');
    $hits  = elastic_search($query);
    if (!is_array($hits)) {
        $hits = [];
    }

    spip_log("FORM traiter: query='$query', hits=".count($hits), 'elasticsearch');

    return [
        'editable'   => true,             // rester sur la même page
        'message_ok' => 'Recherche effectuée',
        'query'      => $query,           // pour ré-afficher la saisie
        'hits'       => $hits             // *** résultats à afficher  ***
    ];
}
