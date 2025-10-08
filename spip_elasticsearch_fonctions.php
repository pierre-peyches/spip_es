<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/elasticsearch'); // charge elastic_search()

// Petit log pour confirmer le chargement du fichier
spip_log('spip_elasticsearch_fonctions.php chargé', 'elasticsearch');

// Filtre utilisable en squelette : [(#ENV{query}|elastic_search)]
function filtre_elastic_search_dist($query) {
    $q = trim((string)$query);
    if ($q === '') return [];
    return elastic_search($q);
}
